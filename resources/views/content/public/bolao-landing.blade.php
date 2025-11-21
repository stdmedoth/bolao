@extends('layouts/blankLayout')

@section('title', 'Bolão - Página Pública')

@section('page-style')
<style>
  .hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1f2937 100%);
    color: #e2e8f0;
    border: none;
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.55);
  }

  .hero-card .card-footer {
    background: rgba(15, 23, 42, 0.75);
    color: #cbd5f5;
  }

  .floating-cta {
    position: sticky;
    top: 0;
    z-index: 9;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
  }

  .floating-cta .btn {
    min-width: 160px;
  }

  .social-card {
    min-height: 135px;
  }

  .responsive-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.5rem;
  }

  .responsive-cards > * {
    width: 100%;
  }

  .responsive-card {
    display: flex;
  }

  .responsive-card > * {
    flex: 1 1 auto;
  }

  .responsive-table {
    overflow: hidden;
  }

  .responsive-table table {
    width: 100%;
  }

  @media (max-width: 767.98px) {
    .responsive-table table,
    .responsive-table thead,
    .responsive-table tbody,
    .responsive-table th,
    .responsive-table td,
    .responsive-table tr {
      display: block;
      width: 100%;
    }

    .responsive-table thead {
      display: none;
    }

    .responsive-table tr {
      margin-bottom: 1rem;
      padding: 1rem;
      background: #f8fafc;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
      border: 1px solid rgba(148, 163, 184, 0.2);
    }

    .responsive-table td {
      position: relative;
      padding-left: 48%;
      padding-right: 1rem;
      text-align: right;
      border: none;
      border-bottom: 1px solid rgba(148, 163, 184, 0.2);
    }

    .responsive-table td:last-child {
      border-bottom: none;
    }

    .responsive-table td::before {
      content: attr(data-label);
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      font-size: 0.75rem;
      color: #475569;
      text-align: left;
    }

    .responsive-table td.td-dezenas {
      text-align: left;
      padding-left: 1rem;
    }

    .responsive-table td.td-dezenas::before {
      position: static;
      transform: none;
      display: block;
      margin-bottom: 0.5rem;
    }

    .responsive-table td.td-dezenas span {
      justify-content: flex-start;
    }
  }

  .responsive-cards-awards {
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  }

  .responsive-cards-results {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }

  .prize-card {
    border-radius: 24px;
    overflow: hidden;
    color: #0f172a;
    min-height: 260px;
    box-shadow: 0 24px 36px rgba(15, 23, 42, 0.2);
    border: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
  }

  .prize-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 32px 48px rgba(15, 23, 42, 0.3);
  }

  .prize-card-section {
    padding: 1.5rem;
    flex: 1 1 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.5rem;
  }

  .prize-card-top {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    color: #fff;
    justify-content: center;
    text-align: center;
  }

  .prize-card-top small {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 600;
    opacity: 0.85;
  }

  .prize-card-middle {
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
  }

  .prize-card-middle h5 {
    font-weight: 700;
    margin-bottom: 0.5rem;
  }

  .prize-card-middle p {
    margin: 0;
    color: #4b5563;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .prize-card-bottom {
    color: #e2e8f0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 1.5rem;
    border-top: 1px solid rgba(15, 23, 42, 0.12);
  }

  .prize-variant-0 .prize-card-top {
    background: linear-gradient(135deg, #7c2d12 0%, #9a3412 100%);
  }

  .prize-variant-0 .prize-card-middle {
    background: #fff6ec;
  }

  .prize-variant-0 .prize-card-bottom {
    background: linear-gradient(135deg, #1f2937 0%, rgba(124, 45, 18, 0.85) 100%);
  }

  .prize-variant-1 .prize-card-top {
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
  }

  .prize-variant-1 .prize-card-middle {
    background: #eef2ff;
  }

  .prize-variant-1 .prize-card-bottom {
    background: linear-gradient(135deg, #1f2937 0%, rgba(30, 58, 138, 0.85) 100%);
  }

  .prize-variant-2 .prize-card-top {
    background: linear-gradient(135deg, #14532d 0%, #15803d 100%);
  }

  .prize-variant-2 .prize-card-middle {
    background: #ecfdf5;
  }

  .prize-variant-2 .prize-card-bottom {
    background: linear-gradient(135deg, #1f2937 0%, rgba(20, 83, 45, 0.85) 100%);
  }

  .prize-variant-3 .prize-card-top {
    background: linear-gradient(135deg, #581c87 0%, #6b21a8 100%);
  }

  .prize-variant-3 .prize-card-middle {
    background: #f5f3ff;
  }

  .prize-variant-3 .prize-card-bottom {
    background: linear-gradient(135deg, #1f2937 0%, rgba(88, 28, 135, 0.85) 100%);
  }

  .prize-variant-4 .prize-card-top {
    background: linear-gradient(135deg, #9a3412 0%, #b45309 100%);
  }

  .prize-variant-4 .prize-card-middle {
    background: #fefce8;
  }

  .prize-variant-4 .prize-card-bottom {
    background: linear-gradient(135deg, #1f2937 0%, rgba(151, 71, 6, 0.85) 100%);
  }

  .prize-variant-5 .prize-card-top {
    background: linear-gradient(135deg, #1f2937 0%, #0f172a 100%);
  }

  .prize-variant-5 .prize-card-middle {
    background: #f1f5f9;
  }

  .prize-variant-5 .prize-card-bottom {
    background: linear-gradient(135deg, #020617 0%, rgba(15, 23, 42, 0.9) 100%);
  }

  .countdown-card {
    background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 70%, #020617 100%);
    color: #e2e8f0;
    border-radius: 22px;
    box-shadow: 0 24px 45px rgba(2, 6, 23, 0.65);
    overflow: hidden;
  }

  .countdown-card .clock-icon {
    font-size: 3.25rem;
    animation: pulse 1.8s ease-in-out infinite;
    color: #38bdf8;
  }

  @keyframes pulse {
    0%, 100% { opacity: 0.75; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.06); }
  }

  .results-card {
    border-radius: 22px;
    min-height: 420px;
    box-shadow: 0 28px 48px rgba(15, 23, 42, 0.18);
    border: none;
    display: flex;
    flex-direction: column;
    width: 100%;
  }

  .numbers-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .numbers-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem;
    border-radius: 12px;
    background: #111827;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
    color: #f8fafc;
  }

  .numbers-item-label {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
  }

  .numbers-item-value {
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: 0.08em;
  }

  .filters-wrapper .form-control {
    border-radius: 50px;
    padding: 0.625rem 1.25rem;
  }

  .filters-wrapper .btn {
    border-radius: 50px;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  @media (max-width: 991.98px) {
    .floating-cta {
      position: static;
    }

    .responsive-cards {
      grid-auto-flow: column;
      grid-auto-columns: minmax(260px, 80vw);
      overflow-x: auto;
      padding-bottom: 0.75rem;
      gap: 1.25rem;
      scroll-snap-type: x proximity;
    }

    .responsive-cards-results {
      grid-auto-columns: minmax(260px, 85vw);
    }

    .responsive-cards > * {
      scroll-snap-align: start;
    }

    .responsive-cards::-webkit-scrollbar {
      height: 6px;
    }

    .responsive-cards::-webkit-scrollbar-thumb {
      background: rgba(148, 163, 184, 0.5);
      border-radius: 999px;
    }

    .responsive-cards::-webkit-scrollbar-track {
      background: rgba(15, 23, 42, 0.1);
    }
  }

  @media (min-width: 992px) {
    .responsive-cards {
      overflow: visible;
    }
  }
</style>
@endsection

@section('content')
<div class="container py-5">
  <div class="floating-cta py-3 mb-4">
    <div class="d-flex flex-row flex-wrap flex-lg-row align-items-stretch gap-3 justify-content-center">
      <a href="#premiacao" class="btn btn-outline-dark btn-lg text-uppercase fw-bold">Premiação</a>
      <a href="#resultados" class="btn btn-outline-dark btn-lg text-uppercase fw-bold">Resultados</a>
      <a href="#apostadores" class="btn btn-outline-dark btn-lg text-uppercase fw-bold">Apostadores</a>
    </div>
  </div>

  <section class="mb-5">
    <div class="row g-3">
      <div class="col-sm-6">
        <div class="card social-card h-100 border-0 shadow-sm bg-success text-white">
          <div class="card-body d-flex flex-column justify-content-center align-items-center text-center px-4">
            <h5 class="fw-bold mb-2 text-uppercase">Grupo Oficial do WhatsApp</h5>
            <p class="mb-3">Entre e acompanhe o bolão em tempo real.</p>
            <a href="https://wa.me/19997655946" target="_blank" rel="noopener" class="btn btn-light text-success fw-semibold px-4">
              Entrar agora
            </a>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="card social-card h-100 border-0 shadow-sm bg-info text-white">
          <div class="card-body d-flex flex-column justify-content-center align-items-center text-center px-4">
            <h5 class="fw-bold mb-2 text-uppercase">Canal Oficial do Telegram</h5>
            <p class="mb-3">Entre e acompanhe o bolão em tempo real.</p>
            <a href="https://t.me/" target="_blank" rel="noopener" class="btn btn-light text-info fw-semibold px-4">
              Entrar agora
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="mb-5">
    <div class="card hero-card">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
          <div>
            <div class="badge bg-light text-dark text-uppercase mb-3 px-3 py-2">
              Concurso em destaque
            </div>
            <h1 class="display-5 fw-bold mb-3">{{ $game->name }}</h1>
            <div class="d-flex flex-wrap align-items-center gap-3">
              <p class="mb-0 lead">Vários sorteios — até sair um ganhador de 10 Pontos!</p>
              <a href="#premiacao" class="btn btn-outline-light btn-lg">Regras e detalhes</a>
            </div>
          </div>
          <div class="text-lg-end">
            <div class="small text-uppercase text-white-50">Status atual</div>
            <div class="h4 mb-2">{{ $gameMeta['status_label'] }}</div>
            @if($gameMeta['start_label'])
              <div class="fw-semibold">{{ $gameMeta['start_label'] }}</div>
            @endif
          </div>
        </div>
      </div>
      <div class="card-footer d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
        <div class="d-flex flex-column flex-md-row gap-2 gap-md-4">
          @if($gameMeta['start_at'])
            <span class="fw-semibold">
              Começa: {{ $gameMeta['start_at']->translatedFormat('D, d/m/Y') }} às {{ $gameMeta['start_at']->format('H:i') }}h
            </span>
          @endif
          @if($gameMeta['close_at'])
            <span class="fw-semibold">
              Fecha: {{ $gameMeta['close_at']->translatedFormat('D, d/m/Y') }} às {{ $gameMeta['close_at']->format('H:i') }}h
            </span>
          @endif
        </div>
        @if($gameMeta['start_relative'])
          <div class="badge bg-warning text-dark px-4 py-2 text-uppercase fw-semibold">
            {{ $gameMeta['start_relative'] }}
          </div>
        @endif
      </div>
    </div>
  </section>

  <section id="premiacao" class="mb-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1">Premiação</h2>
        <p class="text-muted mb-0">Prêmios para os melhores apostadores deste concurso.</p>
      </div>
    </div>

    @if($awards->isEmpty())
      <div class="alert alert-light border shadow-sm mb-0">
        Nenhum prêmio cadastrado para este concurso no momento.
      </div>
    @else
      <div class="responsive-cards responsive-cards-awards">
        @foreach ($awards as $index => $award)
          @php
            $variant = $index % 6;
            $winnersCount = $award->winners_count ?? 0;
            $totalAwarded = $award->winners_total_amount ?? 0;
            $perWinnerAmount = $winnersCount > 0 ? $totalAwarded / $winnersCount : 0;
            $statusMessage = match ($game->status) {
              'OPENED' => 'Nenhum ganhador até agora — concurso em andamento',
              'CLOSED' => 'Nenhum ganhador por este prêmio na rodada atual',
              'FINISHED' => 'Nenhum apostador atingiu os critérios deste prêmio',
              default => 'Nenhum ganhador registrado até o momento',
            };
          @endphp
          <div class="responsive-card">
            <div class="card prize-card h-100 prize-variant-{{ $variant }}">
              <div class="prize-card-section prize-card-top">
                <small>Premiação</small>
                <span class="display-6 fw-bold">
                  R$ {{ number_format($award->amount, 2, ',', '.') }}
                </span>
              </div>
              <div class="prize-card-section prize-card-middle">
                <h5>{{ $award->name }}</h5>
                <p>{{ $award->description ?? 'Conquiste este prêmio atingindo a pontuação necessária.' }}</p>
              </div>
              <div class="prize-card-section prize-card-bottom">
                @if($winnersCount > 0)
                  <div class="text-center">
                    <div class="fw-bold fs-6">{{ $winnersCount }} {{ \Illuminate\Support\Str::plural('apostador', $winnersCount) }} premiado{{ $winnersCount > 1 ? 's' : '' }}</div>
                    @if($perWinnerAmount > 0)
                      <div class="fs-5 fw-semibold mt-2">R$ {{ number_format($perWinnerAmount, 2, ',', '.') }} por ganhador</div>
                    @endif
                    <small class="text-white-50 text-uppercase d-block mt-2">Prêmio já distribuído neste concurso</small>
                  </div>
                @else
                  <div class="text-center">
                    <div class="fw-bold fs-6">{{ $statusMessage }}</div>
                    <small class="text-white-50 text-uppercase d-block mt-2">Prêmio aguardando ganhadores</small>
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </section>

  @if($countdownTarget)
  <section class="mb-5">
    <div class="countdown-card p-4 p-lg-5">
      <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-4">
        <div class="d-flex align-items-center gap-3">
          <div class="clock-icon text-white">
            <i class="bx bx-time-five"></i>
          </div>
          <div>
            <p class="text-uppercase fw-semibold mb-1 text-white-50">Contagem regressiva</p>
            <h3 class="mb-0 fw-bold">O bolão começa em</h3>
          </div>
        </div>
        <div id="bolao-countdown" class="display-5 fw-bold text-white text-center text-lg-end">
          —
        </div>
      </div>
    </div>
  </section>
  @endif

<section id="resultados" class="mb-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1">Resultados</h2>
        <p class="text-muted mb-0">
          {{ $summary['total_draws'] }} sorteios = {{ $summary['total_numbers'] }} dezenas sorteadas
        </p>
      </div>
    </div>

    @if($results->isEmpty())
      <div class="alert alert-info border-0 shadow-sm">
        Ainda não temos resultados divulgados para este concurso. Volte em breve!
      </div>
    @else
      <div class="responsive-cards responsive-cards-results">
        @foreach($results as $result)
          <div class="responsive-card">
            <div class="card results-card h-100">
              <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h5 class="mb-1">{{ $result['description'] ?? 'Resultado do Sorteio' }}</h5>
                    <span class="text-muted">
                      {{ ucfirst($result['created_at']->translatedFormat('l, d/m/Y')) }}
                    </span>
                  </div>
                  <span class="badge bg-primary-subtle text-primary fw-semibold">
                    {{ $result['draw_number'] }}º Sorteio
                  </span>
                </div>

                <div class="mb-3">
                  <div class="text-muted mb-1">
                    {{ $result['created_at']->format('H:i') }}h • {{ $game->name }}
                  </div>
                  <div class="numbers-stack">
                    @foreach($result['raw_numbers'] as $position => $number)
                      <div class="numbers-item">
                        <span class="numbers-item-label">{{ $position + 1 }}º dezena</span>
                        <span class="numbers-item-value">{{ $number }}</span>
                      </div>
                    @endforeach
                  </div>
                </div>

                <div class="mt-auto">
                  <p class="small text-uppercase text-muted mb-1">As dezenas em ordem crescente</p>
                  <p class="fw-semibold">
                    {{ $result['ascending_numbers']->implode(' ') }}
                  </p>
                  <div class="d-flex justify-content-between text-muted small">
                    <span>Cadastrado às {{ $result['created_at']->format('H:i') }}h</span>
                    <span>Conc.: {{ $game->round }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </section>

  <section id="apostadores" class="mb-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1">Apostadores</h2>
        <p class="text-muted mb-0">Acompanhe as apostas e pontuações já registradas.</p>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body">
        <form method="GET" action="{{ route('public-bolao') }}" class="filters-wrapper mb-4">
          <div class="row g-3">
            <div class="col-md-4">
              <input type="text" name="bilhete" value="{{ $filters['bilhete'] ?? '' }}" class="form-control" placeholder="Buscar bilhete">
            </div>
            <div class="col-md-4">
              <input type="text" name="apostador" value="{{ $filters['apostador'] ?? '' }}" class="form-control" placeholder="Buscar apostador">
            </div>
            <div class="col-md-4">
              <input type="text" name="dezenas" value="{{ $filters['dezenas'] ?? '' }}" class="form-control" placeholder="Buscar dezenas">
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
              <button type="submit" class="btn btn-primary">Filtrar</button>
              <a href="{{ route('public-bolao') }}#apostadores" class="btn btn-outline-secondary">Limpar</a>
            </div>
          </div>
        </form>

        <div class="table-responsive responsive-table">
          <table class="table align-middle mb-0">
            <thead class="text-uppercase small text-muted">
              <tr>
                <th>Detalhes</th>
                <th>Bilhete</th>
                <th>Apostador</th>
                <th>Dezenas Jogadas</th>
                <th class="text-end">Pontos</th>
              </tr>
            </thead>
            <tbody>
              @forelse($purchases as $purchase)
                <tr>
                  <td data-label="Detalhes">
                    <span class="badge bg-dark text-white">Bilhete #{{ $purchase->identifier ?? $purchase->id }}</span>
                  </td>
                  <td data-label="Bilhete">{{ $purchase->identifier ?? '—' }}</td>
                  <td data-label="Apostador">
                    <div class="fw-semibold">{{ $purchase->gambler_name ?? 'Não informado' }}</div>
                    <div class="text-muted small">{{ $purchase->gambler_phone ?? '' }}</div>
                  </td>
                  <td data-label="Dezenas Jogadas" class="td-dezenas">
                    <span class="d-inline-flex flex-wrap gap-1">
                      @foreach(explode(' ', $purchase->numbers ?? '') as $number)
                        @if($number !== '')
                          <span class="badge bg-light text-dark">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</span>
                        @endif
                      @endforeach
                    </span>
                  </td>
                  <td data-label="Pontos" class="text-end">
                    <span class="badge bg-success text-white">{{ $purchase->points ?? 0 }} pts</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">
                    Nenhuma aposta encontrada para os filtros selecionados.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end">
          {{ $purchases->appends($filters)->fragment('apostadores')->links('pagination::bootstrap-5') }}
        </div>
      </div>
    </div>
  </section>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    @if($countdownTarget)
    const countdownElement = document.getElementById('bolao-countdown');
    const targetDate = new Date("{{ $countdownTarget }}").getTime();

    if (countdownElement && !Number.isNaN(targetDate)) {
      const formatUnit = (value, singular, plural) => {
        if (value <= 0) {
          return null;
        }
        return `${value}${value === 1 ? singular : plural}`;
      };

      const updateCountdown = () => {
        const now = Date.now();
        const distance = targetDate - now;

        if (distance <= 0) {
          countdownElement.textContent = 'O bolão já começou!';
          clearInterval(intervalId);
          return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const parts = [
          formatUnit(days, 'dia', 'dias'),
          `${String(hours).padStart(2, '0')}h`,
          `${String(minutes).padStart(2, '0')}m`,
          `${String(seconds).padStart(2, '0')}s`,
        ].filter(Boolean);

        countdownElement.textContent = parts.join(' : ');
      };

      updateCountdown();
      const intervalId = setInterval(updateCountdown, 1000);
    }
    @endif

    const shouldAnchorApostadores = @json(collect($filters)->filter(fn($value) => !empty($value))->isNotEmpty() || request()->has('page'));
    if (shouldAnchorApostadores) {
      window.location.hash = 'apostadores';
    }
  });
</script>
@endsection
