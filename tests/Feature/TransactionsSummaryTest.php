<?php

namespace Tests\Feature;

use App\Models\RoleUser;
use App\Models\User;
use App\Models\Game;
use App\Models\Purchase;
use App\Models\Transactions;
use App\Models\ReferEarn;
use App\Models\UserAwards;
use App\Models\GameAward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionsSummaryTest extends TestCase
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
    public function test_summary_includes_all_transaction_types()
    {
        $admin = User::where('email', 'admin@test.com')->first();

        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11988888888',
            'game_credit' => 1000,
            'comission_percent' => 0.15,
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11977777777',
            'game_credit' => 500,
            'seller_id' => $seller->id,
            'invited_by_id' => $seller->id,
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

        // Criar compra e pagar
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11977777777',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 100,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
        ]);

        // Pagar compra
        $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");

        // Criar ReferEarn e pagar
        $referEarn = ReferEarn::create([
            'refer_user_id' => $seller->id,
            'invited_user_id' => $gambler->id,
            'invited_user_bought' => true,
            'earn_paid' => false,
            'amount' => 10,
        ]);

        $this->actingAs($seller)->get("/indique_ganhe/pagar/{$referEarn->id}");

        // Criar prêmio e pagar
        $gameAward = GameAward::create([
            'game_id' => $game->id,
            'name' => 'Prêmio Teste',
            'condition_type' => 'EXACT_POINT',
            'exact_point_value' => 5,
            'amount' => 200,
        ]);

        $userAward = UserAwards::create([
            'user_id' => $gambler->id,
            'game_id' => $game->id,
            'purchase_id' => $purchase->id,
            'game_award_id' => $gameAward->id,
            'points' => 5,
            'amount' => 200,
            'status' => 'PENDING',
            'round' => 1,
        ]);

        $this->actingAs($admin)->get("/meus_premios/pay/{$userAward->id}");

        // Verificar resumo do apostador
        $response = $this->actingAs($gambler)->get('/financeiro/resumo');
        $response->assertStatus(200);

        // Verificar que todas as transações estão no resumo
        $transactions = Transactions::where('user_id', $gambler->id)->get();
        
        $this->assertTrue($transactions->contains(function ($t) {
            return $t->type === 'PAY_PURCHASE';
        }));

        // Verificar resumo do vendedor
        $sellerTransactions = Transactions::where('user_id', $seller->id)->get();
        
        $this->assertTrue($sellerTransactions->contains(function ($t) {
            return $t->type === 'PAY_PURCHASE_COMISSION';
        }));

        $this->assertTrue($sellerTransactions->contains(function ($t) {
            return $t->type === 'REFER_EARN';
        }));
    }

    /** @test */
    public function test_summary_calculates_totals_correctly()
    {
        $admin = User::where('email', 'admin@test.com')->first();

        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11966666666',
            'game_credit' => 0,
            'comission_percent' => 0.1,
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11955555555',
            'game_credit' => 1000,
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
        $totalSpent = 0;
        for ($i = 1; $i <= 3; $i++) {
            $purchase = Purchase::create([
                'gambler_name' => 'Apostador',
                'gambler_phone' => '11955555555',
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
            $totalSpent += 50;
        }

        // Verificar total de saídas (compras)
        $outcomeTransactions = Transactions::where('user_id', $gambler->id)
            ->where('type', 'PAY_PURCHASE')
            ->sum('amount');
        
        $this->assertEquals($totalSpent, $outcomeTransactions);

        // Verificar total de comissões do vendedor
        $commissionTotal = Transactions::where('user_id', $seller->id)
            ->where('type', 'PAY_PURCHASE_COMISSION')
            ->sum('amount');
        
        $expectedCommission = 50 * 0.1 * 3; // 15
        $this->assertEquals($expectedCommission, $commissionTotal);
    }

    /** @test */
    public function test_summary_groups_purchases_by_game()
    {
        $admin = User::where('email', 'admin@test.com')->first();

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11944444444',
            'game_credit' => 500,
            'seller_id' => 1,
        ]);

        // Criar 2 jogos
        $game1 = Game::create([
            'name' => 'Jogo 1',
            'game_id' => 'TEST001',
            'price' => 20,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        $game2 = Game::create([
            'name' => 'Jogo 2',
            'game_id' => 'TEST002',
            'price' => 30,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        // Criar compras em cada jogo
        for ($i = 1; $i <= 2; $i++) {
            Purchase::create([
                'gambler_name' => 'Apostador',
                'gambler_phone' => '11944444444',
                'numbers' => "0{$i} 0" . ($i+1) . " 0" . ($i+2) . " 0" . ($i+3) . " 0" . ($i+4),
                'quantity' => 1,
                'price' => 20,
                'status' => 'PENDING',
                'game_id' => $game1->id,
                'user_id' => $gambler->id,
                'seller_id' => 1,
                'round' => 1,
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            Purchase::create([
                'gambler_name' => 'Apostador',
                'gambler_phone' => '11944444444',
                'numbers' => "0{$i} 0" . ($i+1) . " 0" . ($i+2) . " 0" . ($i+3) . " 0" . ($i+4),
                'quantity' => 1,
                'price' => 30,
                'status' => 'PENDING',
                'game_id' => $game2->id,
                'user_id' => $gambler->id,
                'seller_id' => 1,
                'round' => 1,
            ]);
        }

        // Pagar todas as compras
        $purchases = Purchase::where('user_id', $gambler->id)->get();
        foreach ($purchases as $purchase) {
            $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");
        }

        // Verificar agrupamento por jogo
        $game1Purchases = Transactions::where('user_id', $gambler->id)
            ->where('type', 'PAY_PURCHASE')
            ->whereHas('purchase', function ($q) use ($game1) {
                $q->where('game_id', $game1->id);
            })
            ->count();
        
        $this->assertEquals(2, $game1Purchases);

        $game2Purchases = Transactions::where('user_id', $gambler->id)
            ->where('type', 'PAY_PURCHASE')
            ->whereHas('purchase', function ($q) use ($game2) {
                $q->where('game_id', $game2->id);
            })
            ->count();
        
        $this->assertEquals(3, $game2Purchases);
    }

    /** @test */
    public function test_summary_shows_seller_commissions_grouped()
    {
        $admin = User::where('email', 'admin@test.com')->first();

        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11933333333',
            'game_credit' => 0,
            'comission_percent' => 0.2,
        ]);

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11922222222',
            'game_credit' => 500,
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

        // Criar 5 compras
        for ($i = 1; $i <= 5; $i++) {
            $purchase = Purchase::create([
                'gambler_name' => 'Apostador',
                'gambler_phone' => '11922222222',
                'numbers' => "0{$i} 0" . ($i+1) . " 0" . ($i+2) . " 0" . ($i+3) . " 0" . ($i+4),
                'quantity' => 1,
                'price' => 100,
                'status' => 'PENDING',
                'game_id' => $game->id,
                'user_id' => $gambler->id,
                'seller_id' => $seller->id,
                'round' => 1,
            ]);

            $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");
        }

        // Verificar comissões agrupadas por jogo
        $commissions = Transactions::where('user_id', $seller->id)
            ->where('type', 'PAY_PURCHASE_COMISSION')
            ->where('game_id', $game->id)
            ->get();

        $this->assertCount(5, $commissions);

        $totalCommission = $commissions->sum('amount');
        $expectedTotal = 100 * 0.2 * 5; // 100
        $this->assertEquals($expectedTotal, $totalCommission);
    }

    /** @test */
    public function test_complete_flow_summary_validation()
    {
        $admin = User::where('email', 'admin@test.com')->first();

        // Criar vendedor
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 2,
            'phone' => '11911111111',
            'game_credit' => 0,
            'comission_percent' => 0.15,
        ]);

        // Criar apostador indicado
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11900000000',
            'game_credit' => 1000,
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
            'name' => 'Jogo Completo',
            'game_id' => 'TEST001',
            'price' => 200,
            'status' => 'OPENED',
            'round' => 1,
            'active' => true,
            'open_at' => now(),
            'close_at' => now()->addDays(1),
        ]);

        // Criar e pagar compra
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11900000000',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 200,
            'status' => 'PENDING',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => $seller->id,
            'round' => 1,
        ]);

            $this->actingAs($gambler)->get("/purchase/pay/{$purchase->id}");

        // Pagar bônus de indicação
        $referEarn->invited_user_bought = true;
        $referEarn->save();
        $this->actingAs($seller)->get("/indique_ganhe/pagar/{$referEarn->id}");

        // Criar e pagar prêmio
        $gameAward = GameAward::create([
            'game_id' => $game->id,
            'name' => 'Prêmio Grande',
            'condition_type' => 'EXACT_POINT',
            'exact_point_value' => 5,
            'amount' => 500,
        ]);

        $userAward = UserAwards::create([
            'user_id' => $gambler->id,
            'game_id' => $game->id,
            'purchase_id' => $purchase->id,
            'game_award_id' => $gameAward->id,
            'points' => 5,
            'amount' => 500,
            'status' => 'PENDING',
            'round' => 1,
        ]);

        $this->actingAs($admin)->get("/meus_premios/pay/{$userAward->id}");

        // Validar resumo do apostador
        $gamblerTransactions = Transactions::where('user_id', $gambler->id)->get();
        
        $this->assertTrue($gamblerTransactions->contains(function ($t) {
            return $t->type === 'PAY_PURCHASE';
        }));

        $this->assertTrue($gamblerTransactions->contains(function ($t) {
            return $t->type === 'PAY_AWARD';
        }));

        // Validar resumo do vendedor
        $sellerTransactions = Transactions::where('user_id', $seller->id)->get();
        
        $this->assertTrue($sellerTransactions->contains(function ($t) {
            return $t->type === 'PAY_PURCHASE_COMISSION';
        }));

        $this->assertTrue($sellerTransactions->contains(function ($t) {
            return $t->type === 'REFER_EARN';
        }));

        // Validar totais
        $gamblerOutcome = $gamblerTransactions->whereIn('type', ['PAY_PURCHASE'])->sum('amount');
        $gamblerIncome = $gamblerTransactions->whereIn('type', ['PAY_AWARD'])->sum('amount');
        
        $this->assertEquals(200, $gamblerOutcome);
        $this->assertEquals(500, $gamblerIncome);

        $sellerIncome = $sellerTransactions->whereIn('type', ['PAY_PURCHASE_COMISSION', 'REFER_EARN'])->sum('amount');
        $expectedSellerIncome = (200 * 0.15) + 10; // 30 + 10 = 40
        $this->assertEquals($expectedSellerIncome, $sellerIncome);
    }
}
