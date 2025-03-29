<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Models\User;
use App\Models\Withdrawal;
use CodePhix\Asaas\Asaas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
    return view('content.withdraw.withdraw');
  }

  public function withdraw_pay(Request $request)
  {
    // Obtém o ID do usuário autenticado
    $user_id = Auth::user()->id;
    $user = User::find($user_id);

    // Valida os dados recebidos na request
    $request->validate([
      'pix_key' => 'required|string',
      'amount' => 'required|numeric|min:1',
      'pix_key_type' => 'required|string|in:CPF,CNPJ,EMAIL,PHONE,EVP'
    ]);

    $pix_key = $request->pix_key;
    $amount = $request->amount;
    $pix_key_type = $request->pix_key_type;

    // Verifica se o usuário tem saldo suficiente
    if ($user->balance < $amount) {
      return redirect('/saque')->withErrors(['error' => 'Saldo insuficiente para o saque.']);
    }

    // Instancia o cliente Asaas
    $asaas = new Asaas(env('ASAAS_API_KEY'), env('ASAAS_API_ENV'));

    // Dados da transferência
    $transferData = [
      'pixAddressKey' => $pix_key,
      'value' => $amount,
      "pixAddressKeyType" => $pix_key_type,
      'description' => 'Saque de saldo via PIX',
    ];

    // Cria a transferência no Asaas
    $transfer = $asaas->Transferencia()->conta($transferData);

    // Verifica se houve erro ao criar a transferência
    if (isset($transfer->errors)) {
      return redirect('/saque')->withErrors(['error' => array_map(fn($e) => $e->description, $transfer->errors)]);
    }

    // Deduz o saldo do usuário
    $user->balance -= $amount;
    if (!$user->pix_key) $user->pix_key = $pix_key;
    if (!$user->pix_key_type) $user->pix_key_type = $pix_key_type;
    $user->save();

    // Cria o registro de transação
    Transactions::create([
      "type" => 'WITHDRAWAL',
      "amount" => $amount,
      "user_id" => $user->id,
      "details" => json_encode([
        "pix_key" => $pix_key,
        "transfer_id" => $transfer->id,
      ]),
    ]);

    // Retorna a visão de sucesso
    return redirect('/saque')->with('success', 'Saque realizado com sucesso.');
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
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(Withdrawal $withdrawal)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Withdrawal $withdrawal)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Withdrawal $withdrawal)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Withdrawal $withdrawal)
  {
    //
  }
}
