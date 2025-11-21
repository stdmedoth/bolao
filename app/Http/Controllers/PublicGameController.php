<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameHistory;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Carbon\CarbonInterface;

class PublicGameController extends Controller
{
  /**
   * Exibe a página pública do bolão com informações do concurso atual.
   */
  public function index(Request $request)
  {
    $game = $this->resolveFeaturedGame();

    if (!$game) {
      return view('content.public.bolao-landing-empty');
    }

    $awards = $game->awards()
      ->withCount(['userAwards as winners_count' => function ($query) use ($game) {
        $query->where('game_id', $game->id)
          ->where('round', $game->round);
      }])
      ->withSum(['userAwards as winners_total_amount' => function ($query) use ($game) {
        $query->where('game_id', $game->id)
          ->where('round', $game->round);
      }], 'amount')
      ->orderByDesc('amount')
      ->get();
    $results = $this->getGameResults($game);

    $purchasesQuery = Purchase::query()
      ->where('game_id', $game->id)
      ->where('round', $game->round)
      ->whereIn('status', ['PAID', 'FINISHED']);

    if ($request->filled('bilhete')) {
      $purchasesQuery->where('identifier', 'like', '%' . $request->bilhete . '%');
    }

    if ($request->filled('apostador')) {
      $purchasesQuery->where('gambler_name', 'like', '%' . $request->apostador . '%');
    }

    if ($request->filled('dezenas')) {
      $numbers = trim(preg_replace('/\s+/', ' ', str_replace(',', ' ', $request->dezenas)));
      $purchasesQuery->where('numbers', 'like', '%' . $numbers . '%');
    }

    $purchases = $purchasesQuery
      ->orderByDesc('points')
      ->orderBy('gambler_name')
      ->paginate(12)
      ->withQueryString();

    $countdownTarget = $game->open_at ? Carbon::parse($game->open_at)->toIso8601String() : null;
    $startAt = $game->open_at ? Carbon::parse($game->open_at) : null;
    $closeAt = $game->close_at ? Carbon::parse($game->close_at) : null;

    return view('content.public.bolao-landing', [
      'game' => $game,
      'awards' => $awards,
      'results' => $results,
      'purchases' => $purchases,
      'countdownTarget' => $countdownTarget,
      'summary' => $this->buildSummaryMetrics($awards, $results),
      'filters' => $request->only(['bilhete', 'apostador', 'dezenas']),
      'gameMeta' => [
        'start_at' => $startAt,
        'close_at' => $closeAt,
        'status_label' => $this->resolveStatusLabel($game->status),
        'start_label' => $startAt ? $startAt->translatedFormat('D, d/m/Y \\à\\s H:i\\\h') : null,
        'start_relative' => $this->resolveStartRelativeLabel($startAt),
      ],
    ]);
  }

  private function resolveFeaturedGame(): ?Game
  {
    $featured = Game::query()
      ->where('active', true)
      ->orderByRaw("FIELD(status, 'OPENED', 'CLOSED', 'FINISHED') asc")
      ->orderByDesc('open_at')
      ->first();

    if ($featured) {
      return $featured;
    }

    return Game::query()
      ->orderByRaw("FIELD(status, 'OPENED', 'CLOSED', 'FINISHED') asc")
      ->orderByDesc('open_at')
      ->first();
  }

  private function getGameResults(Game $game): Collection
  {
    $histories = GameHistory::query()
      ->where('game_id', $game->id)
      ->where('round', $game->round)
      ->where('type', 'ADDING_NUMBER')
      ->orderByDesc('created_at')
      ->get()
      ->values();

    $total = $histories->count();

    return $histories->map(function (GameHistory $history, int $index) use ($total) {
      $rawNumbers = collect(explode(' ', trim($history->result_numbers ?? '')))
        ->filter(fn($number) => $number !== '')
        ->map(fn($number) => str_pad($number, 2, '0', STR_PAD_LEFT));

      $processedNumbers = $rawNumbers->values();
      $ascendingNumbers = $processedNumbers->sort()->values();

      return [
        'id' => $history->id,
        'description' => $history->description,
        'created_at' => Carbon::parse($history->created_at),
        'draw_number' => $total - $index,
        'raw_numbers' => $processedNumbers,
        'ascending_numbers' => $ascendingNumbers,
      ];
    });
  }

  private function buildSummaryMetrics(Collection $awards, Collection $results): array
  {
    $totalDraws = $results->count();
    $dezenasPorSorteio = $results->first()
      ? $results->first()['raw_numbers']->count()
      : 0;

    return [
      'total_draws' => $totalDraws,
      'total_numbers' => $totalDraws * $dezenasPorSorteio,
    ];
  }

  private function resolveStatusLabel(?string $status): string
  {
    return match ($status) {
      'OPENED' => 'Vendas Abertas',
      'CLOSED' => 'Vendas Encerradas',
      'FINISHED' => 'Finalizado',
      default => 'Indisponível',
    };
  }

  private function resolveStartRelativeLabel(?Carbon $startAt): ?string
  {
    if (!$startAt) {
      return null;
    }

    $now = Carbon::now();

    if ($startAt->isToday()) {
      return 'Começa: Hoje';
    }

    if ($startAt->isTomorrow()) {
      return 'Começa: Amanhã';
    }

    if ($startAt->isPast()) {
      return 'Concurso em andamento';
    }

    return 'Começa em ' . $startAt->diffForHumans($now, [
      'syntax' => CarbonInterface::DIFF_ABSOLUTE,
      'parts' => 2,
      'short' => true,
    ]);
  }
}
