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
  <?php
  $tab = session('tab') ?? 'tab-details';
  ?>
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
      <a class="nav-link {{($tab == 'tab-details') ? 'active' : ''}}" id="details-tab" data-bs-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="{{$tab == 'tab-details'}}">Detalhes</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{($tab == 'tab-bet') ? 'active' : ''}}" id="bet-form-tab" data-bs-toggle="tab" href="#bet-form" role="tab" aria-controls="bet-form" aria-selected="{{$tab == 'tab-bet'}}">Apostar</a>
    </li>
    <!-- Demais abas não alteradas -->
    <li class="nav-item" role="presentation">
      <a class="nav-link {{($tab == 'tab-mybets') ? 'active' : ''}}" id="mybets-tab" data-bs-toggle="tab" href="#mybets" role="tab" aria-controls="mybets" aria-selected="{{$tab == 'tab-mybets'}}">Minhas apostas</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{($tab == 'tab-results') ? 'active' : ''}}" id="results-tab" data-bs-toggle="tab" href="#results" role="tab" aria-controls="results" aria-selected="{{$tab == 'tab-results'}}">Resultados</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{($tab == 'tab-winners') ? 'active' : ''}}" id="winners-tab" data-bs-toggle="tab" href="#winners" role="tab" aria-controls="winners" aria-selected="{{$tab == 'tab-winners'}}">Ganhadores</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{($tab == 'tab-prizes') ? 'active' : ''}}" id="prizes-tab" data-bs-toggle="tab" href="#prizes" role="tab" aria-controls="prizes" aria-selected="{{$tab == 'tab-prizes'}}">Prêmios</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{($tab == 'tab-rules') ? 'active' : ''}}" id="rules-tab" data-bs-toggle="tab" href="#rules" role="tab" aria-controls="rules" aria-selected="{{$tab == 'tab-rules'}}">Regras</a>
    </li>
  </ul>

  <div class="tab-content mt-4" id="gameTabsContent">
    <!-- Aba Detalhes -->
    <div class="tab-pane fade {{($tab == 'tab-details') ? 'show active' : ''}}" id="details" role="tabpanel" aria-labelledby="details-tab">
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
    <div class="tab-pane fade {{($tab == 'tab-bet') ? 'show active' : ''}}" id="bet-form" role="tabpanel" aria-labelledby="bet-form-tab">
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

            <div class="form-group">
              <label>Método de Seleção</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="selection_method" id="use_grid" value="grid" checked>
                <label class="form-check-label" for="use_grid">Usar grade interativa</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="selection_method" id="use_text" value="text">
                <label class="form-check-label" for="use_text">Inserir dezenas manualmente</label>
              </div>
            </div>

            <!-- Campo de texto (inicialmente oculto) -->
            <div class="form-group" id="text_input_container" style="display: none;">
              <div id="text_input_container">
                <div class="form-group">
                  <label for="manual_numbers">Digite suas dezenas (máximo de 11 números, ex: 11 22 33 44):</label>
                  <input type="text"
                    inputmode="numeric"
                    class="form-control"
                    id="manual_numbers"
                    placeholder="Ex: 11 22 33 10 99"
                    maxlength="32">
                  <small id="error-message" class="text-danger" style="display: none;"></small>
                </div>
              </div>
              <small class="form-text text-muted">Insira até 11 dezenas separadas por espaço.</small>
            </div>

            <!-- Grade de seleção de números -->
            <div class="card" id="grid_input_container">
              <div class="card-body">
                <label>Escolha suas dezenas (máximo de 11)</label>
                <div class="number-grid mb-3 row row-cols-5 row-cols-sm-6 row-cols-md-7 row-cols-lg-10 gx-1 gy-1">
                  @for ($i = 0; $i <= 99; $i++)
                    <div class="col">
                    <button type="button" class="btn btn-outline-primary w-100 number-button btn-sm" data-number="{{ $i }}">
                      {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                    </button>
                </div>
                @endfor
              </div>
              <small class="form-text text-muted">Selecione até 11 números. Clique novamente em um número para desmarcá-lo.</small>
              <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
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
  <div class="tab-pane fade {{($tab == 'tab-results') ? 'show active' : ''}}" id="results" role="tabpanel" aria-labelledby="results-tab">
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
              @if (auth()->user()->role->level_id == 'admin')
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="/concursos/resultados/historico/edit/{{ $history->id }}"><i class="bx bx-edit-alt me-1"></i> Editar</a>
                  <a class="dropdown-item" href="/concursos/resultados/historico/remove/{{ $history->id }}"><i class="bx bx-x me-1"></i> Remover</a>
                </div>
              </div>
              @endif
              <div class="card-body">
                <h3 class="card-title">{{ $history->description }}</h3>
                <h5 class="card-text">{{ ucfirst(\Carbon\Carbon::parse($history->created_at)->translatedFormat('l, d/m/Y')) }}</h5>
                <h5 class="card-text"><strong>{{ $history->result_numbers }}</strong></h5>
                <p class="card-text"><small class="text-muted">Cadastrado: {{ $history->created_at->format('d/m/Y H:i') }}</small></p>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        <!-- Controles de paginação -->
        <div class="d-flex justify-content-center mt-4">
          {{ $histories->links('pagination::bootstrap-5') }}
        </div>
        @endif
      </div>
    </div>
  </div>


  <div class="tab-pane fade {{($tab == 'tab-prizes') ? 'show active' : ''}}" id="prizes" role="tabpanel" aria-labelledby="prizes-tab">
    <h3 class="mb-0">Prêmios Disponíveis</h3>

    @if (auth()->user()->role->level_id == 'admin')
    <a href="{{route('create-game-award-form', $game->id)}}" class="btn btn-primary">Criar novo prêmio</a>
    @endif
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        @if($game->awards->isEmpty())
        <p class="text-muted">Nenhum prêmio foi configurado para este jogo.</p>
        @else
        <ul class="list-group list-group-flush">
          @foreach($game->awards as $award)
          <li class="list-group-item">

            @if (auth()->user()->role->level_id == 'admin')
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="/concursos/premios/edit/{{ $award->id }}"><i class="bx bx-edit-alt me-1"></i> Editar</a>
                <a class="dropdown-item" href="/concursos/premios/remove/{{ $award->id }}"><i class="bx bx-x me-1"></i> Remover</a>

              </div>
            </div>
            @endif

            <h4>{{ $award->name }}</h4>
            @if($award->condition_type === 'EXACT_POINT')
            Quem fizer <strong> {{ $award->exact_point_value }} </strong> pontos ganha<br>
            @endif
            @if($award->condition_type === 'WINNER')
            Quem fizer <strong> {{ $award->winner_point_value }} </strong> pontos vence o torneio em primeiro<br>
            @endif
            @if($award->condition_type === 'LOWEST_POINT')
            Quem fizer <strong> menos </strong> pontos ganha<br>
            @endif
            <strong>Valor do prêmio:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }} <br>
          </li>
          @endforeach
        </ul>
        @endif
      </div>
    </div>
  </div>

  <div class="tab-pane fade {{($tab == 'tab-rules') ? 'show active' : ''}}" id="rules" role="tabpanel" aria-labelledby="rules-tab">
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h3 class="card-title fw-bold text-center mb-3">Regulamento</h3>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">1. Escolha suas dezenas de 0 a 99.</li>
          <li class="list-group-item">2. Bolão concorre pelas loterias PT, PTN e Federal.</li>
          <li class="list-group-item">3. Só valem as dezenas do 1º ao 5º, conforme mostrado no exemplo.</li>
          <li class="list-group-item">4. Quem acertar as 11 dezenas primeiro ganha o bolão.</li>
          <li class="list-group-item">5. Segundo lugar é quem acertar 10 dezenas.</li>
          <li class="list-group-item">6. Não havendo ninguém com 10 dezenas, o segundo lugar será para quem estiver na sequência.</li>
          <li class="list-group-item">7. "Pé frio" será quem obtiver a menor pontuação.</li>
          <li class="list-group-item">8. Premiação para quem acertar da 1ª à 10ª dezena.</li>
          <li class="list-group-item">9. Em caso de empate nos prêmios, o valor será dividido entre os ganhadores.</li>
          <li class="list-group-item">10. Faça seu jogo em local de sua confiança. Prêmios pagos diretamente no local.</li>
          <li class="list-group-item">11. Prêmios pagos em até 5 dias úteis.</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Aba Meus jogos -->
  <div class="tab-pane fade {{($tab == 'tab-mybets') ? 'show active' : ''}}" id="mybets" role="tabpanel" aria-labelledby="mybets-tab">
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
            <!-- <th>Quantidade</th> -->
            <th>Preço</th>
            <th>Status</th>
            <th>Data da Aposta</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          @foreach($purchases as $purchase)
          <tr>
            <td>{{ $purchase->gambler_name }}</td>
            <td>{{ $purchase->gambler_phone }}</td>
            <td>{{ $purchase->numbers }}</td>
            <!-- <td>{{ $purchase->quantity }}</td> -->
            <td>R$ {{ number_format($purchase->price, 2, ',', '.') }}</td>
            <td><span class="badge bg-label-primary">{{ __($purchase->status) }}</span></td>
            <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
            <td>
              @if ($purchase->status == "PENDING")
              <a href="{{route('purchase-pay', $purchase->id)}}" class="btn btn-success">Pagar</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>


