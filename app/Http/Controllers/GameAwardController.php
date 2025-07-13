<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameAward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameAwardController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(Request $request, $game_id)
  {
    //

    if (Auth::user()->role->level_id !== 'admin') {
      redirect('/auth/logout');
    }

    $game = Game::find($game_id);

    return view('content.game.award.create_game_award', compact('game'));
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request, $game_id)
  {
    //
    if (Auth::user()->role->level_id !== 'admin') {
      redirect('/auth/logout');
    }

    $data = $request->all();
    $data['amount'] = str_replace(".", "", $data['amount']);
    $data['amount'] = str_replace(",", ".", $data['amount']);
    $data['game_id'] = $game_id;
    $gameAward = GameAward::create($data);


    return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-prizes']);
  }

  /**
   * Display the specified resource.
   */
  public function show(GameAward $gameAward)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Request $request, $game_award_id)
  {
    //

    if (Auth::user()->role->level_id !== 'admin') {
      redirect('/auth/logout');
    }

    $gameAward = GameAward::with('game')->find($game_award_id);

    return view('content.game.award.edit_game_award', compact('gameAward'));
  }

  public function update(Request $request, $game_award_id)
  {
    if (Auth::user()->role->level_id !== 'admin') {
      redirect('/auth/logout');
    }

    $gameAward = GameAward::with('game')->find($game_award_id);

    $data = $request->all();
    if (isset($data['amount'])) {
      $data['amount'] = str_replace(".", "", $data['amount']);
      $data['amount'] = str_replace(",", ".", $data['amount']);
    }
    $gameAward->update($data);

    $game_id = $gameAward->game_id;

    return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-prizes']);
  }

  /**
   * Update the specified resource in storage.
   */
  public function updateAwards(Request $request, Game $game)
  {
    $awardsData = $request->input('awards', []);

    foreach ($awardsData as $awardData) {
      if (isset($awardData['id'])) {
        // Atualizar prêmio existente
        $award = GameAward::findOrFail($awardData['id']);
        $award->update([
          'condition_type' => $awardData['condition_type'],
          'exact_point_value' => $awardData['exact_point_value'] ?? null,
          'winner_point_value' => $awardData['winner_point_value'] ?? null,
          'only_when_finish_round' => $awardData['only_when_finish_round'] ?? false,
          'amount' => $awardData['amount'],
        ]);
      } else {
        // Criar novo prêmio
        $game->awards()->create([
          'condition_type' => $awardData['condition_type'],
          'exact_point_value' => $awardData['exact_point_value'] ?? null,
          'winner_point_value' => $awardData['winner_point_value'] ?? null,
          'only_when_finish_round' => $awardData['only_when_finish_round'] ?? false,
          'amount' => $awardData['amount'],
        ]);
      }
    }

    return response()->json(['message' => 'Prêmios atualizados com sucesso']);
  }



  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, $game_award_id)
  {
    //
    if (Auth::user()->role->level_id !== 'admin') {
      redirect('/auth/logout');
    }
    $game_award = GameAward::find($game_award_id);

    $game_id = $game_award->game_id;

    $game_award->delete();

    return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-prizes']);
  }
}
