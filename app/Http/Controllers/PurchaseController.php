<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\ReferEarn;
use App\Models\Transactions;
use App\Models\User;
use App\Services\ReferEarnService;
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

    $role_level_id = Auth::user()->role->level_id;
    $isAdmin = $role_level_id == 'admin';

    // Determina quem criou a aposta e de quem deve ser debitado o saldo
    // Regra: Sempre debita de quem criou a aposta (purchase->user_id)
    // Se o criador for admin, debita do vendedor vinculado (purchase->seller_id)
    $creatorUser = User::find($purchase->user_id);
    $creatorRole = $creatorUser->role->level_id;
    
    // Se quem criou foi admin, debita do vendedor vinculado
    if ($creatorRole == 'admin') {
      $sellerId = $purchase->seller_id;
      if (!$sellerId) {
        return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))
          ->withErrors(['error' => "Não é possível pagar: a compra não possui vendedor vinculado."]);
      }
      $creatorUser = User::find($sellerId);
      $creatorRole = $creatorUser->role->level_id;
    }
    
    // Calcula o valor a ser debitado
    // Sempre debita o valor completo do jogo
    $amountToPay = $purchase->price;
    
    // Verifica saldo e debita
    if ($creatorUser->game_credit < $amountToPay) {
      if ($isAdmin) {
        return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))
          ->withErrors(['error' => "O usuário responsável não tem crédito suficiente para realizar a operação"]);
      } else {
        return redirect()->route('transactions.deposito')
          ->with('amount', $amountToPay)
          ->withErrors(['error' => "Sua conta não tem crédito suficiente para realizar a operação"]);
      }
    }
    
    $creatorUser->game_credit -= $amountToPay;
    $creatorUser->save();
    
    // Se quem criou foi apostador, verifica indicação (apenas na primeira compra paga)
    if ($creatorRole == 'gambler') {
      if ($creatorUser->invited_by_id) {
        $has_previous_paid_purchases = Purchase::where('user_id', $creatorUser->id)
          ->where('status', 'PAID')
          ->exists();
        
        if (!$has_previous_paid_purchases) {
          $refer = ReferEarn::where(
            'refer_user_id',
            $creatorUser->invited_by_id
          )->where(
            'invited_user_id',
            $creatorUser->id
          )->where(
            'earn_paid',
            FALSE
          )->first();
          if ($refer) {
            $refer->invited_user_bought = TRUE;
            $refer->save();
            // Pagamento automático do ReferEarn
            ReferEarnService::payAutomatically($refer);
          }
        }
      }
    }

    // Paga comissão ao vendedor vinculado
    // Regra: Vendedor sempre recebe comissão quando a aposta é paga, se houver vendedor vinculado
    $purchase->load('game');
    $payingUser = Auth::user();
    $sellerId = $purchase->seller_id;
    
    // Determina se o vendedor deve receber comissão
    $shouldPayCommission = false;
    if ($sellerId) {
      // Sempre paga comissão quando há vendedor vinculado
      $shouldPayCommission = true;
    }
    
    // Paga comissão ao vendedor vinculado
    if ($shouldPayCommission) {
      $seller = User::find($sellerId);
      if ($seller) {
        $comission = $purchase->price * $seller->comission_percent;
        $description = Transactions::generateDescription('PAY_PURCHASE_COMISSION', $comission, [
          'purchase' => $purchase,
          'seller' => $seller,
          'user' => $seller,
        ]);
        Transactions::create(
          [
            "type" => 'PAY_PURCHASE_COMISSION',
            "game_id" => $purchase->game_id,
            "purchase_id" => $purchase->id,
            "amount" => $comission,
            "user_id" => $sellerId,
            "description" => $description,
          ]
        );
        $seller->game_credit = $seller->game_credit + $comission;
        $seller->save();
      }
    }

    $purchase->status = "PAID";
    $purchase->paid_by_user_id = Auth::user()->id;
    $purchase->save();

    $purchase->load(['game', 'paid_by_user']);
    $description = Transactions::generateDescription('PAY_PURCHASE', $purchase->price, [
      'purchase' => $purchase,
      'paid_by_user' => $purchase->paid_by_user,
    ]);
    Transactions::create(
      [
        "type" => 'PAY_PURCHASE',
        "game_id" => $purchase->game_id,
        "purchase_id" => $purchase->id,
        "amount" => $purchase->price,
        "user_id" => $purchase->paid_by_user_id,
        "description" => $description,
      ]
    );

    return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta paga com sucesso!');
  }




  public function withdraw(Request $request, $id)
  {
    $purchase = Purchase::find($id);

    if ($purchase->status == "PENDING") {
      return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))->with('success', 'Aposta já estava pendente!');
    }

    // Determina quem realmente teve o saldo debitado (criador ou vendedor se criador for admin)
    // Mesma lógica usada no método pay()
    $creatorUser = User::find($purchase->user_id);
    $creatorRole = $creatorUser->role->level_id;
    
    // Se quem criou foi admin, o saldo foi debitado do vendedor vinculado
    if ($creatorRole == 'admin') {
      $sellerId = $purchase->seller_id;
      if (!$sellerId) {
        return redirect()->route('show-game', array_merge(['id' => $purchase->game_id], request()->query()))
          ->withErrors(['error' => "Não é possível estornar: a compra não possui vendedor vinculado."]);
      }
      $creatorUser = User::find($sellerId);
      $creatorRole = $creatorUser->role->level_id;
    }
    
    // Calcula o valor a ser estornado (sempre o valor completo do jogo)
    $amountToRefund = $purchase->price;

    // Estorna o saldo para quem realmente teve o saldo debitado
    $creatorUser->game_credit += $amountToRefund;
    $creatorUser->save();

    // Se quem criou foi apostador, remove indicação apenas se esta era a primeira compra
    if ($creatorRole == 'gambler') {
      // Verifica se esta é a primeira compra paga do apostador (ANTES de marcar como PENDING)
      $purchase_qnt = Purchase::where('user_id', $creatorUser->id)
        ->where('status', 'PAID')
        ->count();
      
      // Se esta é a primeira compra (count = 1), remove a indicação
      if ($purchase_qnt == 1 && $creatorUser->invited_by_id) {
        $refer = ReferEarn::where(
          'refer_user_id',
          $creatorUser->invited_by_id
        )->where(
          'invited_user_id',
          $creatorUser->id
        )->where(
          'earn_paid',
          FALSE
        )->first();
        if ($refer) {
          // Verifica se já foi pago antes de estornar
          $wasPaid = $refer->earn_paid;
          $refer->invited_user_bought = FALSE;
          $refer->save();
          // Estorno automático do ReferEarn se já estava pago
          if ($wasPaid) {
            ReferEarnService::refundAutomatically($refer);
          }
        }
      }
    }

    // Estorna comissão do vendedor vinculado se foi paga
    $sellerId = $purchase->seller_id;
    if ($sellerId) {
      // Sempre estorna comissão quando há vendedor vinculado (agora sempre recebe comissão)
      $shouldRefundCommission = true;
      
      if ($shouldRefundCommission) {
        $seller = User::find($sellerId);
        if ($seller) {
          $comission = $purchase->price * $seller->comission_percent;
          $purchase->load('game');
          $description = Transactions::generateDescription('PAY_PURCHASE_COMISSION_WITHDRAWAL', $comission, [
            'purchase' => $purchase,
            'user' => $seller,
          ]);
          Transactions::create(
            [
              "type" => 'PAY_PURCHASE_COMISSION_WITHDRAWAL',
              "game_id" => $purchase->game_id,
              "purchase_id" => $purchase->id,
              "amount" => $comission,
              "user_id" => $sellerId,
              "description" => $description,
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

    $purchase->load(['game', 'paid_by_user']);
    $description = Transactions::generateDescription('PAY_PURCHASE_WITHDRAWAL', $amountToRefund, [
      'purchase' => $purchase,
      'user' => $creatorUser,
    ]);
    Transactions::create(
      [
        "type" => 'PAY_PURCHASE_WITHDRAWAL',
        "game_id" => $purchase->game_id,
        "purchase_id" => $purchase->id,
        "amount" => $amountToRefund,
        "user_id" => $creatorUser->id,
        "description" => $description,
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

    $gamblerUser = User::find($request->user_id);
    $role_level_id = Auth::user()->role->level_id;
    $isAdmin = $role_level_id == 'admin';

    // Quando seller paga, usa o seller como pagador, senão usa o apostador
    $payingUser = $gamblerUser;
    if ($role_level_id == 'seller') {
      $payingUser = Auth::user();
    }

    // Calcula o preço base do jogo
    $quantity = 1;
    $game = Game::find($request->game_id);
    $basePrice = $game->price * $quantity;

    // Define o preço da compra (sempre o preço completo do jogo)
    $purchase->quantity = $quantity;
    $purchase->price = $basePrice;
    $purchase->round = $game->round;

    // Salvando a compra no banco de dados
    $purchase->save();

    // Processa débito de crédito
    if (!$isAdmin) {
      // Lógica para não-admin
      // Calcula o valor a ser debitado do usuário (sempre o valor completo)
      $amountToPay = $basePrice;

      if ($payingUser->game_credit < $amountToPay) {
        return redirect()->back()->with(['success' => 'Compra realizada com sucesso! Aguardando pagamento...', 'tab' => 'tab-mybets']);
      }

      $payingUser->game_credit -= $amountToPay;
      $payingUser->save();

      // Se estiver sendo pago pelo apostador, o usuario que convidou o apostador ganha indicação apenas na PRIMEIRA compra
      if ($role_level_id == 'gambler') {
        // Adiciona o bonus de indicação apenas na PRIMEIRA compra com valor mínimo
        if ($gamblerUser->invited_by_id && $purchase->price >= 10.00) {

          // Verifica se é a primeira compra paga do apostador (ANTES de marcar como PAID)
          $has_previous_paid_purchases = Purchase::where('user_id', $gamblerUser->id)
            ->where('status', 'PAID')
            ->exists();
          
          // Se NÃO tem compras pagas anteriores, esta é a primeira
          if (!$has_previous_paid_purchases) {
            $refer = ReferEarn::where(
              'refer_user_id',
              $gamblerUser->invited_by_id
            )->where(
              'invited_user_id',
              $gamblerUser->id
            )->where(
              'earn_paid',
              FALSE
            )->first();
            if ($refer) {
              $refer->invited_user_bought = TRUE;
              $refer->save();
              // Pagamento automático do ReferEarn
              ReferEarnService::payAutomatically($refer);
            }
          }
        }
      }
    } else {
      // NOVA LÓGICA: Quando admin cria, debita de quem está criando a compra (user_id do request)
      // Se quem criou foi admin, debita do vendedor do jogo
      $creatorUser = User::find($request->user_id);
      $creatorRole = $creatorUser->role->level_id;
      
      // Se quem criou foi admin, debita do vendedor
      if ($creatorRole == 'admin') {
        $sellerId = $request->seller_id ?? $purchase->seller_id;
        if (!$sellerId) {
          return redirect()->back()->withErrors(['error' => "Não é possível criar a compra: não foi informado o vendedor vinculado."]);
        }
        $creatorUser = User::find($sellerId);
        $creatorRole = $creatorUser->role->level_id;
      }
      
      // Calcula o valor a ser debitado (sempre o valor completo)
      $amountToPay = $basePrice;
      
      // Verifica saldo e debita
      if ($creatorUser->game_credit < $amountToPay) {
        return redirect()->back()->withErrors(['error' => "O usuário responsável não tem crédito suficiente para realizar a operação"]);
      }
      
      $creatorUser->game_credit -= $amountToPay;
      $creatorUser->save();
      
      // Se quem criou foi apostador, verifica indicação
      if ($creatorRole == 'gambler') {
        if ($creatorUser->invited_by_id && $purchase->price >= 10.00) {
          $has_previous_paid_purchases = Purchase::where('user_id', $creatorUser->id)
            ->where('status', 'PAID')
            ->exists();
          
          if (!$has_previous_paid_purchases) {
            $refer = ReferEarn::where(
              'refer_user_id',
              $creatorUser->invited_by_id
            )->where(
              'invited_user_id',
              $creatorUser->id
            )->where(
              'earn_paid',
              FALSE
            )->first();
            if ($refer) {
              $refer->invited_user_bought = TRUE;
              $refer->save();
              // Pagamento automático do ReferEarn
              ReferEarnService::payAutomatically($refer);
            }
          }
        }
      }
      
      // Quando admin cria, o pagador é quem criou a compra (ou vendedor se admin)
      $payingUser = $creatorUser;
    }

    // Paga comissão ao vendedor vinculado
    // Regra: Vendedor sempre recebe comissão quando a aposta é paga, se houver vendedor vinculado
    $sellerId = $request->seller_id ?? $purchase->seller_id;
    
    // Determina se o vendedor deve receber comissão
    $shouldPayCommission = false;
    if ($sellerId) {
      // Sempre paga comissão quando há vendedor vinculado
      $shouldPayCommission = true;
    }
    
    // Paga comissão ao vendedor vinculado
    if ($shouldPayCommission) {
      $seller = User::find($sellerId);
      if ($seller) {
        $comission = $purchase->price * $seller->comission_percent;
        $purchase->load('game');
        $description = Transactions::generateDescription('PAY_PURCHASE_COMISSION', $comission, [
          'purchase' => $purchase,
          'seller' => $seller,
          'user' => $seller,
        ]);
        Transactions::create(
          [
            "type" => 'PAY_PURCHASE_COMISSION',
            "game_id" => $purchase->game_id,
            "purchase_id" => $purchase->id,
            "amount" => $comission,
            "user_id" => $sellerId,
            "description" => $description,
          ]
        );
        $seller->game_credit = $seller->game_credit + $comission;
        $seller->save();
      }
    }

    $purchase->status = "PAID";
    $purchase->paid_by_user_id = $payingUser->id;
    $purchase->save();

    $purchase->load(['game', 'paid_by_user']);
    $description = Transactions::generateDescription('PAY_PURCHASE', $purchase->price, [
      'purchase' => $purchase,
      'paid_by_user' => $purchase->paid_by_user,
    ]);
    Transactions::create(
      [
        "type" => 'PAY_PURCHASE',
        "game_id" => $purchase->game_id,
        "purchase_id" => $purchase->id,
        "amount" => $purchase->price,
        "user_id" => $purchase->user_id,
        "description" => $description,
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

        // Normaliza os números para garantir comparação consistente
        $normalizedNumbers = explode(' ', $old_purchase->numbers);
        $normalizedNumbers = array_map('intval', $normalizedNumbers);
        $normalizedNumbers = implode(' ', $normalizedNumbers);

        // Validação: verifica se já existe uma aposta com os mesmos números no jogo de destino para o mesmo apostador
        // Isso impede que uma aposta seja repetida de volta para o jogo original (criando duplicatas)
        // Busca todas as apostas do jogo de destino e compara números normalizados
        $existingPurchases = Purchase::where('game_id', $request->repeat_game_id)
          ->where('gambler_name', $old_purchase->gambler_name)
          ->where('status', '!=', 'CANCELED') // Considera apenas apostas ativas (PAID, PENDING, FINISHED)
          ->get();
        
        $existingPurchase = null;
        foreach ($existingPurchases as $purchase) {
          // Normaliza os números da aposta do banco para comparar
          $purchaseNumbers = explode(' ', $purchase->numbers);
          $purchaseNumbers = array_map('intval', $purchaseNumbers);
          $purchaseNumbers = implode(' ', $purchaseNumbers);
          
          if ($purchaseNumbers === $normalizedNumbers) {
            $existingPurchase = $purchase;
            break;
          }
        }

        if ($existingPurchase) {
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
        $newPurchase->repeated_from_purchase_id = $old_purchase->id; // Marca que foi criada por repetição (para rastreamento)
        $newPurchase->numbers = $normalizedNumbers; // Garante que os números estejam normalizados

        // Calcula o preço base do jogo
        $quantity = 1;
        $game = Game::find($request->repeat_game_id);
        $basePrice = $game->price * $quantity;

        // Define o preço da compra (sempre o preço completo do jogo)
        // Quando admin repete, mantém o user_id da compra original
        // Quando não-admin repete, usa o user_id de quem está repetindo
        $newPurchase->quantity = $quantity;
        if ($role_level_id == 'admin') {
          $newPurchase->user_id = $old_purchase->user_id;
        } else {
          $newPurchase->user_id = $user->id;
        }
        $newPurchase->price = $basePrice;

        // Salvando a compra no banco de dados
        $newPurchase->save();

        $isAdmin = $role_level_id == 'admin';

        // Processa débito de crédito
        if (!$isAdmin) {
          // Lógica para não-admin
          // Calcula o valor a ser debitado do usuário (sempre o valor completo)
          $amountToPay = $basePrice;

          if ($user->game_credit < $amountToPay) {
            $failed_count++;
            continue;
          }

          $user->game_credit -= $amountToPay;
          $user->save();
          $total_price += $amountToPay;

          // Se estiver sendo pago pelo apostador, o usuario que convidou o apostador ganha indicação apenas na PRIMEIRA compra
          if ($role_level_id == 'gambler') {
            // Adiciona o bonus de indicação apenas na PRIMEIRA compra com valor mínimo
            if ($user->invited_by_id && $basePrice >= 10.00) {
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
                  // Pagamento automático do ReferEarn
                  ReferEarnService::payAutomatically($refer);
                }
              }
            }
          }
          
          $newPurchase->status = "PAID";
          $newPurchase->paid_by_user_id = $user->id;
        } else {
          // NOVA LÓGICA: Quando admin repete, debita de quem está repetindo (user_id da nova compra)
          // Se quem criou foi admin, debita do vendedor do jogo
          $creatorUser = User::find($newPurchase->user_id);
          $creatorRole = $creatorUser->role->level_id;
          
          // Se quem criou foi admin, debita do vendedor
          if ($creatorRole == 'admin') {
            $sellerId = $newPurchase->seller_id;
            if (!$sellerId) {
              $failed_count++;
              continue;
            }
            $creatorUser = User::find($sellerId);
            $creatorRole = $creatorUser->role->level_id;
          }
          
          // Calcula o valor a ser debitado (sempre o valor completo)
          $amountToPay = $basePrice;
          
          // Verifica saldo e debita
          if ($creatorUser->game_credit < $amountToPay) {
            $failed_count++;
            continue;
          }
          
          $creatorUser->game_credit -= $amountToPay;
          $creatorUser->save();
          $total_price += $amountToPay;
          
          // Se quem criou foi apostador, verifica indicação
          if ($creatorRole == 'gambler') {
            if ($creatorUser->invited_by_id && $basePrice >= 10.00) {
              $has_previous_paid_purchases = Purchase::where('user_id', $creatorUser->id)
                ->where('status', 'PAID')
                ->exists();
              
              if (!$has_previous_paid_purchases) {
                $refer = ReferEarn::where(
                  'refer_user_id',
                  $creatorUser->invited_by_id
                )->where(
                  'invited_user_id',
                  $creatorUser->id
                )->where(
                  'earn_paid',
                  FALSE
                )->first();
                if ($refer) {
                  $refer->invited_user_bought = TRUE;
                  $refer->save();
                  // Pagamento automático do ReferEarn
                  ReferEarnService::payAutomatically($refer);
                }
              }
            }
          }
          
          // Quando admin repete, o pagador é quem criou a nova compra (ou vendedor se admin)
          $newPurchase->status = "PAID";
          $newPurchase->paid_by_user_id = $creatorUser->id;
        }
        
        $newPurchase->save();

        // Paga comissão ao vendedor vinculado
        // Regra: Vendedor sempre recebe comissão quando a aposta é paga, se houver vendedor vinculado
        $sellerId = $newPurchase->seller_id;
        
        // Determina se o vendedor deve receber comissão
        $shouldPayCommission = false;
        if ($sellerId) {
          // Sempre paga comissão quando há vendedor vinculado
          $shouldPayCommission = true;
        }
        
        // Paga comissão ao vendedor vinculado
        if ($shouldPayCommission) {
          $seller = User::find($sellerId);
          if ($seller) {
            $comission = $newPurchase->price * $seller->comission_percent;
            $newPurchase->load('game');
            $description = Transactions::generateDescription('PAY_PURCHASE_COMISSION', $comission, [
              'purchase' => $newPurchase,
              'seller' => $seller,
              'user' => $seller,
            ]);
            Transactions::create(
              [
                "type" => 'PAY_PURCHASE_COMISSION',
                "game_id" => $newPurchase->game_id,
                "purchase_id" => $newPurchase->id,
                "amount" => $comission,
                "user_id" => $sellerId,
                "description" => $description,
              ]
            );
            $seller->game_credit = $seller->game_credit + $comission;
            $seller->save();
          }
        }

        $newPurchase->load(['game', 'paid_by_user']);
        $description = Transactions::generateDescription('PAY_PURCHASE', $newPurchase->price, [
          'purchase' => $newPurchase,
          'paid_by_user' => $newPurchase->paid_by_user,
        ]);
        Transactions::create(
          [
            "type" => 'PAY_PURCHASE',
            "game_id" => $newPurchase->game_id,
            "purchase_id" => $newPurchase->id,
            "amount" => $newPurchase->price,
            "user_id" => $newPurchase->user_id,
            "description" => $description,
          ]
        );

        $success_count++;
      } catch (\Exception $e) {
        $failed_count++;
        continue;
      }
    }

    
    // Redirecionamento com mensagem
    $errorMessage = '';
    if ($already_repeated_count > 0) {
      $errorMessage = "{$already_repeated_count} " . ($already_repeated_count == 1 ? 'aposta não pode ser repetida' : 'apostas não podem ser repetidas') . " pois já existe uma aposta com os mesmos números para este jogo.";
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
      if (!empty($errorMessage)) {
        return redirect()->back()->with(['success' => $message, 'error' => $errorMessage, 'tab' => 'tab-mybets']);
      }
      return redirect()->back()->with(['success' => $message, 'tab' => 'tab-mybets']);
    } else {
      if (!empty($errorMessage)) {
        return redirect()->back()->with(['error' => $errorMessage, 'tab' => 'tab-mybets']);
      }
      $message = 'Nenhuma compra foi realizada. Verifique se há crédito suficiente.';
      return redirect()->back()->with(['error' => $message, 'tab' => 'tab-mybets']);
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
