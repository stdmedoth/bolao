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


<!-- Exibição da mensagem de erro geral -->
@if ($errors->has('error'))
<div class="alert alert-danger">
  {{ $errors->first('error') }}
</div>
@endif

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
      @error('amount')
      <small class="text-danger">{{ $message }}</small>
      @enderror

      <div class="form-group">
        <label for="payment_method" class="form-label">Forma de Pagamento</label>
        <select class="form-control" name="payment_method" id="payment_method">
          <option value="pix">Pix</option>
        </select>
        @error('payment_method')
        <small class="text-danger">{{ $message }}</small>
        @enderror

      </div>

      <div class="form-group">
        <label for="pix_key" class="form-label">Chave Pix</label>
        <input type="text" class="form-control" name="pix_key" placeholder="Digite sua Chave PIX">
      </div>
      @error('pix_key')
      <small class="text-danger">{{ $message }}</small>
      @enderror
      <div class="form-group">
        <label for="pix_key_type" class="form-label">Tipo de Pix</label>
        <select class="form-control" name="pix_key_type" id="pix_key_type">
          <option value="CPF">CPF</option>
          <option value="CNPJ">CNPJ</option>
          <option value="EMAIL">E-mail</option>
          <option value="PHONE">Telefone</option>
          <option value="EVP">Chave Aleatória</option>
        </select>
      </div>
      @error('pix_key_type')
      <small class="text-danger">{{ $message }}</small>
      @enderror

      <!-- Botão separado -->
      <div class="form-group mt-4">
        <button type="submit" class="btn btn-primary">Sacar</button>
      </div>
    </form>
  </div>
</div>

@endsection