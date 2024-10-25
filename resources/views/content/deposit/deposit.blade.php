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
  <h5 class="card-header">Depositar</h5>
  <div class="card-body">
    <form action="{{ route('deposito') }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="amount" class="form-label">Valor do Depósito</label>
        <input type="number" class="form-control" id="amount" name="amount" placeholder="Digite o valor" required>
      </div>
      <div class="form-group">
        <label for="payment_method" class="form-label">Forma de Pagamento</label>
        <select class="form-control" name="payment_method" id="payment_method">
          <option value="pix">Pix</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Depositar</button>
    </form>
  </div>
</div>
<!--/ Basic Bootstrap Table -->

@endsection