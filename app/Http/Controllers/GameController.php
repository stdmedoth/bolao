<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameAward;
use App\Models\GameHistory;
use App\Models\Purchase;
use App\Models\User;
use App\Models\UserAwards;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class GameController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    //
    $builder = Game::where('active', 1);
    if ($request->has('search') && $request->search != '') {
      $builder = $builder->where(function ($q) use ($request) {
        $q->where('name', 'like', '%' . $request->search . '%')->orWhere('game_id', 'like', '%' . $request->search . '%');
      });
    }
    if ($request->has('status') && $request->status != '') {
      $builder = $builder->where('status', $request->status);
    }

    $games = $builder->orderBy('close_at', 'DESC')->paginate(20);
    return view('content.game.games', ['games' => $games]);
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

  private function calculateUsersPoints($purchases, $uniqueNumbers)
  {
    $usersPoints = [];
    foreach ($purchases as $purchase) {
      $purchaseNumbers = array_map('intval', explode(' ', $purchase->numbers));
      $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);

      // Caso usersPoints[$purchase->user_id] ainda não esteja definido (Seja o primeiro ponto desse usuario)
      // Então definir ele, o primeiro ponto é o mais alto.
      if (isset($usersPoints[$purchase->user_id])) {
        $usersPoints[$purchase->user_id] = ['points' => (count($matchedNumbers) > $usersPoints[$purchase->user_id]['points']) ?  count($matchedNumbers) : $usersPoints[$purchase->user_id]['points'], 'purchase' => $purchase];
      } else {
        $usersPoints[$purchase->user_id] = ['points' => count($matchedNumbers), 'purchase' => $purchase];
      }
    }
    return $usersPoints;
  }

  /**
   * Display the specified resource.
   */
  public function show(Request $request, $id)
  {
    //
    $game = Game::find($id);
    $user_awards_builder = UserAwards::where('game_id', $id);
    $sellers = User::where('role_user_id', 2)->get();

    $winners = [];
    $round = $game->round;

    // Pegar todos os números válidos desde a última abertura
    $gameHistoriesBuilder = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER')
      ->where('round', $round);

    $gameHistories = $gameHistoriesBuilder->paginate(20, ['*'], 'game_histories_page');

    // Reunir todos os números adicionados desde a última abertura
    $allAddedNumbers = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER')
      ->where('round', $round)->get()->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    $uniqueNumbers = array_unique($allAddedNumbers);

    $user_awards = $user_awards_builder->where('round', $round)
      ->with(['user', 'game_award', 'purchase']) // Eager loading para evitar N+1 queries
      ->orderBy('points', 'DESC')
      ->orderBy('id', 'ASC')
      ->paginate(20, ['*'], 'user_awards_page');

    foreach ($user_awards as $user_award) {
      // Calcular números acertados para os vencedores
      $matchedNumbers = [];
      if ($user_award->purchase && in_array($user_award->purchase->status, ["PAID", "FINISHED"])) {
        $purchaseNumbers = array_map('intval', explode(' ', $user_award->purchase->numbers));
        $matchedNumbers = array_intersect($allAddedNumbers, $purchaseNumbers);
      }

      $winners[] = (object)[
        'id' => $user_award->id,
        'user' => $user_award->user,
        'status' => $user_award->status,
        'game_award' => $user_award->game_award,
        'user_award' => $user_award,
        'purchase' => $user_award->purchase,
        'userPoint' => $user_award->points,
        'result_numbers' => $allAddedNumbers,
        'matched_numbers' => $matchedNumbers
      ];
    }

    $builder = new Purchase();

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

    $builder = $builder->where('game_id', $id);

    if ($request->has('status') && $request->status != '') {
      $builder = $builder->where('status', $request->status);
    }

    // only shows purchases in same round that the last game opening
    $builder = $builder->whereHas('game', function ($query) {
      $query->whereColumn('purchases.round', 'games.round');
    });

    //$builder = $builder->orderBy('created_at', 'desc');
    $builder = $builder->orderBy('gambler_name', 'asc');

    if ($request->has('seller') && $request->seller != '') {
      $builder = $builder->where('seller_id', $request->seller);
    }

    if ($request->has('points') && $request->points != '') {
      $builder = $builder->where('points', $request->points);
    }

    $purchases = $builder->paginate(20, ['*'], 'purchases_page');

    // Dados para classificação (todas as compras ordenadas por pontos)
    $classificationsBuilder = Purchase::where('game_id', $id)
      ->where('status', 'PAID')
      ->with(['user', 'seller',])
      ->where('round', $round)
      ->orderBy('points', 'desc')
      ->orderBy('created_at', 'desc');
    

    if ($request->has('search') && $request->search != '') {
      $classificationsBuilder = $classificationsBuilder->where(function ($q) use ($request) {
        $q->whereHas('game', function ($gameq) use ($request) {
          $gameq->where('name', 'like', '%' . $request->search . '%');
        })->orWhere('numbers', 'like', '%' . $request->search . '%')
          ->orWhere('gambler_name', 'like', '%' . $request->search . '%')
          ->orWhere('gambler_phone', 'like', '%' . $request->search . '%')
          ->orWhere('identifier', 'like', '%' . $request->search . '%')
          ->orWhereHas('seller', function ($sellerq) use ($request) {
            $sellerq->where('name', 'like', '%' . $request->search . '%');
          });
      });
    }
      
    $classifications = $classificationsBuilder->paginate(20, ['*'], 'classifications_page');

    foreach ($purchases as $key => $purchase) {
      // Usa os pontos já calculados no banco de dados
      $points = $purchase->points;
      $matchedNumbers = [];
      
      // Só calcula matched_numbers se a compra está paga/finalizada
      if (in_array($purchase->status, ["PAID", "FINISHED"])) {
        $purchaseNumbers = array_map('intval', explode(' ', $purchase->numbers));
        $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);
      }

      $purchases[$key]['matched_numbers'] = $matchedNumbers;
      $purchases[$key]['points'] = $points;
    }

    // Processar dados de classificação
    foreach ($classifications as $key => $classification) {
      $points = $classification->points;
      $matchedNumbers = [];
      
      // Só calcula matched_numbers se a compra está paga/finalizada
      if (in_array($classification->status, ["PAID", "FINISHED"])) {
        $purchaseNumbers = array_map('intval', explode(' ', $classification->numbers));
        $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);
      }

      $classifications[$key]['matched_numbers'] = $matchedNumbers;
      $classifications[$key]['points'] = $points;
    }




    $games = Game::select(['id', 'status', 'name'])->whereIn('status', ['OPENED', 'CLOSED'])->whereNotIn('id', [$game->id])->get();

    $winner_award = $game->awards()->where('condition_type', 'WINNER')
      ->first();

    return view('content.game.view_game', [
      'game' => $game,
      'games' => $games,
      'purchases' => $purchases,
      'histories' => $gameHistories,
      'winners' => $winners,
      'user_awards' => $user_awards,
      'winner_award' => $winner_award,
      'sellers' => $sellers,
      'classifications' => $classifications,
      'uniqueNumbers' => $uniqueNumbers
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function editGameForm($id)
  {
    $game = Game::with('awards')->findOrFail($id);

    $game->open_at = Carbon::parse($game->open_at)->format("Y-m-d");
    $game->close_at = Carbon::parse($game->close_at)->format("Y-m-d");

    return view('content.game.game_update', compact('game'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'price' => 'required|numeric|min:0',
      'open_at' => 'required|date',
      'round' => 'required|numeric',
      'close_at' => 'required|date|after_or_equal:open_at',
      'status' => 'required|in:OPENED,CLOSED,FINISHED',
      'awards' => 'array',
      'awards.*.name' => 'nullable|string|max:255',
      'awards.*.condition_type' => 'required|in:EXACT_POINT,WINNER,SECONDARY_WINNER',
      'awards.*.only_when_finish_round' => 'nullable|boolean',
      'awards.*.only_on_first_round' => 'nullable|boolean',
      'awards.*.exact_point_value' => 'nullable|integer',
      'awards.*.winner_point_value' => 'nullable|integer',
      'awards.*.amount' => 'required|numeric|min:0',
    ]);

    $game = Game::findOrFail($id);
    $round = $request->input('round');

    if (($game->status == "OPENED") && ($request->input('status') == "FINISHED")) {
      $game_history = GameHistory::create([
        "description" => "JOGO FECHADO",
        "numbers" => "",
        "type" => "FINISHED",
        'game_id' => $game->id,
      ]);
    }


    if (($game->status == "FINISHED") && ($request->input('status') == "OPENED")) {
      $round = $game->round + 1;
      $game_history = GameHistory::create([
        "description" => "JOGO ABERTO",
        "numbers" => "",
        "type" => "OPENED",
        'game_id' => $game->id,
        'round' => $round,
      ]);
    }


    $game->update([
      'name' => $request->input('name'),
      'price' => $request->input('price'),
      'open_at' => $request->input('open_at'),
      'close_at' => $request->input('close_at'),
      'status' => $request->input('status'),
      'round' => $round
    ]);

    // Atualiza os prêmios
    $awardsData = $request->input('awards', []);

    foreach ($awardsData as $awardData) {
      if (isset($awardData['id'])) {
        // Atualizar prêmio existente
        $award = GameAward::findOrFail($awardData['id']);
        $award->update([
          'name' => $awardData['name'] ?? $award->name,
          'condition_type' => $awardData['condition_type'],
          'only_on_first_round' => $awardData['only_on_first_round'] ?? false,
          'only_when_finish_round' => $awardData['only_when_finish_round'] ?? false,
          'exact_point_value' => $awardData['exact_point_value'] ?? null,
          'winner_point_value' => $awardData['winner_point_value'] ?? null,
          'amount' => $awardData['amount'],
        ]);
      } else {
        // Criar novo prêmio
        $game->awards()->create([
          'name' => $awardData['name'] ?? 'Prêmio',
          'condition_type' => $awardData['condition_type'],
          'only_on_first_round' => $awardData['only_on_first_round'] ?? false,
          'only_when_finish_round' => $awardData['only_when_finish_round'] ?? false,
          'exact_point_value' => $awardData['exact_point_value'] ?? null,
          'winner_point_value' => $awardData['winner_point_value'] ?? null,
          'amount' => $awardData['amount'],
        ]);
      }
    }

    return redirect(route("show-game", $game->id))->with('success', 'Jogo e prêmios atualizados com sucesso!');
  }


  public function generatePdf(Request $request, $id)
  {
    $game = Game::findOrFail($id);
    $round = $game->round;

    $purchasesBuilder = Purchase::where('game_id', $id)->where('status', 'PAID');

    $lastClosedHistory = GameHistory::where('game_id', $game->id)
      ->where('type', 'OPENED')
      ->where('round', $round)
      ->first();

    $gameHistoriesBuilder = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER');

    $gameHistoriesBuilder = $gameHistoriesBuilder->where('round', $round);
    $purchasesBuilder = $purchasesBuilder->where('round', $round);

    $gameHistories = $gameHistoriesBuilder->get();
    $purchases = $purchasesBuilder->get();

    $allAddedNumbers = $gameHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    $uniqueNumbers = array_unique($allAddedNumbers);

    $purchases_data = [];
    foreach ($purchases as $purchase) {
      $purchaseNumbers = array_map('intval', explode(' ', $purchase->numbers));
      $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);

      $seller = "";
      if (in_array($purchase->seller->role->level_id, ['seller'])) {
        $seller = $purchase->seller->name;
      } else {
        $seller = "Banca Central";
      }

      $purchases_data[] = [
        'identifier' => $purchase->identifier,
        'gambler_name' => $purchase->gambler_name,
        'seller' => $seller,
        'points' => count($matchedNumbers),
        'numbers' => $purchase->numbers,
      ];
    }
    usort($purchases_data, function ($a, $b) {
      return $a['points'] < $b['points'];
    });

    $awards = GameAward::where('game_id', $id)->get();
    // Gerar o PDF com a view
    $pdf = Pdf::loadView('pdf.game_report', compact(
      'game',
      'lastClosedHistory',
      'gameHistories',
      'purchases_data',
      'uniqueNumbers',
      'awards',
      'purchases'
      //))->setPaper('a4', 'portrait');
    ))->setPaper('a2', 'landscape');

    return $pdf->download("relatorio_jogo_{$id}.pdf");
  }




  public function generateCsv(Request $request, $id)
  {
    $game = Game::findOrFail($id);
    $round = $game->round;
    $purchasesBuilder = Purchase::where('game_id', $id)->where('status', 'PAID');

    $gameHistoriesBuilder = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER');

    $gameHistoriesBuilder = $gameHistoriesBuilder->where('round', $round);
    $purchasesBuilder = $purchasesBuilder->where('round', $round);


    $gameHistories = $gameHistoriesBuilder->get();
    $purchases = $purchasesBuilder->get();

    $allAddedNumbers = $gameHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    $uniqueNumbers = array_unique($allAddedNumbers);

    $purchases_data = [];
    foreach ($purchases as $purchase) {
      $purchaseNumbers = array_map('intval', explode(' ', $purchase->numbers));
      $matchedNumbers = array_intersect($uniqueNumbers, $purchaseNumbers);

      $seller = "";
      if (in_array($purchase->seller->role->level_id, ['seller'])) {
        $seller = $purchase->seller->name;
      } else {
        $seller = "Banca Central";
      }

      $purchases_data[] = [
        'id' => $purchase->id,
        'identifier' => $purchase->identifier,
        'gambler_name' => $purchase->gambler_name,
        'seller' => $seller,
        'points' => count($matchedNumbers),
        'numbers' => $purchase->numbers,
        'created_at' => $purchase->created_at->format('d/m/Y H:i:s'),
      ];
    }

    usort($purchases_data, function ($a, $b) {
      return $a['points'] < $b['points'];
    });

    // Cabeçalhos do CSV
    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => 'attachment; filename="compras_jogo_' . $id . '.csv"',
    ];

    // Criar o arquivo CSV
    $callback = function () use ($purchases_data) {
      $file = fopen('php://output', 'w');

      echo "\xEF\xBB\xBF";

      // Escrever cabeçalho
      fputcsv($file, [
        'ID',
        'Código',
        'Apostador',
        'Vendedor',
        'Pontos',
        'Números',
        'Data/Hora'
      ], ';');

      // Escrever dados
      foreach ($purchases_data as $purchase) {
        fputcsv($file, [
          $purchase['id'],
          $purchase['identifier'],
          $purchase['gambler_name'],
          $purchase['seller'],
          $purchase['points'],
          $purchase['numbers'],
          $purchase['created_at']
        ], ';');
      }

      fclose($file);
    };

    return Response::stream($callback, 200, $headers);
  }


  /**
   * Display the classification for a specific game.
   */

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Game $game)
  {
    //
  }
}
