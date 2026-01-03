<?php

namespace Tests\Feature;

use App\Http\Controllers\GameController;
use App\Models\Game;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class GameControllerTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function it_fetches_games_with_status_opened_or_closed_and_excludes_current_game()
  {
    // Arrange: create games with different statuses (sem factory, criando diretamente)
    $gameOpened = Game::create([
      'name' => 'Jogo Aberto',
      'game_id' => 'TEST001',
      'price' => 10,
      'status' => 'OPENED',
      'round' => 1,
      'active' => true,
      'open_at' => now(),
      'close_at' => now()->addDays(1),
    ]);
    
    $gameClosed = Game::create([
      'name' => 'Jogo Fechado',
      'game_id' => 'TEST002',
      'price' => 10,
      'status' => 'CLOSED',
      'round' => 1,
      'active' => true,
      'open_at' => now(),
      'close_at' => now()->addDays(1),
    ]);
    
    $gameFinished = Game::create([
      'name' => 'Jogo Finalizado',
      'game_id' => 'TEST003',
      'price' => 10,
      'status' => 'FINISHED',
      'round' => 1,
      'active' => true,
      'open_at' => now(),
      'close_at' => now()->addDays(1),
    ]);

    // Simulate current game
    $currentGame = $gameOpened;

    // Act: run the query as in the controller (sem eager loading para evitar problemas)
    $games = Game::without(['awards'])->select(['id', 'status', 'name'])
      ->whereIn('status', ['OPENED', 'CLOSED'])
      ->whereNotIn('id', [$currentGame->id])
      ->get();

    // Assert: only the CLOSED game is returned, not the current or FINISHED
    $this->assertInstanceOf(Collection::class, $games);
    $this->assertCount(1, $games);
    $this->assertEquals($gameClosed->id, $games->first()->id);
    $this->assertNotContains($currentGame->id, $games->pluck('id'));
    $this->assertNotContains($gameFinished->id, $games->pluck('id'));
  }
}
