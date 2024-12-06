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

    return view('content.users.user_update', compact('user', 'roles'));
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
    return view('usuarios.edit', compact('user', 'roles'));
  }

  // Cria um usuário (vendedor ou apostador)
  public function createUser(Request $request)
  {
    // Validação dos dados de entrada
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'document' => 'required|string|max:255',
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
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|max:255|unique:users,email,' . $user->id, // Ignore current user's email
      'role_user_id' => 'required|exists:role_users,id',
    ]);

    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->role_user_id = $request->input('role_user_id');

    // Update password only if a new one is provided
    if ($request->filled('password')) {
      //$user->password = bcrypt($request->input('password'));
      $user->password = Hash::make($request->input('password'));
    }

    $user->save();

    return redirect()->back()->with('success', 'Usuário atualizado com sucesso!');
  }



  public function index()
  {
    $users = User::get();

    return view('content.users.users', ['users' => $users]);
  }

  public function show()
  {
    return view('content.users.view_users');
  }

  public function create_user_form()
  {
    $roles = RoleUser::get();
    $sellers = User::where('role_user_id', 2)->get();
    return view('content.users.create_user', ['roles' => $roles, 'sellers' => $sellers]);
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

  public function addGameHistory(Request $request, $id)
  {
    $game = Game::findOrFail($id);

    // Verificar se o jogo está fechado
    if ($game->status !== 'CLOSED') {
      return redirect()->back()->withErrors(['error' => ["Jogo ainda está aberto"]]);
    }

    $resultNumbersArray = explode(", ", $request->result_numbers);

    foreach ($resultNumbersArray as $resultNumbers) {
      $resultNumbers = explode(" ", $resultNumbers);

      // Última vez que o concurso foi aberto
      $lastClosedHistory = GameHistory::where('game_id', $game->id)
        ->where('type', 'OPENED')
        ->orderBy('created_at', 'DESC')
        ->first();

      // Pegar números já inputados
      $gameHistoriesBuilder = GameHistory::where('game_id', $game->id)
        ->where('type', 'ADDING_NUMBER');

      if ($lastClosedHistory) {
        $gameHistoriesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
      }
      $gameHistories = $gameHistoriesBuilder->get();

      $addedNumbers = $gameHistories->pluck('numbers')
        ->flatMap(fn($numbers) => explode(" ", $numbers))
        ->toArray();

      // Extrair os últimos dois dígitos de cada número
      $numbers = array_map(fn($num) => intval(substr($num, -2)), $resultNumbers);

      // Adicionar os novos números
      GameHistory::create([
        "description" => $request->description,
        "type" => 'ADDING_NUMBER',
        "result_numbers" => implode(" ", $resultNumbers),
        "numbers" => implode(" ", $numbers),
        'game_id' => $game->id,
      ]);

      // Remover números duplicados
      $uniqueNumbers = array_diff($addedNumbers, $numbers);

      // Obter todas as compras relacionadas ao jogo
      $purchasesBuilder = Purchase::where('game_id', $game->id)
        ->whereIn('status', ['PAID']);

      if ($lastClosedHistory) {
        $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
      }
      $purchases = $purchasesBuilder->get();

      // Agrupar pontos por usuário
      $userPoints = [];
      foreach ($purchases as $purchase) {
        $purchaseNumbers = array_map('intval', explode(' ', $purchase->numbers));
        $purchaseNumbers = array_diff($purchaseNumbers, $addedNumbers); // Remover números já considerados

        $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);
        
        // Adicionar os pontos para o usuário
        $userPoints[$purchase->user_id] = (isset($userPoints[$purchase->user_id]) ? $userPoints[$purchase->user_id] :  0) + count($matchedNumbers);
      }
      
      
      // Verificar condições de prêmios e distribuir
      $awards = GameAward::where('game_id', $game->id)->get();
      foreach ($awards as $award) {
        foreach ($userPoints as $userId => $totalPoints) {
          if ((($award->condition_type === 'EXACT_POINT') && ($totalPoints == $award->exact_point_value)) ||
            (($award->condition_type === 'WINNER') && ($totalPoints >= $award->winner_point_value))
          ) {
            
            // Verificar se o prêmio já foi concedido
            $builder =  UserAwards::where('game_id', $game->id)
              ->where('game_award_id', $award->id)
              ->where('user_id', $userId);
            if ($lastClosedHistory) {
              $builder = $builder->where('created_at', '>=', $lastClosedHistory->created_at);
            }
            $awardExists = $builder->exists();

            if (!$awardExists) {
              UserAwards::create([
                'user_id' => $userId,
                'game_id' => $game->id,
                'game_award_id' => $award->id,
                'amount' => $award->amount,
                'status' => 'PENDING'
              ]);

              // Atualizar indicação e ganhos de referência
              $this->updateReferEarn($userId);
            }
          }
        }
      }
    }

    

    return redirect(route('show-game', ['id' => $game->id]));
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
