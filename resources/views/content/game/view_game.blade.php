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
<div class="container-fluid">
  <h1 class="my-4">Detalhes do Jogo</h1>

  <!-- Exibição da mensagem de erro geral -->
  @if ($errors->has('error'))
  <div class="alert alert-danger">
    {{ $errors->first('error') }}
  </div>
  @endif

  <ul class="nav nav-tabs flex-wrap" id="gameTabs" role="tablist">
    <!-- Tabs existentes -->
    <li class="nav-item" role="presentation">
      <a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">Detalhes</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="bet-form-tab" data-bs-toggle="tab" href="#bet-form" role="tab" aria-controls="bet-form" aria-selected="false">Apostar</a>
    </li>
    <!-- Demais abas não alteradas -->
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="mybets-tab" data-bs-toggle="tab" href="#mybets" role="tab" aria-controls="mybets" aria-selected="false">Minhas apostas</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="results-tab" data-bs-toggle="tab" href="#results" role="tab" aria-controls="results" aria-selected="false">Resultados</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="winners-tab" data-bs-toggle="tab" href="#winners" role="tab" aria-controls="winners" aria-selected="false">Ganhadores</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="prizes-tab" data-bs-toggle="tab" href="#prizes" role="tab" aria-controls="prizes" aria-selected="false">Prêmios</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="rules-tab" data-bs-toggle="tab" href="#rules" role="tab" aria-controls="rules" aria-selected="false">Regras</a>
    </li>
  </ul>

  <div class="tab-content mt-4" id="gameTabsContent">
    <!-- Aba Detalhes -->
    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
      <div class="card shadow-sm mb-4">
        @if (auth()->user()->role->level_id == 'admin')
        <a class="dropdown-item" href="/concursos/edit/{{ $game->id }}"><i class="bx bx-edit-alt me-1"></i> Editar</a>
        @endif
        <div class="card-body">
          <p><strong>Nome:</strong> {{ $game->name }}</p>
          <p><strong>Status:</strong>
            <span class="badge bg-label-primary">{{ __($game->status) }}</span>
          </p>
          <p><strong>Preço:</strong> R$ {{ number_format($game->price, 2, ',', '.') }}</p>
          <p><strong>Aberto em:</strong> {{ date("d/m/Y", strtotime($game->open_at)) }}</p>
          <p><strong>Fecha em:</strong> {{ date("d/m/Y", strtotime($game->close_at)) }}</p>
          <p><strong>Ativo:</strong> {{ $game->active ? 'Sim' : 'Não' }}</p>
        </div>
      </div>
    </div>

    <!-- Aba Formulário de Aposta com grade interativa -->
    <div class="tab-pane fade" id="bet-form" role="tabpanel" aria-labelledby="bet-form-tab">
      @if($game->status == "OPENED")
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form id="bet_form" action="{{ route('purchase-store', $game->id) }}" method="POST">
            @csrf
            <div class="form-group">
              <label for="gambler_name">Nome do Apostador</label>
              <input type="text" class="form-control" id="gambler_name" name="gambler_name" placeholder="Digite seu nome" required>
            </div>

            <div class="form-group">
              <label for="gambler_phone">Telefone do Apostador</label>
              <input type="text" class="form-control" id="gambler_phone" name="gambler_phone" placeholder="Digite seu telefone">
            </div>

            <!-- Grade de seleção de números -->
            <div class="card">
              <div class="card-body">
                <div class="form-group">
                  <label>Escolha suas dezenas (máximo de 11)</label>
                  <div class="number-grid mb-3 row row-cols-4 gx-1 gy-1">
                    @for ($i = 0; $i <= 99; $i++)
                      <div class="col">
                      <button type="button" class="btn btn-outline-primary w-100 number-button btn-sm" data-number="{{ $i }}">{{ $i }}</button>
                  </div>
                  @endfor
                </div>
                <small class="form-text text-muted">Selecione até 11 números. Clique novamente em um número para desmarcá-lo.</small>
                <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
              </div>
            </div>
        </div>


        <input type="hidden" name="user_id" value="{{auth()->user()->id}}">
        <input type="hidden" name="game_id" value=" {{$game->id}}">

        <!-- Campo oculto para armazenar os números selecionados -->
        <input type="hidden" id="numbers" name="numbers" value="">

        <button type="submit" class="btn btn-primary">Comprar Dezenas</button>
        </form>
      </div>
    </div>
    @else
    <p class="text-muted">O jogo já está encerrado.</p>
    @endif
  </div>

  <!-- Aba Resultados -->
  <div class="tab-pane fade" id="results" role="tabpanel" aria-labelledby="results-tab">
    <h3 class="mb-4">Histórico de Resultados</h3>

    @if (auth()->user()->role->level_id == 'admin')
    <form id="result_form" action="{{ route('add-game-history', $game->id) }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="description">Descrição para os novos números</label>
        <input type="text" class="form-control" id="description" name="description" placeholder="Descrição para novo resultado">
      </div>
      <div class="form-group">
        <label for="result_numbers">Escolha suas dezenas (11 números por vez, separados por espaços)</label>
        <input type="text" class="form-control" id="result_numbers" name="result_numbers" placeholder="Exemplo: 1111 2211 1211 3211 1211 4311 1211 5411 6511 2311 1211" required>
        <small class="form-text text-muted">As dezenas devem ter dois dígitos cada e serem separadas por espaços, em grupos de 11 números.</small>
        <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
      </div>

      <input type="hidden" name="game_id" value=" {{$game->id}}">

      <button type="submit" class="btn btn-primary">Adicionar novo resultado</button>
    </form>
    @endif

    <div class="card shadow-sm h-100">
      <div class="card-body">
        @if($histories->isEmpty())
        <p class="text-muted">Não há registros de resultados para este jogo.</p>
        @else
        <div class="row">
          @foreach($histories as $history)
          <div class="col-md-4 mb-4">
            <div class="card shadow-lg h-100">
              <div class="card-body">
                <h3 class="card-title">{{ $history->description }}</h3>
                <h5 class="card-text">{{ $history->created_at->format('l, d/m/Y') }}</h5>
                @foreach(explode(" ", $history->result_numbers) as $key => $result_number)
                <h5 class="card-text">{{$key+1}}º: <strong>{{ $result_number }}</strong> => {{explode(" ", $history->numbers)[$key]}}</h5>
                @endforeach
                <p class="card-text"><small class="text-muted">Cadastrado: {{ $history->created_at->format('d/m/Y H:i') }}</small></p>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @endif
      </div>
    </div>
  </div>


  <div class="tab-pane fade" id="prizes" role="tabpanel" aria-labelledby="prizes-tab">
    <h3 class="mb-0">Prêmios Disponíveis</h3>
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        @if($game->awards->isEmpty())
        <p class="text-muted">Nenhum prêmio foi configurado para este jogo.</p>
        @else
        <ul class="list-group list-group-flush">
          @foreach($game->awards as $award)
          <li class="list-group-item">
            <h4>{{ $award->name }}</h4>
            @if($award->condition_type === 'EXACT_POINT')
            Quem fizer <strong> {{ $award->exact_point_value }} </strong> pontos ganha<br>
            @endif
            @if($award->condition_type === 'WINNER')
            Quem fizer <strong> {{ $award->winner_point_value }} </strong> pontos vence o torneio em primeiro<br>
            @endif
            <strong>Valor do prêmio:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }} <br>
          </li>
          @endforeach
        </ul>
        @endif
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="rules" role="tabpanel" aria-labelledby="rules-tab">
    <p>Conteúdo das regras...</p>
  </div>

  <!-- Aba Meus jogos -->
  <div class="tab-pane fade" id="mybets" role="tabpanel" aria-labelledby="mybets-tab">
    @if($purchases->isEmpty())
    <p class="text-muted">Você ainda não realizou nenhuma aposta para este jogo.</p>
    @else
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Nome do Apostador</th>
            <th>Telefone</th>
            <th>Números</th>
            <th>Quantidade</th>
            <th>Preço</th>
            <th>Status</th>
            <th>Data da Aposta</th>
          </tr>
        </thead>
        <tbody>
          @foreach($purchases as $purchase)
          <tr>
            <td>{{ $purchase->gambler_name }}</td>
            <td>{{ $purchase->gambler_phone }}</td>
            <td>{{ $purchase->numbers }}</td>
            <td>{{ $purchase->quantity }}</td>
            <td>R$ {{ number_format($purchase->price, 2, ',', '.') }}</td>
            <td><span class="badge bg-label-primary">{{ __($purchase->status) }}</span></td>
            <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>


