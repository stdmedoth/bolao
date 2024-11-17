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

  // Escolher os ganhadores para e atribuir os prêmios
  public function addGameHistory(Request $request, $id)
  {
    $game = Game::findOrFail($id);
    if ($game->status !== 'CLOSED') {
      return redirect()->back()->withErrors(['error' => [
        "Jogo ainda está aberto"
      ]]);
    }

    $result_numbers_arr = explode(", ", $request->result_numbers);
    foreach ($result_numbers_arr as $result_numbers) {
      $result_numbers = explode(" ", $result_numbers);

      // vê quando que o concurso foi aberto pela ultima vez
      $last_closed_history = GameHistory::where('game_id', $game->id)
        ->where('type', 'OPENED')
        ->orderBy('created_at', "DESC")->first();

      $added_numbers  = [];
      if ($last_closed_history) {
        // Pega todos os numeros que já foram inputados até o momento
        $added_numbers_history = GameHistory::where('game_id', $game->id)
          ->where('type', 'ADDING_NUMBER')
          ->where('created_at', '>=', $last_closed_history->created_at)->get();
        $added_numbers = array_map(fn($e) => explode(" ", $e->numbers), $added_numbers_history->toArray());
        $added_numbers = array_merge(...$added_numbers);
      }


      $numbers = [];
      foreach ($result_numbers as $result_number) {
        $numbers[] = intval(substr($result_number, -2));
      }
      // Adiciona os novos numeros
      $game_history = GameHistory::create([
        "description" => $request->description,
        "type" => 'ADDING_NUMBER',
        "result_numbers" => implode(" ", $result_numbers),
        "numbers" => implode(" ", $numbers),
        'game_id' => $game->id,
      ]);

      // Remove os numeros repetidos
      $commonElements = array_intersect($added_numbers, $numbers);
      $gameNumbers = array_diff($numbers, $commonElements);


      // Obter todas as compras relacionadas ao jogo
      $builder = Purchase::where('game_id', $game->id)->whereIn('status', ['PAID',]);
      if ($last_closed_history) {
        $builder = $builder->where('created_at', '>=', $last_closed_history->created_at);
      }
      $purchases = $builder->get();

      // Verificar cada compra para ver se se qualifica para algum prêmio
      foreach ($purchases as $purchase) {
        $purchaseNumbers = explode(' ', $purchase->numbers); // Números apostados
        foreach ($purchaseNumbers as $key => $purchaseNumber) {
          $purchaseNumbers[$key] = intval($purchaseNumbers[$key]);
        }

        // If the number is repeated, dont consider again
        foreach ($purchaseNumbers as $key => $number) {
          if (in_array($number, $added_numbers)) {
            unset($purchaseNumbers[$key]);
            $purchaseNumbers = array_values($purchaseNumbers);
          }
        }

        $matchedNumbers = array_intersect($gameNumbers, $purchaseNumbers);
        $pointsAchieved = count($matchedNumbers);

        $winners = [];
        // Verificar as condições dos prêmios definidos no GameAwards
        $awards = GameAward::where('game_id', $game->id)->get();

        foreach ($awards as $award) {
          $winners = [];

          foreach ($purchases as $purchase) {
            // Verifica se o usuário se qualifica para o prêmio
            $purchaseNumbers = explode(' ', $purchase->numbers);
            $matchedNumbers = array_intersect($gameNumbers, $purchaseNumbers);
            $pointsAchieved = count($matchedNumbers);

            if (($award->condition_type === 'EXACT_POINT' && $pointsAchieved == $award->exact_point_value) ||
              ($award->condition_type === 'WINNER' && $pointsAchieved == $award->winner_point_value)
            ) {


              // Verifica se algum outro usuario já nao ganhou esse mesmo premio depois de o jogo ter sido aberto pela ultima vez
              $builder = UserAwards::where('game_id', $game->id)
                ->where('game_award_id', $award->id);
              if ($last_closed_history) {
                $builder = $builder->where('created_at', '>=', $last_closed_history->created_at);
              }
              if ($builder->exists()) {
                $winners[] = [
                  'purchase_id' => $purchase->id,
                  'user_id' => $purchase->user_id,
                  'game_id' => $game->id,
                  'game_award_id' => $award->id,
                ];
              }
            }
          }

          // Se houver múltiplos vencedores, dividir o prêmio
          if (count($winners) > 1) {
            $awardAmountPerWinner = $award->amount / count($winners);
            foreach ($winners as &$winner) {
              $winner['amount'] = $awardAmountPerWinner;
              UserAwards::create($winner);


              $has_refer_earn = ReferEarn::where('refer_user_id')->where('invited_user_bought', false)->exists();
              if ($has_refer_earn) {
                $refer_earn = ReferEarn::where('refer_user_id')->where('invited_user_bought', false)->first();
                $refer_earn->invited_user_bought = true;
                $refer_earn->save();
              }
              /*
              // Atualizar o saldo do usuário
              $user = User::find($winner['user_id']);
              $user->balance += $awardAmountPerWinner;
              $user->save();
              */
            }
          } elseif (count($winners) === 1) {
            // Se houver apenas um vencedor, ele recebe o prêmio total
            $winner = $winners[0];
            $winner['amount'] = $award->amount;
            UserAwards::create($winner);

            /*
            $user = User::find($winner['user_id']);
            $user->balance += $award->amount;
            $user->save();
            */
          }
        }
      }
    }

    return redirect(route('show-game', ['id' => $game->id]));
  }
}
