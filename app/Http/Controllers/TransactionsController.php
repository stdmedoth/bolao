<?php

namespace App\Http\Controllers;

use App\Models\Game;
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

    if ($request->has('game_id') && ($request->game_id) != '') {
      $builder = $builder->where('game_id', $request->game_id);
    }


    $users = User::without(['invited_by'])->get();
    $games = Game::without(['awards'])->orderBy('created_at', 'DESC')->get();

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
      'PAY_AWARD' => 'Pagamento de Prêmio'
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

    if ($request->has('game_id') && ($request->game_id) != '') {
      $builder = $builder->where('game_id', $request->game_id);
    }

    $users = User::without(['invited_by'])->get();
    $games = Game::without(['awards'])->orderBy('created_at', 'DESC')->get();

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
      'PAY_AWARD' => 'income'
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
      'PAY_AWARD' => 'Pagamento de Prêmio'
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