<div class="tab-pane fade" id="winners" role="tabpanel" aria-labelledby="winners-tab">
  @if($winners->isEmpty())
  <p class="text-muted">Não há ganhadores ainda.</p>
  @else
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Nome do Apostador</th>
          <th>Números</th>
          <th>Quantidade</th>
          <th>Vlr. Aposta</th>
          <th>Prêmio</th>
          <th>Data da Aposta</th>
        </tr>
      </thead>
      <tbody>
        @foreach($winners as $winner)
        <tr>
          <td>{{ $winner->purchase->gambler_name }}</td>
          <td>{{ $winner->purchase->numbers }}</td>
          <td>{{ $winner->purchase->quantity }}</td>
          <td>R$ {{ number_format($winner->purchase->price, 2, ',', '.') }}</td>
          <td>R$ {{ number_format($winner->game_award->amount, 2, ',', '.') }}</td>
          <td>{{ $winner->purchase->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
</div>

<script>
  if (document.getElementById('result_numbers')) {
    document.getElementById('result_numbers').addEventListener('input', function(e) {
      let input = e.target.value.replace(/[^\d]/g, ''); // Remove caracteres não numéricos
      let formatted = input.match(/.{1,4}/g) || []; // Divide em pares de dígitos

      // Formata para grupos de 5 dezenas, separados por espaços
      let output = [];
      for (let i = 0; i < formatted.length; i++) {
        output.push(formatted[i]);
        // Adiciona um espaço se não for o último número do grupo de 11
        if ((i + 1) % 5 !== 0 && i !== formatted.length - 1) {
          output.push(' ');
        }
        // Adiciona uma vírgula se for o final de um grupo de 5, mas não o último número
        if ((i + 1) % 5 === 0 && i !== formatted.length - 1) {
          output.push(', ');
        }
      }

      // Atualiza o valor do campo com o formato desejado
      e.target.value = output.join('');
    });
  }

  document.getElementById('bet_form').addEventListener('submit', function(e) {
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


  const selectedNumbers = [];
  const selectedResultNumbers = [];
  const maxNumbers = 11;

  document.querySelectorAll('.number-button').forEach(button => {
    button.addEventListener('click', function() {
      const number = this.getAttribute('data-number');

      if (selectedNumbers.includes(number)) {

        console.log(number)
        selectedNumbers.splice(selectedNumbers.indexOf(number), 1);
        this.classList.remove('active');
      } else {
        if (selectedNumbers.length < maxNumbers) {
          selectedNumbers.push(number);
          this.classList.add('active');
        } else {
          document.getElementById('error-message').textContent = 'Você só pode selecionar até 11 números.';
          document.getElementById('error-message').style.display = 'block';
          return;
        }
      }

      document.getElementById('numbers').value = selectedNumbers.join(' ');

      if (selectedNumbers.length <= maxNumbers) {
        document.getElementById('error-message').style.display = 'none';
      }
    });
  });

  document.querySelectorAll('.result_number-button').forEach(button => {
    button.addEventListener('click', function() {
      const number = this.getAttribute('data-number');

      if (selectedResultNumbers.includes(number)) {
        selectedResultNumbers.splice(selectedResultNumbers.indexOf(number), 1);
        this.classList.remove('active');
      } else {

        /*
        if (selectedResultNumbers.length < maxNumbers) {
          selectedResultNumbers.push(number);
          this.classList.add('active');
        } else {
          document.getElementById('error-message').textContent = 'Você só pode selecionar até 11 números.';
          document.getElementById('error-message').style.display = 'block';
          return;
        }
        */
        selectedResultNumbers.push(number);
        this.classList.add('active');
      }

      if (document.getElementById('result_numbers')) {
        document.getElementById('result_numbers').value = selectedResultNumbers.join(' ');
      }

      /*
      if (selectedResultNumbers.length <= maxNumbers) {
        document.getElementById('error-message').style.display = 'none';
      }
      */
    });
  });

  document.getElementById('bet-form').addEventListener('submit', function(e) {
    if (selectedNumbers.length !== maxNumbers) {
      e.preventDefault();
      document.getElementById('error-message').textContent = 'Você deve selecionar exatamente 11 números.';
      document.getElementById('error-message').style.display = 'block';
    }
  });

  if (document.getElementById('result_numbers')) {
    document.getElementById('result_form').addEventListener('submit', function(e) {
      /*
      if (selectedResultNumbers.length !== maxNumbers) {
        e.preventDefault();
        document.getElementById('error-message').textContent = 'Você deve selecionar exatamente 11 números.';
        document.getElementById('error-message').style.display = 'block';
      }
      */
    });
  }
</script>

<style>
  .number-grid {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 5px;
  }

  .number-button {
    width: 100%;
    text-align: center;
  }

  .number-button.active {
    background-color: #007bff;
    color: white;
  }
</style>

@endsection