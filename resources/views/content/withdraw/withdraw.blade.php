@extends('layouts/contentNavbarLayout')

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

<!-- Basic Bootstrap Table -->
<div class="card">
  <h5 class="card-header">Sacar</h5>
  <div class="card-body">
    <form action="{{ route('saque') }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="amount" class="form-label">Valor do Saque</label>
        <input type="number" class="form-control" id="amount" name="amount" placeholder="Digite o valor" required>
      </div>
      <div class="form-group">
        <label for="payment_method" class="form-label">Forma de Pagamento</label>
        <select class="form-control" name="payment_method" id="payment_method">
          <option value="pix">Pix</option>
        </select>
      </div>
      <div class="form-group">
        <label for="pix_key" class="form-label">Chave pix</label>
        <input type="text" class="form-control" name="pix_key" placeholder="Digite sua Chave PIX">
      </div>
      <button type="submit" class="btn btn-primary">Depositar</button>
    </form>
  </div>
</div>
<!--/ Basic Bootstrap Table -->

@endsection