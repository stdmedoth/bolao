<?php

namespace Tests\Feature;

use App\Models\RoleUser;
use App\Models\User;
use App\Models\Game;
use App\Models\Purchase;
use App\Models\Transactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseAndCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar roles necessárias usando DB::table para evitar mass assignment
        DB::table('role_users')->insert([
            ['name' => 'Administrador', 'level_id' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vendedor', 'level_id' => 'seller', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Apostador', 'level_id' => 'gambler', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Criar admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 1,
            'phone' => '11999999999',
            'game_credit' => 10000,
            'comission_percent' => 0,
        ]);
    }

    /** @test */
    public function test_gambler_can_create_purchase()
    {
        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11988888888',
            'game_credit' => 100,
            'seller_id' => 1,
        ]);

        // Criar jogo
        $game = Game::create([
            'name' => 'Jogo Teste',
            'game_id' => 'TEST001',
            'price' => 10,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        // Criar compra diretamente (como nos outros testes)
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11988888888',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 10,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => 1,
            'round' => 1,
        ]);

        // Pagar compra
        $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");

        $this->assertDatabaseHas('purchases', [
            'user_id' => $gambler->id,
            'game_id' => $game->id,
            'status' => 'PAID',
        ]);

        // Verificar que o crédito foi debitado
        $gambler->refresh();
        $this->assertEquals(90, $gambler->game_credit); // 100 - 10
    }

    /** @test */
    public function test_seller_commission_on_gambler_purchase()
    {
        // Criar vendedor com comissão
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11977777777',
            'game_credit' => 0,
            'comission_percent' => 0.15, // 15%
        ]);

        // Criar apostador vinculado ao vendedor
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11966666666',
            'game_credit' => 100,
            'seller_id' => $seller->id,
        ]);

        // Criar jogo
        $game = Game::create([
            'name' => 'Jogo Teste',
            'game_id' => 'TEST001',
            'price' => 100,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        // Criar compra
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11966666666',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 100,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
        ]);

        $initialSellerCredit = $seller->game_credit;

        // Pagar compra como apostador
        $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");

        // Verificar comissão do vendedor
        $seller->refresh();
        $expectedCommission = 100 * 0.15; // 15
        $this->assertEquals($initialSellerCredit + $expectedCommission, $seller->game_credit);

        // Verificar transação de comissão
        $commissionTransaction = Transactions::where('type', 'PAY_PURCHASE_COMISSION')
            ->where('user_id', $seller->id)
            ->where('purchase_id', $purchase->id)
            ->first();
        $this->assertNotNull($commissionTransaction);
        $this->assertEquals($expectedCommission, $commissionTransaction->amount);
    }

    /** @test */
    public function test_seller_pays_for_gambler_purchase()
    {
        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11955555555',
            'game_credit' => 500,
            'comission_percent' => 0.1,
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11944444444',
            'game_credit' => 0,
            'seller_id' => $seller->id,
        ]);

        // Criar jogo
        $game = Game::create([
            'name' => 'Jogo Teste',
            'game_id' => 'TEST001',
            'price' => 50,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        // Criar compra pendente
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11944444444',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 50,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
        ]);

        $initialSellerCredit = $seller->game_credit;

        // Vendedor paga a compra
        $this->actingAs($seller)->get("/purchase/pay/{$purchase->id}");

        // Verificar que o vendedor pagou
        $seller->refresh();
        $this->assertEquals($initialSellerCredit - 50, $seller->game_credit);

        // Verificar que o vendedor recebeu comissão
        $expectedCommission = 50 * $seller->comission_percent; // 5
        $this->assertEquals($initialSellerCredit - 50 + $expectedCommission, $seller->game_credit);

        // Verificar transação de pagamento
        $paymentTransaction = Transactions::where('type', 'PAY_PURCHASE')
            ->where('purchase_id', $purchase->id)
            ->first();
        $this->assertNotNull($paymentTransaction);
        $this->assertEquals(50, $paymentTransaction->amount);
    }

    /** @test */
    public function test_purchase_creates_transaction()
    {
        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11933333333',
            'game_credit' => 100,
            'seller_id' => 1,
        ]);

        // Criar jogo
        $game = Game::create([
            'name' => 'Jogo Teste',
            'game_id' => 'TEST001',
            'price' => 25,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        // Criar compra
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11933333333',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 25,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => 1,
            'round' => 1,
        ]);

        // Pagar compra
        $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");

        // Verificar transação de pagamento
        $transaction = Transactions::where('type', 'PAY_PURCHASE')
            ->where('purchase_id', $purchase->id)
            ->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(25, $transaction->amount);
        $this->assertEquals($gambler->id, $transaction->user_id);
        $this->assertEquals($game->id, $transaction->game_id);
    }

    /** @test */
    public function test_multiple_purchases_accumulate_commissions()
    {
        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11922222222',
            'game_credit' => 0,
            'comission_percent' => 0.2, // 20%
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11911111111',
            'game_credit' => 500,
            'seller_id' => $seller->id,
        ]);

        // Criar jogo
        $game = Game::create([
            'name' => 'Jogo Teste',
            'game_id' => 'TEST001',
            'price' => 50,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        // Criar 3 compras
        for ($i = 1; $i <= 3; $i++) {
            $purchase = Purchase::create([
                'gambler_name' => 'Apostador',
                'gambler_phone' => '11911111111',
                'numbers' => "0{$i} 0" . ($i+1) . " 0" . ($i+2) . " 0" . ($i+3) . " 0" . ($i+4),
                'quantity' => 1,
                'price' => 50,
                'status' => 'PENDING',
                'game_id' => $game->id,
                'user_id' => $gambler->id,
                'seller_id' => $seller->id,
                'round' => 1,
            ]);

            $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");
        }

        // Verificar total de comissões
        $totalCommissions = Transactions::where('type', 'PAY_PURCHASE_COMISSION')
            ->where('user_id', $seller->id)
            ->sum('amount');
        
        $expectedTotal = 50 * 0.2 * 3; // 30
        $this->assertEquals($expectedTotal, $totalCommissions);

        // Verificar crédito do vendedor
        $seller->refresh();
        $this->assertEquals($expectedTotal, $seller->game_credit);
    }
}
