<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameHistory;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
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
      'GAME_CREDIT_REVERSAL' => 'Estorno de Crédito para Jogar',
      'CUSTOM_INCOME' => 'Entrada Personalizada',
      'CUSTOM_OUTCOME' => 'Saída Personalizada'
    ];

    return view('content.transactions.transactions', compact('transactions', 'typeTranslations', 'users', 'games'));
  }


  public function summary(Request $request)
  {
    $builder = new Transactions();
    $hasExplicitStart = $request->has('start_date') && $request->start_date != '';
    $hasExplicitEnd = $request->has('end_date') && $request->end_date != '';
    $hasExplicitPeriod = $hasExplicitStart || $hasExplicitEnd;
    $hasSelectedMonth = $request->has('month') && $request->month != '';
    $selectedMonth = null;

    // Filter by user if not admin
    if (Auth::user()->role->level_id !== 'admin') {
      $builder = $builder->where('user_id', Auth::user()->id);
    } else {
      if ($request->has('user_id') && $request->user_id !=  '') {
        $builder = $builder->where('user_id', $request->user_id);
      }
    }

    // Apply month filter:
    // - if user selected a month, use it
    // - otherwise, if no explicit start/end date was provided, use current month as default
    if ($hasSelectedMonth || !$hasExplicitPeriod) {
      $month = $hasSelectedMonth ? (int)$request->month : (int)date('n'); // current month as default
      $year = date('Y'); // Current year
      $selectedMonth = $month;
      
      // Calculate start and end dates for the selected month
      $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
      $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year)); // Get last day of month
      $endDate = date('Y-m-' . $lastDay, mktime(0, 0, 0, $month, 1, $year));
      
      $builder = $builder->whereDate('created_at', '>=', $startDate)
                         ->whereDate('created_at', '<=', $endDate);
    }

    // Apply date filters (for backward compatibility)
    if ($hasExplicitStart) {
      $builder = $builder->whereDate('created_at', '>=', $request->start_date);
    }

    if ($hasExplicitEnd) {
      $builder = $builder->whereDate('created_at', '<=', $request->end_date);
    }

    $users = User::without(['invited_by'])->get();
    $games = Game::without(['awards'])->orderBy('created_at', 'DESC')->get();

    if ($request->has('game_id') && ($request->game_id) != 'all') {
      $builder = $builder->where('game_id', $request->game_id);


      // If the date wasnt specified and month wasnt selected, so we should get the date of last opened game for game_id
      if ((!$request->has('end_date') || $request->end_date == '') && (!$request->has('month') || $request->month == '')) {

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

    // Get all transactions for summary with relationships
    $transactions = $builder->with(['game', 'purchase', 'purchase.seller', 'purchase.paid_by_user', 'purchase.user', 'purchase.userAwards'])->orderBy('created_at', 'DESC')->get();

    // Get selected user info
    $selectedUserId = Auth::user()->role->level_id === 'admin' && $request->has('user_id') && $request->user_id != '' 
      ? $request->user_id 
      : Auth::user()->id;
    $selectedUser = User::find($selectedUserId);
    
    // Get user info (inclui informações de limite de crédito para manter coerência com a plataforma)
    $userInfo = [
      'name' => $selectedUser->name ?? 'N/A',
      'comission_percent' => ($selectedUser->comission_percent ?? 0) * 100,
      'total_games' => 0,
      'game_credit' => $selectedUser->game_credit ?? 0,
      'game_credit_limit' => $selectedUser->game_credit_limit ?? 0,
      // Saldo devedor = limite inicial - crédito atual (se positivo)
      'credit_debt' => max(0, ($selectedUser->game_credit_limit ?? 0) - ($selectedUser->game_credit ?? 0)),
    ];

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
      'GAME_CREDIT_REVERSAL' => 'outcome',
      'CUSTOM_INCOME' => 'income',
      'CUSTOM_OUTCOME' => 'outcome'
    ];

    // Calculate totals and detailed breakdown
    $totalIncome = 0;
    $totalOutcome = 0;

    // Agrupamentos por concurso (game) apenas para:
    // - Pagamentos de jogos (PAY_PURCHASE)
    // - Comissão do vendedor (PAY_PURCHASE_COMISSION onde o user da transação é o seller)
    $groupedByGame = [
      'game_payments' => [],       // Pagamento de jogos por concurso
      'seller_commissions' => [],  // Comissão do vendedor por concurso
    ];

    // Demais transações ficam detalhadas individualmente
    $detailedRows = [];

    $uniqueGames = [];

    foreach ($transactions as $transaction) {
      $amount = $transaction->amount;
      $type = $transaction->type;

      if (!isset($typeCategories[$type])) {
        continue;
      }

      // Ignora estornos no resumo detalhado
      if (in_array($type, [
        'PAY_PURCHASE_WITHDRAWAL',
        'PAY_PURCHASE_COMISSION_WITHDRAWAL',
        'REFER_EARN_REVERSAL',
        'GAME_CREDIT_REVERSAL',
        'PAY_AWARD_WITHDRAWAL',
        'DEPOSIT_REVERSAL',
        'WITHDRAWAL_REVERSAL'
      ])) {
        continue;
      }

      if ($typeCategories[$type] === 'income') {
        $totalIncome += $amount;
      } else {
        $totalOutcome += $amount;
      }

      $game = $transaction->game;
      $purchase = $transaction->purchase;
      $gameId = $game ? $game->id : null;
      $gameName = $game ? $game->name : 'N/A';

      if ($gameId) {
        $uniqueGames[$gameId] = $gameName;
      }

      // 1) Agrupamento de pagamentos de jogos por concurso
      if ($type === 'PAY_PURCHASE' && $gameId) {
        if (!isset($groupedByGame['game_payments'][$gameId])) {
          $groupedByGame['game_payments'][$gameId] = [
            'game_name' => $gameName,
            'count' => 0,
            'total' => 0,
          ];
        }

        $groupedByGame['game_payments'][$gameId]['count']++;
        $groupedByGame['game_payments'][$gameId]['total'] += $amount;
        continue;
      }

      // 2) Agrupamento de comissão do vendedor por concurso
      if ($type === 'PAY_PURCHASE_COMISSION' && $purchase && $gameId) {
        // Comissão do vendedor: user da transação é o seller da compra
        if ($purchase->seller_id == $transaction->user_id) {
          if (!isset($groupedByGame['seller_commissions'][$gameId])) {
            $groupedByGame['seller_commissions'][$gameId] = [
              'game_name' => $gameName,
              'count' => 0,
              'total' => 0,
            ];
          }

          $groupedByGame['seller_commissions'][$gameId]['count']++;
          $groupedByGame['seller_commissions'][$gameId]['total'] += $amount;
          continue;
        }
      }

      // 3) Demais transações: detalhadas individualmente
      $rowTypeLabel = null;

      switch ($type) {
        case 'REFER_EARN':
          $rowTypeLabel = 'Bônus de indicação';
          break;
        case 'GAME_CREDIT':
          $rowTypeLabel = 'Adição de Credito';
          break;
        case 'PAY_AWARD':
          // Prêmio: usa sempre o estado atual do UserAward.
          // Se o prêmio foi estornado, o UserAward deixa de estar "PAID" e,
          // portanto, não será considerado no resumo.
          $purchase = $transaction->purchase;

          // Se não houver purchase vinculada, não conseguimos identificar o prêmio; ignora.
          if (!$purchase || !$gameId) {
            // pula esta transação e segue para a próxima do foreach
            continue 2;
          }

          // Usa os userAwards já carregados na compra
          $userAward = $purchase->userAwards
            ->where('status', 'PAID')
            ->where('game_id', $gameId)
            ->sortByDesc('created_at')
            ->first();

          // Se não houver prêmio pago associado (por exemplo, foi estornado),
          // não exibe esta transação no resumo.
          if (!$userAward) {
            continue 2;
          }

          // Monta o nome do prêmio
          $rowTypeLabel = 'Prêmio';
          $userAward->loadMissing('game_award');
          if ($userAward->game_award && $userAward->game_award->name) {
            $rowTypeLabel = $userAward->game_award->name;
          }
          if ($userAward->points) {
            $rowTypeLabel .= " ({$userAward->points} dezena" . ($userAward->points > 1 ? 's' : '') . ")";
          }
          break;
        case 'WITHDRAWAL':
          $rowTypeLabel = 'Saque';
          break;
        case 'DEPOSIT':
          $rowTypeLabel = 'Depósito';
          break;
        case 'CUSTOM_INCOME':
          //$rowTypeLabel = 'Entrada personalizada';
          $rowTypeLabel = $transaction->description;
          break;
        case 'CUSTOM_OUTCOME':
          $rowTypeLabel = 'Saída personalizada';
          break;
        case 'PAY_PURCHASE_COMISSION':
          // Comissão que não é do vendedor (por exemplo, comissão do apostador)
          $rowTypeLabel = 'Comissão de jogos';
          break;
        default:
          // Qualquer outro tipo não previsto explicitamente mantém o próprio código
          $rowTypeLabel = $type;
          break;
      }

      $detailedRows[] = [
        'type' => $rowTypeLabel,
        'game_name' => $gameName,
        'quantity' => '-',
        'total' => $amount,
        'category' => $typeCategories[$type],
      ];
    }

    // Calculate total games
    $userInfo['total_games'] = count($uniqueGames);

    // Monta linhas para paginação do resumo detalhado
    $rows = [];

    // Pagamentos de jogos por concurso
    foreach ($groupedByGame['game_payments'] as $gameId => $data) {
      $rows[] = [
        'type' => 'Pagamento de jogos',
        'game_name' => $data['game_name'],
        'quantity' => $data['count'],
        'total' => $data['total'],
        'category' => 'outcome',
      ];
    }

    // Comissão do vendedor por concurso
    foreach ($groupedByGame['seller_commissions'] as $gameId => $data) {
      $rows[] = [
        'type' => 'Comissão do vendedor',
        'game_name' => $data['game_name'],
        'quantity' => $data['count'],
        'total' => $data['total'],
        'category' => 'income',
      ];
    }

    // Demais transações detalhadas (já vêm na ordem das transações)
    foreach ($detailedRows as $detailRow) {
      $rows[] = $detailRow;
    }

    // Paginação manual das linhas do resumo
    $perPage = 20;
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $currentItems = array_slice($rows, ($currentPage - 1) * $perPage, $perPage);

    $paginatedSummary = new LengthAwarePaginator(
      $currentItems,
      count($rows),
      $perPage,
      $currentPage,
      [
        'path' => $request->url(),
        'query' => $request->query(),
      ]
    );

    return view('content.transactions.summary', [
      'totalIncome' => $totalIncome,
      'totalOutcome' => $totalOutcome,
      'net' => $totalIncome - $totalOutcome,
      'userInfo' => $userInfo,
      'summaryRows' => $paginatedSummary,
      'users' => $users,
      'games' => $games,
      'selectedMonth' => $selectedMonth ?? ($hasSelectedMonth ? (int)$request->month : null),
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
    // Only admin can create custom transactions
    if (Auth::user()->role->level_id !== 'admin') {
      return redirect()->back()->with('error', 'Apenas administradores podem criar lançamentos personalizados.');
    }

    $request->validate([
      'description' => 'required|string|max:500',
      'amount' => 'required|numeric|min:0.01',
      'transaction_type' => 'required|in:income,outcome',
      'user_id' => 'required|exists:users,id',
    ]);

    $type = $request->transaction_type === 'income' ? 'CUSTOM_INCOME' : 'CUSTOM_OUTCOME';
    $user = User::find($request->user_id);

    // Create transaction
    $transaction = Transactions::create([
      'type' => $type,
      'amount' => $request->amount,
      'description' => $request->description,
      'user_id' => $request->user_id,
    ]);

    // Update user balance
    if ($type === 'CUSTOM_INCOME') {
      $user->balance += $request->amount;
    } else {
      $user->balance -= $request->amount;
      // Ensure balance doesn't go negative
      if ($user->balance < 0) {
        $user->balance = 0;
      }
    }
    $user->save();

    $redirectParams = [];
    if ($request->has('filter_user_id') && $request->filter_user_id) {
      $redirectParams['user_id'] = $request->filter_user_id;
    }
    if ($request->has('filter_game_id') && $request->filter_game_id) {
      $redirectParams['game_id'] = $request->filter_game_id;
    }
    if ($request->has('filter_month') && $request->filter_month) {
      $redirectParams['month'] = $request->filter_month;
    }

    return redirect()->route('finances.summary', $redirectParams)
      ->with('success', 'Lançamento criado com sucesso!');
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
