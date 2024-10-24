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
<div class="container">
  <h1 class="my-4">Detalhes do Jogo</h1>
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p><strong>Nome:</strong> {{ $game->name }}</p>
      <p><strong>Status:</strong> {{ $game->status }}</p>
      <p><strong>Preço:</strong> R$ {{ number_format($game->price, 2, ',', '.') }}</p>
      <p><strong>Aberto em:</strong> {{ date("d/m/Y", strtotime($game->open_at)) }}</p>
      <p><strong>Fecha em:</strong> {{ date("d/m/Y", strtotime($game->close_at)) }}</p>
      <p><strong>Ativo:</strong> {{ $game->active ? 'Sim' : 'Não' }}</p>
    </div>
    <div class="card-footer d-flex justify-content-between">
      @if($game->status == "CLOSED")
      <a href="/concursos/open/{{ $game->id }}" class="btn btn-success">Abrir</a>
      @endif
      <a href="/concursos/close/{{ $game->id }}" class="btn btn-danger">Fechar Jogo</a>
    </div>
  </div>

  @if(count($errors) > 0)
  <div class="alert alert-danger">
    @foreach( $errors->all() as $message )
    <span>{{ $message }}</span><br>
    @endforeach
  </div>
  @endif

  <h3 class="mb-0">Prêmios Disponíveis</h3>
  <div class="card shadow-sm mb-4">

    <div class="card-body">
      @if($game->awards->isEmpty())
      <p class="text-muted">Nenhum prêmio foi configurado para este jogo.</p>
      @else
      <ul class="list-group list-group-flush">
        @foreach($game->awards as $award)
        <li class="list-group-item">
          <strong>Tipo:</strong> {{ $award->condition_type }} <br>
          <strong>Valor:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }} <br>
          @if($award->condition_type === 'MINIMUM_POINT')
          <strong>Pontos Mínimos Necessários:</strong> {{ $award->minimum_point_value }}
          @endif
        </li>
        @endforeach
      </ul>
      @endif
    </div>
  </div>
</div>
@endsection