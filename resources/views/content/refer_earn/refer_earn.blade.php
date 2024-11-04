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
  <h1 class="my-4">Indique e Ganhe</h1>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="mb-3">
        <label for="referralLink" class="form-label">Seu Link de Indicação</label>
        <div class="input-group">
          <input type="text" id="referralLink" class="form-control" value="{{env('APP_URL')}}/indique_ganhe/register?code={{ $code }}" readonly>
          <button class="btn btn-primary" onclick="copyLink()">Copiar</button>
        </div>
      </div>

      <div class="mt-4">
        <h5>Estatísticas de Indicação</h5>
        <div class="row">
          <div class="col-md-4">
            <div class="card border-info mb-3">
              <div class="card-body text-center">
                <h6 class="card-title">Pessoas Cadastradas</h6>
                <p class="display-4" id="totalRegistrations">{{$refered_qnt}}</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-success mb-3">
              <div class="card-body text-center">
                <h6 class="card-title">Pessoas que Compraram</h6>
                <p class="display-4" id="totalPurchases">{{$refered_qnt_bought}}</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-warning mb-3">
              <div class="card-body text-center">
                <h6 class="card-title">Valor Recebido por Indicação</h6>
                <p class="display-4" id="totalEarned">R$ {{number_format($refered_amount_earned, 2, ',', '.')}}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Orientações de Indicação -->
  <div class="alert alert-info mt-3">
    <h6>Como Funciona:</h6>
    <ul class="mb-0">
      <li>Envie seu link para algum amigo.</li>
      <li>O seu amigo deve criar uma conta em nosso site com seu link.</li>
      <li>Ganhe um bônus de R$10,00 na primeira compra do amigo que você indicou!</li>
      <li>O seu amigo deve realizar uma compra mínima de R$10,00 para que o bônus seja ativado.</li>
    </ul>
  </div>
</div>

<script>
  function copyLink() {
    var copyText = document.getElementById("referralLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // Para dispositivos móveis
    navigator.clipboard.writeText(copyText.value).then(() => {
      alert("Link copiado com sucesso!");
    }).catch(err => {
      console.error('Erro ao copiar o link: ', err);
    });
  }
</script>
@endsection