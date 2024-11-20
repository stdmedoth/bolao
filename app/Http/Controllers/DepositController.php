<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;

use CodePhix\Asaas\Asaas;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
    return view('content.deposit.deposit');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }



  public function create_pix(Request $request)
  {
    $user_id = Auth::user()->id;
    $user = User::find($user_id);

    $amount = $request->amount;

    // Instancie o cliente Asaas usando a instância do adapter previamente criada.
    $asaas = new Asaas(env('ASAAS_API_KEY'), env('ASAAS_API_ENV'));


    if (!$user->external_finnancial_id) {
      $client_data = [
        "name"              =>          $user->name,
        "cpfCnpj"           =>          $user->document,
        "email"             =>          $user->email,
        //"phone"             =>          $user->phone,
        //"mobilePhone"       =>          $user->cellphone,
        //"externalReference" =>          $user->account . "_" . $user->id
      ];

      $client = $asaas->Cliente()->create($client_data);
      $user->update(['external_finnancial_id' => $client->id]);
      $user->external_finnancial_id = $client->id;
    }

    $dadosCobranca = [
      'customer'             => $user->external_finnancial_id,
      'billingType'          => 'PIX',
      'value'                => $amount,
      'dueDate'              => date("Y-m-d H:i:s", strtotime("+1 day")), // TODO: Estipular vencimento em Y-m-d
      'description'          => "Deposito de saldo",
      'externalReference'    => "",
      // 'discount'             => '', //TODO: verificar saldo da carteira e aplicar desconto com if(saldo_carteira)
      // 'interest'             => [ // TODO: informar apenas caso queira cobrar juros apos vencimento
      //     "value"
      // ],
      // 'fine'                 => [ // TODO: informar apenas caso queira cobrar multa apos vencimento
      //     "value"
      // ],
    ];

    $cobranca = $asaas->Cobranca()->create($dadosCobranca);
    if (isset($cobranca->error)) {
      return redirect('/deposito');
    }

    $Pix = $asaas->Pix()->create($cobranca->id);
    if ($Pix->success) {
      return view('content.deposit.deposit', ['pix' => $Pix->encodedImage, 'amount' => $amount, 'copy_paste' => $Pix->payload]);
    }


    return redirect('/deposito');
  }

  public function pay_credit_card(Request $request)
  {

    $data = $request->all();

    $user_id = Auth::user()->id;
    $user = User::find($user_id);

    $amount = $request->amount;

    // Instancie o cliente Asaas
    $asaas = new Asaas(env('ASAAS_API_KEY'), env('ASAAS_API_ENV'));

    // Criar cliente no Asaas, se ainda não existir
    if (!$user->external_finnancial_id) {
      $client_data = [
        "name"    => $user->name,
        "cpfCnpj" => $user->document,
        "email"   => $user->email,
      ];

      $client = $asaas->Cliente()->create($client_data);
      if (isset($client->errors)) {
        return redirect('/deposito')->withErrors(['error' => array_map(fn($e) => $e->description, $client->errors)]);
      }
      $user->update(['external_finnancial_id' => $client->id]);
      $user->external_finnancial_id = $client->id;
    }

    // Dados do pagamento usando informações armazenadas no usuário
    $paymentData = [
      'customer'         => $user->external_finnancial_id,
      'billingType'      => 'CREDIT_CARD',
      'value'            => $amount,
      'dueDate'          => date("Y-m-d H:i:s", strtotime("+1 day")),
      'description'      => "Depósito de saldo com cartão de crédito",
      'creditCard'       => [
        'holderName'      => $user->cc_name,
        'number'          => $user->cc_number,
        'expiryMonth'     => $user->cc_expiry_month,
        'expiryYear'      => $user->cc_expiry_year,
        'ccv'             => $user->cc_ccv,
      ],
      'creditCardHolderInfo' => [
        'name'           => $user->name,
        'email'          => $user->email,
        'cpfCnpj'        => $user->document,
        'phone'          => $data['phone'],
        'postalCode'     => $data['postal_code'],
        'addressNumber'     => $data['address_number'],
      ],
    ];

    $cobranca = $asaas->Cobranca()->create($paymentData);

    if (isset($cobranca->errors)) {
      return redirect('/deposito')->withErrors(['error' => array_map(fn($e) => $e->description, $cobranca->errors)]);
    }

    $user->balance += $amount;
    $user->save();

    Transactions::create(
      [
        "type" => 'DEPOSIT',
        "amount" => $amount,
        "user_id" => $user->id,
      ]
    );

    // Retorna a visão de sucesso
    return redirect('/deposito');
  }



  public function webhook(Request $request)
  {
    $data = $request->all();
    switch ($data['event']) {
      case 'PAYMENT_RECEIVED':
        $customer_id = $data['payment']['customer'];
        $user = User::where('external_finnancial_id', $customer_id)->first();
        if (!$user) return response()->json(['message' => 'Usuario não encontrado'], 400);

        $user->balance += $data['payment']['value'];
        $user->save();

        break;
    }
    return response()->json(['message' => 'OK'], 200);
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
  public function show(Deposit $deposit)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Deposit $deposit)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Deposit $deposit)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Deposit $deposit)
  {
    //
  }
}
