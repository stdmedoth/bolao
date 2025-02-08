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

<div class="contatiner">
  <h1 class="my-4">Concursos</h1>


  @if (auth()->user()->role->level_id == 'admin')
  <a class="btn btn-secondary" href="/concursos/create_game_form">Criar novo jogo</a>
  @endif

  <!-- Lista de Jogos -->
  <div class="card shadow-lg p-3 mb-5 bg-white rounded">
    <h5 class="card-header">Jogos</h5>
    <div class="row mt-3">
      <!-- Iterando sobre os jogos para exibir como cards -->
      @if(!count($games))
      <p class="text-muted">Não há concursos disponíveis.</p>
      @endif

      @foreach ($games as $game)
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <a href='/concursos/{{$game->id}}'>
              <i class="menu-icon tf-icons bx bxs-barcode"></i>
              {{ $game->name }}
            </a>
            @if (auth()->user()->role->level_id == 'admin')
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="/concursos/edit/{{ $game->id }}"><i class="bx bx-edit-alt me-1"></i> Editar</a>
              </div>
            </div>
            @endif
          </div>
          <div class="card-body" onclick="window.location = '/concursos/{{$game->id}}'">
            <p class="card-text"><strong>Aberto em:</strong> {{ date('d/m/Y', strtotime($game->open_at)) }}</p>
            <p class="card-text"><strong>Fecha em:</strong> {{ date('d/m/Y', strtotime($game->close_at)) }}</p>
            <p class="card-text"><strong>Tempo Restante:</strong> <span id="countdown-{{$game->id}}"></span></p>
            <p class="card-text"><strong>Preço:</strong> R$ {{ number_format($game->price, 2, ',', '.') }} </p>
            @if(!$game->awards->isEmpty())
            @foreach($game->awards as $award)
            @if($award->condition_type === 'WINNER')
            <p class="card-text"><strong>Prêmio {{$award->name}}:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }} </p>
            @endif
            @endforeach
            @else
            <span class="badge bg-label-warning">Cadastrando prêmios</span>
            @endif

            <p class="card-text">
              @switch($game->status)
              @case('OPENED')
              <span class="badge bg-label-success">{{ __($game->status) }}</span>
              @break
              @case('CLOSED')
              <span class="badge bg-label-warning">{{ __($game->status) }}</span>
              @break
              @case('FINISHED')
              <span class="badge bg-label-secondary">{{ __($game->status) }}</span>
              @break
              @endswitch
            </p>
          </div>

          <div class="card-footer">

          </div>
        </div>
      </div>

      <script>
        function startCountdown(elementId, closeAt) {

          let dots = 0;

          function updateCountdown() {
            let now = new Date().getTime();
            let targetTime = new Date(closeAt).getTime();
            let timeDiff = targetTime - now;

            if (timeDiff <= 0) {
              let interval = setInterval(() => {
                dots = (dots % 3) + 1;
                document.getElementById(elementId).innerText = "Fechando" + ".".repeat(dots);
              }, 500);
              return;
            }

            let hours = Math.floor(timeDiff / (1000 * 60 * 60));
            let minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);

            let text = "";
            if (hours > 0) {
              text += hours + (hours === 1 ? " hora, " : " horas, ");
            }
            if (minutes > 0) {
              text += minutes + (minutes === 1 ? " minuto e " : " minutos e ");
            }
            text += seconds + (seconds === 1 ? " segundo" : " segundos");

            if (hours === 0 && minutes === 0) {
              text = "Menos de um minuto";
            }

            document.getElementById(elementId).innerText = text;
          }

          updateCountdown();
          setInterval(updateCountdown, 1000);
        }

        document.addEventListener("DOMContentLoaded", function() {
          startCountdown("countdown-{{$game->id}}", "{{ $game->close_at }}");
        });
      </script>

      @endforeach
    </div>

  </div>
</div>
@endsection