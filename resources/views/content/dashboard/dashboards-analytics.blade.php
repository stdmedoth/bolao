@extends('layouts/contentNavbarLayout')

@php
    use App\Models\Aviso;
    use App\Models\Banner;

    // Carrega os avisos e os banners ativos
    $avisosPublicos = Aviso::orderByDesc('created_at')->get();
    $bannersAtivos = Banner::where('ativo', true)->orderByDesc('id')->get();
@endphp

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
  @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
  @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
  @vite('resources/assets/js/dashboards-analytics.js')
@endsection

@section('content')

<style>
  /* ==== Layout base ==== */
  .announcement-board {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
    animation: fadeIn 1s ease-in;
  }
  .announcement-board h1 {
    font-size: 1.5rem;
    color: #374151;
    margin-bottom: 15px;
    font-weight: 600;
  }
  .announcement-list {
    list-style: none;
    padding-left: 0;
  }
  .announcement-list li {
    padding: 10px 12px;
    border-radius: 6px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    opacity: 0;
    animation: fadeIn 0.8s ease forwards;
  }
  .announcement-list li:nth-child(1) { animation-delay: 0.2s; }
  .announcement-list li:nth-child(2) { animation-delay: 0.4s; }
  .announcement-list li:nth-child(3) { animation-delay: 0.6s; }
  .announcement-list li:nth-child(4) { animation-delay: 0.8s; }

  .announcement-list .icon { font-size: 1.3rem; }
  .announcement-info { background-color: #e8f4ff; color: #0369a1; }
  .announcement-alerta { background-color: #fff7e6; color: #92400e; }
  .announcement-erro { background-color: #fee2e2; color: #991b1b; }
  .announcement-list b { font-weight: 600; }

  /* ==== Layout responsivo ==== */
  @media (min-width: 992px) {
    .desktop-order-1 { order: 1; }
    .desktop-order-2 { order: 2; }
  }
  @media (max-width: 991px) {
    .mobile-order-1 { order: 1; }
    .mobile-order-2 { order: 2; }
    .mobile-order-3 { order: 3; }
    .banner-image { margin-bottom: 10px; animation: fadeIn 1s ease-in; }
    .announcement-board { margin-top: 10px; }
  }

  /* ==== Animações ==== */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ==== Botão piscando ==== */
  .btn-blink {
    background-color: #00FF00 !important;
    color: #000 !important;
    font-weight: bold;
    border: none !important;
    animation: blink 1.5s infinite;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .btn-blink:hover {
    background-color: darkred !important;
    color: #fff !important;
    box-shadow: 0 0 10px rgba(255,0,0,0.6);
    transform: scale(1.05);
  }
  @keyframes blink {
    0%, 50%, 100% { opacity: 1; }
    25%, 75% { opacity: 0.6; }
  }
</style>

<div class="row">
  <div class="col-12 mb-6 order-0">
    <div class="card welcome-card">
      <div class="row align-items-start">

        <!-- Saudação + Banner -->
        <div class="col-lg-7 desktop-order-1 mobile-order-1">
          <div class="card-body text-center" style="animation: fadeIn 0.8s ease;">
            <h4 style="color:#3173A8; font-size:1.5rem; margin-bottom:20px;">
              Olá, {{ auth()->user()->name }}
            </h4>

            <div class="btn-group-custom mb-3">
              <a href="/concursos" class="btn btn-sm btn-blink">VEJA OS CONCURSOS</a>
            </div>

            {{-- Carrossel de Banners Dinâmicos --}}
            @if ($bannersAtivos->isNotEmpty())
              <div id="carouselBanners" class="carousel slide banner-image" data-bs-ride="carousel" data-bs-interval="5000">
                <div class="carousel-inner rounded-3 shadow-sm">
                  @foreach ($bannersAtivos as $index => $banner)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                      <img 
                        src="{{ asset($banner->imagem) }}" 
                        class="d-block w-100 rounded-3"
                        alt="Banner {{ $index + 1 }}"
                        style="max-height:700px; object-fit:cover;">
                    </div>
                  @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselBanners" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselBanners" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Próximo</span>
                </button>
              </div>
            @else
              <div class="banner-image">
                <img src="{{ asset('assets/img/illustrations/slide-01h.png') }}" class="img-fluid rounded-3" alt="Banner padrão">
              </div>
            @endif

          </div>
        </div>

        <!-- Quadro de Avisos -->
        <div class="col-lg-5 desktop-order-2 mobile-order-3">
          <div class="card-body">
            <div class="announcement-board">
              <h1>📢 Quadro de Avisos</h1>

              @if ($avisosPublicos->isNotEmpty())
                <ul class="announcement-list">
                  @foreach ($avisosPublicos as $aviso)
                    <li class="announcement-{{ $aviso->tipo ?? 'info' }}">
                      @if($aviso->tipo === 'alerta')
                        <span class="icon">⚠️</span>
                      @elseif($aviso->tipo === 'erro')
                        <span class="icon">❌</span>
                      @else
                        <span class="icon">ℹ️</span>
                      @endif
                      <div>
                        <b>{{ $aviso->titulo }}:</b>
                        <span style="color:#000;">{!! $aviso->mensagem !!}</span>
                      </div>
                    </li>
                  @endforeach
                </ul>
              @else
                <p class="text-muted">Nenhum aviso disponível no momento.</p>
              @endif
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

@endsection
