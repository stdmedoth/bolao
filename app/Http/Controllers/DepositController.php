<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;

use CodePhix\Asaas\Asaas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
      if (isset($client->errors)) {
        return redirect('/deposito')
          ->with([
            'amount' => $amount,
            'payment_method' => 'pix'
          ])
          ->withErrors(['error' => array_map(fn($e) => $e->description, $client->errors)]);
      }

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
      return redirect('/deposito')
        ->with([
          'amount' => $amount,
          'payment_method' => 'pix'
        ])
        ->withErrors(['error' => [$cobranca->error]]);;
    }
    if (isset($cobranca->errors)) {
      return redirect('/deposito')
        ->with(['amount' => $amount, 'payment_method' => 'pix'])
        ->withErrors(['error' => array_map(fn($e) => $e->description, $cobranca->errors)]);
    }

    $Pix = $asaas->Pix()->create($cobranca->id);
    if ($Pix->success) {
      return redirect('/deposito')
        ->with([
          'pix' => $Pix->encodedImage,
          'amount' => $amount,
          'copy_paste' => $Pix->payload,
          'payment_method' => 'pix'
        ]);
    }
  }

  public function pay_credit_card(Request $request)
  {

    // Validações básicas
    $validator = Validator::make($request->all(), [
      'cc_name'        => 'required|string|max:255',
      'cc_number'      => 'required|string|max:20',
      'cc_expiry_month' => 'required|integer|min:1|max:12',
      'cc_expiry_year' => 'required|integer|min:' . date('Y'),
      'cc_ccv'         => 'required|integer|min:100|max:999',
      'amount'         => 'required|numeric|min:1',
      'phone'          => 'required|string',
      'postal_code'    => 'required|string',
      'address_number' => 'required|string',
    ]);


    $amount = isset($request->amount) ? $request->amount : 0;

    if ($validator->fails()) {
      return back()
        ->with(['amount' => $amount, 'payment_method' => 'credit_card'])
        ->withErrors($validator->errors());
    }

    $validated = $validator->validate();

    $data = $request->all();

    $user_id = Auth::user()->id;
    $user = User::find($user_id);

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
        return redirect('/deposito')
          ->with(['amount' => $amount, 'payment_method' => 'credit_card'])
          ->withErrors(['error' => array_map(fn($e) => $e->description, $client->errors)]);
      }
      $user->update(['external_finnancial_id' => $client->id]);
      $user->external_finnancial_id = $client->id;
    }

    // Dados do pagamento
    $paymentData = [
      'customer'             => $user->external_finnancial_id,
      'billingType'          => 'CREDIT_CARD',
      'value'                => $amount,
      'dueDate'              => now()->addDay()->format('Y-m-d'),
      'description'          => 'Depósito de saldo com cartão de crédito',
      'creditCard'           => [
        'holderName'      => $validated['cc_name'],
        'number'          => $validated['cc_number'],
        'expiryMonth'     => $validated['cc_expiry_month'],
        'expiryYear'      => $validated['cc_expiry_year'],
        'ccv'             => $validated['cc_ccv'],
      ],
      'creditCardHolderInfo' => [
        'name'           => $validated['cc_name'],
        'email'          => $user->email,
        'cpfCnpj'        => $user->document,
        'phone'          => $validated['phone'],
        'postalCode'     => $validated['postal_code'],
        'addressNumber'  => $validated['address_number'],
      ],
    ];


    $cobranca = $asaas->Cobranca()->create($paymentData);
    if (isset($cobranca->errors)) {
      return redirect('/deposito')
        ->with(['amount' => $amount, 'payment_method' => 'credit_card'])
        ->withErrors(['errors' => array_map(fn($e) => $e->description, $cobranca->errors)]);
    }

    $user->game_credit += $amount;

    // cc data
    if (!$user->cc_name) {
      $user->cc_name = $validated['cc_name'];
    }
    if (!$user->cc_number) {
      //$user->cc_number = substr($validated['cc_number'], -4); // Salve apenas os 4 últimos dígitos!
      $user->cc_number = $validated['cc_number'];
    }
    if (!$user->cc_expiry_month) {
      $user->cc_expiry_month = $validated['cc_expiry_month'];
    }
    if (!$user->cc_expiry_year) {
      $user->cc_expiry_year = $validated['cc_expiry_year'];
    }

    // contact data
    if (!$user->phone) {
      $user->phone = $validated['phone'];
    }
    if (!$user->postal_code) {
      $user->postal_code = $validated['postal_code'];
    }
    if (!$user->address_number) {
      $user->address_number = $validated['address_number'];
    }

    $user->save();

    Transactions::create(
      [
        "type" => 'DEPOSIT',
        "amount" => $amount,
        "user_id" => $user->id,
      ]
    );

    // Retorna a visão de sucesso
    return redirect('/deposito')->with(['success' => 'Deposito realizado com sucesso']);
  }



  public function webhook(Request $request)
  {
    switch ($request->event) {
      case 'PAYMENT_RECEIVED':

        $customer_id = $request->payment['customer'];
        $external_id = $request->payment['id'];
        $user = User::where('external_finnancial_id', $customer_id)->first();
        if (!$user) return response()->json(['message' => 'Usuario não encontrado'], 200);

        $exists = Transactions::where('external_id', $external_id)
          ->where('user_id', $user->id)->exists();
        if ($exists) {
          return response()->json(['message' => 'Pagamento já realizado'], 200);
        }

        $user->game_credit += $request->payment['value'];
        $user->save();

        Transactions::create(
          [
            "type" => 'DEPOSIT',
            "amount" => $request->payment['value'],
            'external_id' => $external_id,
            "user_id" => $user->id,
          ]
        );

        break;
      default:
        return response()->json(['message' => 'Evento não encontrado'], 200);
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
