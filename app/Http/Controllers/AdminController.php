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

  public function editUserForm($id){
    $user = User::findOrFail($id);

    $roles = RoleUser::all();

    return view('user.edit', compact('user', 'roles'));
  }

  public function delete($id){
    $user = User::findOrFail($id);
    $user->delete();
    return redirect()->route('usuarios.index')->with('success', 'Usuário deletado com sucesso.');
}


  public function edit($id){
    $user = User::findOrFail($id);
    $roles = RoleUser::get(); // Para preencher as opções de papel
    return view('usuarios.edit', compact('user', 'roles'));
  }

  // Criar um usuário (vendedor ou apostador)
  public function createUser(Request $request){
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8',
      'role_user_id' => 'required|in:seller,gambler',
    ]);

    $user = User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'role_user_id' => $request->role_user_id,
    ]);

    return redirect(route('show-user', ['id' => $user->id]));
  }

  // AdminController.php
  public function update(Request $request, $id){
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



  public function index(){
    $users = User::get();

    return view('content.users.users', ['users' => $users]);

  }

  public function show(){
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
    ]);

    $game = Game::create([
      'name' => $request->name,
      'price' => $request->price,
      'open_at' => $request->open_at,
      'close_at' => $request->close_at,
      'status' => 'OPENED',
      'active' => true,
    ]);

    return redirect(route('show-game', ['id' => $game->id]));
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


    // Fechar o jogo
    $game->status = 'CLOSED';
    $game->save();

    // Obter todas as compras relacionadas ao jogo
    $purchases = Purchase::where('game_id', $game->id)->get();

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

    return redirect(route('show-game', ['id' => $game->id]));
  }
}