<div class="tab-pane fade {{($tab == 'tab-winners') ? 'show active' : ''}}" id="winners" role="tabpanel" aria-labelledby="winners-tab">
  @if(!count($winners))
  <p class="text-muted">Não há ganhadores ainda.</p>
  @else
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Nome do Apostador</th>
          <th>Prêmio</th>
          <th>Vlr. Prêmio</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach($winners as $index => $winner)
        <tr>
          <td>{{ $winner->user->name }}</td>
          <td>{{ $winner->game_award->name }}</td>
          <td>R$ {{ number_format($winner->game_award->amount, 2, ',', '.') }}</td>
          <td><span class="badge bg-label-primary me-1">{{ __($winner->status) }}</span></td>
          <td>
            <button class="btn btn-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#details-{{ $index }}" aria-expanded="false" aria-controls="details-{{ $index }}">
              Detalhes
            </button>
            @if (auth()->user()->role->level_id == 'admin')
            <a href="{{ route('user_award-pay', $winner->id) }}" class="btn btn-success {{$winner->status == "PAID" ? "disabled" : ""}}">Pagar</a>
            @endif
            @if (auth()->user()->role->level_id == 'admin' && ($winner->status == "PAID"))
            <a href="{{ route('user_award-withdraw', $winner->id) }}" class="btn btn-info {{$winner->status !== "PAID" ? "disabled" : ""}}">Estornar</a>
            @endif
          </td>
        </tr>
        <tr class="collapse" id="details-{{ $index }}">
          <td colspan="3">
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>Números</th>
                  <th>Quantidade</th>
                  <th>Vlr. Aposta</th>
                  <th>Data da Aposta</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($winner->purchases as $purchase)
                <tr>
                  <td>{{ $purchase->numbers }}</td>
                  <td>{{ $purchase->quantity }}</td>
                  <td>R$ {{ number_format($purchase->price, 2, ',', '.') }}</td>
                  <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
            <!-- Controles de paginação -->
            <div class="d-flex justify-content-center mt-4">
              {{ $user_awards->links('pagination::bootstrap-5') }}
            </div>
          </td>
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
      let formatted = input.match(/.{1,2}/g) || []; // Divide em pares de dígitos

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


  document.addEventListener('DOMContentLoaded', function() {
    const maxNumbers = 11;
    const selectedNumbers = [];
    const gridContainer = document.getElementById('grid_input_container');
    const textContainer = document.getElementById('text_input_container');
    const manualInput = document.getElementById('manual_numbers');
    const errorMessage = document.getElementById('error-message');
    const hiddenField = document.getElementById('numbers'); // Campo oculto para envio


    // Alternar entre grade e campo de texto
    document.querySelectorAll('input[name="selection_method"]').forEach(input => {
      input.addEventListener('change', function() {
        if (this.id === 'use_grid') {
          gridContainer.style.display = 'block';
          textContainer.style.display = 'none';
          manualInput.value = '';
        } else {
          gridContainer.style.display = 'none';
          textContainer.style.display = 'block';
          selectedNumbers.length = 0;
          document.getElementById('numbers').value = '';
          document.querySelectorAll('.number-button').forEach(button => button.classList.remove('active'));
        }
      });
    });



    manualInput.addEventListener('input', function() {

      // Remove todos os caracteres que não sejam números
      let rawValue = this.value.replace(/[^0-9]/g, "");

      // Divide os números em partes de dois dígitos
      let parts = rawValue.match(/.{1,2}/g) || []; // Garante que não seja null

      let uniqueParts = [...new Set(parts)];

      // Limita o número máximo de partes permitidas
      if (uniqueParts.length > maxNumbers) {
        parts = parts.slice(0, maxNumbers);
        errorMessage.textContent = `Você pode selecionar no máximo ${maxNumbers} números.`;
        errorMessage.style.display = 'block';
      } else {
        errorMessage.style.display = 'none';
      }

      // Recria o valor formatado com espaços entre os pares
      let displayValue = uniqueParts.join(" ");

      // Atualiza o valor do campo de entrada e o campo oculto
      this.value = displayValue;
      hiddenField.value = displayValue;
    });



    // Validar o formulário antes de enviar
    document.getElementById('bet_form').addEventListener('submit', function(e) {
      const isGridSelected = document.getElementById('use_grid').checked;

      if (isGridSelected && selectedNumbers.length !== maxNumbers) {
        e.preventDefault();
        document.getElementById('error-message').textContent = 'Você deve selecionar exatamente 11 números.';
        document.getElementById('error-message').style.display = 'block';
      } else if (!isGridSelected) {


        const manualNumbers = manualInput.value.trim().split(/\s+/);

        // Validar o formato e quantidade de números no campo de texto
        if (manualNumbers.length !== maxNumbers) {
          e.preventDefault();
          document.getElementById('error-message').textContent = `Insira exatamente ${maxNumbers} dezenas válidas separadas por espaços.`;
          document.getElementById('error-message').style.display = 'block';
        } else {
          document.getElementById('error-message').style.display = 'none';
        }
      }
    });

    // Manipular a seleção na grade
    document.querySelectorAll('.number-button').forEach(button => {
      button.addEventListener('click', function() {
        const number = this.getAttribute('data-number');

        if (selectedNumbers.includes(number)) {
          selectedNumbers.splice(selectedNumbers.indexOf(number), 1);
          this.classList.remove('active');
        } else {
          if (selectedNumbers.length < maxNumbers) {
            selectedNumbers.push(number);
            this.classList.add('active');
          } else {
            document.getElementById('error-message').textContent = `Você só pode selecionar até ${maxNumbers} números.`;
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
    grid-template-columns: repeat(auto-fill, minmax(20px, 1fr));
    /* Ajusta as colunas automaticamente */
    gap: 4px;
  }

  .number-button {
    width: 100%;
    text-align: center;
    padding: 10px;
    /* Ajusta o tamanho do botão para ficar proporcional */
    font-size: 12px;
    /* Ajusta o tamanho da fonte para caber nos botões */
  }

  .number-button.active {
    background-color: #007bff;
    color: white;
  }
</style>

@endsection