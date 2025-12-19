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
            const loadOnClickButtons = document.querySelectorAll('.btn-loadonclick');

            loadOnClickButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    // Verifica se o botão já está desabilitado pela lógica do Blade
                    if (this.classList.contains('disabled')) {
                        event.preventDefault(); // Impede a navegação se já estiver desabilitado
                        return;
                    }

                    // Desabilita o botão imediatamente para evitar clique duplo
                    this.classList.add('disabled');
                    this.textContent = 'Processando...';

                    // Para botões de submit de formulário, o navegador continuará com o submit normalmente.
                    // Para links, o navegador continuará com a navegação normalmente.
                    // Não precisamos de setTimeout ou reativar o botão, pois a página vai recarregar.
                });
            });
        });
    </script>

@endsection
