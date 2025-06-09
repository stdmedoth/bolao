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
      'role_user_id' => 'required|exists:role_users,id',
      'invited_by_id' => 'exists:users,id',
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
    $validatedData = $request->validate([
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
      'invited_by_id' => 'exists:users,id',
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
      'awards.*.condition_type' => 'required|in:EXACT_POINT,LOWEST_POINT,WINNER',
      'awards.*.exact_point_value' => 'nullable|integer',
      'awards.*.winner_point_value' => 'nullable|integer',
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

      if (isset($purchasePoints[$purchase->id])) {
        $purchasePoints[$purchase->id] = (count($matchedNumbers) > $purchasePoints[$purchase->id]) ?  count($matchedNumbers) : $purchasePoints[$purchase->id];
      } else {
        $purchasePoints[$purchase->id] = count($matchedNumbers);
      }
    }
    return $purchasePoints;
  }

  protected function handleAwards($gameId, $purchasePoints, $awards, $round, $gameHistoryId)
  {


    foreach ($awards as $award) {
      $exists = UserAwards::where('game_id', $gameId)
        ->where('game_award_id', $award->id)
        ->where('round', $round)->exists();

      if ($exists) {
        // Se o prêmio já foi concedido neste round, não faz nada
        continue;
      }

      // Filtrar usuários que atendem às condições do prêmio
      $eligiblePurchases = [];
      $needyPoint = 0;
      foreach ($purchasePoints as $purchaseId => $totalPoints) {
        if (($award->condition_type === 'EXACT_POINT')) {
          $needyPoint = $award->exact_point_value;
        } elseif ($award->condition_type === 'WINNER') {
          $needyPoint = $award->winner_point_value;
        }

        if ($needyPoint && ($totalPoints >= $needyPoint)) {
          $eligiblePurchases[] = $purchaseId;
        }
      }


      // Se houver usuários elegíveis
      $numWinners = count($eligiblePurchases);
      if ($numWinners > 0) {
        // Dividir o valor do prêmio igualmente entre os ganhadores
        $awardAmountPerUser = $award->amount / $numWinners;


        var_dump($eligiblePurchases);
        foreach ($eligiblePurchases as $purchaseId) {

          $purchase = Purchase::find($purchaseId);
          $userId = $purchase->user_id;

          // Verificar se o prêmio já foi concedido para este usuário
          $builder = UserAwards::where('game_id', $gameId)
            ->where('game_award_id', $award->id)
            ->where('purchase_id', $purchaseId)
            ->where('user_id', $userId)
            ->where('round', $round);

          $awardExists = $builder->exists();

          // Criar o prêmio para o usuário, se ainda não foi concedido
          if (!$awardExists) {
            UserAwards::create([
              'user_id' => $userId,
              'game_id' => $gameId,
              'purchase_id' => $purchaseId,
              'game_history_id' => $gameHistoryId,
              'round' => $round,
              'points' => $needyPoint,
              'game_award_id' => $award->id,
              'amount' => $awardAmountPerUser,
              'status' => 'PENDING',
            ]);

            // Atualizar indicação e ganhos de referência
            $this->updateReferEarn($userId);
          }
        }
      }
    }
  }


  public function addGameHistory(Request $request, $id)
  {
    $game = Game::findOrFail($id);

    // Verifica se o jogo está fechado
    if ($game->status !== 'CLOSED') {
      return redirect()->back()->withErrors(['error' => ["Jogo ainda está aberto"]]);
    }

    // Divide os conjuntos de números de resultado informados
    $resultNumbersArray = explode(", ", $request->result_numbers);

    // Obtém todos os números adicionados neste round (via GameHistory)
    $addedNumberHistories = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER')
      ->where('round', $game->round) // novo uso do campo round
      ->get();

    // Reúne todos os números previamente adicionados
    $allAddedNumbers = $addedNumberHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    // Adiciona os novos números ao histórico e à lista acumulada
    foreach ($resultNumbersArray as $resultSet) {
      $rawNumbers = explode(" ", $resultSet);

      // Extrai os dois últimos dígitos de cada número
      $numbers = array_map(fn($num) => intval(substr($num, -2)), $rawNumbers);

      // Cria novo registro no histórico
      $gameHistory = GameHistory::create([
        "description" => $request->description,
        "type" => 'ADDING_NUMBER',
        "result_numbers" => implode(" ", $rawNumbers),
        "numbers" => implode(" ", $numbers),
        "game_id" => $game->id,
        "round" => $game->round,
      ]);

      $allAddedNumbers = array_merge($allAddedNumbers, $numbers);
    }

    // Remove duplicados
    $uniqueNumbers = array_unique($allAddedNumbers);

    // Obtém todas as compras deste jogo e round
    $purchases = Purchase::where('game_id', $game->id)
      ->where('round', $game->round)
      ->where('status', 'PAID')
      ->get();

    // Calcula os pontos por usuário
    $userPoints = $this->calculateUserPoints($purchases, $uniqueNumbers);

    // Pega os prêmios configurados para o jogo
    $awards = GameAward::where('game_id', $game->id)->get();

    // Distribui os prêmios conforme os pontos
    $this->handleAwards($game->id, $userPoints, $awards, $game->round, $gameHistory->id);

    return redirect(route('show-game', ['id' => $game->id]))->with(['tab' => 'tab-results']);
  }



  public function editGameHistory(Request $request, $game_history_id)
  {
    if (Auth::user()->role->level_id !== 'admin') {
      redirect('/auth/logout');
    }

    $gameHistory = GameHistory::find($game_history_id);


    return view('content.game.history.edit_game_history', compact('gameHistory'));
  }

  public function updateGameHistory(Request $request, $game_history_id)
  {
    $game_history = GameHistory::findOrFail($game_history_id);
    $game_id = $game_history->game_id;
    $round = $game_history->round;

    UserAwards::where('game_id', $game_id)
      ->where('round', $round)
      ->where('game_history_id', $game_history_id)
      ->delete();

    // Validação
    $validatedData = $request->validate([
      "description" => "string",
      "result_numbers" => "string",
    ]);

    // Se não tiver novos números, só atualiza os dados e retorna
    if (!$request->filled('result_numbers')) {
      $game_history->update($validatedData);
      return redirect(route('show-game', ['id' => $game_id]));
    }

    // Processa os novos números
    $resultNumbers = explode(" ", $request->result_numbers);
    $numbers = array_map(fn($num) => intval($num), $resultNumbers);

    $validatedData['result_numbers'] = implode(" ", $resultNumbers);
    $validatedData['numbers'] = implode(" ", $numbers);
    $game_history->update($validatedData);

    // Recalcular os pontos com base nos históricos deste round
    $gameHistories = GameHistory::where('game_id', $game_id)
      ->where('round', $round)
      ->where('type', 'ADDING_NUMBER')
      ->get();

    $allAddedNumbers = $gameHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    // Obtém as compras pagas para esse round
    $purchases = Purchase::where('game_id', $game_id)
      ->where('round', $round)
      ->where('status', 'PAID')
      ->get();

    // Calcula pontos
    $userPoints = $this->calculateUserPoints($purchases, $allAddedNumbers);

    // Reatribui os prêmios com base nos novos pontos
    $awards = GameAward::where('game_id', $game_id)->get();
    $this->handleAwards($game_id, $userPoints, $awards, $round, $game_history_id);

    return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-results']);
  }


  public function removeGameHistory(Request $request, $game_history_id)
  {
    $game_history = GameHistory::findOrFail($game_history_id);
    $game_id = $game_history->game_id;
    $round = $game_history->round;

    // Deleta o histórico e os prêmios do round correspondente
    $game_history->delete();

    UserAwards::where('game_id', $game_id)
      ->where('round', $round)
      ->where('game_history_id', $game_history_id)
      ->delete();

    // Recalcular os pontos com base nos históricos restantes desse round
    $gameHistories = GameHistory::where('game_id', $game_id)
      ->where('round', $round)
      ->where('type', 'ADDING_NUMBER')
      ->get();

    $allAddedNumbers = $gameHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    // Obter todas as compras válidas para esse round
    $purchases = Purchase::where('game_id', $game_id)
      ->where('round', $round)
      ->where('status', 'PAID')
      ->get();

    // Calcular pontos
    $userPoints = $this->calculateUserPoints($purchases, $allAddedNumbers);

    // Reatribuir prêmios com base nos novos pontos
    $awards = GameAward::where('game_id', $game_id)->get();
    $this->handleAwards($game_id, $userPoints, $awards, $round, $game_history_id);

    return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-results']);
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
      redirect('/auth/logout');
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
