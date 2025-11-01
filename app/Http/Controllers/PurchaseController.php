<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\ReferEarn;
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

    if (Auth::user()->role->level_id == 'gambler') {
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
          ->orWhere('gambler_name', 'like', '%' . $request->search . '%')
          ->orWhere('gambler_phone', 'like', '%' . $request->search . '%')
          ->orWhere('identifier', 'like', '%' . $request->search . '%');
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

    if ($purchase->status == "PAID") {
      return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta já estava paga!');
    }

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

      // Se estiver sendo pago pelo apostador, o usuario que convidou o apostador ganha indicação apenas na PRIMEIRA compra
      if ($role_level_id == 'gambler') {
        if ($user->invited_by_id) {
          // Verifica se é a primeira compra paga do apostador (ANTES de marcar como PAID)
          $has_previous_paid_purchases = Purchase::where('user_id', $user->id)
            ->where('status', 'PAID')
            ->exists();
          
          // Se NÃO tem compras pagas anteriores, esta é a primeira
          if (!$has_previous_paid_purchases) {
            $refer = ReferEarn::where(
              'refer_user_id',
              $user->invited_by_id
            )->where(
              'invited_user_id',
              $user->id
            )->where(
              'earn_paid',
              FALSE
            )->first();
            if ($refer) {
              $refer->invited_user_bought = TRUE;
              $refer->save();
            }
          }
        }

        // Se estiver sendo pago pelo apostador, o vendedor que convidou o apostador ganha uma porcentagem em cima da venda
        if ($user->seller_id) {
          $seller = User::find($user->seller_id);
          $comission = $purchase->price * $seller->comission_percent;
          Transactions::create(
            [
              "type" => 'PAY_PURCHASE_COMISSION',
              "game_id" => $purchase->game_id,
              "purchase_id" => $purchase->id,
              "amount" => $comission,
              "user_id" => $user->seller_id,
            ]
          );
          $seller->game_credit = $seller->game_credit + $comission;
          $seller->save();
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

    if ($purchase->status == "PENDING") {
      return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta já estava pendente!');
    }

    $user = User::find($purchase->paid_by_user_id);

    $role_level_id = $user->role->level_id;

    if (!in_array($role_level_id, ['admin'])) {

      $user->game_credit += $purchase->price;
      $user->save();

      // Se estiver sendo cancelado pelo apostador, remove indicação apenas se esta era a primeira compra
      if (in_array($role_level_id, ['gambler'])) {
        // Verifica se esta é a primeira compra paga do apostador (ANTES de marcar como PENDING)
        $purchase_qnt = Purchase::where('user_id', $user->id)
          ->where('status', 'PAID')
          ->count();
        
        // Se esta é a primeira compra (count = 1), remove a indicação
        if ($purchase_qnt == 1 && $user->invited_by_id) {
          $refer = ReferEarn::where(
            'refer_user_id',
            $user->invited_by_id
          )->where(
            'invited_user_id',
            $user->id
          )->where(
            'earn_paid',
            FALSE
          )->first();
          if ($refer) {
            $refer->invited_user_bought = FALSE;
            $refer->save();
          }
        }

        if ($user->seller_id) {
          $seller = User::find($user->seller_id);
          $comission = $purchase->price * $seller->comission_percent;
          Transactions::create(
            [
              "type" => 'PAY_PURCHASE_COMISSION_WITHDRAWAL',
              "game_id" => $purchase->game_id,
              "purchase_id" => $purchase->id,
              "amount" => $comission,
              "user_id" => $user->seller_id,
            ]
          );
          $seller->game_credit = $seller->game_credit - $comission;
          $seller->save();
        }
      }
    }

    $purchase->status = "PENDING";

    $purchase->paid_by_user_id = NULL;
    $purchase->save();

    if (in_array($role_level_id, ['seller'])) {
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
    $purchase->identifier = generate_identifier();
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

      // Se estiver sendo pago pelo apostador, o vendedor vinculado ganha comissão em TODA compra
      if ($role_level_id == 'gambler') {
        // Usa o seller_id do request (enviado pelo formulário) ou do usuário
        $sellerId = $request->seller_id ?? $user->seller_id;
        if ($sellerId) {
          $seller = User::find($sellerId);
          $comission = $purchase->price * $seller->comission_percent;
          Transactions::create(
            [
              "type" => 'PAY_PURCHASE_COMISSION',
              "game_id" => $purchase->game_id,
              "purchase_id" => $purchase->id,
              "amount" => $comission,
              "user_id" => $sellerId,
            ]
          );
          $seller->game_credit = $seller->game_credit + $comission;
          $seller->save();
        }

        // Adiciona o bonus de indicação apenas na PRIMEIRA compra com valor mínimo
        if ($user->invited_by_id && $purchase->price >= 10.00) {
          // Verifica se é a primeira compra paga do apostador (ANTES de marcar como PAID)
          $has_previous_paid_purchases = Purchase::where('user_id', $user->id)
            ->where('status', 'PAID')
            ->exists();
          
          // Se NÃO tem compras pagas anteriores, esta é a primeira
          if (!$has_previous_paid_purchases) {
            $refer = ReferEarn::where(
              'refer_user_id',
              $user->invited_by_id
            )->where(
              'invited_user_id',
              $user->id
            )->where(
              'earn_paid',
              FALSE
            )->first();
            if ($refer) {
              $refer->invited_user_bought = TRUE;
              $refer->save();
            }
          }
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
    $rules = [
      'repeat_game_id' => 'required|exists:games,id',
    ];

    // Verifica se é repetição em lote ou individual
    $has_batch_ids = $request->has('repeat_game_purchase_ids') && 
                     is_array($request->repeat_game_purchase_ids);
    
    if ($has_batch_ids) {
      // Remove valores vazios/null e verifica se há pelo menos um ID
      $purchase_ids = array_filter($request->repeat_game_purchase_ids, function($id) {
        return !empty($id);
      });
      
      if (!empty($purchase_ids)) {
        // Repetição em lote - valida array de purchase_ids
        $rules['repeat_game_purchase_ids'] = 'required|array|min:1';
        $rules['repeat_game_purchase_ids.*'] = 'required|exists:purchases,id';
        $purchase_ids = array_values($purchase_ids); // Reindexa o array
      } else {
        // Array vazio, tratar como erro
        return redirect()->back()
          ->withErrors(['repeat_game_purchase_ids' => 'Selecione pelo menos uma aposta para repetir.'])
          ->with(['tab' => 'tab-mybets']);
      }
    } else {
      // Repetição individual (backward compatible)
      $rules['repeat_game_purchase_id'] = 'required|exists:purchases,id';
      $purchase_ids = [$request->repeat_game_purchase_id];
    }

    $request->validate($rules);
    $user = User::find(Auth::user()->id);
    $role_level_id = $user->role->level_id;
    $repeat_game = Game::find($request->repeat_game_id);
    $success_count = 0;
    $failed_count = 0;
    $already_repeated_count = 0;
    $total_price = 0;

    // Processa cada compra
    foreach ($purchase_ids as $purchase_id) {
      try {
        $old_purchase = Purchase::find($purchase_id);
        if (!$old_purchase) {
          $failed_count++;
          continue;
        }

        // Validação: não permite repetir uma compra que já foi criada por repetição
        if ($old_purchase->repeated_from_purchase_id !== null) {
          $already_repeated_count++;
          continue;
        }

        // Criação da compra
        $newPurchase = $old_purchase->replicate();
        $newPurchase->status = "PENDING";
        $newPurchase->identifier = generate_identifier();
        $newPurchase->game_id = $request->repeat_game_id;
        $newPurchase->round = $repeat_game->round;
        $newPurchase->points = 0;
        $newPurchase->imported = false;
        $newPurchase->repeated_from_purchase_id = $old_purchase->id; // Marca que foi criada por repetição

        // Se estiver sendo pago pelo vendedor, a compra fica mais barata, o apostador paga menos credito
        if ($role_level_id == 'seller') {
          $price = $old_purchase->price - $old_purchase->price * $user->comission_percent;
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
            $failed_count++;
            continue;
          }

          $user->game_credit -= $price;
          $user->save();
          $total_price += $price;

          // Se estiver sendo pago pelo apostador, o vendedor vinculado ganha comissão em TODA compra
          if ($role_level_id == 'gambler') {
            if ($user->seller_id) {
              $seller = User::find($user->seller_id);

              $comission = $newPurchase->price * $seller->comission_percent;
              Transactions::create(
                [
                  "type" => 'PAY_PURCHASE_COMISSION',
                  "game_id" => $newPurchase->game_id,
                  "purchase_id" => $newPurchase->id,
                  "amount" => $comission,
                  "user_id" => $user->seller_id,
                ]
              );
              $seller->game_credit = $seller->game_credit + $comission;
              $seller->save();
            }

            // Adiciona o bonus de indicação apenas na PRIMEIRA compra com valor mínimo
            if ($user->invited_by_id && $price >= 10.00) {
              // Verifica se é a primeira compra paga do apostador (ANTES de marcar como PAID)
              $has_previous_paid_purchases = Purchase::where('user_id', $user->id)
                ->where('status', 'PAID')
                ->exists();
              
              // Se NÃO tem compras pagas anteriores, esta é a primeira
              if (!$has_previous_paid_purchases) {
                $refer = ReferEarn::where(
                  'refer_user_id',
                  $user->invited_by_id
                )->where(
                  'invited_user_id',
                  $user->id
                )->where(
                  'earn_paid',
                  FALSE
                )->first();
                if ($refer) {
                  $refer->invited_user_bought = TRUE;
                  $refer->save();
                }
              }
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

        $success_count++;
      } catch (\Exception $e) {
        $failed_count++;
        continue;
      }
    }

    
    // Redirecionamento com mensagem de sucesso
    $errors = [];
    if ($already_repeated_count > 0) {
      $errors[] = "{$already_repeated_count} " . ($already_repeated_count == 1 ? 'aposta já foi criada por repetição' : 'apostas já foram criadas por repetição') . " e não podem ser repetidas novamente.";
    }

    if ($success_count > 0 && $failed_count == 0 && $already_repeated_count == 0) {
      $message = count($purchase_ids) > 1 
        ? "{$success_count} compras realizadas com sucesso!" 
        : 'Compra realizada com sucesso!';
      return redirect()->back()->with(['success' => $message, 'tab' => 'tab-mybets']);
    } elseif ($success_count > 0) {
      $message = "{$success_count} compra(s) realizada(s) com sucesso!";
      if ($failed_count > 0) {
        $message .= " {$failed_count} compra(s) falharam (crédito insuficiente).";
      }
      if (!empty($errors)) {
        $message .= " " . implode(' ', $errors);
      }
      return redirect()->back()->with(['success' => $message, 'tab' => 'tab-mybets']);
    } else {
      if (!empty($errors)) {
        return redirect()->back()->withErrors(['repeat_game_purchase_ids' => implode(' ', $errors)])->with(['tab' => 'tab-mybets']);
      }
      $message = 'Nenhuma compra foi realizada. Verifique se há crédito suficiente.';
      return redirect()->back()->withErrors(['repeat_game_purchase_ids' => $message])->with(['tab' => 'tab-mybets']);
    }
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
