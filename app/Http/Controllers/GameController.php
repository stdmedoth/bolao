<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameAward;
use App\Models\GameHistory;
use App\Models\Purchase;
use App\Models\User;
use App\Models\UserAwards;
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
    $histories = GameHistory::where('game_id', $id)->where('type', 'ADDING_NUMBER')->paginate(20);
    $user_awards = UserAwards::where('game_id', $id)->paginate(20);

    $winners = [];
    $lastClosedHistory = GameHistory::where('game_id', $game->id)
      ->where('type', 'OPENED')
      ->orderBy('created_at', 'DESC')
      ->first();


    foreach ($user_awards as $user_award) {

      // Obter todas as compras relacionadas ao jogo
      $purchasesBuilder = Purchase::where('game_id', $game->id)
        ->whereIn('status', ['PAID'])
        ->where('user_id', $user_award->user_id);

      if ($lastClosedHistory) {
        $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
      }
      $_purchases = $purchasesBuilder->get();

      $user = User::find($user_award->user_id);
      $game_award = GameAward::find($user_award->game_award_id);
      $winners[] = (object)[
        'id' => $user_award->id,
        'user' => $user,
        'status' => $user_award->status,
        'game_award' => $game_award,
        'purchases' => $_purchases
      ];
    }

    return view('content.game.view_game', [
      'game' => $game,
      'purchases' => $purchases,
      'histories' => $histories,
      'winners' => $winners,
      'user_awards' => $user_awards
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

    if (($game->status == "OPENED") && ($request->input('status') == "CLOSED")) {
      $game_history = GameHistory::create([
        "description" => "JOGO FECHADO",
        "numbers" => "",
        "type" => "CLOSED",
        'game_id' => $game->id,
      ]);
    }


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
