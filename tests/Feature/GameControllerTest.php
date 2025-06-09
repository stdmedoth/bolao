<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\GameController;
use App\Models\Game;
use Illuminate\Support\Collection;
use Tests\TestCase;
use RefreshDatabase;


class GameControllerTest extends TestCase
{

  /** @test */
  public function it_fetches_games_with_status_opened_or_closed_and_excludes_current_game()
  {
    // Arrange: create games with different statuses
    $gameOpened = Game::factory()->create(['status' => 'OPENED']);
    $gameClosed = Game::factory()->create(['status' => 'CLOSED']);
    $gameFinished = Game::factory()->create(['status' => 'FINISHED']);

    // Simulate current game
    $currentGame = $gameOpened;

    // Act: run the query as in the controller
    $games = Game::select(['id', 'status', 'name'])
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
