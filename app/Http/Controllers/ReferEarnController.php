<?php

namespace App\Http\Controllers;

use App\Models\ReferEarn;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferEarnController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
    $refered_qnt = ReferEarn::where('refer_user_id', Auth::user()->id)
      ->count();

    $refered_qnt_bought = ReferEarn::where('refer_user_id', Auth::user()->id)
      ->where('invited_user_bought', true)
      ->count();

    $refered_amount_earned = ReferEarn::where('refer_user_id', Auth::user()->id)
      ->where('invited_user_bought', true)
      ->where('earn_paid', true)
      ->sum('amount');

    $referEarns = ReferEarn::where('refer_user_id', Auth::user()->id)
                    ->get();

    // Traduções para os status
    $statusTranslations = [
      'invited_user_bought' => [
        true => 'Sim',
        false => 'Não',
      ],
      'earn_paid' => [
        true => 'Pago',
        false => 'Pendente',
      ],
    ];

    $data = [
      'refered_qnt' => $refered_qnt,
      'refered_qnt_bought' => $refered_qnt_bought,
      'refered_amount_earned' => $refered_amount_earned,
      'code' => base64_encode('refered_by_' . Auth::user()->id),
      'referEarns' => $referEarns,
      'statusTranslations' => $statusTranslations,
    ];

    if (Auth::user()->role->level_id == 'admin') {
      $referEarns = ReferEarn::with(['referUser', 'invitedUser'])->get();
      $data['referEarns'] = $referEarns;
    }

    return view('content.refer_earn.refer_earn', $data);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(Request $request)
  {
    $code = $request->input('code');
    $code = base64_decode($code);
    $refered_by_id = str_replace('refered_by_', "", $code);

    return view("content.authentications.auth-register-basic", ['refered_by_id' => $refered_by_id]);
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
  public function show(ReferEarn $referEarn)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(ReferEarn $referEarn)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, ReferEarn $referEarn)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(ReferEarn $referEarn)
  {
    //
  }

  public function pay(Request $request, $id)
  {

    $refer = ReferEarn::find($id);

    $user = User::find($refer->refer_user_id);
    $user->balance +=  $refer->amount;
    $user->save();

    $refer->earn_paid = true;
    $refer->save();

    Transactions::create(
      [
        "type" => 'REFER_EARN',
        "amount" => $refer->amount,
        "user_id" => $user->id,
      ]
    );

    return redirect(route('refer_earn-view'));
  }


  public function payback(Request $request, $id)
  {

    $refer = ReferEarn::find($id);

    $user = User::find($refer->refer_user_id);
    $user->balance -=  $refer->amount;
    $user->save();

    $refer->earn_paid = false;
    $refer->save();

    Transactions::create(
      [
        "type" => 'REFER_EARN_REVERSAL',
        "amount" => $refer->amount,
        "user_id" => $user->id,
      ]
    );

    return redirect(route('refer_earn-view'));
  }
}
