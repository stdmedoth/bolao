<?php

namespace App\Http\Controllers;

use App\Models\Awards;
use App\Models\Game;
use App\Models\Purchase;
use App\Models\Transactions;
use App\Models\User;
use App\Models\UserAwards;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserAwardController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //

    $builder = new UserAwards();
    if (Auth::user()->role->level_id !== 'admin') {
      $builder = $builder->where('user_id', Auth::user()->id);
    }
    $user_awards = $builder->orderBy('created_at', 'DESC')->paginate(6);

    return view('content.awards.my-awards', ['user_awards' => $user_awards]);
  }

  public function pay(Request $request, $id)
  {
    $user_award = UserAwards::find($id);
    $purchase = Purchase::find($user_award->purchase_id);

    $user = User::find($purchase->paid_by_user_id);

    $user->balance += $user_award->amount;
    $user->save();


    $user_award->status = "PAID";
    $user_award->save();

    $game = Game::find($user_award->game_id);
    $description = Transactions::generateDescription('PAY_AWARD', $user_award->amount, [
      'game' => $game,
      'user' => $user,
    ]);
    Transactions::create(
      [
        "type" => 'PAY_AWARD',
        "game_id" => $user_award->game_id,
        "purchase_id" => $user_award->purchase_id,
        "amount" => $user_award->amount,
        "user_id" => $user->id,
        "description" => $description,
      ]
    );

    return redirect(route("show-game", $user_award->game_id))->with('success', 'Prêmio atualizados com sucesso!');
  }


  public function withdraw(Request $request, $id)
  {
    $user_award = UserAwards::find($id);

    $purchase = Purchase::find($user_award->purchase_id);
    $user = User::find($purchase->paid_by_user_id);

    $user->balance -= $user_award->amount;
    $user->save();


    $user_award->status = "PENDING";
    $user_award->save();

    $game = Game::find($user_award->game_id);
    $description = Transactions::generateDescription('PAY_AWARD_WITHDRAWAL', $user_award->amount, [
      'game' => $game,
      'user' => $user,
    ]);
    Transactions::create(
      [
        "type" => 'PAY_AWARD_WITHDRAWAL',
        "game_id" => $user_award->game_id,
        "purchase_id" => $user_award->purchase_id,
        "amount" => $user_award->amount,
        "user_id" => $user->id,
        "description" => $description,
      ]
    );

    return redirect(route("show-game", $user_award->game_id))->with('success', 'Prêmio atualizados com sucesso!');
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
  public function show(UserAwards $awards)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function editMyAwards(UserAwards $awards)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    //
    $user_award = UserAwards::find($id);
    $user_award->status = $request->status;
    $user_award->amount = $request->amount;
    $user_award->save();
  }



  /**
   * Remove the specified resource from storage.
   */
  public function destroy(UserAwards $awards)
  {
    //
  }
}
