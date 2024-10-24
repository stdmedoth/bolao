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
  <h2>Criar Novo Jogo</h2>
  <form action="{{ route('create-game') }}" method="POST">
    @csrf
    <div class="form-group">
      <label for="name">Nome do Jogo:</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="price">Preço:</label>
      <input type="number" name="price" class="form-control" step="0.01" required>
    </div>

    <div class="form-group">
      <label for="open_at">Data de Abertura:</label>
      <input type="datetime-local" name="open_at" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="close_at">Data de Fechamento:</label>
      <input type="datetime-local" name="close_at" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Criar Jogo</button>
  </form>
</div>
@endsection