<?php

namespace Tests\Feature;

use App\Models\RoleUser;
use App\Models\User;
use App\Models\ReferEarn;
use App\Models\Game;
use App\Models\Purchase;
use App\Models\Transactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReferEarnFlowTest extends TestCase
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
            'game_credit' => 0,
            'comission_percent' => 0,
        ]);
    }

    /** @test */
    public function test_refer_earn_created_on_registration()
    {
        // Criar vendedor que vai indicar
        $seller = User::create([
            'name' => 'Vendedor Indicador',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11988888888',
            'game_credit' => 0,
            'comission_percent' => 0.1,
        ]);

        // Registrar apostador com indicação
        $this->post('/auth/register-basic', [
            'name' => 'Apostador Indicado',
            'email' => 'apostador@test.com',
            'document' => '12345678901',
            'phone' => '11977777777',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'refered_by_id' => $seller->id,
        ]);

        // Verificar ReferEarn criado
        $referEarn = ReferEarn::where('refer_user_id', $seller->id)->first();
        $this->assertNotNull($referEarn);
        $this->assertEquals(10, $referEarn->amount);
        $this->assertFalse($referEarn->invited_user_bought);
        $this->assertFalse($referEarn->earn_paid);
    }

    /** @test */
    public function test_refer_earn_marked_as_bought_on_first_purchase()
    {
        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11988888888',
            'game_credit' => 1000,
            'comission_percent' => 0.1,
        ]);

        // Criar apostador indicado
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11977777777',
            'game_credit' => 100,
            'seller_id' => $seller->id,
            'invited_by_id' => $seller->id,
        ]);

        // Criar ReferEarn
        $referEarn = ReferEarn::create([
            'refer_user_id' => $seller->id,
            'invited_user_id' => $gambler->id,
            'invited_user_bought' => false,
            'earn_paid' => false,
            'amount' => 10,
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

        // Criar compra
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11977777777',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 10,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
        ]);

        // Pagar compra como apostador
        $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");

        // Verificar que ReferEarn foi marcado como comprado
        $referEarn->refresh();
        $this->assertTrue($referEarn->invited_user_bought);
    }

    /** @test */
    public function test_refer_earn_payment_creates_transaction()
    {
        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11988888888',
            'game_credit' => 0,
            'comission_percent' => 0.1,
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11977777777',
            'game_credit' => 0,
            'seller_id' => $seller->id,
            'invited_by_id' => $seller->id,
        ]);

        // Criar ReferEarn já marcado como comprado
        $referEarn = ReferEarn::create([
            'refer_user_id' => $seller->id,
            'invited_user_id' => $gambler->id,
            'invited_user_bought' => true,
            'earn_paid' => false,
            'amount' => 10,
        ]);

        $initialCredit = $seller->game_credit;

        // Pagar bônus de indicação
        $this->actingAs($seller)->get("/indique_ganhe/pagar/{$referEarn->id}");

        // Verificar que o crédito foi adicionado
        $seller->refresh();
        $this->assertEquals($initialCredit + 10, $seller->game_credit);

        // Verificar que a transação foi criada
        $transaction = Transactions::where('type', 'REFER_EARN')
            ->where('user_id', $seller->id)
            ->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(10, $transaction->amount);

        // Verificar que ReferEarn foi marcado como pago
        $referEarn->refresh();
        $this->assertTrue($referEarn->earn_paid);
    }

    /** @test */
    public function test_refer_earn_only_on_first_purchase()
    {
        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11988888888',
            'game_credit' => 1000,
            'comission_percent' => 0.1,
        ]);

        // Criar apostador indicado
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11977777777',
            'game_credit' => 200,
            'seller_id' => $seller->id,
            'invited_by_id' => $seller->id,
        ]);

        // Criar ReferEarn
        $referEarn = ReferEarn::create([
            'refer_user_id' => $seller->id,
            'invited_user_id' => $gambler->id,
            'invited_user_bought' => false,
            'earn_paid' => false,
            'amount' => 10,
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

        // Primeira compra
        $purchase1 = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11977777777',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 10,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
        ]);

        $this->actingAs($gambler)->get("/purchase/pay/{$purchase1->id}");

        // Verificar que foi marcado como comprado
        $referEarn->refresh();
        $this->assertTrue($referEarn->invited_user_bought);

        // Segunda compra - não deve criar novo ReferEarn
        $purchase2 = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11977777777',
            'numbers' => '06 07 08 09 10',
            'quantity' => 1,
            'price' => 10,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
        ]);

        $referEarnCountBefore = ReferEarn::where('refer_user_id', $seller->id)
            ->where('invited_user_id', $gambler->id)
            ->count();

        $this->actingAs($gambler)->get("/purchase/pay/{$purchase2->id}");

        $referEarnCountAfter = ReferEarn::where('refer_user_id', $seller->id)
            ->where('invited_user_id', $gambler->id)
            ->count();

        // Não deve criar novo ReferEarn
        $this->assertEquals($referEarnCountBefore, $referEarnCountAfter);
    }
}
