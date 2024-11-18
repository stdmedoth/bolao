<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
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
    $asaas = new Asaas(env('ASAAS_API_KEY', 'ASAAS_API_ENV'));


    if(!$user->external_finnancial_id){
      $client_data = [
        "name"              =>          $user->name,
        "cpfCnpj"           =>          $user->document,
        "email"             =>          $user->email,
        "phone"             =>          $user->phone,
        "mobilePhone"       =>          $user->cellphone,
        "externalReference" =>          $user->account . "_" . $user->id
      ];
  
      $client = $asaas->Cliente()->create($client_data);

      $user->update(['external_finnancial_id' => $client->getId()]);
    }

    $dadosCobranca = [
      'customer'             => $client->external_finnancial_id,
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

    $Pix = $asaas->Pix()->create($cobranca->getId());
    if ($Pix->success) {
      return view('content.deposit.deposit', ['pix' => $Pix->encodedImage, 'value' => $amount]);
    }else{
      
    }

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
