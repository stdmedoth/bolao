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

    if (Auth::user()->role->level_id == 'gumbler') {
      $builder = $builder->where('user_id', Auth::user()->id);
    }

    if (Auth::user()->role->level_id == 'seller') {
      // Mostrar compras feitas por ele ou compras de clientes dele
      $builder = $builder->where(function ($query) {
        $query->where('user_id', Auth::user()->id)
          ->orWhere('seller_id', Auth::user()->id);
      });
    }


    if ($request->has('search') && $request->search != '') {
      $builder = $builder->where(function ($q) use ($request) {
        $q->whereHas('game', function ($gameq) use ($request) {
          $gameq->where('name', 'like', '%' . $request->search . '%');
        })->orWhere('numbers', 'like', '%' . $request->search . '%')
          ->orWhere('gambler_name', 'like', '%' . $request->search . '%');
      });
    }

    if ($request->has('game_id') && $request->game_id != '') {
      $builder = $builder->where('game_id', $request->game_id);
      //dd($builder);
    }

    if ($request->has('status') && $request->status != '') {
      $builder = $builder->where('status', $request->status);
    }

    // only shows purchases in same round that the last game opening
    $builder = $builder->whereHas('game', function ($query) {
      $query->whereColumn('purchases.round', 'games.round');
    });

    //$builder = $builder->orderBy('created_at', 'desc');
    $builder = $builder->orderBy('gambler_name', 'asc');

    $purchases = $builder->paginate(20);

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
        return redirect()->route('transactions.deposito')
          ->with('amount', $purchase->price)
          ->withErrors(['error' => "Sua conta não tem crédito suficiente para realizar a operação"]);
      }


      $user->game_credit -= $purchase->price;
      $user->save();

      // Se estiver sendo pago pelo apostador, o vendedor que convidou o apostador ganha uma porcentagem em cima da venda
      if ($role_level_id == 'gambler') {
        if ($user->invited_by_id) {
          $invited_by = User::find($user->invited_by_id);

          $comission = $purchase->price * $invited_by->comission_percent;
          Transactions::create(
            [
              "type" => 'PAY_PURCHASE_COMISSION',
              "game_id" => $purchase->game_id,
              "purchase_id" => $purchase->id,
              "amount" => $comission,
              "user_id" => $user->invited_by_id,
            ]
          );
          $invited_by->game_credit = $invited_by->game_credit + $comission;
          $invited_by->save();
        }
      }
    }

    $purchase->status = "PAID";
    $purchase->paid_by_user_id = Auth::user()->id;
    $purchase->save();

    Transactions::create(
      [
        "type" => 'PAY_PURCHASE',
        "game_id" => $purchase->game_id,
        "purchase_id" => $purchase->id,
        "amount" => $purchase->price,
        "user_id" => $purchase->paid_by_user_id,
      ]
    );

    if ($role_level_id == 'seller') {
      $comission = $purchase->price * $user->comission_percent;
      Transactions::create(
        [
          "type" => 'PAY_PURCHASE_COMISSION',
          "game_id" => $purchase->game_id,
          "purchase_id" => $purchase->id,
          "amount" => $comission,
          "user_id" => $user->id,
        ]
      );
      $user->game_credit = $user->game_credit + $comission;
      $user->save();
    }

    return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta paga com sucesso!');
  }




  public function withdraw(Request $request, $id)
  {
    $purchase = Purchase::find($id);

    $user = User::find($purchase->paid_by_user_id);

    $role_level_id = Auth::user()->role->level_id;

    if (!in_array($role_level_id, ['admin'])) {

      $user->game_credit += $purchase->price;
      $user->save();

      // Se estiver sendo pago pelo apostador, o vendedor que convidou o apostador ganha uma porcentagem em cima da venda
      if ($role_level_id == 'gambler') {
        if ($user->invited_by_id) {
          $invited_by = User::find($user->invited_by_id);
          if ($invited_by->role->level_id == 'seller') {
            $comission = $purchase->price * $invited_by->comission_percent;
            Transactions::create(
              [
                "type" => 'PAY_PURCHASE_COMISSION_WITHDRAWAL',
                "game_id" => $purchase->game_id,
                "purchase_id" => $purchase->id,
                "amount" => $comission,
                "user_id" => $user->invited_by_id,
              ]
            );
            $invited_by->game_credit = $invited_by->game_credit - $comission;
            $invited_by->save();
          }
        }
      }
    }

    $purchase->status = "PENDING";

    $purchase->paid_by_user_id = NULL;
    $purchase->save();

    if ($role_level_id == 'seller') {
      $comission = $purchase->price * $user->comission_percent;
      Transactions::create(
        [
          "type" => 'PAY_PURCHASE_COMISSION_WITHDRAWAL',
          "game_id" => $purchase->game_id,
          "purchase_id" => $purchase->id,
          "amount" => $comission,
          "user_id" => $user->id,
        ]
      );
      $user->game_credit = $user->game_credit - $comission;
      $user->save();
    }

    Transactions::create(
      [
        "type" => 'PAY_PURCHASE_WITHDRAWAL',
        "game_id" => $purchase->game_id,
        "purchase_id" => $purchase->id,
        "amount" => $purchase->price,
        "user_id" => $user->id,
      ]
    );

    return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta estornada com sucesso!')->with('tab', 'tab-mybets');
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
      'seller_id' => 'required|exists:users,id',
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
    $purchase->seller_id = $request->seller_id;
    $purchase->user_id = $request->user_id;

    $array = explode(' ', $request->numbers);


    //$numbers = implode(" ", $array);
    //$numbers = explode(' ', $numbers);

    //$quantity = count($array);

    $user = User::find($request->user_id);
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

    $purchase->round = $game->round;

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
        if ($request->seller_id) {
          $invited_by = User::find($request->seller_id);
          $comission = $purchase->price * $invited_by->comission_percent;
          Transactions::create(
            [
              "type" => 'PAY_PURCHASE_COMISSION',
              "game_id" => $purchase->game_id,
              "purchase_id" => $purchase->id,
              "amount" => $comission,
              "user_id" => $request->seller_id,
            ]
          );
          $invited_by->game_credit = $invited_by->game_credit + $comission;
          $invited_by->save();
        }
      }
    }

    $purchase->status = "PAID";
    $purchase->paid_by_user_id = $user->id;
    $purchase->save();

    if ($role_level_id == 'seller') {
      $comission = $purchase->price * $user->comission_percent;
      Transactions::create(
        [
          "type" => 'PAY_PURCHASE_COMISSION',
          "game_id" => $purchase->game_id,
          "purchase_id" => $purchase->id,
          "amount" => $comission,
          "user_id" => $user->id,
        ]
      );
      $user->game_credit = $user->game_credit + $comission;
      $user->save();
    }

    Transactions::create(
      [
        "type" => 'PAY_PURCHASE',
        "game_id" => $purchase->game_id,
        "purchase_id" => $purchase->id,
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
    $newPurchase->user_id = $user->id;
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
        if ($request->seller_id) {
          $invited_by = User::find($request->seller_id);

          $comission = $newPurchase->price * $invited_by->comission_percent;
          Transactions::create(
            [
              "type" => 'PAY_PURCHASE_COMISSION',
              "game_id" => $newPurchase->game_id,
              "purchase_id" => $newPurchase->id,
              "amount" => $comission,
              "user_id" => $request->seller_id,
            ]
          );
          $invited_by->game_credit = $invited_by->game_credit + $comission;
          $invited_by->save();
        }
      }
    }

    $newPurchase->status = "PAID";
    $newPurchase->paid_by_user_id = $user->id;
    $newPurchase->save();

    if ($role_level_id == 'seller') {
      $comission = $newPurchase->price * $user->comission_percent;
      Transactions::create(
        [
          "type" => 'PAY_PURCHASE_COMISSION',
          "game_id" => $newPurchase->game_id,
          "purchase_id" => $newPurchase->id,
          "amount" => $comission,
          "user_id" => $user->id,
        ]
      );
      $user->game_credit = $user->game_credit + $comission;
      $user->save();
    }

    Transactions::create(
      [
        "type" => 'PAY_PURCHASE',
        "game_id" => $newPurchase->game_id,
        "purchase_id" => $newPurchase->id,
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
      'seller_id' => 'required|exists:users,id',
    ]);

    $purchase = Purchase::find($id);
    $purchase->update($request->all());
    return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta realizada com sucesso!')->with('tab', 'tab-mybets');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function delete(Request $request)
  {

    $purchase = Purchase::find($request->delete_game_purchase_id);
    $purchase->status =  'CANCELED';
    $purchase->save();

    //$purchase->delete();
    return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta deletada com sucesso!')->with('tab', 'tab-mybets');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, $id)
  {
    $purchase = Purchase::find($id);
    $purchase->status =  'CANCELED';
    $purchase->save();

    //$purchase->delete();
    return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta deletada com sucesso!')->with('tab', 'tab-mybets');
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
