<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameHistory;
use App\Models\Purchase;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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

    // Filter by user if not admin (non-admin users only see their own transactions)
    if (Auth::user()->role->level_id !== 'admin') {
      // Se for vendedor, inclui também transações PAY_PURCHASE onde purchase.seller_id = vendedor
      // (jogos criados pelos apostadores vinculados)
      if (Auth::user()->role->level_id === 'seller') {
        $builder = $builder->where(function ($query) {
          $query->where('user_id', Auth::user()->id)
            ->orWhere(function ($q) {
              $q->where('type', 'PAY_PURCHASE')
                ->whereHas('purchase', function ($purchaseQuery) {
                  $purchaseQuery->where('seller_id', Auth::user()->id);
                });
            });
        });
      } else {
        $builder = $builder->where('user_id', Auth::user()->id);
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

    // Filter by user if provided (only for admin)
    // Unifica os filtros de vendedor e apostador em um único filtro de usuário
    $selectedUserId = Auth::user()->id;
    $hasUserFilter = false;
    $isSellerFilter = false;

    // Verifica se o usuário logado é vendedor (quando acessa diretamente, sem filtro de admin)
    if (Auth::user()->role->level_id === 'seller') {
      $selectedUser = Auth::user();
      $isSellerFilter = true;
    }

    if (Auth::user()->role->level_id === 'admin' && $request->has('user_id') && $request->user_id != '') {
      $selectedUserId = $request->user_id;
      $hasUserFilter = true;
      $selectedUser = User::find($selectedUserId);

      // Verifica se o usuário é vendedor
      $isSellerFilter = $selectedUser && $selectedUser->role_user_id == 2;

      if ($isSellerFilter) {
        // Se for vendedor, mostra:
        // 1) Transações relacionadas a purchases onde ele foi o vendedor
        // 2) OU transações do próprio vendedor (user_id) que não têm purchase (DEPOSIT, WITHDRAWAL, REFER_EARN, etc.)
        $builder = $builder->where(function ($query) use ($selectedUserId) {
          $query->whereHas('purchase', function ($q) use ($selectedUserId) {
            $q->where('seller_id', $selectedUserId);
          })->orWhere('user_id', $selectedUserId);
        });
      } else {
        // Se for apostador ou outro tipo, filtra apenas por user_id da transação
        $builder = $builder->where('user_id', $selectedUserId);
      }
    }

    if ($request->has('game_id') && ($request->game_id) != 'all' && $request->game_id != '') {
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
    }

    // Get all transactions for summary with relationships
    $transactions = $builder->with(['game', 'user', 'purchase', 'purchase.seller', 'purchase.paid_by_user', 'purchase.user', 'purchase.userAwards'])->orderBy('created_at', 'DESC')->get();

    // Get selected user info
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
    // Quando filtrar por vendedor, agrupa por concurso E usuário (apostador)
    $groupedByGame = [
      'game_payments' => [],       // Pagamento de jogos por concurso (e usuário se for vendedor)
      'seller_commissions' => [],  // Comissão do vendedor por concurso
    ];

    // Demais transações ficam detalhadas individualmente
    $detailedRows = [];

    $uniqueGames = [];
    $totalGamesCount = 0; // Contador total de jogos (purchases)

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

      // Conta jogos baseado no tipo de filtro aplicado
      if ($gameId) {
        $shouldCountGame = true;
        // Se há filtro de vendedor, conta jogos onde o vendedor é o selecionado (purchase.seller_id)
        // Se há filtro de apostador, conta jogos onde o apostador é o selecionado (purchase.user_id)
        if ($purchase) {
          if ($isSellerFilter) {
            $shouldCountGame = ($purchase->seller_id == $selectedUserId);
          } elseif ($hasUserFilter) {
            $shouldCountGame = ($purchase->user_id == $selectedUserId);
          }
        }

        if ($shouldCountGame) {
          $uniqueGames[$gameId] = $gameName;
        }
      }

      // Conta o total de jogos (purchases) para transações PAY_PURCHASE
      if ($type === 'PAY_PURCHASE' && $purchase) {
        $shouldCountPurchase = true;

        // Se tiver um 'PAY_PURCHASE_WITHDRAWAL' depois de ter sido criada
        $withdrawal = Transactions::where('purchase_id', $purchase->id)
          ->where('type', 'PAY_PURCHASE_WITHDRAWAL')
          ->where('created_at', '>', $transaction->created_at)
          ->first();
        if ($withdrawal) {
          continue;
        }

        // Se há filtro de vendedor, conta purchases onde o vendedor é o selecionado
        if ($isSellerFilter) {
          $shouldCountPurchase = ($purchase->seller_id == $selectedUserId);
        }
        // Se há filtro de apostador, conta purchases onde o apostador é o selecionado
        elseif ($hasUserFilter) {
          $shouldCountPurchase = ($purchase->user_id == $selectedUserId);
        }
        // Se não há filtro específico mas é seller, conta purchases onde o seller_id corresponde ao usuário logado
        elseif (!$hasUserFilter && Auth::user()->role->level_id === 'seller') {
          $shouldCountPurchase = ($purchase->seller_id == $selectedUserId);
        }
        // Se não há filtro específico mas é admin, conta todas as purchases (sem filtro)
        // (já está como true por padrão)

        if ($shouldCountPurchase) {
          $totalGamesCount++;
        }
      }

      // 1) Agrupamento de pagamentos de jogos por concurso
      if ($type === 'PAY_PURCHASE' && $gameId && $purchase) {


        // Verifica se deve agrupar este jogo baseado no filtro
        $shouldGroup = true;

        // Se há filtro de vendedor, só agrupa jogos onde o vendedor da purchase corresponde ao vendedor selecionado
        if ($isSellerFilter) {
          $shouldGroup = ($purchase->seller_id == $selectedUserId);

          // Quando filtrar por vendedor, agrupa por concurso E usuário (apostador)
          if ($shouldGroup) {
            $groupKey = $gameId . '_' . $purchase->user_id; // Chave composta: game_id + user_id

            // Verifica se o vendedor criou o jogo (é o paid_by_user_id)
            $isCreator = ($purchase->paid_by_user_id == $selectedUserId);

            if (!isset($groupedByGame['game_payments'][$groupKey])) {
              $groupedByGame['game_payments'][$groupKey] = [
                'game_name' => $gameName,
                'user_name' => $purchase->user ? $purchase->user->name : 'N/A',
                'is_creator' => $isCreator, // Flag para identificar se o vendedor criou
                'count' => 0,
                'total' => 0,
              ];
            }

            $groupedByGame['game_payments'][$groupKey]['count']++;
            $groupedByGame['game_payments'][$groupKey]['total'] += $amount;
            continue;
          }
        }
        // Se há filtro de apostador, só agrupa jogos onde o apostador da purchase corresponde ao apostador selecionado
        elseif ($hasUserFilter) {
          $shouldGroup = ($purchase->user_id == $selectedUserId);
        }

        if ($shouldGroup) {
          // Agrupamento normal (apenas por game_id) quando não for filtro de vendedor
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
      }

      // 2) Agrupamento de comissão do vendedor por concurso
      if ($type === 'PAY_PURCHASE_COMISSION' && $purchase && $gameId) {


        // Se tiver um 'PAY_PURCHASE_COMISSION_WITHDRAWAL' depois de ter sido criada
        $withdrawal = Transactions::where('purchase_id', $purchase->id)
          ->where('type', 'PAY_PURCHASE_COMISSION_WITHDRAWAL')
          ->where('created_at', '>', $transaction->created_at)
          ->first();

        if ($withdrawal) {
          continue;
        }

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
          //$rowTypeLabel = 'Saída personalizada';
          $rowTypeLabel = $transaction->description;
          break;
        case 'PAY_PURCHASE':
          $rowTypeLabel = 'Pagamento de jogos';
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

      // Determina qual usuário mostrar para esta transação
      $userName = null;
      if ($transaction->user) {
        $userName = $transaction->user->name;
      }
      // Para prêmios, mostra o usuário que ganhou (purchase.user)
      if ($type === 'PAY_AWARD' && $purchase && $purchase->user) {
        $userName = $purchase->user->name;
      }

      $detailedRows[] = [
        'type' => $rowTypeLabel,
        'game_name' => $gameName,
        'quantity' => '-',
        'total' => $amount,
        'category' => $typeCategories[$type],
        'user_name' => $userName,
      ];
    }

    // Calculate total games (total de purchases/jogos, não apenas concursos diferentes)
    $userInfo['total_games'] = $totalGamesCount;

    // Monta linhas para paginação do resumo detalhado
    $rows = [];

    // Pagamentos de jogos por concurso
    foreach ($groupedByGame['game_payments'] as $key => $data) {
      // Se for filtro de vendedor, verifica se ele criou o jogo
      if ($isSellerFilter) {
        // Se o vendedor criou o jogo (paid_by_user_id), mostra "Pagamento de jogos"
        // Se não criou, mostra "Jogo do Apostador"
        $typeLabel = (isset($data['is_creator']) && $data['is_creator']) ? 'Pagamento de jogos' : 'Jogo do Apostador';
      } else {
        $typeLabel = 'Pagamento de jogos';
      }

      $rows[] = [
        'type' => $typeLabel,
        'game_name' => $data['game_name'],
        'quantity' => $data['count'],
        'total' => $data['total'],
        'category' => 'outcome',
        'user_name' => isset($data['user_name']) ? $data['user_name'] : null,
      ];
    }

    // Comissão do vendedor por concurso (apenas para vendedores e admin)
    if (Auth::user()->role->level_id !== 'gambler') {
      foreach ($groupedByGame['seller_commissions'] as $gameId => $data) {
        $rows[] = [
          'type' => 'Comissão do vendedor',
          'game_name' => $data['game_name'],
          'quantity' => $data['count'],
          'total' => $data['total'],
          'category' => 'income',
        ];
      }
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
   * Generate PDF for summary
   */
  public function summaryPdf(Request $request)
  {
    // Only admin and seller can generate PDF
    if (!in_array(Auth::user()->role->level_id, ['admin', 'seller'])) {
      return redirect()->back()->with('error', 'Acesso negado.');
    }

    // Reuse the same logic from summary method but without pagination
    $builder = new Transactions();
    $hasExplicitStart = $request->has('start_date') && $request->start_date != '';
    $hasExplicitEnd = $request->has('end_date') && $request->end_date != '';
    $hasExplicitPeriod = $hasExplicitStart || $hasExplicitEnd;
    $hasSelectedMonth = $request->has('month') && $request->month != '';
    $selectedMonth = null;

    // Filter by user if not admin
    if (Auth::user()->role->level_id !== 'admin') {
      // Se for vendedor, inclui também transações PAY_PURCHASE onde purchase.seller_id = vendedor
      // (jogos criados pelos apostadores vinculados)
      if (Auth::user()->role->level_id === 'seller') {
        $builder = $builder->where(function ($query) {
          $query->where('user_id', Auth::user()->id)
            ->orWhere(function ($q) {
              $q->where('type', 'PAY_PURCHASE')
                ->whereHas('purchase', function ($purchaseQuery) {
                  $purchaseQuery->where('seller_id', Auth::user()->id);
                });
            });
        });
      } else {
        $builder = $builder->where('user_id', Auth::user()->id);
      }
    }

    // Apply month filter
    if ($hasSelectedMonth || !$hasExplicitPeriod) {
      $month = $hasSelectedMonth ? (int)$request->month : (int)date('n');
      $year = date('Y');
      $selectedMonth = $month;

      $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
      $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));
      $endDate = date('Y-m-' . $lastDay, mktime(0, 0, 0, $month, 1, $year));

      $builder = $builder->whereDate('created_at', '>=', $startDate)
        ->whereDate('created_at', '<=', $endDate);
    }

    if ($hasExplicitStart) {
      $builder = $builder->whereDate('created_at', '>=', $request->start_date);
    }

    if ($hasExplicitEnd) {
      $builder = $builder->whereDate('created_at', '<=', $request->end_date);
    }

    $users = User::without(['invited_by'])->get();
    $games = Game::without(['awards'])->orderBy('created_at', 'DESC')->get();

    // Filter by user if provided (only for admin)
    // Unifica os filtros de vendedor e apostador em um único filtro de usuário
    $selectedUserId = Auth::user()->id;
    $hasUserFilter = false;
    $isSellerFilter = false;

    // Verifica se o usuário logado é vendedor (quando acessa diretamente, sem filtro de admin)
    if (Auth::user()->role->level_id === 'seller') {
      $selectedUser = Auth::user();
      $isSellerFilter = true;
    }

    if (Auth::user()->role->level_id === 'admin' && $request->has('user_id') && $request->user_id != '') {
      $selectedUserId = $request->user_id;
      $hasUserFilter = true;
      $selectedUser = User::find($selectedUserId);

      // Verifica se o usuário é vendedor
      $isSellerFilter = $selectedUser && $selectedUser->role_user_id == 2;

      if ($isSellerFilter) {
        // Se for vendedor, mostra:
        // 1) Transações relacionadas a purchases onde ele foi o vendedor
        // 2) OU transações do próprio vendedor (user_id) que não têm purchase (DEPOSIT, WITHDRAWAL, REFER_EARN, etc.)
        $builder = $builder->where(function ($query) use ($selectedUserId) {
          $query->whereHas('purchase', function ($q) use ($selectedUserId) {
            $q->where('seller_id', $selectedUserId);
          })->orWhere('user_id', $selectedUserId);
        });
      } else {
        // Se for apostador ou outro tipo, filtra apenas por user_id da transação
        $builder = $builder->where('user_id', $selectedUserId);
      }
    }

    if ($request->has('game_id') && ($request->game_id) != 'all' && $request->game_id != '') {
      $builder = $builder->where('game_id', $request->game_id);

      if ((!$request->has('end_date') || $request->end_date == '') && (!$request->has('month') || $request->month == '')) {
        $lastClosedHistory = GameHistory::where('game_id', $request->game_id)
          ->where('type', 'OPENED')
          ->orderBy('created_at', 'DESC')
          ->first();

        if ($lastClosedHistory) {
          $builder = $builder->whereDate('created_at', '>=', $lastClosedHistory->created_at);
        }
      }
    }

    // Get all transactions for summary with relationships (no pagination)
    $transactions = $builder->with(['game', 'purchase', 'purchase.seller', 'purchase.paid_by_user', 'purchase.user', 'purchase.userAwards'])->orderBy('created_at', 'DESC')->get();

    // Get selected user info
    $selectedUser = User::find($selectedUserId);

    $userInfo = [
      'name' => $selectedUser->name ?? 'N/A',
      'comission_percent' => ($selectedUser->comission_percent ?? 0) * 100,
      'total_games' => 0,
      'game_credit' => $selectedUser->game_credit ?? 0,
      'game_credit_limit' => $selectedUser->game_credit_limit ?? 0,
      'credit_debt' => max(0, ($selectedUser->game_credit_limit ?? 0) - ($selectedUser->game_credit ?? 0)),
    ];

    // Same logic as summary method to build rows
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

    $totalIncome = 0;
    $totalOutcome = 0;
    $groupedByGame = [
      'game_payments' => [],
      'seller_commissions' => [],
    ];
    $detailedRows = [];
    $uniqueGames = [];
    $totalGamesCount = 0; // Contador total de jogos (purchases)

    foreach ($transactions as $transaction) {
      $amount = $transaction->amount;
      $type = $transaction->type;

      if (!isset($typeCategories[$type])) {
        continue;
      }

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

      // Conta jogos baseado no tipo de filtro aplicado
      if ($gameId) {
        $shouldCountGame = true;
        // Se há filtro de vendedor, conta jogos onde o vendedor é o selecionado (purchase.seller_id)
        // Se há filtro de apostador, conta jogos onde o apostador é o selecionado (purchase.user_id)
        if ($purchase) {
          if ($isSellerFilter) {
            $shouldCountGame = ($purchase->seller_id == $selectedUserId);
          } elseif ($hasUserFilter) {
            $shouldCountGame = ($purchase->user_id == $selectedUserId);
          }
        }

        if ($shouldCountGame) {
          $uniqueGames[$gameId] = $gameName;
        }
      }

      // Conta o total de jogos (purchases) para transações PAY_PURCHASE
      if ($type === 'PAY_PURCHASE' && $purchase) {
        $shouldCountPurchase = true;


        // Se tiver um 'PAY_PURCHASE_WITHDRAWAL' depois de ter sido criada
        $withdrawal = Transactions::where('purchase_id', $purchase->id)
          ->where('type', 'PAY_PURCHASE_WITHDRAWAL')
          ->where('created_at', '>', $transaction->created_at)
          ->first();
        if ($withdrawal) {
          continue;
        }

        // Se há filtro de vendedor, conta purchases onde o vendedor é o selecionado
        if ($isSellerFilter) {
          $shouldCountPurchase = ($purchase->seller_id == $selectedUserId);
        }
        // Se há filtro de apostador, conta purchases onde o apostador é o selecionado
        elseif ($hasUserFilter) {
          $shouldCountPurchase = ($purchase->user_id == $selectedUserId);
        }
        // Se não há filtro específico mas é seller, conta purchases onde o seller_id corresponde ao usuário logado
        elseif (!$hasUserFilter && Auth::user()->role->level_id === 'seller') {
          $shouldCountPurchase = ($purchase->seller_id == $selectedUserId);
        }
        // Se não há filtro específico mas é admin, conta todas as purchases (sem filtro)
        // (já está como true por padrão)

        if ($shouldCountPurchase) {
          $totalGamesCount++;
        }
      }

      // 1) Agrupamento de pagamentos de jogos por concurso
      if ($type === 'PAY_PURCHASE' && $gameId && $purchase) {
        // Verifica se deve agrupar este jogo baseado no filtro
        $shouldGroup = true;

        // Se há filtro de vendedor, só agrupa jogos onde o vendedor da purchase corresponde ao vendedor selecionado
        if ($isSellerFilter) {
          $shouldGroup = ($purchase->seller_id == $selectedUserId);

          // Quando filtrar por vendedor, agrupa por concurso E usuário (apostador)
          if ($shouldGroup) {
            $groupKey = $gameId . '_' . $purchase->user_id; // Chave composta: game_id + user_id

            // Verifica se o vendedor criou o jogo (é o paid_by_user_id)
            $isCreator = ($purchase->paid_by_user_id == $selectedUserId);

            if (!isset($groupedByGame['game_payments'][$groupKey])) {
              $groupedByGame['game_payments'][$groupKey] = [
                'game_name' => $gameName,
                'user_name' => $purchase->user ? $purchase->user->name : 'N/A',
                'is_creator' => $isCreator, // Flag para identificar se o vendedor criou
                'count' => 0,
                'total' => 0,
              ];
            }

            $groupedByGame['game_payments'][$groupKey]['count']++;
            $groupedByGame['game_payments'][$groupKey]['total'] += $amount;
            continue;
          }
        }
        // Se há filtro de apostador, só agrupa jogos onde o apostador da purchase corresponde ao apostador selecionado
        elseif ($hasUserFilter) {
          $shouldGroup = ($purchase->user_id == $selectedUserId);
        }

        if ($shouldGroup) {
          // Agrupamento normal (apenas por game_id) quando não for filtro de vendedor
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
      }

      // 2) Agrupamento de comissão do vendedor por concurso
      if ($type === 'PAY_PURCHASE_COMISSION' && $purchase && $gameId) {

        // Se tiver um 'PAY_PURCHASE_COMISSION_WITHDRAWAL' depois de ter sido criada
        $withdrawal = Transactions::where('purchase_id', $purchase->id)
          ->where('type', 'PAY_PURCHASE_COMISSION_WITHDRAWAL')
          ->where('created_at', '>', $transaction->created_at)
          ->first();

        if ($withdrawal) {
          continue;
        }

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

      $rowTypeLabel = null;
      switch ($type) {
        case 'REFER_EARN':
          $rowTypeLabel = 'Bônus de indicação';
          break;
        case 'GAME_CREDIT':
          $rowTypeLabel = 'Adição de Credito';
          break;
        case 'PAY_AWARD':
          $purchase = $transaction->purchase;
          if (!$purchase || !$gameId) {
            continue 2;
          }
          $userAward = $purchase->userAwards
            ->where('status', 'PAID')
            ->where('game_id', $gameId)
            ->sortByDesc('created_at')
            ->first();
          if (!$userAward) {
            continue 2;
          }
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
          $rowTypeLabel = $transaction->description;
          break;
        case 'CUSTOM_OUTCOME':
          $rowTypeLabel = 'Saída personalizada';
          break;
        case 'PAY_PURCHASE':
          $rowTypeLabel = 'Pagamento de jogos';
          break;
        case 'PAY_PURCHASE_COMISSION':
          $rowTypeLabel = 'Comissão de jogos';
          break;
        default:
          $rowTypeLabel = $type;
          break;
      }

      // Determina qual usuário mostrar para esta transação
      $userName = null;
      if ($transaction->user) {
        $userName = $transaction->user->name;
      }
      // Para prêmios, mostra o usuário que ganhou (purchase.user)
      if ($type === 'PAY_AWARD' && $purchase && $purchase->user) {
        $userName = $purchase->user->name;
      }

      $detailedRows[] = [
        'type' => $rowTypeLabel,
        'game_name' => $gameName,
        'quantity' => '-',
        'total' => $amount,
        'category' => $typeCategories[$type],
        'user_name' => $userName,
      ];
    }

    // Calculate total games (total de purchases/jogos, não apenas concursos diferentes)
    $userInfo['total_games'] = $totalGamesCount;

    $rows = [];
    foreach ($groupedByGame['game_payments'] as $key => $data) {
      // Se for filtro de vendedor, verifica se ele criou o jogo
      if ($isSellerFilter) {
        // Se o vendedor criou o jogo (paid_by_user_id), mostra "Pagamento de jogos"
        // Se não criou, mostra "Jogo do Apostador"
        $typeLabel = (isset($data['is_creator']) && $data['is_creator']) ? 'Pagamento de jogos' : 'Jogo do Apostador';
      } else {
        $typeLabel = 'Pagamento de jogos';
      }

      $rows[] = [
        'type' => $typeLabel,
        'game_name' => $data['game_name'],
        'quantity' => $data['count'],
        'total' => $data['total'],
        'category' => 'outcome',
        'user_name' => isset($data['user_name']) ? $data['user_name'] : null,
      ];
    }

    // Comissão do vendedor por concurso (apenas para vendedores e admin)
    if (Auth::user()->role->level_id !== 'gambler') {
      foreach ($groupedByGame['seller_commissions'] as $gameId => $data) {
        $rows[] = [
          'type' => 'Comissão do vendedor',
          'game_name' => $data['game_name'],
          'quantity' => $data['count'],
          'total' => $data['total'],
          'category' => 'income',
        ];
      }
    }

    foreach ($detailedRows as $detailRow) {
      $rows[] = $detailRow;
    }

    // Calculate net value
    $net = $totalIncome - $totalOutcome;

    // Get filter info
    $filterInfo = [
      'game' => $request->has('game_id') && $request->game_id != 'all' ? $games->firstWhere('id', $request->game_id) : null,
      'month' => $selectedMonth,
      'seller' => $request->has('seller') && $request->seller != '' ? $users->firstWhere('id', $request->seller) : null,
    ];

    $pdf = Pdf::loadView('pdf.summary_report', compact(
      'totalIncome',
      'totalOutcome',
      'net',
      'userInfo',
      'rows',
      'filterInfo',
      'selectedUser'
    ))->setPaper('a4', 'portrait');

    return $pdf->download('resumo_transacoes_' . date('Y-m-d') . '.pdf');
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

    // Update user game_credit (saldo único)
    if ($type === 'CUSTOM_INCOME') {
      $user->game_credit += $request->amount;
    } else {
      $user->game_credit -= $request->amount;
    }
    $user->save();

    $redirectParams = [];
    if ($request->has('filter_game_id') && $request->filter_game_id) {
      $redirectParams['game_id'] = $request->filter_game_id;
    }
    if ($request->has('filter_month') && $request->filter_month) {
      $redirectParams['month'] = $request->filter_month;
    }
    if ($request->has('filter_user_id') && $request->filter_user_id) {
      $redirectParams['user_id'] = $request->filter_user_id;
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
