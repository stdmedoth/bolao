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
use App\Models\UserAwards;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


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
      'document' => 'string|max:255',
      'phone' => 'required|string|max:255',
      'password' => 'required|string|min:6',
      'role_user_id' => 'required|exists:role_users,id',
      'invited_by_id' => 'exists:users,id',
    ]);

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
      'phone' => 'string|max:255',
      'role_user_id' => 'exists:role_users,id',
      'invited_by_id' => 'exists:users,id',
    ]);

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
      $builder->where(function ($q) use ($request) {
        $q->where('name', 'like', '%' . $request->search . '%')
          ->orWhere('email', 'like', '%' . $request->search . '%');
      });
    }

    // Filtro de role_user_id
    if ($request->has('role_user_id') && $request->role_user_id != '') {
      $builder->where('role_user_id', $request->role_user_id);
    }

    $users = $builder->paginate(5);
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

    // Criar o jogo
    $game = Game::create([
      'name' => $request->name,
      'price' => $request->price,
      'open_at' => $request->open_at,
      'close_at' => $request->close_at,
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
    $game->save();

    $game_history = GameHistory::create([
      "description" => "JOGO ABERTO",
      "numbers" => "",
      "type" => "OPENED",
      'game_id' => $game->id,
    ]);

    return redirect(route('show-game', ['id' => $game->id]));
  }
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

  protected function handleAwards($gameId, $userPoints, $awards, $lastClosedHistory)
  {
    foreach ($awards as $award) {
      // Filtrar usuários que atendem às condições do prêmio
      $eligibleUsers = [];
      foreach ($userPoints as $userId => $totalPoints) {
        if ((($award->condition_type === 'EXACT_POINT') && ($totalPoints == $award->exact_point_value)) ||
          (($award->condition_type === 'WINNER') && ($totalPoints >= $award->winner_point_value))
        ) {
          $eligibleUsers[] = $userId;
        }
      }

      // Se houver usuários elegíveis
      $numWinners = count($eligibleUsers);
      if ($numWinners > 0) {
        // Dividir o valor do prêmio igualmente entre os ganhadores
        $awardAmountPerUser = $award->amount / $numWinners;

        foreach ($eligibleUsers as $userId) {
          // Verificar se o prêmio já foi concedido para este usuário
          $builder = UserAwards::where('game_id', $gameId)
            ->where('game_award_id', $award->id)
            ->where('user_id', $userId);
          if ($lastClosedHistory) {
            $builder = $builder->where('created_at', '>=', $lastClosedHistory->created_at);
          }
          $awardExists = $builder->exists();

          // Criar o prêmio para o usuário, se ainda não foi concedido
          if (!$awardExists) {
            UserAwards::create([
              'user_id' => $userId,
              'game_id' => $gameId,
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

    // Verificar se o jogo está fechado
    if ($game->status !== 'CLOSED') {
      return redirect()->back()->withErrors(['error' => ["Jogo ainda está aberto"]]);
    }

    $resultNumbersArray = explode(", ", $request->result_numbers);

    // Última vez que o concurso foi aberto
    $lastClosedHistory = GameHistory::where('game_id', $game->id)
      ->where('type', 'OPENED')
      ->orderBy('created_at', 'DESC')
      ->first();

    // Pegar todos os números válidos desde a última abertura
    $gameHistoriesBuilder = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER');
    if ($lastClosedHistory) {
      $gameHistoriesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }
    $gameHistories = $gameHistoriesBuilder->get();

    // Reunir todos os números adicionados desde a última abertura
    $allAddedNumbers = $gameHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    foreach ($resultNumbersArray as $resultNumbers) {
      $resultNumbers = explode(" ", $resultNumbers);

      // Extrair os últimos dois dígitos de cada número
      $numbers = array_map(fn($num) => intval(substr($num, -2)), $resultNumbers);

      // Adicionar os novos números ao histórico
      GameHistory::create([
        "description" => $request->description,
        "type" => 'ADDING_NUMBER',
        "result_numbers" => implode(" ", $resultNumbers),
        "numbers" => implode(" ", $numbers),
        'game_id' => $game->id,
      ]);

      // Adicionar os novos números à lista acumulada
      $allAddedNumbers = array_merge($allAddedNumbers, $numbers);
    }

    // Remover duplicados para garantir que só os números únicos sejam usados
    $uniqueNumbers = array_unique($allAddedNumbers);

    // Obter todas as compras relacionadas ao jogo desde o último concurso aberto
    $purchasesBuilder = Purchase::where('game_id', $game->id)
      ->whereIn('status', ['PAID']);
    if ($lastClosedHistory) {
      $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }
    $purchases = $purchasesBuilder->get();

    // Calcular pontos por usuário usando TODOS os números acumulados
    $userPoints = $this->calculateUserPoints($purchases, $uniqueNumbers);
    //dd($userPoints);

    // Verificar condições de prêmios e distribuir
    $awards = GameAward::where('game_id', $game->id)->get();
    $this->handleAwards($game->id, $userPoints, $awards, $lastClosedHistory);

    return redirect(route('show-game', ['id' => $game->id]));
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
    $game_history = GameHistory::find($game_history_id);

    $user_awards = UserAwards::where('game_id', $game_history->game_id)
      ->where('created_at', '>=', $game_history->created_at);

    // Validação dos dados de entrada
    $validatedData = $request->validate([
      "description" => "string",
      "result_numbers" => "string",
    ]);
    $game_id = $game_history->game_id;

    if (!isset($request->result_numbers)) {
      $game_history->update($validatedData);
      return redirect(route('show-game', ['id' => $game_id]));
    }

    $user_awards->delete();

    $resultNumbers = explode(" ", $request->result_numbers);

    // Extrair os últimos dois dígitos de cada número
    $numbers = array_map(fn($num) => intval(substr($num, -2)), $resultNumbers);


    $validatedData['result_numbers'] = implode(" ", $resultNumbers);
    $validatedData['numbers'] = implode(" ", $numbers);
    $game_history->update($validatedData);

    // Recalcular

    // Última vez que o concurso foi aberto
    $lastClosedHistory = GameHistory::where('game_id', $game_id)
      ->where('type', 'OPENED')
      ->orderBy('created_at', 'DESC')
      ->first();


    // Pegar todos os números válidos desde a última abertura
    $gameHistoriesBuilder = GameHistory::where('game_id', $game_id)
      ->where('type', 'ADDING_NUMBER');
    if ($lastClosedHistory) {
      $gameHistoriesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }
    $gameHistories = $gameHistoriesBuilder->get();

    // Reunir todos os números adicionados desde a última abertura
    $allAddedNumbers = $gameHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();


    // Obter todas as compras relacionadas ao jogo desde o último concurso aberto
    $purchasesBuilder = Purchase::where('game_id', $game_id)
      ->whereIn('status', ['PAID']);
    if ($lastClosedHistory) {
      $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }
    $purchases = $purchasesBuilder->get();

    // Calcular pontos por usuário usando TODOS os números acumulados
    $userPoints = $this->calculateUserPoints($purchases, $allAddedNumbers);
    //dd($userPoints);

    // Verificar condições de prêmios e distribuir
    $awards = GameAward::where('game_id', $game_id)->get();
    $this->handleAwards($game_id, $userPoints, $awards, $lastClosedHistory);

    return redirect(route('show-game', ['id' => $game_id]))->with(['tab' => 'tab-results']);
  }

  public function removeGameHistory(Request $request, $game_history_id)
  {
    $game_history = GameHistory::find($game_history_id);

    $user_awards = UserAwards::where('game_id', $game_history->game_id)
      ->where('created_at', '>=', $game_history->created_at);


    $game_id = $game_history->game_id;
    $game_history->delete();
    $user_awards->delete();


    // Recalcular

    // Última vez que o concurso foi aberto
    $lastClosedHistory = GameHistory::where('game_id', $game_id)
      ->where('type', 'OPENED')
      ->orderBy('created_at', 'DESC')
      ->first();


    // Pegar todos os números válidos desde a última abertura
    $gameHistoriesBuilder = GameHistory::where('game_id', $game_id)
      ->where('type', 'ADDING_NUMBER');
    if ($lastClosedHistory) {
      $gameHistoriesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }
    $gameHistories = $gameHistoriesBuilder->get();

    // Reunir todos os números adicionados desde a última abertura
    $allAddedNumbers = $gameHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();


    // Obter todas as compras relacionadas ao jogo desde o último concurso aberto
    $purchasesBuilder = Purchase::where('game_id', $game_id)
      ->whereIn('status', ['PAID']);
    if ($lastClosedHistory) {
      $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }
    $purchases = $purchasesBuilder->get();

    // Calcular pontos por usuário usando TODOS os números acumulados
    $userPoints = $this->calculateUserPoints($purchases, $allAddedNumbers);
    //dd($userPoints);

    // Verificar condições de prêmios e distribuir
    $awards = GameAward::where('game_id', $game_id)->get();
    $this->handleAwards($game_id, $userPoints, $awards, $lastClosedHistory);
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
}
