@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Minhas Compras')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
    <script src="{{ asset('js/game-tabs.js') }}"></script>
    <script src="{{ asset('js/my-bets-tab.js') }}"></script>
@endsection


@section('content')

    <style>
        /* Cor padrão para todas as abas (Inativas) */
        .nav-tabs .nav-link {
            color: var(--bs-secondary) !important;
        }

        /* Cor para a aba que o Bootstrap marcar como ativa */
        .nav-tabs .nav-link.active {
            color: var(--bs-primary) !important;
        }
    </style>

    <div class="container-fluid px-0">
        <?php $tab = session('tab') ?? (old('tab') ?? 'tab-classifications');
        ?>
        <div class="row px-2">
            <div class="col-12">
                <h1 class="my-2 my-md-3 h4 h3-md text-primary">
                    <i class="bx bx-trophy me-2"></i>{{ $game->name }}
                </h1>
            </div>
        </div>

        @if ($errors->has('error'))
            <div class="alert alert-danger mx-2 border-danger shadow-sm">
                <i class="bx bx-error-circle me-2"></i><strong>Erro:</strong> {{ $errors->first('error') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger mx-2 border-danger shadow-sm">
                <i class="bx bx-error-circle me-2"></i><strong>Erro:</strong> {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success mx-2 border-success shadow-sm">
                <i class="bx bx-check-circle me-2"></i><strong>Sucesso:</strong> {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                @include('content.game.components.tab-menu', ['tab' => $tab])
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="tab-content mt-2 mt-md-3 px-2" id="gameTabsContent">
                    {{-- @include('content.game.components.tab-details', ['tab' => $tab, 'game' => $game]) --}}
                    @php
                        $sellers = isset($users)
                            ? $users->filter(function ($user) {
                                return $user->role && $user->role->level_id === 'seller';
                            })
                            : collect([]);
                    @endphp
                    @include('content.game.components.tab-bet-form', [
                        'tab' => $tab,
                        'game' => $game,
                        'sellers' => $sellers,
                    ])
                    @include('content.game.components.tab-results', [
                        'tab' => $tab,
                        'game' => $game,
                        'histories' => $histories,
                    ])
                    @include('content.game.components.tab-my-bets', [
                        'tab' => $tab,
                        'purchases' => $purchases,
                        'users' => $users ?? [],
                    ])
                    @include('content.game.components.tab-classifications', [
                        'tab' => $tab,
                        'purchases' => $purchases,
                        'game' => $game,
                        'users' => $users ?? [],
                    ])
                    @include('content.game.components.tab-prizes', ['tab' => $tab, 'game' => $game])
                    @include('content.game.components.tab-rules', ['tab' => $tab])
                    @include('content.game.components.tab-winners', [
                        'tab' => $tab,
                        'winners' => $winners,
                        'user_awards' => $user_awards,
                        'game' => $game,
                        'users' => $users ?? [],
                        'participants' => $participants ?? collect(),
                    ])
                </div>
            </div>
        </div>
    </div>

    <script>
        window.backendSelectedTab = @json(session('tab'));

        // Script global para bloquear botões após submit
        document.addEventListener('DOMContentLoaded', function() {
            // Seleciona todos os botões com classe btn-loadonclick na página
            const loadOnClickButtons = document.querySelectorAll('#bet-form .btn-loadonclick');

            loadOnClickButtons.forEach(button => {
                let form = button.closest('form');
                if (form) {
                    // Registra se o botão já estava desabilitado no carregamento da página
                    const initiallyDisabled = button.classList.contains('disabled') || button.disabled;

                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            // Let the browser show the "Required" bubbles
                            return;
                        }

                        // Se o botão já vinha desabilitado quando a página carregou, Bloqueia submit
                        if (initiallyDisabled) {
                            event.preventDefault();
                            return;
                        }

                        // Adia a desabilitação para permitir que outros handlers de submit (validações
                        // específicas de formulário) rodem e possam cancelar o submit via
                        // event.preventDefault(). Se o submit for prevenido, não muda o botão.
                        setTimeout(() => {
                            try {
                                if (event.defaultPrevented) return;
                                // Marca botão como processando
                                button.classList.add('disabled');
                                try {
                                    button.textContent = 'Processando...';
                                } catch (e) {}

                                // Fallback: se após X segundos ainda estivermos na mesma página, reabilita botão
                                // para evitar ficar permanentemente travado quando o submit for interceptado.
                                const existingTimer = button.getAttribute(
                                    'data-processing-timer');
                                if (existingTimer) {
                                    try {
                                        clearTimeout(Number(existingTimer));
                                    } catch (e) {}
                                }
                                const t = setTimeout(() => {
                                    try {
                                        button.classList.remove('disabled');
                                        // Restaura texto apenas se estiver em Processando...
                                        if ((button.textContent || '').trim() ===
                                            'Processando...') {
                                            // tenta restaurar um label mais genérico
                                            // se o botão tem data-original-text, restaura
                                            const orig = button.getAttribute(
                                                'data-original-text');
                                            if (orig) button.textContent = orig;
                                            else button.textContent = 'Enviar';
                                        }
                                        button.removeAttribute(
                                            'data-processing-timer');
                                    } catch (e) {
                                        console.error(
                                            'Erro ao reabilitar botão de fallback:',
                                            e);
                                    }
                                }, 8000);
                                button.setAttribute('data-processing-timer', String(t));
                            } catch (err) {
                                console.error('Erro ao definir estado do botão:', err);
                            }
                        }, 0);
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Bloqueia o botão repeat_game_repeat_button_batch_id_tab para evitar múltiplos submits
            const repeatButton = document.getElementById('repeat_game_repeat_button_batch_id_tab');
            if (repeatButton) {
                let form = repeatButton.closest('form');
                if (form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            // Let the browser show the "Required" bubbles
                            return;
                        }
                        // Marca botão como processando
                        repeatButton.classList.add('disabled');
                        try {
                            repeatButton.textContent = 'Processando...';
                        } catch (e) {}
                    });
                }
            }

            // O mesmo para o repeat_game_repeat_button_id
            const repeatButton2 = document.getElementById('repeat_game_repeat_button_id');
            if (repeatButton2) {
                let form = repeatButton2.closest('form');
                if (form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            // Let the browser show the "Required" bubbles
                            return;
                        }
                        // Marca botão como processando
                        repeatButton2.classList.add('disabled');
                        try {
                            repeatButton2.textContent = 'Processando...';
                        } catch (e) {}
                    });
                }
            }
        });
    </script>

@endsection
