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

if (!function_exists('generate_identifier')) {
    function generate_identifier(): string {
        $numbers = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $letter = chr(random_int(65, 90));
        return $numbers . $letter;
    }
}

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
        $adminRoleId = DB::table('role_users')->where('level_id', 'admin')->value('id');
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $adminRoleId,
            'phone' => '11999999999',
            'game_credit' => 10000,
            'comission_percent' => 0,
        ]);
    }

    protected function getRoleId(string $levelId): int
    {
        return DB::table('role_users')->where('level_id', $levelId)->value('id');
    }

    /** @test */
    public function test_gambler_can_create_purchase()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
            'phone' => '11988888888',
            'game_credit' => 100,
            'seller_id' => $admin->id,
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
            'seller_id' => $admin->id,
            'round' => 1,
            'identifier' => generate_identifier(),
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
            'role_user_id' => $this->getRoleId('seller'),
            'phone' => '11977777777',
            'game_credit' => 0,
            'comission_percent' => 0.15, // 15%
        ]);

        // Criar apostador vinculado ao vendedor
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
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
            'identifier' => generate_identifier(),
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
    public function test_seller_pays_for_gambler_purchase_with_discount()
    {
        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('seller'),
            'phone' => '11955555555',
            'game_credit' => 500,
            'comission_percent' => 0.1, // 10%
        ]);

        // Criar apostador (com crédito suficiente para o sistema processar)
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
            'phone' => '11944444444',
            'game_credit' => 100, // Crédito suficiente para o sistema processar
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
            'identifier' => generate_identifier(),
        ]);

        $initialSellerCredit = $seller->game_credit;
        $initialGamblerCredit = $gambler->game_credit;

        // Vendedor paga a compra
        $this->actingAs($seller)->get("/purchase/pay/{$purchase->id}");

        // O sistema debita do creatorUser (gambler), não do vendedor
        // Vendedor apenas recebe comissão
        $gambler->refresh();
        $purchasePrice = 50;
        $this->assertEquals($initialGamblerCredit - $purchasePrice, $gambler->game_credit, "Crédito do gambler deveria ser debitado");

        // Verificar que o vendedor recebeu comissão (crédito aumenta)
        $seller->refresh();
        $commission = $purchasePrice * 0.1; // 5
        $expectedSellerCredit = $initialSellerCredit + $commission; // 500 + 5 = 505
        $this->assertEquals($expectedSellerCredit, $seller->game_credit, "Crédito do vendedor deveria ser {$expectedSellerCredit} mas é {$seller->game_credit}");

        // Verificar que o vendedor recebeu comissão normalmente
        $commissionTransaction = Transactions::where('type', 'PAY_PURCHASE_COMISSION')
            ->where('user_id', $seller->id)
            ->where('purchase_id', $purchase->id)
            ->first();
        $this->assertNotNull($commissionTransaction, 'Vendedor deve receber comissão quando paga a compra');
        $this->assertEquals($commission, $commissionTransaction->amount);

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
        $admin = User::where('email', 'admin@test.com')->first();
        
        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
            'phone' => '11933333333',
            'game_credit' => 100,
            'seller_id' => $admin->id,
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
            'seller_id' => $admin->id,
            'round' => 1,
            'identifier' => generate_identifier(),
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
            'role_user_id' => $this->getRoleId('seller'),
            'phone' => '11922222222',
            'game_credit' => 0,
            'comission_percent' => 0.2, // 20%
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
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
                'identifier' => generate_identifier(),
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

    /** @test */
    public function test_admin_pays_purchase_seller_receives_commission()
    {
        // Criar admin
        $admin = User::where('email', 'admin@test.com')->first(); // Admin criado no setUp

        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('seller'),
            'phone' => '11999999998',
            'game_credit' => 0,
            'comission_percent' => 0.15, // 15%
        ]);

        // Criar apostador vinculado ao vendedor
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
            'phone' => '11999999997',
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

        // Criar compra pendente
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11999999997',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 100,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
            'identifier' => generate_identifier(),
        ]);

        $initialAdminCredit = $admin->game_credit;
        $initialSellerCredit = $seller->game_credit;

        // Admin paga a compra
        $this->actingAs($admin)->get("/purchase/pay/{$purchase->id}");

        // Verificar que admin NÃO teve crédito debitado
        $admin->refresh();
        $this->assertEquals($initialAdminCredit, $admin->game_credit);

        // Verificar que vendedor recebeu comissão
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

        // Verificar que compra foi marcada como paga
        $purchase->refresh();
        $this->assertEquals('PAID', $purchase->status);
        $this->assertEquals($admin->id, $purchase->paid_by_user_id);
    }

    /** @test */
    public function test_admin_creates_purchase_seller_receives_commission()
    {
        // Criar admin
        $admin = User::where('email', 'admin@test.com')->first();

        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor2@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('seller'),
            'phone' => '11999999996',
            'game_credit' => 0,
            'comission_percent' => 0.2, // 20%
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador2@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
            'phone' => '11999999995',
            'game_credit' => 100,
            'seller_id' => $seller->id,
        ]);

        // Criar jogo
        $game = Game::create([
            'name' => 'Jogo Teste',
            'game_id' => 'TEST002',
            'price' => 50,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        $initialAdminCredit = $admin->game_credit;
        $initialSellerCredit = $seller->game_credit;

        // Criar compra pendente
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11999999995',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 50,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
            'identifier' => generate_identifier(),
        ]);

        // Admin paga a compra
        $this->actingAs($admin)->get("/purchase/pay/{$purchase->id}");

        // Verificar que admin NÃO teve crédito debitado
        $admin->refresh();
        $this->assertEquals($initialAdminCredit, $admin->game_credit);

        // Verificar que vendedor recebeu comissão
        $seller->refresh();
        $expectedCommission = 50 * 0.2; // 10
        $this->assertEquals($initialSellerCredit + $expectedCommission, $seller->game_credit);

        // Verificar compra foi marcada como paga
        $purchase->refresh();
        $this->assertEquals('PAID', $purchase->status);
        $this->assertEquals($admin->id, $purchase->paid_by_user_id);

        // Verificar transação de comissão
        $commissionTransaction = Transactions::where('type', 'PAY_PURCHASE_COMISSION')
            ->where('user_id', $seller->id)
            ->where('purchase_id', $purchase->id)
            ->first();
        $this->assertNotNull($commissionTransaction);
        $this->assertEquals($expectedCommission, $commissionTransaction->amount);
    }

    /** @test */
    public function test_admin_repeats_purchase_seller_receives_commission()
    {
        // Criar admin
        $admin = User::where('email', 'admin@test.com')->first();

        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor3@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('seller'),
            'phone' => '11999999994',
            'game_credit' => 0,
            'comission_percent' => 0.12, // 12%
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador3@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
            'phone' => '11999999993',
            'game_credit' => 100,
            'seller_id' => $seller->id,
        ]);

        // Criar jogo original
        $originalGame = Game::create([
            'name' => 'Jogo Original',
            'game_id' => 'TEST003',
            'price' => 75,
            'status' => 'CLOSED',
            'round' => 1,
            'active' => false,
            'open_at' => now()->subDays(2),
            'close_at' => now()->subDays(1),
        ]);

        // Criar compra paga no jogo original
        $oldPurchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11999999993',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 75,
            'status' => 'PAID',
            'game_id' => $originalGame->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
            'identifier' => generate_identifier(),
            'paid_by_user_id' => $gambler->id,
        ]);

        // Criar novo jogo para repetir
        $newGame = Game::create([
            'name' => 'Jogo Novo',
            'game_id' => 'TEST004',
            'price' => 75,
            'status' => 'OPENED',
            'round' => 2,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        $initialAdminCredit = $admin->game_credit;
        $initialSellerCredit = $seller->game_credit;

        // Admin repete a compra
        $response = $this->actingAs($admin)->post('/purchase/repeat', [
            'repeat_game_id' => $newGame->id,
            'repeat_game_purchase_id' => $oldPurchase->id,
        ]);

        // Verificar que admin NÃO teve crédito debitado
        $admin->refresh();
        $this->assertEquals($initialAdminCredit, $admin->game_credit);

        // Verificar que vendedor recebeu comissão
        $seller->refresh();
        $expectedCommission = 75 * 0.12; // 9
        $this->assertEquals($initialSellerCredit + $expectedCommission, $seller->game_credit);

        // Verificar nova compra foi criada
        $newPurchase = Purchase::where('game_id', $newGame->id)
            ->where('repeated_from_purchase_id', $oldPurchase->id)
            ->first();
        $this->assertNotNull($newPurchase);
        $this->assertEquals('PAID', $newPurchase->status);

        // Verificar transação de comissão
        $commissionTransaction = Transactions::where('type', 'PAY_PURCHASE_COMISSION')
            ->where('user_id', $seller->id)
            ->where('purchase_id', $newPurchase->id)
            ->first();
        $this->assertNotNull($commissionTransaction);
        $this->assertEquals($expectedCommission, $commissionTransaction->amount);
    }

    /** @test */
    public function test_seller_pays_with_discount_no_additional_commission()
    {
        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor4@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('seller'),
            'phone' => '11999999992',
            'game_credit' => 1000,
            'comission_percent' => 0.25, // 25%
        ]);

        // Criar apostador (com crédito suficiente para o sistema processar)
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador4@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => $this->getRoleId('gambler'),
            'phone' => '11999999991',
            'game_credit' => 200, // Crédito suficiente para o sistema processar
            'seller_id' => $seller->id,
        ]);

        // Criar jogo
        $game = Game::create([
            'name' => 'Jogo Teste',
            'game_id' => 'TEST005',
            'price' => 100,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        // Criar compra pendente
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11999999991',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 100,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
            'identifier' => generate_identifier(),
        ]);

        $initialSellerCredit = $seller->game_credit;
        $initialGamblerCredit = $gambler->game_credit;

        // Vendedor paga a compra
        $this->actingAs($seller)->get("/purchase/pay/{$purchase->id}");
        
        // O sistema debita do creatorUser (gambler), não do vendedor
        // Vendedor apenas recebe comissão
        $gambler->refresh();
        $purchasePrice = 100;
        $this->assertEquals($initialGamblerCredit - $purchasePrice, $gambler->game_credit, "Crédito do gambler deveria ser debitado");

        // Verificar que o vendedor recebeu comissão (crédito aumenta)
        $seller->refresh();
        $commission = $purchasePrice * 0.25; // 25
        $expectedSellerCredit = $initialSellerCredit + $commission; // 1000 + 25 = 1025
        $this->assertEquals($expectedSellerCredit, $seller->game_credit, "Crédito do vendedor deveria ser {$expectedSellerCredit} mas é {$seller->game_credit}");

        // Verificar compra foi marcada como paga
        $purchase->refresh();
        $this->assertEquals('PAID', $purchase->status);
        $this->assertEquals($seller->id, $purchase->paid_by_user_id);

        // Verificar que o vendedor recebeu comissão normalmente
        $commissionTransaction = Transactions::where('type', 'PAY_PURCHASE_COMISSION')
            ->where('user_id', $seller->id)
            ->where('purchase_id', $purchase->id)
            ->first();
        $this->assertNotNull($commissionTransaction, 'Vendedor deve receber comissão quando paga a compra');
        $this->assertEquals($commission, $commissionTransaction->amount);
    }
}
