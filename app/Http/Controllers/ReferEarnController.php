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
  public function index(Request $request)
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
      ->paginate(5);

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

    // Buscar todos os usuários (vendedores e apostadores) para o filtro
    $users = User::whereHas('role', function ($query) {
      $query->whereIn('level_id', ['seller', 'gambler']);
    })->get();

    $data = [
      'refered_qnt' => $refered_qnt,
      'refered_qnt_bought' => $refered_qnt_bought,
      'refered_amount_earned' => $refered_amount_earned,
      'code' => base64_encode('refered_by_' . Auth::user()->id),
      'referEarns' => $referEarns,
      'statusTranslations' => $statusTranslations,
      'users' => $users,
    ];

    // Filter by user if provided (filters by refer_user_id or invited_user_id)
    if ($request->has('user') && $request->user != '') {
      $referEarnsBuilder = ReferEarn::with(['referUser', 'invitedUser']);
      $referEarnsBuilder = $referEarnsBuilder->where(function ($query) use ($request) {
        $query->where('refer_user_id', $request->user)
          ->orWhere('invited_user_id', $request->user);
      });
      
      $referEarns = $referEarnsBuilder->paginate(5);
      $data['referEarns'] = $referEarns;
    } elseif (Auth::user()->role->level_id == 'admin') {
      // Se for admin e não houver filtro, mostra todos
      $referEarnsBuilder = ReferEarn::with(['referUser', 'invitedUser']);
      $referEarns = $referEarnsBuilder->paginate(5);
      $data['referEarns'] = $referEarns;
    }
    // Se não for admin e não houver filtro, mantém o comportamento padrão (já definido acima)

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
    $user->game_credit +=  $refer->amount;
    $user->save();

    $refer->earn_paid = true;
    $refer->save();

    $refer->load(['referUser', 'invitedUser']);
    $description = Transactions::generateDescription('REFER_EARN', $refer->amount, [
      'user' => $user,
      'refer_user' => $refer->referUser,
      'invited_user' => $refer->invitedUser,
    ]);
    Transactions::create(
      [
        "type" => 'REFER_EARN',
        "amount" => $refer->amount,
        "user_id" => $user->id,
        "description" => $description,
      ]
    );

    return redirect(route('refer_earn-view'));
  }


  public function payback(Request $request, $id)
  {

    $refer = ReferEarn::find($id);

    $user = User::find($refer->refer_user_id);
    $user->game_credit -=  $refer->amount;
    $user->save();

    $refer->earn_paid = false;
    $refer->save();

    $refer->load(['referUser', 'invitedUser']);
    $description = Transactions::generateDescription('REFER_EARN_REVERSAL', $refer->amount, [
      'user' => $user,
      'refer_user' => $refer->referUser,
      'invited_user' => $refer->invitedUser,
    ]);
    Transactions::create(
      [
        "type" => 'REFER_EARN_REVERSAL',
        "amount" => $refer->amount,
        "user_id" => $user->id,
        "description" => $description,
      ]
    );

    return redirect(route('refer_earn-view'));
  }
  public function delete(Request $request, $id)
  {

    $refer = ReferEarn::find($id);
    $refer->delete();

    return redirect(route('refer_earn-view'));
  }
}
