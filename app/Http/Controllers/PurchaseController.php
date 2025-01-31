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
  public function index()
  {
    //
    $builder = new Purchase();
    if (Auth::user()->role->level_id !== 'admin') {
      $builder = $builder->where('user_id', Auth::user()->id);
    }

    if (Auth::user()->role->level_id == 'seller') {
      // se for vendedor, filtrar compras de usuarios desse vendedor
      $builder->orWhere(function ($q)  {
        $q->whereHas('user', fn($q2) => $q2->where('invited_by_id', Auth::user()->id));
      });
    }

    $purchases = $builder->orderBy('created_at', 'desc');
    $purchases = $builder->paginate(5);
    return view('content.purchase.my-purchases', ['purchases' => $purchases]);
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

    if (!in_array(Auth::user()->role->level_id, ['admin' , 'seller'])) {
      if ($user->balance < $purchase->price) {
        return redirect()->route('deposito')
                    ->with('amount', $purchase->price)
                    ->withErrors(['error' => "Sua conta não tem saldo suficiente para realizar a operação, faça um depósito"]);
      }

      $user->balance -= $purchase->price;
      $user->save();
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

    return redirect()->route('minhas_compras')->with('success', 'Aposta paga com sucesso!');
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
    $numbers = array_map('intval',$numbers);
    sort($numbers);
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
    $quantity = 1;
    $game = Game::find($request->game_id);
    $price = $game->price * $quantity;

    $purchase->quantity = $quantity;
    $purchase->price = $price;

    // Salvando a compra no banco de dados
    $purchase->save();

    $user = User::find($request->user_id);
    if ($user->balance >= $price) {
      $user->balance -= $price;
      $user->save();


      $purchase->status = "PAID";
      $purchase->save();

      Transactions::create(
        [
          "type" => 'PAY_PURCHASE',
          "amount" => $purchase->price,
          "user_id" => $purchase->user_id,
        ]
      );
    }


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
    return redirect()->route('minhas_compras')->with('success', 'Compra deletada com sucesso!');
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
