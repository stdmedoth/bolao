<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Game;
use App\Models\GameAward;
use App\Models\GameHistory;
use App\Models\Purchase;
use App\Models\ReferEarn;
use App\Models\Transactions;
use App\Models\UserAwards;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;


class AdminController extends Controller
{

  public function editMeForm(Request $request)
  {
    $user = User::find(Auth::user()->id);

    $roles = RoleUser::all();

    return view('content.users.user_update', compact('user', 'roles'));
  }

  public function editUserForm($id)
  {
    $user = User::findOrFail($id);

    $roles = RoleUser::all();
    $sellers = User::where('role_user_id', 2)->get();


    return view('content.users.user_update', compact('user', 'roles', 'sellers'));
  }

  public function delete($id)
  {
    $user = User::findOrFail($id);
    $user->delete();
    return redirect()->route('list-user')->with('success', 'Usuário deletado com sucesso.');
  }


  public function edit($id)
  {
    $user = User::findOrFail($id);
    $roles = RoleUser::get(); // Para preencher as opções de papel
    $sellers = User::where('role_user_id', 2)->get();

    return view('usuarios.edit', compact('user', 'roles', 'sellers'));
  }

  // Cria um usuário (vendedor ou apostador)
  public function createUser(Request $request)
  {
    // Validação dos dados de entrada
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'document' => 'string|max:255|unique:users',
      'balance' => 'required',
      'game_credit' => 'required',
      //'game_credit_limit' => 'required',
      'comission_percent' => 'required',
      'phone' => 'required|string|max:255',
      'password' => 'required|string|min:6',
      'active_refer_earn' => 'boolean',
      'role_user_id' => 'required|exists:role_users,id',
      //'invited_by_id' => 'exists:users,id',
      'seller_id' => 'exists:users,id', // Adiciona a validação para seller_id
    ]);

    $formatFloatInputs = [
      'balance',
      'game_credit',
      'game_credit_limit',
      'comission_percent'
    ];
    foreach ($formatFloatInputs as $formatFloatInput) {
      if (isset($validatedData[$formatFloatInput])) {
        $validatedData[$formatFloatInput] = str_replace(".", "", $validatedData[$formatFloatInput]);
        $validatedData[$formatFloatInput] = str_replace(",", ".", $validatedData[$formatFloatInput]);
      }
    }

    $validatedData['game_credit_limit'] = $validatedData['game_credit'];

    try {
      // Criação do usuário com os dados validados
      $user = User::create($validatedData);

      // Redireciona para a página de exibição do usuário criado
      return redirect()->route('edit-user-form', ['id' => $user->id])
        ->with('success', 'Usuário criado com sucesso!');
    } catch (\Exception $e) {
      // Retorna um erro em caso de falha na criação do usuário
      return back()->withErrors(['error' => 'Falha ao criar usuário: ' . $e->getMessage()]);
    }
  }


  // AdminController.php
  public function update(Request $request, $id)
  {
    $user = User::findOrFail($id);
    $validatedData = $request->validate(
      [
        'name' => 'string|max:255',
        'email' => [
          'string',
          'email',
          'max:255',
          Rule::unique('users')->ignore($user->id),
        ],
        'document' => 'string|max:255',
        'balance' => 'required',
        'game_credit' => 'required',
        'game_credit_limit' => 'string',
        'comission_percent' => 'required',
        'phone' => 'string|max:255',
        'role_user_id' => 'exists:role_users,id',
        'active_refer_earn' => 'boolean',
        'invited_by_id' => 'exists:users,id',
        'seller_id' => 'exists:users,id', // Adiciona a validação para seller_id
      ],
      [
        'name.string' => 'O nome deve ser uma string.',
        'email.email' => 'O email deve ser um endereço de email válido.',
        'email.unique' => 'Este email já está em uso.',
        'document.string' => 'O documento deve ser uma string.',
        'balance.required' => 'O campo saldo é obrigatório.',
        'game_credit.required' => 'O campo crédito do jogo é obrigatório.',
        'game_credit_limit.string' => 'O limite de crédito do jogo deve ser uma string.',
        'comission_percent.required' => 'A porcentagem de comissão é obrigatória.',
        'phone.string' => 'O telefone deve ser uma string.',
        'role_user_id.exists' => 'O papel do usuário selecionado não existe.',
        'invited_by_id.exists' => 'O usuário referenciador não existe.',
      ]
    );

    $formatFloatInputs = [
      'balance',
      'game_credit',
      'game_credit_limit',
      'comission_percent'
    ];
    foreach ($formatFloatInputs as $formatFloatInput) {
      if (isset($validatedData[$formatFloatInput])) {
        $validatedData[$formatFloatInput] = str_replace(".", "", $validatedData[$formatFloatInput]);
        $validatedData[$formatFloatInput] = str_replace(",", ".", $validatedData[$formatFloatInput]);
      }
    }

    // if invited by so fill seller_id
    if (isset($validatedData['invited_by_id']) && $validatedData['invited_by_id'] != null) {
      $refered_by_user = User::find($validatedData['invited_by_id']);
      $validatedData['seller_id'] = ($refered_by_user && $refered_by_user->role->level_id == 'seller') ? $refered_by_user->id : null;
    } else {
      $validatedData['seller_id'] = null; // Se não houver convidado, seller_id deve ser nulo
    }

    $user->update($validatedData);

    // Update password only if a new one is provided
    if ($request->filled('password')) {
      //$user->password = bcrypt($request->input('password'));
      $user->password = Hash::make($request->input('password'));
    }

    $user->save();

    return redirect(route('edit-user-form', ['id' => $user->id]))->with('success', 'Usuário atualizado com sucesso!');
  }



  public function index(Request $request)
  {
    $builder = User::orderBy('created_at', 'desc');
    if (Auth::user()->role->level_id !== 'admin') {
      $builder = $builder->where('invited_by_id', Auth::user()->id);
    }
    if (Auth::user()->role->level_id == 'seller') {
      $builder = $builder->where('seller_id', Auth::user()->id);
    }


    if ($request->has('search') && $request->search != '') {
      $builder = $builder->where(function ($q) use ($request) {
        $q->where('name', 'like', '%' . $request->search . '%')
          ->orWhere('email', 'like', '%' . $request->search . '%');
      });
    }

    // Filtro de role_user_id
    if ($request->has('role_user_id') && $request->role_user_id != '') {
      $builder = $builder->where('role_user_id', $request->role_user_id);
    }

    //dd($builder);
    $users = $builder->paginate(10);
    $roles = RoleUser::all(); // Obtém todas as roles para o select

    return view('content.users.users', ['users' => $users, 'roles' => $roles]);
  }

  public function show()
  {
    return view('content.users.view_users');
  }

  public function create_user_form()
  {
    $roles = RoleUser::get();
    $gambler_role = RoleUser::where('level_id', 'gambler')->first();
    $sellers = User::where('role_user_id', 2)->get();
    return view('content.users.create_user', [
      'roles' => $roles,
      'sellers' => $sellers,
      'gambler_role' => $gambler_role
    ]);
  }

  public function create_game_form()
  {
    return view('content.game.create_game');
  }

  // Criar um jogo
  public function createGame(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'price' => 'required|numeric|min:0',
      'open_at' => 'required|date',
      'close_at' => 'required|date|after:open_at',
      'awards' => 'array',
      'awards.*.condition_type' => 'required|in:EXACT_POINT,WINNER',
      'awards.*.exact_point_value' => 'nullable|integer',
      'awards.*.winner_point_value' => 'nullable|integer',
      'awards.*.only_on_first_round' => 'boolean',
      'awards.*.name' => 'required|string|max:255',
      'awards.*.amount' => 'required|numeric|min:0',
    ]);

    $game_id = NULL;
    $exists = NULL;
    do {
      $game_id = rand(10000, 99999);
      $exists = Game::where('game_id', $game_id)->exists();
    } while ($exists);

    // Criar o jogo
    $game = Game::create([
      'name' => $request->name,
      'game_id' => $game_id,
      'price' => $request->price,
      'open_at' => $request->open_at,
      'close_at' => $request->close_at,
      'round' => 1,
      'status' => 'OPENED',
      'active' => true,
    ]);

    // Criar os prêmios associados ao jogo
    if ($request->has('awards')) {
      foreach ($request->awards as $awardData) {
        $awardData['game_id'] = $game->id;
        GameAward::create($awardData);
      }
    }

    $game_history = GameHistory::create([
      "description" => "JOGO ABERTO",
      "numbers" => "",
      "type" => "OPENED",
      'game_id' => $game->id,
    ]);


    return redirect(route('show-game', ['id' => $game->id]))->with('success', 'Jogo criado com sucesso, juntamente com os prêmios!');
  }

  public function openGame(Request $request, $id)
  {
    $game = Game::findOrFail($id);
    // Fechar o jogo
    $game->status = 'OPENED';
    $game->round = $game->round + 1;

    $game->save();

    $game_history = GameHistory::create([
      "description" => "JOGO ABERTO",
      "numbers" => "",
      "type" => "OPENED",
      'game_id' => $game->id,
    ]);

    return redirect(route('show-game', ['id' => $game->id]));
  }

  /*
  private function calculateUserPoints($purchases, $uniqueNumbers)
  {
    $userPoints = [];
    foreach ($purchases as $purchase) {
      $purchaseNumbers = array_map('intval', explode(' ', $purchase->numbers));
      $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);

      $userPoints[$purchase->user_id] = ($userPoints[$purchase->user_id] ?? 0) + count($matchedNumbers);
    }
    return $userPoints;
  }
  */

  private function calculateUserPoints($purchases, $uniqueNumbers)
  {
    $purchasePoints = [];
    foreach ($purchases as $purchase) {

      $purchaseNumbers = array_map('intval', explode(' ', $purchase->numbers));
      $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);

      // A pontuação é calculada por compra (cartela)
      $purchasePoints[$purchase->id] = count($matchedNumbers);
    }
    return $purchasePoints;
  }

  protected function handleAwards($gameId, $purchasePoints, $awards, $round, $gameHistoryId, $gameHistoryQnt)
  {

    $hasWinner = false;
    $winners = [];
    $only_when_finish_round_awards = [];
    foreach ($awards as $award) {
      // Verifica se o prêmio já foi concedido nesta rodada para evitar duplicação.
      $exists = UserAwards::where('game_id', $gameId)
        ->where('game_award_id', $award->id)
        ->where('round', $round)->exists();

      if ($exists) {
        continue;
      }

      if ($award->only_on_first_round && $gameHistoryQnt > 0) {
        // Se o prêmio é apenas para a primeira rodada e já existem históricos, pula este prêmio.
        continue;
      }

      $eligiblePurchases = [];
      $needyPoint = 0;

      if ($award->condition_type === 'EXACT_POINT') {
        $needyPoint = $award->exact_point_value;
      } elseif ($award->condition_type === 'WINNER') {
        $needyPoint = $award->winner_point_value;
      }

      // Filtra as compras que atingiram a pontuação necessária.
      foreach ($purchasePoints as $purchaseId => $totalPoints) {
        if ($needyPoint && ($totalPoints >= $needyPoint)) {

          if (($award->only_when_finish_round !== 0) && in_array($purchaseId, $winners)) {
            continue; // Se o prêmio é concedido apenas quando o jogo termina, e já é um vencedor do torneio, pula.
          }

          if ($award->condition_type === 'WINNER') {
            $hasWinner = true; // Marca que há pelo menos um vencedor
            $winners[] = $purchaseId; // Armazena o ID da compra do vencedor
          }

          $eligiblePurchases[] = $purchaseId;
        }
      }

      $numWinners = count($eligiblePurchases);

      if ($numWinners > 0) {
        // Divide o prêmio igualmente entre os vencedores.
        $awardAmountPerUser = $award->amount / $numWinners;

        foreach ($eligiblePurchases as $purchaseId) {
          $purchase = Purchase::find($purchaseId);
          $userId = $purchase->user_id;

          // Garante que o prêmio não seja concedido duas vezes para a mesma compra.
          $awardExists = UserAwards::where('game_id', $gameId)
            ->where('game_award_id', $award->id)
            ->where('purchase_id', $purchaseId)
            ->where('round', '>=', $round) // Verifica a rodada atual e futuras
            ->exists();

          if (!$awardExists) {


            $data = [
              'user_id' => $userId,
              'game_id' => $gameId,
              'purchase_id' => $purchaseId,
              'condition_type' => $award->condition_type,
              'exact_point_value' => $award->exact_point_value,
              'game_history_id' => $gameHistoryId,
              'round' => $round,
              'points' => $needyPoint,
              'game_award_id' => $award->id,
              'amount' => $awardAmountPerUser,
              'status' => 'PENDING',
            ];

            if ($award->only_when_finish_round == 0) {
              UserAwards::create($data);
              $this->updateReferEarn($userId);
            } else {
              // Se o prêmio é concedido apenas quando o jogo termina, armazena para posterior processamento.
              $only_when_finish_round_awards[] = $data;
            }
          }
        }
      }
    }
    if ($hasWinner) {
      // Atualiza o status do jogo para 'CLOSED' se houver pelo menos um vencedor.
      $game = Game::find($gameId);
      if ($game && $game->status !== 'FINISHED') {
        $game->status = 'FINISHED';
        $game->save();
        $game_history = GameHistory::create([
          "description" => "JOGO FECHADO",
          "numbers" => "",
          "type" => "FINISHED",
          'game_id' => $game->id,
        ]);
      }

      // Processa os prêmios que são concedidos apenas quando o jogo termina.
      foreach ($only_when_finish_round_awards as $awardData) {

        if (($awardData['condition_type'] != "WINNER") && in_array($awardData['purchase_id'], $winners)) {
          continue; // Evita duplicação de prêmios para vencedores
        }

        if (($awardData['condition_type'] == "EXACT_POINT") && ($awardData['exact_point_value'] == 0)) {
          continue; // Evita conceder prêmios de 0 pontos
        }

        UserAwards::create($awardData);
        $this->updateReferEarn($awardData['user_id']);
      }

      // Procura se há algum premio que deve ser concedido para quem fez menos pontos
      $minPointsAward = GameAward::where('game_id', $gameId)
        ->where('condition_type', 'EXACT_POINT')
        ->where('exact_point_value', 0)
        ->where('only_when_finish_round', true)
        ->first();

      if ($minPointsAward && !empty($purchasePoints)) {
        $lowestScore = min(array_values($purchasePoints)); // Encontra a menor pontuação
        $eligibleLosers = [];

        foreach ($purchasePoints as $purchaseId => $totalPoints) {
          if ($totalPoints === $lowestScore) { // Pega todos que tiveram a menor pontuação
            $eligibleLosers[] = $purchaseId;
          }
        }

        $numLosers = count($eligibleLosers);
        if ($numLosers > 0) {
          $loserAwardAmountPerUser = $minPointsAward->amount / $numLosers;

          foreach ($eligibleLosers as $purchaseId) {
            $purchase = Purchase::find($purchaseId);
            $userId = $purchase->user_id;

            $awardExists = UserAwards::where('game_id', $gameId)
              ->where('game_award_id', $minPointsAward->id)
              ->where('purchase_id', $purchaseId)
              ->where('round', '>=', $round)
              ->exists();

            if (!$awardExists) {
              UserAwards::create([
                'user_id' => $userId,
                'game_id' => $gameId,
                'purchase_id' => $purchaseId,
                'game_history_id' => $gameHistoryId,
                'round' => $round,
                'points' => $purchasePoints[$purchaseId],
                'game_award_id' => $minPointsAward->id,
                'amount' => $loserAwardAmountPerUser,
                'status' => 'PENDING',
              ]);
              $this->updateReferEarn($userId);
            }
          }
        }
      }

      // Procura se há algum premio que deve ser concedido para quem ganhou em segundo lugar
      $maxPointsAward = GameAward::where('game_id', $gameId)
        ->where('condition_type', 'SECONDARY_WINNER')
        ->where('only_when_finish_round', true)
        ->first();

      if ($maxPointsAward && !empty($purchasePoints)) {
        $highestScore = max(array_values($purchasePoints)); // Encontra a segunda maior pontuação
        $purchasePointsWithoutMax = array_filter(array_values($purchasePoints), function ($value) use ($highestScore) {
          return intval($value) !== intval($highestScore);
        });
        $secondHighestScore = max($purchasePointsWithoutMax); // Encontra a segunda maior pontuação


        $eligibleWinners = [];

        foreach ($purchasePoints as $purchaseId => $totalPoints) {

          if ($totalPoints === $secondHighestScore) { // Pega todos que tiveram a menor pontuação
            $eligibleWinners[] = $purchaseId;
          }
        }

        $numWinners = count($eligibleWinners);
        if ($numWinners > 0) {
          $winnerAwardAmountPerUser = $maxPointsAward->amount / $numWinners;

          foreach ($eligibleWinners as $purchaseId) {
            $purchase = Purchase::find($purchaseId);
            $userId = $purchase->user_id;

            $awardExists = UserAwards::where('game_id', $gameId)
              ->where('game_award_id', $maxPointsAward->id)
              ->where('purchase_id', $purchaseId)
              ->where('round', '>=', $round)
              ->exists();

            if (!$awardExists) {
              UserAwards::create([
                'user_id' => $userId,
                'game_id' => $gameId,
                'purchase_id' => $purchaseId,
                'game_history_id' => $gameHistoryId,
                'round' => $round,
                'points' => $purchasePoints[$purchaseId],
                'game_award_id' => $maxPointsAward->id,
                'amount' => $winnerAwardAmountPerUser,
                'status' => 'PENDING',
              ]);
              $this->updateReferEarn($userId);
            }
          }
        }
      }
    }
  }

  public function addGameHistory(Request $request, $id)
  {
    $game = Game::findOrFail($id);

    if ($game->status !== 'CLOSED') {
      return redirect()->back()->withErrors(['error' => ["Jogo não está disponível para adicionar histórico."]]);
    }

    $resultNumbersArray = explode(", ", $request->result_numbers);

    $addedNumberHistories = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER')
      ->where('round', $game->round)
      ->get();

    $gameHistoryQnt = $addedNumberHistories->count();

    $allAddedNumbers = $addedNumberHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    $gameHistory = null;
    foreach ($resultNumbersArray as $resultSet) {
      $rawNumbers = explode(" ", $resultSet);
      $numbers = array_map(fn($num) => intval(substr($num, -2)), $rawNumbers);

      $gameHistory = GameHistory::create([
        "description" => $request->description,
        "type" => 'ADDING_NUMBER',
        "result_numbers" => implode(" ", $rawNumbers),
        "numbers" => implode(" ", $numbers),
        "game_id" => $game->id,
        "round" => $game->round,
      ]);

      $allAddedNumbers = array_merge($allAddedNumbers, $numbers);

      $uniqueNumbers = array_unique($allAddedNumbers);

      $purchases = Purchase::where('game_id', $game->id)
        ->where('round', $game->round)
        ->where('status', 'PAID')
        ->get();

      $purchasePoints = $this->calculateUserPoints($purchases, $uniqueNumbers);

      // Atualiza os pontos de cada compra
      foreach ($purchases as $purchase) {
        $points = $purchasePoints[$purchase->id] ?? 0;
        $purchase->points = $points;
        $purchase->save();
      }


      $awards = GameAward::where('game_id', $game->id)->get();

      if ($gameHistory) {
        $this->handleAwards($game->id, $purchasePoints, $awards, $game->round, $gameHistory->id, $gameHistoryQnt);
      }
      $gameHistoryQnt++;
    }


    return redirect(route('show-game', ['id' => $game->id]))->with(['tab' => 'tab-results', 'success' => 'Histórico adicionado com sucesso!']);
  }




  public function editGameHistory(Request $request, $game_history_id)
  {
    if (Auth::user()->role->level_id !== 'admin') {
      return redirect('/auth/logout');
    }

    $gameHistory = GameHistory::find($game_history_id);


    return view('content.game.history.edit_game_history', compact('gameHistory'));
  }

  public function updateGameHistory(Request $request, $game_history_id)
  {
    if (Auth::user()->role->level_id !== 'admin') {
      // Como você me pediu para ter opiniões e corrigir, sugiro um `abort(403)`
      // ou um redirecionamento para o login, se a intenção for sempre deslogar.
      // O `redirect('/auth/logout')` é válido, mas `abort(403)` é mais direto para "não autorizado".
      abort(403, 'Você não tem permissão para realizar esta ação.');
    }

    $game_history = GameHistory::findOrFail($game_history_id);
    $game_id = $game_history->game_id;
    $round = $game_history->round;

    // Validação
    $validatedData = $request->validate([
      "description" => "string|nullable", // Ajustei para nullable se não for obrigatório
      "result_numbers" => "string|nullable", // Ajustei para nullable
    ]);

    // Pega os números antigos do histórico para comparação
    $oldNumbersRaw = $game_history->result_numbers;
    $oldNumbersProcessed = $game_history->numbers; // Estes são os números inteiros do jogo (ex: 05, 12)

    // Prepara os novos números, se forem fornecidos
    $newNumbersRaw = $request->filled('result_numbers') ? $request->result_numbers : $oldNumbersRaw;
    $newNumbersProcessed = '';
    if ($request->filled('result_numbers')) {
      $resultNumbersArray = explode(" ", $newNumbersRaw);
      $newNumbersProcessed = implode(" ", array_map(fn($num) => intval(substr($num, -2)), $resultNumbersArray));
    } else {
      $newNumbersProcessed = $oldNumbersProcessed; // Mantém os números antigos se não foram fornecidos novos
    }

    // Verifica se houve mudança relevante nos números para justificar reprocessamento completo
    $numbersChanged = ($newNumbersProcessed !== $oldNumbersProcessed);

    // Se a descrição mudou mas os números não, apenas atualiza e retorna.
    if (!$numbersChanged && $request->description === $game_history->description) {
      $game_history->update($validatedData);
      return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-results', 'success' => 'Histórico atualizado com sucesso (apenas descrição).']);
    }

    // --- Lógica de Reprocessamento (similar à remoção) ---

    // 1. Encontrar todos os históricos a partir do histórico editado (inclusive ele),
    // ordenados para reprocessamento na ordem original.
    $historiesToReprocessData = [];
    $historiesToDeleteIds = []; // IDs para deletar e recriar

    $originalAndSubsequentHistories = GameHistory::where('game_id', $game_id)
      ->where('round', $round)
      ->where('created_at', '>=', $game_history->created_at) // Pega ele e todos os posteriores
      ->where('type', 'ADDING_NUMBER')
      ->orderBy('created_at', 'asc')
      ->get();

    foreach ($originalAndSubsequentHistories as $history) {
      // Para o histórico que está sendo editado, usamos os novos dados
      if ($history->id === $game_history->id) {
        $historiesToReprocessData[] = [
          'id' => $history->id, // Mantém o ID para identificação
          'description' => $validatedData['description'] ?? $history->description,
          'result_numbers' => $newNumbersRaw,
          'numbers' => $newNumbersProcessed,
          'type' => $history->type,
          'game_id' => $history->game_id,
          'round' => $history->round,
          'created_at' => $history->created_at, // Mantém a data de criação original
        ];
      } else {
        // Para os históricos subsequentes, pegamos os dados originais deles
        $historiesToReprocessData[] = [
          'id' => $history->id, // Mantém o ID para identificação
          'description' => $history->description,
          'result_numbers' => $history->result_numbers,
          'numbers' => $history->numbers,
          'type' => $history->type,
          'game_id' => $history->game_id,
          'round' => $history->round,
          'created_at' => $history->created_at, // Mantém a data de criação original
        ];
      }
      $historiesToDeleteIds[] = $history->id;
    }

    // 2. Apagar todos os prêmios associados aos históricos que serão reprocessados.
    UserAwards::where('game_id', $game_id)
      ->where('round', $round)
      ->whereIn('game_history_id', $historiesToDeleteIds)
      ->delete();

    // 3. Deletar os históricos afetados (o editado e os posteriores) do banco de dados.
    // Isso garante que serão recriados com os dados corretos e a lógica de reprocessamento.
    GameHistory::whereIn('id', $historiesToDeleteIds)->delete();


    // 4. Reprocessar os históricos em ordem cronológica
    $allAddedNumbers = []; // Acumula os números de todos os históricos já processados neste loop

    foreach ($historiesToReprocessData as $historyData) {
      $game = Game::findOrFail($game_id); // Pega o jogo novamente para garantir dados frescos

      // Adiciona os números deste histórico aos números totais da rodada
      $currentHistoryNumbers = array_map('intval', explode(" ", $historyData['numbers']));
      $allAddedNumbers = array_merge($allAddedNumbers, $currentHistoryNumbers);
      $uniqueNumbers = array_unique($allAddedNumbers);

      // Recria o histórico com os dados atualizados (para o editado) ou originais (para os subsequentes)
      // e com a data de criação original
      $newGameHistory = GameHistory::create([
        "description" => $historyData['description'],
        "type" => $historyData['type'],
        "result_numbers" => $historyData['result_numbers'],
        "numbers" => $historyData['numbers'],
        "game_id" => $historyData['game_id'],
        "round" => $historyData['round'],
        "created_at" => $historyData['created_at'], // Define a data de criação original
      ]);

      // Consulta novamente os históricos para obter a quantidade de históricos até o momento
      // para o cálculo do `only_on_first_round` (se aplicável no `handleAwards`).
      $gameHistoryQnt = GameHistory::where('game_id', $game->id)
        ->where('type', 'ADDING_NUMBER')
        ->where('round', $game->round)
        ->count();


      $purchases = Purchase::where('game_id', $game->id)
        ->where('round', $game->round)
        ->where('status', 'PAID')
        ->get();

      $purchasePoints = $this->calculateUserPoints($purchases, $uniqueNumbers);
      // Atualiza os pontos de cada compra
      foreach ($purchases as $purchase) {
        $points = $purchasePoints[$purchase->id] ?? 0;
        $purchase->points = $points;
        $purchase->save();
      }

      $awards = GameAward::where('game_id', $game->id)->get();

      // Chama o handler de prêmios para o histórico recém-criado
      $this->handleAwards($game->id, $purchasePoints, $awards, $game->round, $newGameHistory->id, $gameHistoryQnt);
    }
    // Se o jogo estava fechado e não houve vencedor, atualiza o status para 'CLOSED' após o reprocessamento
    $game = Game::findOrFail($game_id);
    if ($game->status === 'FINISHED') {
      $winnerAward = GameAward::where('game_id', $game_id)
        ->where('condition_type', 'WINNER')
        ->where('only_when_finish_round', true)
        ->first();
      if (
        $winnerAward || UserAwards::where('game_id', $game_id)
        ->where('game_award_id', $winnerAward->id)
        ->where('round', $game->round)
        ->exists()
      ) {
        // Se não há prêmio de vencedor ou já foi concedido, mantém o status 'CLOSED'
        $game->status = 'CLOSED';
        $game->save();
        GameHistory::create([
          "description" => "JOGO EM ANDAMENTO NOVAMENTE",
          "numbers" => "",
          "type" => "CLOSED",
          'game_id' => $game->id,
        ]);
      }
    }



    return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-results', 'success' => 'Histórico atualizado e reprocessado com sucesso!']);
  }



  public function removeGameHistory(Request $request, $game_history_id)
  {
    $historyToRemove = GameHistory::findOrFail($game_history_id);
    $game_id = $historyToRemove->game_id;
    $round = $historyToRemove->round;

    // 1. Encontrar todos os históricos a partir DO PRÓXIMO histórico (não incluindo o que será removido),
    // ordenados para reprocessamento na ordem original.
    $subsequentHistories = GameHistory::where('game_id', $game_id)
      ->where('round', $round)
      ->where('created_at', '>', $historyToRemove->created_at) // <--- Mudei para '>' (maior que)
      ->where('type', 'ADDING_NUMBER')
      ->orderBy('created_at', 'asc')
      ->get();

    // 2. Coletar os dados dos históricos subsequentes para reprocessamento
    $historiesToReprocessData = [];
    foreach ($subsequentHistories as $history) {
      $historiesToReprocessData[] = [
        'description' => $history->description,
        'result_numbers' => $history->result_numbers,
        'type' => $history->type,
        'game_id' => $history->game_id,
        'round' => $history->round,
        'numbers' => $history->numbers,
        'created_at' => $history->created_at,
      ];
    }

    // 3. Apagar o histórico que o usuário quer remover e todos os prêmios relacionados a ele.
    // Primeiro o registro de histórico que será deletado
    $historyToRemove->delete();

    // Remove os prêmios vinculados ESPECIFICAMENTE ao histórico que foi removido
    UserAwards::where('game_history_id', $game_history_id)
      ->where('game_id', $game_id)
      ->where('round', $round)
      ->delete();

    // E também os prêmios dos históricos subsequentes, pois eles serão recriados
    if (!$subsequentHistories->isEmpty()) {
      UserAwards::where('game_id', $game_id)
        ->where('round', $round)
        ->whereIn('game_history_id', $subsequentHistories->pluck('id'))
        ->delete();

      // Deleta fisicamente os históricos subsequentes para que possam ser recriados
      GameHistory::whereIn('id', $subsequentHistories->pluck('id'))->delete();
    }


    // 4. Reprocessar os históricos subsequentes em ordem cronológica
    $allAddedNumbers = []; // Acumula os números de todos os históricos já processados neste loop

    // Precisamos recarregar todos os históricos *anteriores* ao que foi removido
    // para que a base de $allAddedNumbers esteja correta para o reprocessamento.
    $precedingHistories = GameHistory::where('game_id', $game_id)
      ->where('round', $round)
      ->where('created_at', '<', $historyToRemove->created_at) // Menor que a data do removido
      ->where('type', 'ADDING_NUMBER')
      ->orderBy('created_at', 'asc')
      ->get();

    foreach ($precedingHistories as $history) {
      $allAddedNumbers = array_merge($allAddedNumbers, array_map('intval', explode(" ", $history->numbers)));
    }


    foreach ($historiesToReprocessData as $historyData) {
      // Os números deste histórico são adicionados ao conjunto total de números sorteados
      $currentHistoryNumbers = array_map('intval', explode(" ", $historyData['numbers']));
      $allAddedNumbers = array_merge($allAddedNumbers, $currentHistoryNumbers);
      $uniqueNumbers = array_unique($allAddedNumbers);
      $game = Game::findOrFail($game_id);

      // Recria o histórico
      $newGameHistory = GameHistory::create([
        "description" => $historyData['description'],
        "type" => $historyData['type'],
        "result_numbers" => $historyData['result_numbers'],
        "numbers" => $historyData['numbers'],
        "game_id" => $historyData['game_id'],
        "round" => $historyData['round'],
        "created_at" => $historyData['created_at'],
      ]);

      // Consulta novamente os históricos para obter a quantidade de históricos até o momento
      $gameHistoryQnt = GameHistory::where('game_id', $game_id)
        ->where('type', 'ADDING_NUMBER')
        ->where('round', $game->round)
        ->count();


      $purchases = Purchase::where('game_id', $game_id)
        ->where('round', $game->round)
        ->where('status', 'PAID')
        ->get();

      $purchasePoints = $this->calculateUserPoints($purchases, $uniqueNumbers);
      // Atualiza os pontos de cada compra
      foreach ($purchases as $purchase) {
        $points = $purchasePoints[$purchase->id] ?? 0;
        $purchase->points = $points;
        $purchase->save();
      }

      $awards = GameAward::where('game_id', $game_id)->get();

      $this->handleAwards($game_id, $purchasePoints, $awards, $game->round, $newGameHistory->id, $gameHistoryQnt);
    }

    // Se o jogo estava fechado e não houve vencedor, atualiza o status para 'CLOSED' após o reprocessamento
    $game = Game::findOrFail($game_id);
    if ($game->status === 'FINISHED') {
      $winnerAward = GameAward::where('game_id', $game_id)
        ->where('condition_type', 'WINNER')
        ->where('only_when_finish_round', true)
        ->first();
      if (
        $winnerAward || UserAwards::where('game_id', $game_id)
        ->where('game_award_id', $winnerAward->id)
        ->where('round', $game->round)
        ->exists()
      ) {
        // Se não há prêmio de vencedor ou já foi concedido, mantém o status 'CLOSED'
        $game->status = 'CLOSED';
        $game->save();
        GameHistory::create([
          "description" => "JOGO EM ANDAMENTO NOVAMENTE",
          "numbers" => "",
          "type" => "CLOSED",
          'game_id' => $game->id,
        ]);
      }
    }

    return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-results', 'success' => 'Histórico removido e subsequentes reprocessados com sucesso!']);
  }



  // Atualizar indicação e ganhos de referência
  private function updateReferEarn($userId)
  {
    $referEarn = ReferEarn::where('refer_user_id', $userId)
      ->where('invited_user_bought', false)
      ->first();

    if ($referEarn) {
      $referEarn->invited_user_bought = true;
      $referEarn->save();
    }
  }


  public function user_limit_credit_restart(Request $request, $user_id)
  {
    if (Auth::user()->role->level_id !== 'admin') {
      return redirect('/auth/logout');
    }
    $user = User::find($user_id);

    Transactions::create(
      [
        "type" => 'GAME_CREDIT',

        "amount" => $user->game_credit_limit - $user->game_credit,
        "user_id" => $user->id,
      ]
    );

    $user->game_credit = $user->game_credit_limit;
    $user->save();
    return redirect(route('edit-user-form', ['id' => $user->id]))->with('success', 'Usuário atualizado com sucesso!');
  }
}
