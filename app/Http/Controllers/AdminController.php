<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Game;
use App\Models\GameAward;
use App\Models\GameHistory;
use App\Models\Purchase;
use App\Models\UserAwards;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

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
      'password' => 'required|string|min:8',
      'role_user_id' => 'required|exists:role_users,id',
    ]);

    try {
      // Criação do usuário com os dados validados
      $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
        'role_user_id' => $validatedData['role_user_id'],
      ]);

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
      'role_user_id' => 'required|in:seller,gambler',
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
    return view('content.users.create_user', ['roles' => $roles]);
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
      'awards.*.condition_type' => 'required|in:MINIMUM_POINT,EXACT_POINT',
      'awards.*.minimum_point_value' => 'nullable|integer',
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

    return redirect(route('show-game', ['id' => $game->id]))->with('success', 'Jogo criado com sucesso, juntamente com os prêmios!');
  }

  public function openGame(Request $request, $id)
  {
    $game = Game::findOrFail($id);
    // Fechar o jogo
    $game->status = 'OPENED';
    $game->save();

    return redirect(route('show-game', ['id' => $game->id]));
  }

  // Fechar o jogo e definir o status para "CLOSED"
  // Escolher um ganhador para o jogo e atribuir o prêmio
  public function closeGame(Request $request, $id)
  {
    $game = Game::findOrFail($id);

    if ($game->status !== 'OPENED') {
      return redirect()->back()->withErrors(['errors' => [
        "Jogo já está fechado"
      ]]);
    }

    $last_closed_history = GameHistory::where('game_id', $game->id)
      ->where('status', 'CLOSED')
      ->orderBy('created_at', "DESC")->first();

    // Obter todas as compras relacionadas ao jogo
    $builder = Purchase::where('game_id', $game->id);
    if ($last_closed_history) {
      $builder = $builder->where('created_at', '>=', $last_closed_history->createt_at);
    }
    $purchases = $builder->get();

    // Extrair os números do jogo
    $gameNumbers = explode(',', $game->numbers); // Números sorteados no jogo

    // Verificar cada compra para ver se se qualifica para algum prêmio
    foreach ($purchases as $purchase) {
      $purchaseNumbers = explode(',', $purchase->numbers); // Números apostados
      $matchedNumbers = array_intersect($gameNumbers, $purchaseNumbers);
      $pointsAchieved = count($matchedNumbers);

      // Verificar as condições dos prêmios definidos no GameAwards
      $awards = GameAward::where('game_id', $game->id)->get();
      foreach ($awards as $award) {
        $userAwardCreated = false;
        if ($award->condition_type === 'MINIMUM_POINT' && $pointsAchieved >= $award->minimum_point_value) {
          $userAwardCreated = true;
        } elseif ($award->condition_type === 'EXACT_POINT' && $pointsAchieved == count($gameNumbers)) {
          $userAwardCreated = true;
        }

        if ($userAwardCreated) {
          // Atualizar o saldo do usuário
          $user = $purchase->user;
          $user->balance += $award->amount;
          $user->save();

          // Criar prêmio para o apostador com status "PENDING"
          UserAwards::create([
            'purchase_id' => $purchase->id,
            'user_id' => $user->id,
            'amount' => $award->amount,
            'status' => 'PAID',
          ]);
        }
      }
    }

    GameHistory::create([
      'status' => 'CLOSED',
      'game_id' => $game->id
    ]);


    // Fechar o jogo
    $game->status = 'CLOSED';
    $game->save();

    return redirect(route('show-game', ['id' => $game->id]));
  }
}
