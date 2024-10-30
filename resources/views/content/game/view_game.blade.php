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
      <p><strong>Status:</strong>
      <p class="card-text">
        <span class="badge bg-label-primary">{{ __($game->status) }}</span>
      </p>
      </p>
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

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form id="form" action="{{ route('purchase-store', $game->id) }}" method="POST">
        @csrf
        <div class="form-group">
          <label for="gambler_name">Nome do Apostador</label>
          <input type="text" class="form-control" id="gambler_name" name="gambler_name" placeholder="Digite seu nome" required>
        </div>

        <div class="form-group">
          <label for="gambler_phone">Telefone do Apostador</label>
          <input type="text" class="form-control" id="gambler_phone" name="gambler_phone" placeholder="Digite seu telefone">
        </div>

        <div class="form-group">
          <label for="numbers">Escolha suas dezenas (11 números por vez, separados por espaços)</label>
          <input type="text" class="form-control" id="numbers" name="numbers" placeholder="Exemplo: 11 22 12 32 12 43 12 54 65 23 12" required>
          <small class="form-text text-muted">As dezenas devem ter dois dígitos cada e serem separadas por espaços, em grupos de 11 números.</small>
          <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
        </div>

        <input type="hidden" name="game_id" value="{{ $game->id }}">
        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

        <button type="submit" class="btn btn-primary">Comprar Dezenas</button>
      </form>

    </div>
  </div>

  <script>
    document.getElementById('numbers').addEventListener('input', function(e) {
      let input = e.target.value.replace(/[^\d]/g, ''); // Remove caracteres não numéricos
      let formatted = input.match(/.{1,2}/g) || []; // Divide em pares de dígitos

      // Formata para grupos de 11 dezenas, separados por espaços
      let output = [];
      for (let i = 0; i < formatted.length; i++) {
        output.push(formatted[i]);
        // Adiciona um espaço se não for o último número do grupo de 11
        if ((i + 1) % 11 !== 0 && i !== formatted.length - 1) {
          output.push(' ');
        }
        // Adiciona uma vírgula se for o final de um grupo de 11, mas não o último número
        if ((i + 1) % 11 === 0 && i !== formatted.length - 1) {
          output.push(', ');
        }
      }

      // Atualiza o valor do campo com o formato desejado
      e.target.value = output.join('');
    });

    document.getElementById('form').addEventListener('submit', function(e) {
      const numbersValue = document.getElementById('numbers').value.trim();
      const groups = numbersValue.split(/,\s*/); // Divide grupos separados por vírgula

      errorMessage = document.getElementById('error-message')
      for (let group of groups) {
        const numbersArray = group.trim().split(/\s+/); // Divide o grupo em números
        if (numbersArray.length < 11) {
          e.preventDefault(); // Evita o envio do formulário
          errorMessage.textContent = 'Cada grupo deve conter pelo menos 11 números.';
          errorMessage.style.display = 'block';
          return;
        }
      }

      errorMessage.style.display = 'none'; // Oculta a mensagem de erro se tudo estiver certo
    });
  </script>
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
          @if($award->condition_type === 'MINIMUM_POINT')
          Apostador deve fazer <strong>pelo menos {{ $award->minimum_point_value }} pontos </strong> <br>
          @endif
          @if($award->condition_type === 'EXACT_POINT')
          Apostador deve fazer <strong>exatamente {{ $award->minimum_point_value }} pontos </strong> <br>
          @endif
          <strong>Valor do prêmio:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }} <br>
        </li>
        @endforeach
      </ul>
      @endif
    </div>
  </div>
</div>
@endsection
