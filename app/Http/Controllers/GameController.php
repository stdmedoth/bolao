<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameHistory;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
    $games = Game::where('active', 1)->get();
    return view('content.game.games', ['games' => $games]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show($id)
  {
    //
    $game = Game::find($id);
    $purchases = Purchase::where('user_id', Auth::user()->id)->where('game_id', $id)->get();
    $histories = GameHistory::where('game_id', $id)->where('type', 'ADDING_NUMBER')->get();
    return view('content.game.view_game', [
      'game' => $game,
      'purchases' => $purchases,
      'histories' => $histories
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function editGameForm($id)
  {
    $game = Game::findOrFail($id);

    $game->open_at = Carbon::parse($game->open_at)->format("Y-m-d");
    $game->close_at = Carbon::parse($game->close_at)->format("Y-m-d");

    return view('content.game.game_update', compact('game'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'open_at' => 'required|date',
        'close_at' => 'required|date|after_or_equal:open_at',
        'status' => 'required|in:OPENED,CLOSED',
        'awards' => 'array',
        'awards.*.condition_type' => 'required|in:MINIMUM_POINT,EXACT_POINT',
        'awards.*.minimum_point_value' => 'nullable|integer',
        'awards.*.amount' => 'required|numeric|min:0',
    ]);

    $game = Game::findOrFail($id);
    $game->update([
        'name' => $request->input('name'),
        'price' => $request->input('price'),
        'open_at' => $request->input('open_at'),
        'close_at' => $request->input('close_at'),
        'status' => $request->input('status'),
    ]);

    // Chama o método para atualizar os prêmios
    app(GameAwardController::class)->updateAwards($request, $game);

    return redirect(route("show-game", $game->id))->with('success', 'Jogo e prêmios atualizados com sucesso!');
}
  



  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Game $game)
  {
    //
  }
}
