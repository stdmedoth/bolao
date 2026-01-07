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


  /**
   * Constrói a query builder com todos os filtros aplicados
   */
  private function buildSummaryQuery(Request $request)
  {
    $builder = new Transactions();
    $hasExplicitStart = $request->has('start_date') && $request->start_date != '';
    $hasExplicitEnd = $request->has('end_date') && $request->end_date != '';
    $hasExplicitPeriod = $hasExplicitStart || $hasExplicitEnd;
    $hasSelectedMonth = $request->has('month') && $request->month != '';

    // Filtro por usuário se não for admin
    if (Auth::user()->role->level_id !== 'admin') {
      if (Auth::user()->role->level_id === 'seller') {
        // Vendedor: inclui transações próprias + PAY_PURCHASE e PAY_PURCHASE_COMISSION onde ele é o vendedor
        $builder = $builder->where(function ($query) {
          $query->where('user_id', Auth::user()->id)
            ->orWhere(function ($q) {
              $q->whereIn('type', ['PAY_PURCHASE', 'PAY_PURCHASE_COMISSION'])
                ->whereHas('purchase', function ($purchaseQuery) {
                  $purchaseQuery->where('seller_id', Auth::user()->id);
                });
            });
        });
      } else {
        $builder = $builder->where('user_id', Auth::user()->id);
      }
    }

    // Aplicar filtro de mês
    if ($hasSelectedMonth || !$hasExplicitPeriod) {
      $month = $hasSelectedMonth ? (int)$request->month : (int)date('n');
      $year = date('Y');
      $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
      $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));
      $endDate = date('Y-m-' . $lastDay, mktime(0, 0, 0, $month, 1, $year));

      $builder = $builder->whereDate('created_at', '>=', $startDate)
        ->whereDate('created_at', '<=', $endDate);
    }

    // Aplicar filtros de data explícitos (compatibilidade)
    if ($hasExplicitStart) {
      $builder = $builder->whereDate('created_at', '>=', $request->start_date);
    }
    if ($hasExplicitEnd) {
      $builder = $builder->whereDate('created_at', '<=', $request->end_date);
    }

    // Filtro por usuário (apenas para admin)
    if (Auth::user()->role->level_id === 'admin' && $request->has('user_id') && $request->user_id != '') {
      $selectedUserId = $request->user_id;
      $selectedUser = User::find($selectedUserId);
      $isSellerFilter = $selectedUser && $selectedUser->role_user_id == 2;

      if ($isSellerFilter) {
        // Vendedor: transações relacionadas a purchases onde ele foi vendedor OU transações próprias
        $builder = $builder->where(function ($query) use ($selectedUserId) {
          $query->whereHas('purchase', function ($q) use ($selectedUserId) {
            $q->where('seller_id', $selectedUserId);
          })->orWhere('user_id', $selectedUserId);
        });
      } else {
        // Apostador ou outro: filtra apenas por user_id da transação
        $builder = $builder->where('user_id', $selectedUserId);
      }
    }

    // Filtro por jogo
    if ($request->has('game_id') && ($request->game_id) != 'all' && $request->game_id != '') {
      $builder = $builder->where('game_id', $request->game_id);

      // Se não houver data/mês especificado, usa data da última abertura do jogo
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

    return $builder;
  }

  /**
   * Determina informações sobre o filtro de usuário aplicado
   */
  private function determineUserFilter(Request $request)
  {
    $selectedUserId = Auth::user()->id;
    $hasUserFilter = false;
    $isSellerFilter = false;

    // Verifica se o usuário logado é vendedor (acesso direto, sem filtro de admin)
    if (Auth::user()->role->level_id === 'seller') {
      $isSellerFilter = true;
    }

    // Se admin selecionou um usuário específico
    if (Auth::user()->role->level_id === 'admin' && $request->has('user_id') && $request->user_id != '') {
      $selectedUserId = $request->user_id;
      $hasUserFilter = true;
      $selectedUser = User::find($selectedUserId);
      $isSellerFilter = $selectedUser && $selectedUser->role_user_id == 2;
    }

    return [
      'selectedUserId' => $selectedUserId,
      'hasUserFilter' => $hasUserFilter,
      'isSellerFilter' => $isSellerFilter,
    ];
  }

  /**
   * Constrói informações do usuário para exibição
   */
  private function buildUserInfo($selectedUser)
  {
    return [
      'name' => $selectedUser->name ?? 'N/A',
      'comission_percent' => ($selectedUser->comission_percent ?? 0) * 100,
      'total_games' => 0,
      'game_credit' => $selectedUser->game_credit ?? 0,
      'game_credit_limit' => $selectedUser->game_credit_limit ?? 0,
      'credit_debt' => max(0, ($selectedUser->game_credit_limit ?? 0) - ($selectedUser->game_credit ?? 0)),
    ];
  }

  /**
   * Processa todas as transações e calcula totais, agrupamentos e linhas detalhadas
   */
  private function processTransactions($transactions, $selectedUserId, $isSellerFilter, $hasUserFilter)
  {
    $typeCategories = $this->getTransactionTypeCategories();
    
    // Pré-carrega todos os estornos de uma vez para evitar N+1 queries
    $withdrawalsMap = $this->loadWithdrawalsMap($transactions);
    
    $totalIncome = 0;
    $totalOutcome = 0;
    $groupedByGame = [
      'game_payments' => [],
      'seller_commissions' => [],    // Comissões do vendedor quando vendedor pagou (agrupadas por concurso)
      'gambler_commissions_seller' => [], // Comissões do vendedor quando apostador pagou (agrupadas por concurso)
      'admin_commissions' => [],    // Comissões do administrador (agrupadas por concurso)
      'gambler_commissions' => [],   // Comissões do apostador (não agrupadas, ficam detalhadas)
    ];
    $detailedRows = [];
    $uniqueGames = [];
    $totalGamesCount = 0;

    foreach ($transactions as $transaction) {
      $amount = $transaction->amount;
      $type = $transaction->type;

      // Ignora tipos não categorizados
      if (!isset($typeCategories[$type])) {
        continue;
      }

      // Ignora estornos no resumo detalhado
      if ($this->isReversalType($type)) {
        continue;
      }

      // Verifica se REFER_EARN foi estornado antes de acumular totais
      if ($type === 'REFER_EARN') {
        $referEarnKey = $transaction->user_id . '_' . $transaction->amount;
        if (isset($withdrawalsMap['refer_earn_withdrawals'][$referEarnKey])) {
          $withdrawals = $withdrawalsMap['refer_earn_withdrawals'][$referEarnKey];
          foreach ($withdrawals as $withdrawal) {
            if ($withdrawal->created_at > $transaction->created_at) {
              // Este bônus foi estornado, não deve ser contado nos totais
              continue 2; // Pula para a próxima transação
            }
          }
        }
      }

      // Acumula totais
      if ($typeCategories[$type] === 'income') {
        $totalIncome += $amount;
      } else {
        $totalOutcome += $amount;
      }

      $game = $transaction->game;
      $purchase = $transaction->purchase;
      $gameId = $game ? $game->id : null;
      $gameName = $game ? $game->name : 'N/A';

      // Conta jogos únicos
      $this->countUniqueGames($gameId, $gameName, $purchase, $isSellerFilter, $hasUserFilter, $selectedUserId, $uniqueGames);

      // Conta total de purchases (jogos)
      if ($type === 'PAY_PURCHASE' && $purchase) {
        if ($this->shouldCountPurchase($transaction, $purchase, $isSellerFilter, $hasUserFilter, $selectedUserId, $withdrawalsMap)) {
          $totalGamesCount++;
        }
      }

      // Tenta agrupar pagamentos de jogos
      if ($this->tryGroupGamePayment($transaction, $type, $gameId, $gameName, $purchase, $isSellerFilter, $hasUserFilter, $selectedUserId, $groupedByGame, $withdrawalsMap)) {
        continue;
      }

      // Tenta agrupar comissões do vendedor
      if ($this->tryGroupSellerCommission($transaction, $type, $purchase, $gameId, $gameName, $groupedByGame, $withdrawalsMap, $isSellerFilter, $selectedUserId)) {
        continue;
      }

      // Cria linha detalhada para transações não agrupadas
      $detailedRow = $this->createDetailedRow($transaction, $type, $gameName, $amount, $typeCategories, $withdrawalsMap);
      if ($detailedRow !== null) {
        $detailedRows[] = $detailedRow;
      }
    }

    return [
      'totalIncome' => $totalIncome,
      'totalOutcome' => $totalOutcome,
      'groupedByGame' => $groupedByGame,
      'detailedRows' => $detailedRows,
      'totalGamesCount' => $totalGamesCount,
    ];
  }

  /**
   * Retorna categorias de tipos de transação (income/outcome)
   */
  private function getTransactionTypeCategories()
  {
    return [
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
  }

  /**
   * Verifica se o tipo é um estorno
   */
  private function isReversalType($type)
  {
    return in_array($type, [
      'PAY_PURCHASE_WITHDRAWAL',
      'PAY_PURCHASE_COMISSION_WITHDRAWAL',
      'REFER_EARN_REVERSAL',
      'GAME_CREDIT_REVERSAL',
      'PAY_AWARD_WITHDRAWAL',
      'DEPOSIT_REVERSAL',
      'WITHDRAWAL_REVERSAL'
    ]);
  }

  /**
   * Conta jogos únicos baseado nos filtros aplicados
   */
  private function countUniqueGames($gameId, $gameName, $purchase, $isSellerFilter, $hasUserFilter, $selectedUserId, &$uniqueGames)
  {
    if (!$gameId) {
      return;
    }

    $shouldCountGame = true;
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

  /**
   * Pré-carrega todos os estornos em um mapa para evitar N+1 queries
   */
  private function loadWithdrawalsMap($transactions)
  {
    // Coleta todos os purchase_ids únicos
    $purchaseIds = [];
    // Coleta user_ids e amounts para verificar estornos de REFER_EARN
    $referEarnKeys = [];
    
    foreach ($transactions as $transaction) {
      if ($transaction->purchase_id) {
        $purchaseIds[$transaction->purchase_id] = true;
      }
      if ($transaction->type === 'REFER_EARN') {
        // Cria uma chave única: user_id + amount para identificar estornos
        $key = $transaction->user_id . '_' . $transaction->amount;
        $referEarnKeys[$key] = true;
      }
    }
    
    $uniquePurchaseIds = array_keys($purchaseIds);
    $uniqueReferEarnKeys = array_keys($referEarnKeys);
    
    $result = [
      'purchase_withdrawals' => [],
      'commission_withdrawals' => [],
      'award_withdrawals' => [],
      'refer_earn_withdrawals' => [],
    ];
    
    if (!empty($uniquePurchaseIds)) {
      // Carrega todos os estornos de uma vez (apenas campos necessários)
      $result['purchase_withdrawals'] = Transactions::whereIn('purchase_id', $uniquePurchaseIds)
        ->where('type', 'PAY_PURCHASE_WITHDRAWAL')
        ->select('id', 'purchase_id', 'created_at')
        ->get()
        ->groupBy('purchase_id');
      
      $result['commission_withdrawals'] = Transactions::whereIn('purchase_id', $uniquePurchaseIds)
        ->where('type', 'PAY_PURCHASE_COMISSION_WITHDRAWAL')
        ->select('id', 'purchase_id', 'created_at')
        ->get()
        ->groupBy('purchase_id');
      
      $result['award_withdrawals'] = Transactions::whereIn('purchase_id', $uniquePurchaseIds)
        ->where('type', 'PAY_AWARD_WITHDRAWAL')
        ->select('id', 'user_award_id', 'purchase_id', 'created_at')
        ->get()
        ->groupBy('user_award_id');
    }
    
    // Carrega estornos de REFER_EARN baseado em user_id e amount
    if (!empty($uniqueReferEarnKeys)) {
      $referEarnWithdrawals = Transactions::where('type', 'REFER_EARN_REVERSAL')
        ->select('id', 'user_id', 'amount', 'created_at')
        ->get();
      
      // Agrupa por chave user_id_amount
      $groupedReferEarnWithdrawals = [];
      foreach ($referEarnWithdrawals as $withdrawal) {
        $key = $withdrawal->user_id . '_' . $withdrawal->amount;
        if (!isset($groupedReferEarnWithdrawals[$key])) {
          $groupedReferEarnWithdrawals[$key] = [];
        }
        $groupedReferEarnWithdrawals[$key][] = $withdrawal;
      }
      
      $result['refer_earn_withdrawals'] = $groupedReferEarnWithdrawals;
    }
    
    return $result;
  }

  /**
   * Verifica se uma purchase deve ser contada no total de jogos
   */
  private function shouldCountPurchase($transaction, $purchase, $isSellerFilter, $hasUserFilter, $selectedUserId, $withdrawalsMap)
  {
    // Verifica se há estorno posterior usando o mapa pré-carregado
    if (isset($withdrawalsMap['purchase_withdrawals'][$purchase->id])) {
      $withdrawals = $withdrawalsMap['purchase_withdrawals'][$purchase->id];
      foreach ($withdrawals as $withdrawal) {
        if ($withdrawal->created_at > $transaction->created_at) {
          return false;
        }
      }
    }

    // Aplica filtros
    if ($isSellerFilter) {
      return ($purchase->seller_id == $selectedUserId);
    } elseif ($hasUserFilter) {
      return ($purchase->user_id == $selectedUserId);
    } elseif (!$hasUserFilter && Auth::user()->role->level_id === 'seller') {
      return ($purchase->seller_id == $selectedUserId);
    }

    return true;
  }

  /**
   * Tenta agrupar pagamento de jogo. Retorna true se agrupou (e deve continuar)
   */
  private function tryGroupGamePayment($transaction, $type, $gameId, $gameName, $purchase, $isSellerFilter, $hasUserFilter, $selectedUserId, &$groupedByGame, $withdrawalsMap)
  {
    if ($type !== 'PAY_PURCHASE' || !$gameId || !$purchase) {
      return false;
    }

    // Verifica se há estorno posterior usando o mapa pré-carregado
    if (isset($withdrawalsMap['purchase_withdrawals'][$purchase->id])) {
      $withdrawals = $withdrawalsMap['purchase_withdrawals'][$purchase->id];
      foreach ($withdrawals as $withdrawal) {
        if ($withdrawal->created_at > $transaction->created_at) {
          return true; // Ignora esta transação (já foi estornada)
        }
      }
    }

    $shouldGroup = true;

    // Filtro de vendedor: agrupa por concurso E usuário (apostador) apenas se o vendedor criou o jogo
    // Se não criou, agrupa apenas por concurso (Jogo do Apostador)
    if ($isSellerFilter) {
      $shouldGroup = ($purchase->seller_id == $selectedUserId);
      if ($shouldGroup) {
        $isCreator = ($purchase->paid_by_user_id == $selectedUserId);

        if(!$purchase->paid_by_user) return true;
        $payerRole = $purchase->paid_by_user->role ? $purchase->paid_by_user->role->level_id : 'N/A';
        
        // Se o vendedor criou o jogo, agrupa por concurso + usuário
        if ($isCreator) {
          $groupKey = $gameId . '_' . $purchase->user_id;
          
          if (!isset($groupedByGame['game_payments'][$groupKey])) {
            $groupedByGame['game_payments'][$groupKey] = [
              'game_name' => $gameName,
              'user_name' => $purchase->user ? $purchase->user->name : 'N/A',
              'is_creator' => true,
              'payer_role' => $payerRole,
              'count' => 0,
              'total' => 0,
            ];
          }
        } else {
          // Agrupa apenas por concurso para "Jogo do Apostador" ou "Jogo do Administrador"
          // Usa uma chave prefixada para diferenciar do agrupamento normal
          $groupKey = 'gambler_' . $gameId . '_' . $payerRole;
          
          if (!isset($groupedByGame['game_payments'][$groupKey])) {
            $groupedByGame['game_payments'][$groupKey] = [
              'game_name' => $gameName,
              'user_name' => null, // Não mostra usuário específico quando agrupado por concurso
              'is_creator' => false,
              'payer_role' => $payerRole,
              'count' => 0,
              'total' => 0,
            ];
          }
        }

        $groupedByGame['game_payments'][$groupKey]['count']++;
        $groupedByGame['game_payments'][$groupKey]['total'] += $transaction->amount;
        return true;
      }
    } elseif ($hasUserFilter) {
      $shouldGroup = ($purchase->user_id == $selectedUserId);
    }

    if ($shouldGroup) {
      // Agrupamento normal (apenas por game_id)
      if (!isset($groupedByGame['game_payments'][$gameId])) {
        $groupedByGame['game_payments'][$gameId] = [
          'game_name' => $gameName,
          'count' => 0,
          'total' => 0,
        ];
      }

      $groupedByGame['game_payments'][$gameId]['count']++;
      $groupedByGame['game_payments'][$gameId]['total'] += $transaction->amount;
      return true;
    }

    return false;
  }

  /**
   * Tenta agrupar comissões por tipo. Retorna true se agrupou (e deve continuar)
   */
  private function tryGroupSellerCommission($transaction, $type, $purchase, $gameId, $gameName, &$groupedByGame, $withdrawalsMap, $isSellerFilter, $selectedUserId)
  {
    if ($type !== 'PAY_PURCHASE_COMISSION' || !$purchase || !$gameId) {
      return false;
    }

    // Verifica se há estorno posterior usando o mapa pré-carregado
    if (isset($withdrawalsMap['commission_withdrawals'][$purchase->id])) {
      $withdrawals = $withdrawalsMap['commission_withdrawals'][$purchase->id];
      foreach ($withdrawals as $withdrawal) {
        if ($withdrawal->created_at > $transaction->created_at) {
          return true; // Ignora esta transação
        }
      }
    }

    // Identifica o tipo de comissão baseado no papel do usuário que recebeu
    $commissionRecipient = $transaction->user;
    $commissionSellerId = $purchase->seller_id;
    $isSellerCommission = ($commissionSellerId == $transaction->user_id);
    $isSellerTarget = $isSellerFilter && $commissionSellerId == $selectedUserId;
    
    // Se não é comissão do vendedor, verifica se é admin ou apostador
    if (!$isSellerTarget && !$isSellerCommission && $commissionRecipient && $commissionRecipient->role) {
      $recipientRole = $commissionRecipient->role->level_id;
      
      // Comissão do administrador: agrupa por concurso
      if ($recipientRole === 'admin') {
        if (!isset($groupedByGame['admin_commissions'][$gameId])) {
          $groupedByGame['admin_commissions'][$gameId] = [
            'game_name' => $gameName,
            'count' => 0,
            'total' => 0,
          ];
        }
        $groupedByGame['admin_commissions'][$gameId]['count']++;
        $groupedByGame['admin_commissions'][$gameId]['total'] += $transaction->amount;
        return true;
      }
      
      // Comissão do apostador: não agrupa, fica detalhada
      if ($recipientRole === 'gambler') {
        // Não agrupa, deixa passar para criar linha detalhada
        return false;
      }
    }

    // Comissão do vendedor: agrupa sempre que a compra pertence ao seller filtrado,
    // ou quando o próprio seller recebeu a comissão
    if ($isSellerTarget || $isSellerCommission) {
      // Verifica quem pagou o jogo para separar as comissões
      $paidByUserId = $purchase->paid_by_user_id;
      $paidByUser = $purchase->paid_by_user;
      
      // Se o vendedor pagou o jogo (criou o jogo), agrupa em seller_commissions
      if ($paidByUserId == $selectedUserId) {
        if (!isset($groupedByGame['seller_commissions'][$gameId])) {
          $groupedByGame['seller_commissions'][$gameId] = [
            'game_name' => $gameName,
            'count' => 0,
            'total' => 0,
          ];
        }
        $groupedByGame['seller_commissions'][$gameId]['count']++;
        $groupedByGame['seller_commissions'][$gameId]['total'] += $transaction->amount;
        return true;
      }
      
      // Se um apostador pagou o jogo, agrupa em gambler_commissions_seller
      if ($paidByUser && $paidByUser->role && $paidByUser->role->level_id === 'gambler') {
        if (!isset($groupedByGame['gambler_commissions_seller'][$gameId])) {
          $groupedByGame['gambler_commissions_seller'][$gameId] = [
            'game_name' => $gameName,
            'count' => 0,
            'total' => 0,
          ];
        }
        $groupedByGame['gambler_commissions_seller'][$gameId]['count']++;
        $groupedByGame['gambler_commissions_seller'][$gameId]['total'] += $transaction->amount;
        return true;
      }
      
      // Fallback: se não conseguir determinar, agrupa em seller_commissions (comportamento original)
      if (!isset($groupedByGame['seller_commissions'][$gameId])) {
        $groupedByGame['seller_commissions'][$gameId] = [
          'game_name' => $gameName,
          'count' => 0,
          'total' => 0,
        ];
      }
      $groupedByGame['seller_commissions'][$gameId]['count']++;
      $groupedByGame['seller_commissions'][$gameId]['total'] += $transaction->amount;
      return true;
    }

    return false;
  }

  /**
   * Cria uma linha detalhada para uma transação não agrupada
   */
  private function createDetailedRow($transaction, $type, $gameName, $amount, $typeCategories, $withdrawalsMap)
  {
    $rowTypeLabel = $this->getTransactionTypeLabel($transaction, $type, $withdrawalsMap);
    
    // Se retornou null, significa que deve pular esta transação
    if ($rowTypeLabel === null) {
      return null;
    }

    // Determina qual usuário mostrar
    $userName = null;
    if ($transaction->user) {
      $userName = $transaction->user->name;
    }
    if ($type === 'PAY_AWARD' && $transaction->purchase && $transaction->purchase->user) {
      $userName = $transaction->purchase->user->name;
    }

    return [
      'type' => $rowTypeLabel,
      'game_name' => $gameName,
      'quantity' => '-',
      'total' => $amount,
      'category' => $typeCategories[$type],
      'user_name' => $userName,
      'transaction_id' => $transaction->id,
      'is_custom' => in_array($type, ['CUSTOM_INCOME', 'CUSTOM_OUTCOME']),
      'original_type' => $type,
    ];
  }

  /**
   * Retorna o label de tipo de transação para exibição
   */
  private function getTransactionTypeLabel($transaction, $type, $withdrawalsMap)
  {
    switch ($type) {
      case 'REFER_EARN':
        // Verifica se há estorno posterior
        $referEarnKey = $transaction->user_id . '_' . $transaction->amount;
        if (isset($withdrawalsMap['refer_earn_withdrawals'][$referEarnKey])) {
          $withdrawals = $withdrawalsMap['refer_earn_withdrawals'][$referEarnKey];
          foreach ($withdrawals as $withdrawal) {
            if ($withdrawal->created_at > $transaction->created_at) {
              return null; // Deve pular esta transação (foi estornada)
            }
          }
        }
        return 'Bônus de indicação';
      
      case 'GAME_CREDIT':
        return 'Adição de Credito';
      
      case 'PAY_AWARD':
        return $this->getAwardLabel($transaction, $withdrawalsMap);
      
      case 'WITHDRAWAL':
        return 'Saque';
      
      case 'DEPOSIT':
        return 'Depósito';
      
      case 'CUSTOM_INCOME':
        return $transaction->description;
      
      case 'CUSTOM_OUTCOME':
        return $transaction->description;
      
      case 'PAY_PURCHASE':
        return 'Pagamento de jogos';
      
      case 'PAY_PURCHASE_COMISSION':
        // Identifica o tipo de comissão para exibir o label correto
        $purchase = $transaction->purchase;
        if ($purchase && $transaction->user && $transaction->user->role) {
          //$isSellerCommission = ($purchase->seller_id == $transaction->user_id);
          //if ($isSellerCommission) {
          //  return 'Comissão de jogos do vendedor';
          //}
          $recipientRole = $transaction->user->role->level_id;
          if ($recipientRole === 'admin') {
            return 'Comissão de jogos do administrador';
          }
          if ($recipientRole === 'seller') {
            return 'Comissão de jogos do vendedor';
          }
          if ($recipientRole === 'gambler') {
            return 'Comissão de jogos do apostador';
          }
        }
        return 'Comissão de jogos';
      
      default:
        return $type;
    }
  }

  /**
   * Retorna o label para transações de prêmio
   */
  private function getAwardLabel($transaction, $withdrawalsMap)
  {
    $purchase = $transaction->purchase;
    $gameId = $transaction->game ? $transaction->game->id : null;

    // Verifica se há estorno posterior usando o mapa pré-carregado
    if ($transaction->user_award_id && isset($withdrawalsMap['award_withdrawals'][$transaction->user_award_id])) {
      $withdrawals = $withdrawalsMap['award_withdrawals'][$transaction->user_award_id];
      foreach ($withdrawals as $withdrawal) {
        if ($withdrawal->created_at > $transaction->created_at) {
          return null; // Deve pular esta transação
        }
      }
    }
    

    // Se não houver purchase ou game, ignora
    if (!$purchase || !$gameId) {
      return null;
    }

    // Busca prêmio pago associado
    $userAward = $purchase->userAwards
      ->where('status', 'PAID')
      ->where('id', $transaction->user_award_id)
      ->where('game_id', $gameId)
      ->sortByDesc('created_at')
      ->first();

    if (!$userAward) {
      return null; // Deve pular esta transação
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

    return $rowTypeLabel;
  }

  /**
   * Monta as linhas finais do resumo (agrupadas + detalhadas)
   */
  private function buildSummaryRows($groupedByGame, $detailedRows, $isSellerFilter)
  {
    $rows = [];

    // Adiciona pagamentos de jogos agrupados
    foreach ($groupedByGame['game_payments'] as $key => $data) {

      $total = $data['total'];
      if ($isSellerFilter) {
        $typeLabel = (isset($data['is_creator']) && $data['is_creator']) 
          ? 'Pagamento de jogos' 
          : ($data['payer_role'] === 'admin' ? 'Jogo do Administrador' : 'Jogo do Apostador');

          $total = (isset($data['is_creator']) && $data['is_creator']) 
          ? $data['total']
          : ($data['payer_role'] === 'admin' ? $data['total'] : 0);
      } else {
        $typeLabel = 'Pagamento de jogos';
      }

      $rows[] = [
        'type' => $typeLabel,
        'game_name' => $data['game_name'],
        'quantity' => $data['count'],
        'total' => $total,
        'category' => 'outcome',
        'user_name' => isset($data['user_name']) ? $data['user_name'] : null,
      ];
    }

    // Adiciona comissões agrupadas (apenas para vendedores e admin)
    if (Auth::user()->role->level_id !== 'gambler') {
      // Comissões do vendedor quando vendedor pagou (agrupadas por concurso)
      foreach ($groupedByGame['seller_commissions'] as $gameId => $data) {
        $rows[] = [
          'type' => 'Comissão de jogos do vendedor',
          'game_name' => $data['game_name'],
          'quantity' => $data['count'],
          'total' => $data['total'],
          'category' => 'income',
        ];
      }
      
      // Comissões do vendedor quando apostador pagou (agrupadas por concurso)
      foreach ($groupedByGame['gambler_commissions_seller'] as $gameId => $data) {
        $rows[] = [
          'type' => 'Comissão de jogos do apostador',
          'game_name' => $data['game_name'],
          'quantity' => $data['count'],
          'total' => $data['total'],
          'category' => 'income',
        ];
      }
      
      // Comissões do administrador (agrupadas por concurso)
      foreach ($groupedByGame['admin_commissions'] as $gameId => $data) {
        $rows[] = [
          'type' => 'Comissão de jogos do administrador',
          'game_name' => $data['game_name'],
          'quantity' => $data['count'],
          'total' => $data['total'],
          'category' => 'income',
        ];
      }
    }
    
    // Comissões do apostador não são agrupadas, ficam nas linhas detalhadas

    // Adiciona transações detalhadas
    foreach ($detailedRows as $detailRow) {
      $rows[] = $detailRow;
    }

    return $rows;
  }

  /**
   * Pagina as linhas do resumo
   */
  private function paginateSummaryRows($rows, Request $request)
  {
    $perPage = 20;
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $currentItems = array_slice($rows, ($currentPage - 1) * $perPage, $perPage);

    return new LengthAwarePaginator(
      $currentItems,
      count($rows),
      $perPage,
      $currentPage,
      [
        'path' => $request->url(),
        'query' => $request->query(),
      ]
    );
  }

  /**
   * Obtém o mês selecionado
   */
  private function getSelectedMonth(Request $request, $hasSelectedMonth)
  {
    $hasExplicitStart = $request->has('start_date') && $request->start_date != '';
    $hasExplicitEnd = $request->has('end_date') && $request->end_date != '';
    $hasExplicitPeriod = $hasExplicitStart || $hasExplicitEnd;

    if ($hasSelectedMonth || !$hasExplicitPeriod) {
      return $hasSelectedMonth ? (int)$request->month : (int)date('n');
    }

    return null;
  }

  public function summary(Request $request)
  {
    // 1. Construir query builder com todos os filtros
    $builder = $this->buildSummaryQuery($request);
    
    // 2. Obter transações com relacionamentos (otimizado para carregar apenas campos necessários)
    $transactions = $builder->with([
      'game:id,name',
      'user:id,name,role_user_id',
      'user.role:id,level_id',
      'purchase:id,user_id,seller_id,paid_by_user_id,game_id',
      'purchase.seller:id,name',
      'purchase.paid_by_user:id,role_user_id',
      'purchase.paid_by_user.role:id,level_id',
      'purchase.user:id,name',
      'purchase.userAwards:id,purchase_id,game_id,status,points,created_at',
      'purchase.userAwards.game_award:id,name'
    ])->orderBy('created_at', 'DESC')->get();

    // 3. Determinar informações do usuário selecionado
    $filterInfo = $this->determineUserFilter($request);
    $selectedUser = User::find($filterInfo['selectedUserId']);
    
    // 4. Preparar informações do usuário para exibição
    $userInfo = $this->buildUserInfo($selectedUser);
    
    // 5. Processar transações e calcular totais
    $processingResult = $this->processTransactions(
      $transactions, 
      $filterInfo['selectedUserId'], 
      $filterInfo['isSellerFilter'], 
      $filterInfo['hasUserFilter']
    );
    
    // 6. Atualizar total de jogos no userInfo
    $userInfo['total_games'] = $processingResult['totalGamesCount'];
    
    // 7. Montar linhas finais do resumo (agrupadas + detalhadas)
    $rows = $this->buildSummaryRows(
      $processingResult['groupedByGame'], 
      $processingResult['detailedRows'], 
      $filterInfo['isSellerFilter']
    );
    
    // 8. Paginar linhas do resumo
    $paginatedSummary = $this->paginateSummaryRows($rows, $request);
    
    // 9. Obter dados auxiliares para a view
    $users = User::without(['invited_by'])->get();
    $games = Game::without(['awards'])->orderBy('created_at', 'DESC')->get();
    $hasSelectedMonth = $request->has('month') && $request->month != '';
    $selectedMonth = $this->getSelectedMonth($request, $hasSelectedMonth);

    return view('content.transactions.summary', [
      'totalIncome' => $processingResult['totalIncome'],
      'totalOutcome' => $processingResult['totalOutcome'],
      'net' => $processingResult['totalIncome'] - $processingResult['totalOutcome'],
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

    // 1. Construir query builder com todos os filtros (mesma lógica do summary)
    $builder = $this->buildSummaryQuery($request);
    
    // 2. Obter transações com relacionamentos (sem paginação para PDF)
    $transactions = $builder->with([
      'game:id,name',
      'user:id,name,role_user_id',
      'user.role:id,level_id',
      'purchase:id,user_id,seller_id,paid_by_user_id,game_id',
      'purchase.seller:id,name',
      'purchase.paid_by_user:id,role_user_id',
      'purchase.paid_by_user.role:id,level_id',
      'purchase.user:id,name',
      'purchase.userAwards:id,purchase_id,game_id,status,points,created_at',
      'purchase.userAwards.game_award:id,name'
    ])->orderBy('created_at', 'DESC')->get();

    // 3. Determinar informações do usuário selecionado
    $filterInfo = $this->determineUserFilter($request);
    $selectedUser = User::find($filterInfo['selectedUserId']);
    
    // 4. Preparar informações do usuário para exibição
    $userInfo = $this->buildUserInfo($selectedUser);
    
    // 5. Processar transações e calcular totais (mesma lógica do summary)
    $processingResult = $this->processTransactions(
      $transactions, 
      $filterInfo['selectedUserId'], 
      $filterInfo['isSellerFilter'], 
      $filterInfo['hasUserFilter']
    );
    
    // 6. Atualizar total de jogos no userInfo
    $userInfo['total_games'] = $processingResult['totalGamesCount'];
    
    // 7. Montar linhas finais do resumo (agrupadas + detalhadas) - sem paginação
    $rows = $this->buildSummaryRows(
      $processingResult['groupedByGame'], 
      $processingResult['detailedRows'], 
      $filterInfo['isSellerFilter']
    );
    
    // 8. Obter dados auxiliares para o PDF
    $users = User::without(['invited_by'])->get();
    $games = Game::without(['awards'])->orderBy('created_at', 'DESC')->get();
    $hasSelectedMonth = $request->has('month') && $request->month != '';
    $selectedMonth = $this->getSelectedMonth($request, $hasSelectedMonth);

    // 9. Extrair variáveis para o PDF
    $totalIncome = $processingResult['totalIncome'];
    $totalOutcome = $processingResult['totalOutcome'];
    $net = $totalIncome - $totalOutcome;

    // 10. Preparar informações de filtro para o PDF
    $filterInfo = [
      'game' => $request->has('game_id') && $request->game_id != 'all' ? $games->firstWhere('id', $request->game_id) : null,
      'month' => $selectedMonth,
      'seller' => $request->has('seller') && $request->seller != '' ? $users->firstWhere('id', $request->seller) : null,
    ];

    // 11. Gerar PDF
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
  public function destroy(Request $request, Transactions $transaction)
  {
    // Apenas admin pode excluir lançamentos personalizados
    if (Auth::user()->role->level_id !== 'admin') {
      return redirect()->back()->with('error', 'Apenas administradores podem excluir lançamentos personalizados.');
    }

    // Garante que só transações customizadas possam ser removidas por aqui
    if (!in_array($transaction->type, ['CUSTOM_INCOME', 'CUSTOM_OUTCOME'])) {
      return redirect()->back()->with('error', 'Apenas lançamentos personalizados podem ser excluídos por esta ação.');
    }

    $user = $transaction->user;

    // Reverte o impacto no saldo único do usuário
    if ($user) {
      if ($transaction->type === 'CUSTOM_INCOME') {
        $user->game_credit -= $transaction->amount;
      } else {
        $user->game_credit += $transaction->amount;
      }
      $user->save();
    }

    $transaction->delete();

    // Mantém os filtros atuais do resumo ao redirecionar
    $redirectParams = [];
    if ($request->has('filter_game_id') && $request->filter_game_id) {
      $redirectParams['game_id'] = $request->filter_game_id;
    } elseif ($request->has('game_id') && $request->game_id) {
      $redirectParams['game_id'] = $request->game_id;
    }

    if ($request->has('filter_month') && $request->filter_month) {
      $redirectParams['month'] = $request->filter_month;
    } elseif ($request->has('month') && $request->month) {
      $redirectParams['month'] = $request->month;
    }

    if ($request->has('filter_user_id') && $request->filter_user_id) {
      $redirectParams['user_id'] = $request->filter_user_id;
    } elseif ($request->has('user_id') && $request->user_id) {
      $redirectParams['user_id'] = $request->user_id;
    }

    return redirect()->route('finances.summary', $redirectParams)
      ->with('success', 'Lançamento personalizado excluído com sucesso!');
  }
}
