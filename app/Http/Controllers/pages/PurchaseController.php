<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    //
    $builder = new Purchase();
    $games = Game::select(['id', 'status', 'name'])->whereIn('status', ['OPENED', 'CLOSED'])->get();

    if (Auth::user()->role->level_id !== 'admin') {
      $builder = $builder->where('user_id', Auth::user()->id);
    }

    if (Auth::user()->role->level_id == 'seller') {
      // se for vendedor, filtrar compras de usuarios desse vendedor
      $builder = $builder->orWhere(function ($q) {
        $q->whereHas('user', fn($q2) => $q2->where('invited_by_id', Auth::user()->id));
      });
    }

    if ($request->has('search') && $request->search != '') {
      $builder = $builder->where(function ($q) use ($request) {
        $q->whereHas('game', function ($gameq) use ($request) {
          $gameq->where('name', 'like', '%' . $request->search . '%');
        })->orWhere('numbers', 'like', '%' . $request->search . '%');
      });
    }

    if ($request->has('game_id') && $request->game_id != '') {
      $builder = $builder->where('game_id', $request->game_id);
    }

    if ($request->has('status') && $request->status != '') {
      $builder = $builder->where('status', $request->status);
    }

    $builder = $builder->orderBy('created_at', 'desc');
    $purchases = $builder->paginate(5);

    return view('content.purchase.my-purchases', ['purchases' => $purchases, 'games' => $games]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  public function pay(Request $request, $id)
  {

    $purchase = Purchase::find($id);

    $user = User::find($purchase->user_id);

    $role_level_id = Auth::user()->role->level_id;

    if (!in_array($role_level_id, ['admin'])) {

      if ($role_level_id == 'seller' && ($purchase->user_id !== Auth::user()->id)) {
        $user = User::find(Auth::user()->id);
      }

      if ($user->game_credit < $purchase->price) {
        return redirect()->route('deposito')
          ->with('amount', $purchase->price)
          ->withErrors(['error' => "Sua conta não tem crédito suficiente para realizar a operação"]);
      }


      $user->game_credit -= $purchase->price;
      $user->save();

      // Se estiver sendo pago pelo apostador, o vendedor que convidou o apostador ganha uma porcentagem em cima da venda
      if ($role_level_id == 'gambler') {
        if ($user->invited_by_id) {
          $invited_by = User::find($user->invited_by_id);
          $invited_by->balance = $invited_by->balance + $purchase->price * $invited_by->comission_percent;
        }
      }
    }

    $purchase->status = "PAID";
    $purchase->paid_by_user_id = Auth::user()->id;
    $purchase->save();

    Transactions::create(
      [
        "type" => 'PAY_PURCHASE',
        "amount" => $purchase->price,
        "user_id" => $purchase->user_id,
      ]
    );

    return redirect()->route('minhas_compras', request()->query())->with('success', 'Aposta paga com sucesso!');
  }




  public function withdraw(Request $request, $id)
  {
    $purchase = Purchase::find($id);

    $user = User::find($purchase->user_id);

    $role_level_id = Auth::user()->role->level_id;

    if (!in_array($role_level_id, ['admin'])) {

      if ($role_level_id == 'seller' && ($purchase->user_id !== Auth::user()->id)) {
        $user = User::find(Auth::user()->id);
      }

      $user->game_credit += $purchase->price;
      $user->save();

      // Se estiver sendo pago pelo apostador, o vendedor que convidou o apostador ganha uma porcentagem em cima da venda
      if ($role_level_id == 'gambler') {
        if ($user->invited_by_id) {
          $invited_by = User::find($user->invited_by_id);
          if ($invited_by->role->level_id == 'seller') {
            $invited_by->balance = $invited_by->balance - $purchase->price * $invited_by->comission_percent;
          }
        }
      }
    }

    $purchase->status = "PENDING";
    $purchase->save();

    Transactions::create(
      [
        "type" => 'PAY_PURCHASE_WITHDRAWAL',
        "amount" => $purchase->price,
        "user_id" => $purchase->user_id,
      ]
    );

    return redirect()->route('minhas_compras', request()->query())->with('success', 'Aposta estornada com sucesso!');
  }



  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // Validação dos dados recebidos
    $request->validate([
      'gambler_name' => 'required|string|max:255',
      'gambler_phone' => 'nullable|string|max:20',
      'numbers' => 'required|string',
      //'price' => 'required|numeric|min:0',
      //'status' => 'required|in:PAID,PENDING,CANCELED',
      //'quantity' => 'required|numeric|min:0',
      'game_id' => 'required|exists:games,id',
      'user_id' => 'required|exists:users,id',
    ]);

    $numbers = explode(' ', $request->numbers);
    $numbers = array_map('intval', $numbers);
    //sort($numbers); // Não deve ficar ordenado
    $numbers = implode(' ', $numbers);

    // Criação da compra
    $purchase = new Purchase();
    $purchase->gambler_name = $request->gambler_name;
    $purchase->gambler_phone = $request->gambler_phone;
    $purchase->numbers = $numbers;
    //$purchase->quantity = $request->quantity;

    $purchase->quantity = 1;
    $purchase->status = "PENDING";
    $purchase->game_id = $request->game_id;
    $purchase->user_id = $request->user_id;
    
    $array = explode(' ', $request->numbers);
    
    
    //$numbers = implode(" ", $array);
    //$numbers = explode(' ', $numbers);
    
    //$quantity = count($array);
    
    $user = User::find($request->user_id);
    $purchase->paid_by_user_id = $user->id;

    $role_level_id = Auth::user()->role->level_id;

    // Se estiver sendo pago pelo vendedor, a compra fica mais barata, o apostador paga menos credito
    if ($role_level_id == 'seller') {
      $price = $purchase->price - $purchase->price * $user->comission_percent;
    }


    $quantity = 1;
    $game = Game::find($request->game_id);
    $price = $game->price * $quantity;

    $purchase->quantity = $quantity;
    $purchase->price = $price;

    // Salvando a compra no banco de dados
    $purchase->save();

    if (!in_array($role_level_id, ['admin'])) {
      if ($user->game_credit < $price) {
        return redirect()->back()->with(['success' => 'Compra realizada com sucesso! Aguardando pagamento...', 'tab' => 'tab-mybets']);
      }

      $user->game_credit -= $price;
      $user->save();

      // Se estiver sendo pago pelo apostador, o vendedor que convidou o apostado ganha uma porcentagem em cima da venda
      if ($role_level_id == 'gambler') {
        if ($user->invited_by_id) {
          $invited_by = User::find($user->invited_by_id);
          $invited_by->balance = $invited_by->balance + $purchase->price * $invited_by->comission_percent;
        }
      }
    }

    $purchase->status = "PAID";
    $purchase->save();

    Transactions::create(
      [
        "type" => 'PAY_PURCHASE',
        "amount" => $purchase->price,
        "user_id" => $purchase->user_id,
      ]
    );



    // Redirecionamento com mensagem de sucesso
    return redirect()->back()->with(['success' => 'Compra realizada com sucesso!', 'tab' => 'tab-mybets']);
  }


  /**
   * Store a newly created resource in storage.
   */
  public function repeat(Request $request)
  {
    // Validação dos dados recebidos
    $request->validate([
      'repeat_game_id' => 'required|exists:games,id',
      'repeat_game_purchase_id' => 'required|exists:purchases,id',
    ]);

    $old_purchase = Purchase::find($request->repeat_game_purchase_id);

    // Criação da compra
    $newPurchase = $old_purchase->replicate();
    $newPurchase->status = "PENDING";
    $newPurchase->game_id = $request->repeat_game_id;

    $user = User::find(Auth::user()->id);
    $role_level_id = $user->role->level_id;

    // Se estiver sendo pago pelo vendedor, a compra fica mais barata, o apostador paga menos credito
    if ($role_level_id == 'seller') {
      $price = $newPurchase->price - $newPurchase->price * $user->comission_percent;
    }


    $quantity = 1;
    $game = Game::find($request->repeat_game_id);
    $price = $game->price * $quantity;

    $newPurchase->quantity = $quantity;
    $newPurchase->price = $price;

    // Salvando a compra no banco de dados
    $newPurchase->save();

    if (!in_array($role_level_id, ['admin'])) {
      if ($user->game_credit < $price) {
        return redirect()->back()->with(['success' => 'Compra realizada com sucesso! Aguardando pagamento...', 'tab' => 'tab-mybets']);
      }

      $user->game_credit -= $price;
      $user->save();

      // Se estiver sendo pago pelo apostador, o vendedor que convidou o apostado ganha uma porcentagem em cima da venda
      if ($role_level_id == 'gambler') {
        if ($user->invited_by_id) {
          $invited_by = User::find($user->invited_by_id);
          $invited_by->balance = $invited_by->balance + $newPurchase->price * $invited_by->comission_percent;
        }
      }
    }

    $newPurchase->status = "PAID";
    $newPurchase->save();

    Transactions::create(
      [
        "type" => 'PAY_PURCHASE',
        "amount" => $newPurchase->price,
        "user_id" => $newPurchase->user_id,
      ]
    );



    // Redirecionamento com mensagem de sucesso
    return redirect()->back()->with(['success' => 'Compra realizada com sucesso!', 'tab' => 'tab-mybets']);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    $request->validate([
      'numbers' => 'required|string',
      'status' => 'required|in:PAID,PENDING,CANCELED,FINISHED',
      'quantity' => 'required|integer',
      'price' => 'required|numeric',
      'game_id' => 'required|exists:games,id',
      'user_id' => 'required|exists:users,id',
    ]);

    $purchase = Purchase::find($id);
    $purchase->update($request->all());
    return redirect()->route('minhas_compras')->with('success', 'Compra atualizada com sucesso!');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, $id)
  {
    $purchase = Purchase::find($id);
    $purchase->delete();
    return redirect()->route('minhas_compras', request()->query())->with('success', 'Compra deletada com sucesso!');
  }

  /**
   * Display the specified resource.
   */
  public function show(Purchase $purchase)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Purchase $purchase)
  {
    //
  }
}
