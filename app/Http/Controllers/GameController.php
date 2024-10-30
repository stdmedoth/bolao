<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

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
    return view('content.game.view_game', ['game' => $game]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    $game = Game::findOrFail($id);
    return view('concursos.edit', compact('game'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id){
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'open_at' => 'required|date',
        'closed_at' => 'required|date|after_or_equal:open_at',
        'status' => 'required|in:active,pending,closed',
    ]);

    $game = Game::findOrFail($id);
    $game->name = $request->input('name');
    $game->price = $request->input('price');
    $game->open_at = $request->input('open_at');
    $game->closed_at = $request->input('closed_at');
    $game->status = $request->input('status');

    $game->save();

    return redirect()->back()->with('success', 'Jogo atualizado com sucesso!');
}




  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Game $game)
  {
    //
  }
}
