<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameHistory;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionsController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $builder = new Transactions();
    if (Auth::user()->role->level_id !== 'admin') {
      $builder = $builder->where('user_id', Auth::user()->id);
    } else {
      if ($request->has('user_id') && $request->user_id != '') {
        $builder = $builder->where('user_id', $request->user_id);
      }
    }

    // Apply date filters
    if ($request->has('start_date') && $request->start_date != '') {
      $builder = $builder->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date != '') {
      $builder = $builder->whereDate('created_at', '<=', $request->end_date);
    }

    $users = User::without(['invited_by'])->get();
    $games = Game::without(['awards'])->orderBy('created_at', 'DESC')->get();
    if ($request->has('game_id') && ($request->game_id) != 'all') {
      $builder = $builder->where('game_id', $request->game_id);

      // If the date wasnt specified, so we should get the date of last opened game for game_id
      if (!$request->has('end_date') || $request->end_date == '') {

        // only shows transactions in same round that the last game opening
        $lastClosedHistory = GameHistory::where('game_id', $request->game_id)
          ->where('type', 'OPENED')
          ->orderBy('created_at', 'DESC')
          ->first();

        if ($lastClosedHistory) {
          $builder = $builder->whereDate('created_at', '>=', $lastClosedHistory->created_at);
        }
      }
    } elseif (Auth::user()->role->level_id !== 'admin' && $games->last()) {
      $builder = $builder->where('game_id', $games->first()->id);
    }

    $builder = $builder->orderBy('created_at', 'DESC');
    $transactions = $builder->paginate(20);

    // Mapeamento de tipos para traduções
    $typeTranslations = [
      'DEPOSIT' => 'Depósito',
      'WITHDRAWAL' => 'Saque',
      'DEPOSIT_REVERSAL' => 'Estorno de Depósito',
      'WITHDRAWAL_REVERSAL' => 'Estorno de Saque',
      'REFER_EARN' => 'Bônus de Indicação',
      'REFER_EARN_REVERSAL' => 'Estorno de Bônus de Indicação',
      'PAY_PURCHASE' => 'Pagamento de Compra',
      'PAY_PURCHASE_WITHDRAWAL' => 'Estorno de Pagamento de Compra',
      'PAY_PURCHASE_COMISSION' => 'Pagamento de Comissão',
      'PAY_PURCHASE_COMISSION_WITHDRAWAL' => 'Estorno de Pagamento de Comissão',
      'PAY_AWARD' => 'Pagamento de Prêmio',
      'PAY_AWARD_WITHDRAWAL' => 'Estorno de Pagamento de Prêmio',
      'GAME_CREDIT' => 'Crédito para Jogar',
      'GAME_CREDIT_REVERSAL' => 'Estorno de Crédito para Jogar'
    ];

    return view('content.transactions.transactions', compact('transactions', 'typeTranslations', 'users', 'games'));
  }


  public function summary(Request $request)
  {
    $builder = new Transactions();

    // Filter by user if not admin
    if (Auth::user()->role->level_id !== 'admin') {
      $builder = $builder->where('user_id', Auth::user()->id);
    } else {
      if ($request->has('user_id') && $request->user_id !=  '') {
        $builder = $builder->where('user_id', $request->user_id);
      }
    }

    // Apply date filters
    if ($request->has('start_date') && $request->start_date != '') {
      $builder = $builder->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date != '') {
      $builder = $builder->whereDate('created_at', '<=', $request->end_date);
    }

    $users = User::without(['invited_by'])->get();
    $games = Game::without(['awards'])->orderBy('created_at', 'DESC')->get();

    if ($request->has('game_id') && ($request->game_id) != 'all') {
      $builder = $builder->where('game_id', $request->game_id);


      // If the date wasnt specified, so we should get the date of last opened game for game_id
      if (!$request->has('end_date') || $request->end_date == '') {

        // only shows transactions in same round that the last game opening
        $lastClosedHistory = GameHistory::where('game_id', $request->game_id)
          ->where('type', 'OPENED')
          ->orderBy('created_at', 'DESC')
          ->first();

        if ($lastClosedHistory) {
          $builder = $builder->whereDate('created_at', '>=', $lastClosedHistory->created_at);
        }
      }
    } elseif (Auth::user()->role->level_id !== 'admin' && $games->last()) {
      $builder = $builder->where('game_id', $games->first()->id);
    }

    // Get all transactions for summary
    $transactions = $builder->orderBy('created_at', 'DESC')->get();

    // Define transaction types and their categories (income/outcome)
    $typeCategories = [
      'DEPOSIT' => 'income',
      'WITHDRAWAL' => 'outcome',
      'DEPOSIT_REVERSAL' => 'outcome',
      'WITHDRAWAL_REVERSAL' => 'income',
      'REFER_EARN' => 'income',
      'REFER_EARN_REVERSAL' => 'outcome',
      'PAY_PURCHASE' => 'outcome',
      'PAY_PURCHASE_WITHDRAWAL' => 'income',
      'PAY_PURCHASE_COMISSION' => 'income',
      'PAY_PURCHASE_COMISSION_WITHDRAWAL' => 'outcome',
      'PAY_AWARD' => 'income',
      'PAY_AWARD_WITHDRAWAL' => 'outcome',
      'GAME_CREDIT' => 'income',
      'GAME_CREDIT_REVERSAL' => 'outcome'
    ];

    // Type translations (same as your index)
    $typeTranslations = [
      'DEPOSIT' => 'Depósito',
      'WITHDRAWAL' => 'Saque',
      'DEPOSIT_REVERSAL' => 'Estorno de Depósito',
      'WITHDRAWAL_REVERSAL' => 'Estorno de Saque',
      'REFER_EARN' => 'Bônus de Indicação',
      'REFER_EARN_REVERSAL' => 'Estorno de Bônus de Indicação',
      'PAY_PURCHASE' => 'Pagamento de Compra',
      'PAY_PURCHASE_WITHDRAWAL' => 'Estorno de Pagamento de Compra',
      'PAY_PURCHASE_COMISSION' => 'Pagamento de Comissão',
      'PAY_PURCHASE_COMISSION_WITHDRAWAL' => 'Estorno de Pagamento de Comissão',
      'PAY_AWARD' => 'Pagamento de Prêmio',
      'PAY_AWARD_WITHDRAWAL' => 'Estorno de Pagamento de Prêmio',
      'GAME_CREDIT' => 'Crédito para Jogar',
      'GAME_CREDIT_REVERSAL' => 'Estorno de Crédito para Jogar'
    ];

    // Calculate totals
    $totalIncome = 0;
    $totalOutcome = 0;
    $typeDetails = [];

    foreach ($transactions as $transaction) {
      $amount = $transaction->amount;
      $type = $transaction->type;

      if (!isset($typeCategories[$type])) continue;

      if ($typeCategories[$type] === 'income') {
        $totalIncome += $amount;
      } else {
        $totalOutcome += $amount;
      }

      // Build details by type
      if (!isset($typeDetails[$type])) {
        $typeDetails[$type] = [
          'name' => $typeTranslations[$type] ?? $type,
          'count' => 0,
          'total' => 0,
          'category' => $typeCategories[$type]
        ];
      }

      $typeDetails[$type]['count']++;
      $typeDetails[$type]['total'] += $amount;
    }

    // Sort type details by total amount (descending)
    uasort($typeDetails, function ($a, $b) {
      return $b['total'] <=> $a['total'];
    });

    if (isset($typeDetails['DEPOSIT_REVERSAL'])) {
      $typeDetails['DEPOSIT']['total'] -= $typeDetails['DEPOSIT_REVERSAL']['total'];
      $typeDetails['DEPOSIT']['count'] -= $typeDetails['DEPOSIT_REVERSAL']['count'];
      unset($typeDetails['DEPOSIT_REVERSAL']);
    }

    if (isset($typeDetails['WITHDRAWAL_REVERSAL'])) {
      $typeDetails['WITHDRAWAL']['total'] -= $typeDetails['WITHDRAWAL_REVERSAL']['total'];
      $typeDetails['WITHDRAWAL']['count'] -= $typeDetails['WITHDRAWAL_REVERSAL']['count'];
      unset($typeDetails['WITHDRAWAL_REVERSAL']);
    }

    if (isset($typeDetails['REFER_EARN_REVERSAL'])) {
      $typeDetails['REFER_EARN']['total'] -= $typeDetails['REFER_EARN_REVERSAL']['total'];
      $typeDetails['REFER_EARN']['count'] -= $typeDetails['REFER_EARN_REVERSAL']['count'];
      unset($typeDetails['REFER_EARN_REVERSAL']);
    }

    if (isset($typeDetails['PAY_PURCHASE_WITHDRAWAL'])) {
      $typeDetails['PAY_PURCHASE']['total'] -= $typeDetails['PAY_PURCHASE_WITHDRAWAL']['total'];
      $typeDetails['PAY_PURCHASE']['count'] -= $typeDetails['PAY_PURCHASE_WITHDRAWAL']['count'];
      unset($typeDetails['PAY_PURCHASE_WITHDRAWAL']);
    }

    if (isset($typeDetails['PAY_PURCHASE_COMISSION_WITHDRAWAL'])) {
      $typeDetails['PAY_PURCHASE_COMISSION']['total'] -= $typeDetails['PAY_PURCHASE_COMISSION_WITHDRAWAL']['total'];
      $typeDetails['PAY_PURCHASE_COMISSION']['count'] -= $typeDetails['PAY_PURCHASE_COMISSION_WITHDRAWAL']['count'];
      unset($typeDetails['PAY_PURCHASE_COMISSION_WITHDRAWAL']);
    }

    if (isset($typeDetails['PAY_AWARD_WITHDRAWAL'])) {
      $typeDetails['PAY_AWARD']['total'] -= $typeDetails['PAY_AWARD_WITHDRAWAL']['total'];
      $typeDetails['PAY_AWARD']['count'] -= $typeDetails['PAY_AWARD_WITHDRAWAL']['count'];
      unset($typeDetails['PAY_AWARD_WITHDRAWAL']);
    }

    if (isset($typeDetails['GAME_CREDIT_REVERSAL'])) {
      $typeDetails['GAME_CREDIT']['total'] -= $typeDetails['GAME_CREDIT_REVERSAL']['total'];
      $typeDetails['GAME_CREDIT']['count'] -= $typeDetails['GAME_CREDIT_REVERSAL']['count'];
      unset($typeDetails['GAME_CREDIT_REVERSAL']);
    }


    return view('content.transactions.summary', [
      'totalIncome' => $totalIncome,
      'totalOutcome' => $totalOutcome,
      'net' => $totalIncome - $totalOutcome,
      'typeDetails' => $typeDetails,
      'typeTranslations' => $typeTranslations,
      'users' => $users,
      'games' => $games,
    ]);
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
  public function show(Transactions $transactions)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Transactions $transactions)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Transactions $transactions)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Transactions $transactions)
  {
    //
  }
}
