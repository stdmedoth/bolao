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
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
  {{ $error }}
</div>
@endforeach
@endif


<!-- Basic Bootstrap Table -->
<div class="card">
  <h5 class="card-header">Depositar</h5>
  <div class="card-body">
    <div class="row">
      @if(isset($pix))
      <div class="col">
        <img src="data:image/jpeg;base64, {{ $pix }}" />
        <p id="pix_data_copy">{{$copy_paste}}</p>
        <button onclick="copyToClipboard()">Copiar</button>
      </div>
      @endif

      <div class="col">
        <form id="depositForm" action="{{ route('deposit-create-pix') }}" method="POST">
          @csrf
          <div class="form-group">
            <label for="amount" class="form-label">Valor do Depósito</label>
            <input type="number" class="form-control" id="amount" name="amount" placeholder="Digite o valor" value="{{ isset($amount) ? $amount : '' }}" required>
          </div>
          <div class="form-group">
            <label for="payment_method" class="form-label">Forma de Pagamento</label>
            <select class="form-control" name="payment_method" id="payment_method">
              <option value="pix">Pix</option>
              <option value="credit_card">Cartão Credito</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Depositar</button>
        </form>
      </div>

      <div class="col">
        <div id="credit_card_infos" class="card">
          <div class="form-group">
            <label for="cc_name" class="form-label">Nome no cartão</label>
            <input class="form-control" type="text" name="cc_name" value="{{auth()->user()->cc_name}}">
          </div>
          <div class="form-group">
            <label for="cc_number" class="form-label">Número no cartão</label>
            <input class="form-control" type="text" name="cc_number" value="{{auth()->user()->cc_number}}">
          </div>
          <div class="form-group">
            <label for="cc_expiry_month" class="form-label">Mês de expiração</label>
            <input class="form-control" type="number" name="cc_expiry_month" value="{{auth()->user()->cc_expiry_month}}">
          </div>
          <div class="form-group">
            <label for="cc_expiry_year" class="form-label">Ano de expiração</label>
            <input class="form-control" type="number" name="cc_expiry_year" value="{{auth()->user()->cc_expiry_year}}">
          </div>
          <div class="form-group">
            <label for="cc_ccv" class="form-label">Código de Segurança</label>
            <input class="form-control" type="number" name="cc_ccv" value="{{auth()->user()->cc_ccv}}">
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<!--/ Basic Bootstrap Table -->

<script>
  document.getElementById("credit_card_infos").style.display = 'none';
  document.getElementById('payment_method').addEventListener('change', function() {
    const form = document.getElementById('depositForm');
    const selectedMethod = this.value;

    if (selectedMethod === 'pix') {
      form.action = "{{ route('deposit-create-pix') }}";
      document.getElementById("credit_card_infos").style.display = 'none';
    } else if (selectedMethod === 'credit_card') {
      form.action = "{{ route('deposit-create-credit-card') }}";
      document.getElementById("credit_card_infos").style.display = 'block';
    }
  });

  function copyToClipboard() {

    text = document.getElementById("pix_data_copy").innerHTML;

    // Cria um elemento temporário para armazenar o texto
    const tempInput = document.createElement("textarea");
    tempInput.value = text;
    document.body.appendChild(tempInput);

    // Seleciona o texto no elemento temporário
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // Para compatibilidade com dispositivos móveis

    // Copia o texto para a área de transferência
    document.execCommand("copy");

    // Remove o elemento temporário
    document.body.removeChild(tempInput);

    // Exibe uma mensagem de sucesso no console
    console.log("Texto copiado: " + text);
  }
</script>

<style>
  #credit_card_infos.card {
    background-color: #f9f9f9;
    /* Cor de fundo neutra */
    border: 1px solid #ddd;
    /* Borda sutil */
    border-radius: 8px;
    /* Cantos arredondados */
    padding: 16px;
    /* Espaçamento interno */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    /* Sombra para dar um efeito 3D */
    max-width: 400px;
    /* Largura máxima para centralizar a atenção */
    margin: 16px auto;
    /* Centraliza horizontalmente na página */
  }

  #credit_card_infos .form-group {
    margin-bottom: 16px;
    /* Espaçamento entre os campos */
  }

  #credit_card_infos .form-label {
    font-weight: bold;
    /* Dá destaque aos labels */
    display: block;
    /* Garante que os labels fiquem acima dos inputs */
    margin-bottom: 8px;
    /* Espaçamento abaixo dos labels */
  }

  #credit_card_infos .form-control {
    width: 100%;
    /* Campo ocupa toda a largura disponível */
    padding: 8px;
    /* Espaçamento interno dos inputs */
    font-size: 14px;
    /* Tamanho de fonte confortável */
    border: 1px solid #ccc;
    /* Borda leve */
    border-radius: 4px;
    /* Cantos arredondados */
  }

  #credit_card_infos .form-control:focus {
    border-color: #007bff;
    /* Cor azul ao focar no campo */
    box-shadow: 0 0 4px rgba(0, 123, 255, 0.25);
    /* Efeito de foco */
    outline: none;
    /* Remove o outline padrão */
  }
</style>

@endsection