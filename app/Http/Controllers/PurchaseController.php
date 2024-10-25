<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
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
    $purchases = Purchase::where('user_id', Auth::user()->id)->get();
    return view('content.purchase.my-purchases', ['purchases' => $purchases]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
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

    // Criação da compra
    $purchase = new Purchase();
    $purchase->gambler_name = $request->gambler_name;
    $purchase->gambler_phone = $request->gambler_phone;
    $purchase->numbers = $request->numbers;
    $purchase->quantity = $request->quantity;
    $purchase->status = "PENDING";
    $purchase->game_id = $request->game_id;
    $purchase->user_id = $request->user_id;

    $array = explode(',', $request->numbers);


    //$numbers = implode(" ", $array);
    //$numbers = explode(' ', $numbers);

    $quantity = count($array);
    $game = Game::find($request->game_id);
    $price = $game->price * $quantity;

    $purchase->quantity = $quantity;
    $purchase->price = $price;

    // TODO: aqui eu devo diminuir o saldo do usuario e atualizar o status do purchase



    // Salvando a compra no banco de dados
    $purchase->save();

    // Redirecionamento com mensagem de sucesso
    return redirect()->back()->with('success', 'Compra realizada com sucesso!');
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

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Purchase $purchase)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Purchase $purchase)
  {
    //
  }
}
