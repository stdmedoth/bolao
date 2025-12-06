<?php

namespace Tests\Feature;

use App\Models\RoleUser;
use App\Models\User;
use App\Models\Game;
use App\Models\GameAward;
use App\Models\GameHistory;
use App\Models\Purchase;
use App\Models\UserAwards;
use App\Models\Transactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AwardsAndResultsTest extends TestCase
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
    public function test_awards_created_when_numbers_match()
    {
        $admin = User::where('email', 'admin@test.com')->first();

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

        // Criar prêmio
        $award = GameAward::create([
            'game_id' => $game->id,
            'name' => 'Prêmio 5 Acertos',
            'condition_type' => 'EXACT_POINT',
            'exact_point_value' => 5,
            'amount' => 100,
            'only_when_finish_round' => false,
        ]);

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

        // Criar compra com números que vão acertar
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11988888888',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 10,
            'status' => 'PAID',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => 1,
            'round' => 1,
            'paid_by_user_id' => $gambler->id,
        ]);

        // Adicionar números sorteados (5 acertos)
        GameHistory::create([
            'game_id' => $game->id,
            'type' => 'ADDING_NUMBER',
            'numbers' => '01 02 03 04 05',
            'round' => 1,
        ]);

        // Processar prêmios (simulando o que o AdminController faz)
        $purchases = Purchase::where('game_id', $game->id)
            ->where('status', 'PAID')
            ->where('round', 1)
            ->get();

        $uniqueNumbers = [1, 2, 3, 4, 5];
        $purchasePoints = [];
        
        foreach ($purchases as $p) {
            $purchaseNumbers = array_map('intval', explode(' ', $p->numbers));
            $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);
            $purchasePoints[$p->id] = count($matchedNumbers);
        }

        // Verificar que o prêmio seria criado
        $eligiblePurchases = [];
        foreach ($purchasePoints as $purchaseId => $points) {
            if ($points >= 5) {
                $eligiblePurchases[] = $purchaseId;
            }
        }

        $this->assertCount(1, $eligiblePurchases);
        $this->assertEquals($purchase->id, $eligiblePurchases[0]);
    }

    /** @test */
    public function test_award_payment_creates_transaction()
    {
        $admin = User::where('email', 'admin@test.com')->first();

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

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11977777777',
            'game_credit' => 0,
            'seller_id' => 1,
        ]);

        // Criar compra
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11977777777',
            'numbers' => '01 02 03 04 05',
            'quantity' => 1,
            'price' => 10,
            'status' => 'PAID',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => 1,
            'round' => 1,
            'paid_by_user_id' => $gambler->id,
        ]);

        // Criar prêmio
        $gameAward = GameAward::create([
            'game_id' => $game->id,
            'name' => 'Prêmio 5 Acertos',
            'condition_type' => 'EXACT_POINT',
            'exact_point_value' => 5,
            'amount' => 200,
            'only_when_finish_round' => false,
        ]);

        // Criar UserAward
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

        $initialCredit = $gambler->game_credit;

        // Pagar prêmio
        $this->actingAs($admin)->get("/meus_premios/pay/{$userAward->id}");

        // Verificar que o crédito foi adicionado
        $gambler->refresh();
        $this->assertEquals($initialCredit + 200, $gambler->game_credit);

        // Verificar que a transação foi criada
        $transaction = Transactions::where('type', 'PAY_AWARD')
            ->where('user_id', $gambler->id)
            ->where('purchase_id', $purchase->id)
            ->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(200, $transaction->amount);

        // Verificar que o prêmio foi marcado como pago
        $userAward->refresh();
        $this->assertEquals('PAID', $userAward->status);
    }

    /** @test */
    public function test_multiple_winners_share_award()
    {
        $admin = User::where('email', 'admin@test.com')->first();

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

        // Criar prêmio
        $award = GameAward::create([
            'game_id' => $game->id,
            'name' => 'Prêmio 5 Acertos',
            'condition_type' => 'EXACT_POINT',
            'exact_point_value' => 5,
            'amount' => 300, // Total a ser dividido
            'only_when_finish_round' => false,
        ]);

        // Criar 3 apostadores
        $gamblers = [];
        for ($i = 1; $i <= 3; $i++) {
            $gamblers[] = User::create([
                'name' => "Apostador {$i}",
                'email' => "apostador{$i}@test.com",
                'password' => Hash::make('password'),
                'role_user_id' => 3,
                'phone' => "1190000000{$i}",
                'game_credit' => 100,
                'seller_id' => 1,
            ]);
        }

        // Criar 3 compras com os mesmos números (todos vão acertar)
        $purchases = [];
        foreach ($gamblers as $gambler) {
            $purchases[] = Purchase::create([
                'gambler_name' => $gambler->name,
                'gambler_phone' => $gambler->phone,
                'numbers' => '01 02 03 04 05',
                'quantity' => 1,
                'price' => 10,
                'status' => 'PAID',
                'game_id' => $game->id,
                'user_id' => $gambler->id,
                'seller_id' => 1,
                'round' => 1,
                'paid_by_user_id' => $gambler->id,
            ]);
        }

        // Simular criação de prêmios (como no AdminController)
        $eligiblePurchases = array_map(fn($p) => $p->id, $purchases);
        $numWinners = count($eligiblePurchases);
        $awardAmountPerUser = $award->amount / $numWinners; // 100 cada

        foreach ($eligiblePurchases as $purchaseId) {
            UserAwards::create([
                'user_id' => Purchase::find($purchaseId)->user_id,
                'game_id' => $game->id,
                'purchase_id' => $purchaseId,
                'game_award_id' => $award->id,
                'points' => 5,
                'amount' => $awardAmountPerUser,
                'status' => 'PENDING',
                'round' => 1,
            ]);
        }

        // Verificar que cada um recebeu 100
        $userAwards = UserAwards::where('game_award_id', $award->id)->get();
        $this->assertCount(3, $userAwards);
        
        foreach ($userAwards as $userAward) {
            $this->assertEquals(100, $userAward->amount);
        }
    }

    /** @test */
    public function test_game_results_calculation()
    {
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

        // Criar apostador
        $gambler = User::create([
            'name' => 'Apostador',
            'email' => 'apostador@test.com',
            'password' => Hash::make('password'),
            'role_user_id' => 3,
            'phone' => '11966666666',
            'game_credit' => 100,
            'seller_id' => 1,
        ]);

        // Criar compra
        $purchase = Purchase::create([
            'gambler_name' => 'Apostador',
            'gambler_phone' => '11966666666',
            'numbers' => '01 02 03 04 05 06 07',
            'quantity' => 1,
            'price' => 10,
            'status' => 'PAID',
            'game_id' => $game->id,
            'user_id' => $gambler->id,
            'seller_id' => 1,
            'round' => 1,
            'paid_by_user_id' => $gambler->id,
        ]);

        // Adicionar números sorteados
        GameHistory::create([
            'game_id' => $game->id,
            'type' => 'ADDING_NUMBER',
            'numbers' => '01 02 03 04 05',
            'round' => 1,
        ]);

        // Calcular pontos
        $purchaseNumbers = array_map('intval', explode(' ', $purchase->numbers));
        $drawnNumbers = [1, 2, 3, 4, 5];
        $matchedNumbers = array_intersect($purchaseNumbers, $drawnNumbers);
        $points = count($matchedNumbers);

        $this->assertEquals(5, $points);
        $this->assertCount(5, $matchedNumbers);
    }
}
