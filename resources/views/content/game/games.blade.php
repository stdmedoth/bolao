@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Minhas Compras')

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

@if (auth()->user()->role->level_id == 'admin')
<a class="btn btn-secondary" href="/concursos/create_game_form">Criar novo jogo</a>
@endif

<!-- Lista de Jogos -->
<div class="card shadow-lg p-3 mb-5 bg-white rounded">
  <h5 class="card-header">Jogos</h5>
  <div class="row mt-3">
    <!-- Iterando sobre os jogos para exibir como cards -->
    @foreach ($games as $game)
    <div class="col-md-4 mb-4">
      <div class="card h-100">
        <div class="card-header">
          <a href='/concursos/{{$game->id}}'>
            <i class="menu-icon tf-icons bx bxs-barcode"></i>
            {{ $game->name }}
          </a>
          <div class="dropdown">
            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="/concursos/edit/{{ $game->id }}"><i class="bx bx-edit-alt me-1"></i> Editar</a>
            </div>
          </div>
        </div>
        <div class="card-body" onclick="window.location = '/concursos/{{$game->id}}'">
          <p class="card-text"><strong>Aberto em:</strong> {{ date('d/m/Y', strtotime($game->open_at)) }}</p>
          <p class="card-text"><strong>Fecha em:</strong> {{ date('d/m/Y', strtotime($game->close_at)) }}</p>
          <p class="card-text">
            <span class="badge bg-label-primary">{{ $game->status }}</span>
          </p>
          <p class="card-text"><strong>Preço:</strong> R$ {{ number_format($game->price, 2, ',', '.') }} </p>
          @if($game->awards->isEmpty())
          @foreach($game->awards as $award)
          <strong>Prêmio:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }} <br>
          @endforeach
          @endif
        </div>

        <div class="card-footer">

        </div>
      </div>
    </div>
    @endforeach
  </div>
</div>

@endsection
