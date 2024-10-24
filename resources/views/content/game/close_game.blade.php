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
  <h1>Fechar Jogo</h1>

  @if(count($errors) > 0)
  @foreach( $errors->all() as $message )
  <div class="alert alert-danger display-hide">
    <span>{{ $message }}</span>
  </div>
  @endforeach
  @endif

  <form action="{{ route('admin.closeGame', $game->id) }}" method="POST">
    @csrf
    <div class="mb-3">
      <label for="gameName" class="form-label">Nome do Jogo</label>
      <input type="text" class="form-control" id="gameName" value="{{ $game->name }}" readonly>
    </div>
    <div class="mb-3">
      <label for="gameStatus" class="form-label">Status Atual</label>
      <input type="text" class="form-control" id="gameStatus" value="{{ $game->status }}" readonly>
    </div>
    <div class="mb-3">
      <label for="gamePrice" class="form-label">Preço</label>
      <input type="text" class="form-control" id="gamePrice" value="{{ $game->price }}" readonly>
    </div>
    <div class="mb-3">
      <label for="gameOpenAt" class="form-label">Aberto em</label>
      <input type="text" class="form-control" id="gameOpenAt" value="{{ $game->open_at }}" readonly>
    </div>
    <div class="mb-3">
      <label for="gameCloseAt" class="form-label">Fecha em</label>
      <input type="text" class="form-control" id="gameCloseAt" value="{{ $game->close_at }}" readonly>
    </div>

    @if($game->status === 'OPENED')
    <button type="submit" class="btn btn-danger">Fechar Jogo</button>
    @else
    <div class="alert alert-warning">Este jogo já foi fechado.</div>
    @endif
  </form>
</div>
@endsection