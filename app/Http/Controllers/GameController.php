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

      // Caso userPoints[$purchase->user_id] ainda não esteja definido (Seja o primeiro ponto desse usuario)
      // Então definir ele, o primeiro ponto é o mais alto.
      if (isset($userPoints[$purchase->user_id])) {
        $usersPoints[$purchase->user_id] = ['points' => (count($matchedNumbers) > $usersPoints[$purchase->user_id]) ?  count($matchedNumbers) : $usersPoints[$purchase->user_id], 'purchase' => $purchase];
      } else {
        $usersPoints[$purchase->user_id] = ['points' => count($matchedNumbers), 'purchase' => $purchase];
      }
    }
    return $usersPoints;
  }

  /**
   * Display the specified resource.
   */
  public function show($id)
  {
    //
    $game = Game::find($id);
    $user_awards_builder = UserAwards::where('game_id', $id);
    $sellers = User::where('role_user_id', 2)->get();

    $winners = [];
    $lastClosedHistory = GameHistory::where('game_id', $game->id)
      ->where('type', 'OPENED')
      ->orderBy('created_at', 'DESC')
      ->first();

    // Pegar todos os números válidos desde a última abertura
    $gameHistoriesBuilder = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER');
    if ($lastClosedHistory) {
      $gameHistoriesBuilder = $gameHistoriesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }
    $gameHistories = $gameHistoriesBuilder->paginate(20);

    // Reunir todos os números adicionados desde a última abertura
    $allAddedNumbers = $gameHistories->pluck('numbers')
      ->flatMap(fn($numbers) => explode(" ", $numbers))
      ->toArray();

    $uniqueNumbers = array_unique($allAddedNumbers);

    if ($lastClosedHistory) {
      $user_awards = $user_awards_builder->where('created_at', '>=', $lastClosedHistory->created_at);
    } else {
      $user_awards = $user_awards_builder->get();
    }

    $purchasesBuilder = Purchase::where('game_id', $game->id)
      ->where('user_id', Auth::user()->id)
      ->whereIn('status', ['PAID', 'PENDING']);

    if ($lastClosedHistory) {
      $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }
    $purchases = $purchasesBuilder->get();

    foreach ($user_awards as $user_award) {

      // Obter todas as compras relacionadas ao jogo
      $purchasesBuilder = Purchase::where('game_id', $game->id)
        ->whereIn('status', ['PAID'])
        ->where('user_id', $user_award->user_id);

      if ($lastClosedHistory) {
        $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
      }
      $_purchases = $purchasesBuilder->get();
      $usersPoints = $this->calculateUsersPoints($_purchases, $uniqueNumbers);

      $user = User::find($user_award->user_id);
      $game_award = GameAward::find($user_award->game_award_id);
      $winners[] = (object)[
        'id' => $user_award->id,
        'user' => $user,
        'status' => $user_award->status,
        'game_award' => $game_award,
        'purchase' => $usersPoints[$user->id]['purchase'],
        'userPoint' => $usersPoints[$user->id]['points'],
        'result_numbers' => $allAddedNumbers
      ];
    }

    return view('content.game.view_game', [
      'game' => $game,
      'purchases' => $purchases,
      'histories' => $gameHistories,
      'winners' => $winners,
      'user_awards' => $user_awards,
      'sellers' => $sellers
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function editGameForm($id)
  {
    $game = Game::findOrFail($id);

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
      'close_at' => 'required|date|after_or_equal:open_at',
      'status' => 'required|in:OPENED,CLOSED,FINISHED',
      'awards' => 'array',
      'awards.*.condition_type' => 'required|in:MINIMUM_POINT,EXACT_POINT',
      'awards.*.minimum_point_value' => 'nullable|integer',
      'awards.*.amount' => 'required|numeric|min:0',
    ]);

    $game = Game::findOrFail($id);

    if (($game->status == "OPENED") && ($request->input('status') == "CLOSED")) {
      $game_history = GameHistory::create([
        "description" => "JOGO FECHADO",
        "numbers" => "",
        "type" => "CLOSED",
        'game_id' => $game->id,
      ]);
    }


    $game->update([
      'name' => $request->input('name'),
      'price' => $request->input('price'),
      'open_at' => $request->input('open_at'),
      'close_at' => $request->input('close_at'),
      'status' => $request->input('status'),
    ]);

    // Chama o método para atualizar os prêmios
    app(GameAwardController::class)->updateAwards($request, $game);

    return redirect(route("show-game", $game->id))->with('success', 'Jogo e prêmios atualizados com sucesso!');
  }


  public function generatePdf(Request $request, $id)
  {
    $game = Game::findOrFail($id);
    $purchasesBuilder = Purchase::where('game_id', $id)->where('status', 'PAID');

    $lastClosedHistory = GameHistory::where('game_id', $game->id)
      ->where('type', 'OPENED')
      ->orderBy('created_at', 'DESC')
      ->first();

    $gameHistoriesBuilder = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER');

    if ($lastClosedHistory) {
      $gameHistoriesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
      $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }

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

      $seller = $purchase->seller->name;

      $purchases_data[] = [
        'id' => $purchase->id,
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
    $purchasesBuilder = Purchase::where('game_id', $id)->where('status', 'PAID');

    $lastClosedHistory = GameHistory::where('game_id', $game->id)
      ->where('type', 'OPENED')
      ->orderBy('created_at', 'DESC')
      ->first();

    $gameHistoriesBuilder = GameHistory::where('game_id', $game->id)
      ->where('type', 'ADDING_NUMBER');

    if ($lastClosedHistory) {
      $gameHistoriesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
      $purchasesBuilder->where('created_at', '>=', $lastClosedHistory->created_at);
    }

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

      $seller = $purchase->seller->name;

      $purchases_data[] = [
        'id' => $purchase->id,
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

      // Escrever cabeçalho
      fputcsv($file, [
        'ID',
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
   * Remove the specified resource from storage.
   */
  public function destroy(Game $game)
  {
    //
  }
}
