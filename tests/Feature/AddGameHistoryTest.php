<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameAward;
use App\Models\Purchase;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddGameHistoryTest extends TestCase
{
    use WithFaker;

    public function test_awards_are_generated_when_adding_game_history()
    {
        // create roles
        $adminRole = new RoleUser();
        $adminRole->name = 'Admin';
        $adminRole->level_id = 'admin';
        $adminRole->save();

        $gamblerRole = new RoleUser();
        $gamblerRole->name = 'Gambler';
        $gamblerRole->level_id = 'gambler';
        $gamblerRole->save();

        // create users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1111',
            'password' => bcrypt('secret'),
            'role_user_id' => $adminRole->id,
        ]);

        $user = User::create([
            'name' => 'Gambler',
            'email' => 'gambler@example.com',
            'phone' => '2222',
            'password' => bcrypt('secret'),
            'role_user_id' => $gamblerRole->id,
        ]);

        // create game closed
        $game = Game::create([
            'name' => 'Test Game',
            'game_id' => '12345',
            'price' => 10,
            'open_at' => now(),
            'close_at' => now(),
            'status' => 'CLOSED',
            'round' => 1,
            'active' => true,
        ]);

        // award for exact 3 points
        $award = GameAward::create([
            'name' => 'Exact 3',
            'condition_type' => 'EXACT_POINT',
            'exact_point_value' => 3,
            'amount' => 90,
            'game_id' => $game->id,
        ]);

        // purchase with matching numbers
        Purchase::create([
            'numbers' => '10 20 30',
            'gambler_name' => 'Test',
            'gambler_phone' => '123',
            'status' => 'PAID',
            'quantity' => 1,
            'price' => 10,
            'round' => 1,
            'identifier' => '1234A',
            'game_id' => $game->id,
            'user_id' => $user->id,
            'seller_id' => $user->id,
        ]);

        $this->actingAs($admin);

        $response = $this->post('/concursos/add_game_history/'.$game->id, [
            'description' => 'numbers',
            'result_numbers' => '10 20 30',
        ]);

        $response->assertRedirect(route('show-game', ['id' => $game->id]));

        $this->assertDatabaseHas('user_awards', [
            'user_id' => $user->id,
            'game_award_id' => $award->id,
            'amount' => 90.0,
            'round' => 1,
        ]);
    }
}
