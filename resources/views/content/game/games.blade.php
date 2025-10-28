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

        <!-- Estilo do botão piscando e cards -->
        <style>
            .btn-blink {
                background-color: #00FF00 !important;
                color: #000000 !important;
                box-shadow: 0 0 10px rgba(33, 33, 33, 0.5);
                border: none !important;
                animation: blink 1.5s infinite;
                transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
                width: 100%;
            }

            .btn-blink:hover,
            .btn-blink:focus,
            .btn-blink:active {
                background-color: darkred !important;
                color: white !important;
                box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
                transform: scale(1.02);
                text-decoration: none;
            }

            @keyframes blink {
                0%, 50%, 100% { opacity: 1; }
                25%, 75% { opacity: 0.5; }
            }

            /* Estilo para cards mais sólidos */
            .col-md-4 > .card {
                border: 2px solid #dee2e6 !important;
                border-radius: 8px !important;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.1) !important;
                transition: box-shadow 0.3s ease, border-color 0.3s ease;
                background-color: #ffffff !important;
            }

            .col-md-4 > .card:hover {
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2), 0 4px 8px rgba(0, 0, 0, 0.15) !important;
                border-color: #adb5bd !important;
            }

            .col-md-4 > .card .card-header {
                border-bottom: 2px solid #e9ecef !important;
                background-color: #f8f9fa !important;
                font-weight: 600;
            }

            /* Alerta de início amanhã */
            .start-tomorrow-alert {
                color: red !important;
                font-weight: bold;
                animation: redBlink 1s infinite;
                font-size: 0.9rem;
            }

            @keyframes redBlink {
                0%, 50%, 100% { opacity: 1; }
                25%, 75% { opacity: 0.4; }
            }
        </style>

        <form action="{{ url('/concursos') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-2">
                    @if (auth()->user()->role->level_id == 'admin')
                        <a class="btn btn-secondary" href="/concursos/create_game_form">Criar novo jogo</a>
                    @endif
                </div>
                <div class="col-md-4">
                    <!-- Campo de pesquisa -->
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                            placeholder="Pesquisar por nome do concurso, numeros..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Select de filtro por role -->
                    <select name="status" class="form-select">
                        <option value="">Todos os status</option>
                        @foreach (['OPENED', 'CLOSED', 'FINISHED'] as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ __($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" type="submit">Aplicar Filtros</button>
                </div>
            </div>
        </form>
        <!-- Lista de Jogos -->
        <div class="card shadow-lg p-3 mb-5 bg-white rounded">
            <h5 class="card-header">Jogos</h5>
            <div class="row mt-3">
                <!-- Iterando sobre os jogos para exibir como cards -->
                @if (!count($games))
                    <p class="text-muted">Não há concursos disponíveis.</p>
                @endif

                @foreach ($games as $game)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <a href='/concursos/{{ $game->id }}'>
                                    <i class="menu-icon tf-icons bx bxs-barcode"></i>
                                    {{ $game->name }}
                                </a>
                                @if (auth()->user()->role->level_id == 'admin')
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="/concursos/edit/{{ $game->id }}"><i
                                                    class="bx bx-edit-alt me-1"></i> Editar</a>
                                            <a class="dropdown-item" href="/concursos/generate_pdf/{{ $game->id }}"><i
                                                    class="bx bx-export me-1"></i> Gerar PDF</a>
                                            <a class="dropdown-item" href="/concursos/generate_csv/{{ $game->id }}"><i
                                                    class="bx bx-list-check me-1"></i> Gerar CSV</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body" onclick="window.location = '/concursos/{{ $game->id }}'">
                                @php
                                    $openAt = \Carbon\Carbon::parse($game->open_at);
                                    $now = \Carbon\Carbon::now();
                                    $hoursUntilStart = $now->diffInHours($openAt, false);
                                @endphp
                                

                                
                                <p class="card-text"><strong>Aberto em:</strong>
                                    {{ date('d/m/Y', strtotime($game->open_at)) }}</p>
                                <p class="card-text"><strong>Fecha em:</strong>
                                    {{ date('d/m/Y', strtotime($game->close_at)) }}</p>
                                <p class="card-text"><strong>Tempo Restante:</strong> <span
                                        id="countdown-{{ $game->id }}"></span></p>
                                <p class="card-text"><strong>Preço:</strong> R$
                                    {{ number_format($game->price, 2, ',', '.') }} </p>

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

                                                                @if($hoursUntilStart > 0 && $hoursUntilStart <= 24)
                                    <p class="card-text start-tomorrow-alert">
                                        <i class="bx bx-bell-ring me-1"></i>Início amanhã!
                                    </p>
                                @endif
                            </div>

                            <div class="card-footer" style="padding: 0;">
                                @if($game->status == 'OPENED')
                                <div style="padding: 10px;" > 
                                    <a href="/concursos/{{ $game->id }}" class="btn btn-sm btn-blink" style="width: 100%;">
                                        <i class="bx bx-plus-circle me-1"></i><b>FAÇA SEU JOGO</b>
                                    </a>
                                </div>
                                @endif
                                @if($game->status == 'CLOSED')
                                <div style="padding: 10px;" > 
                                    <a href="/concursos/{{ $game->id }}" class="btn btn-sm btn-blink" style="width: 100%;">
                                        <i class="bx bx-plus-circle me-1"></i><b>Aguardando Sorteio</b>
                                    </a>
                                </div>
                                @endif
                                <div style="background-color: #e9ecef; padding: 8px; margin-top: 0;">
                                    @if (!$game->awards->isEmpty())
                                        @foreach ($game->awards as $award)
                                            @if ($award->condition_type === 'WINNER')
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                    <strong>Prêmio {{ $award->name }}:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }}
                                                </small>
                                            @endif
                                        @endforeach
                                    @else
                                        <small class="badge bg-label-warning" style="font-size: 0.65rem;">Cadastrando prêmios</small>
                                    @endif
                                </div>
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

                                let days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
                                let hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                let minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                                let seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);

                                let text = "";
                                if (days > 0) {
                                    text += days + "d ";
                                }
                                if (hours > 0) {
                                    text += hours + "h ";
                                }
                                if (minutes > 0) {
                                    text += minutes + "m ";
                                }
                                text += seconds + "s";

                                if (days === 0 && hours === 0 && minutes === 0) {
                                    text = seconds + "s";
                                }

                                document.getElementById(elementId).innerText = text;
                            }

                            updateCountdown();
                            setInterval(updateCountdown, 1000);
                        }

                        document.addEventListener("DOMContentLoaded", function() {
                            startCountdown("countdown-{{ $game->id }}", "{{ $game->close_at }}");
                        });
                    </script>
                @endforeach
            </div>
            <!-- Controles de paginação -->
            <div class="d-flex justify-content-center mt-4">
                {{ $games->appends(request()->all())->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
@endsection
