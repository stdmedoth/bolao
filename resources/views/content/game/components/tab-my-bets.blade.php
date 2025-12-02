<div class="tab-pane fade {{ $tab == 'tab-mybets' ? 'show active' : '' }}" id="mybets" role="tabpanel"
    aria-labelledby="mybets-tab">

    <style>
        /* Tabela responsiva - usar toda largura disponível */
        .table-my-bets {
            width: 100%;
            table-layout: fixed;
            font-size: 0.85rem;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Coluna de Checkbox - primeira coluna */
        .table-my-bets th:nth-child(1),
        .table-my-bets td:nth-child(1) {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            text-align: center;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna de Participantes (ID/Ticket + Apostador + Vendedor) - segunda coluna */
        .table-my-bets th:nth-child(2),
        .table-my-bets td:nth-child(2) {
            width: 180px;
            min-width: 180px;
            max-width: 180px;
            font-size: 0.85rem;
            vertical-align: middle;
            padding: 3px 4px;
        }

        /* Coluna de Pontos - terceira coluna */
        .table-my-bets th:nth-child(3),
        .table-my-bets td:nth-child(3) {
            width: 100px;
            min-width: 100px;
            max-width: 100px;
            text-align: center;
            font-size: 0.85rem;
            vertical-align: middle;
            padding: 3px 8px;
        }

        /* Coluna de Números - quarta coluna */
        .table-my-bets th:nth-child(4),
        .table-my-bets td:nth-child(4) {
            width: 240px;
            min-width: 240px;
            max-width: 240px;
            text-align: center;
            font-size: 0.75rem;
            vertical-align: middle;
            padding: 3px 6px;
        }

        /* Coluna de Status - quinta coluna */
        .table-my-bets th:nth-child(5),
        .table-my-bets td:nth-child(5) {
            width: 80px;
            min-width: 80px;
            max-width: 80px;
            text-align: center;
            font-size: 0.75rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna de Pago por - sexta coluna */
        .table-my-bets th:nth-child(6),
        .table-my-bets td:nth-child(6) {
            width: 100px;
            min-width: 100px;
            max-width: 100px;
            font-size: 0.75rem;
            vertical-align: middle;
            padding: 3px 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Coluna de Ações - sétima coluna */
        .table-my-bets th:nth-child(7),
        .table-my-bets td:nth-child(7) {
            width: 180px;
            min-width: 180px;
            max-width: 180px;
            font-size: 0.7rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna "Usuário" (penúltima coluna antes de "Compra em") */
        .table-my-bets th:nth-last-child(2),
        .table-my-bets td:nth-last-child(2) {
            width: 100px;
            min-width: 100px;
            max-width: 100px;
            font-size: 0.75rem;
            vertical-align: middle;
            padding: 3px 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Coluna "Compra em" - última coluna */
        .table-my-bets th:last-child,
        .table-my-bets td:last-child {
            width: 80px;
            min-width: 80px;
            max-width: 80px;
            font-size: 0.75rem;
            vertical-align: middle;
            padding: 3px 1px;
            white-space: nowrap;
        }

        /* Botões de ações compactos */
        .table-my-bets .btn-sm {
            padding: 2px 4px;
            font-size: 0.7rem;
            line-height: 1;
            border-radius: 2px;
            margin: 1px;
        }

        /* Container de números no estilo de classificações - sem quebra de linha */
        .my-bets-numbers-container {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 2px;
            max-width: 100%;
            justify-content: center;
            align-items: center;
            overflow: visible;
        }

        .my-bets-number-ball {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 100%;
            font-size: 0.65rem;
            font-weight: 600;
            border: 1px solid;
            margin: 0;
            flex-shrink: 0;
            flex-grow: 0;
        }

        .my-bets-number-ball.hit {
            background-color: #fbbf24;
            color: #1a365d;
            border-color: #f59e0b;
        }

        .my-bets-number-ball.miss {
            background-color: #e5e7eb;
            color: #6b7280;
            border-color: #d1d5db;
        }

        /* Estilo para participantes (similar a classificações) */
        .my-bets-participants {
            display: flex;
            flex-direction: column;
        }

        .my-bets-participants .participant-name {
            font-weight: bold;
            color: #3b82f6;
            font-size: 0.85rem;
        }

        .my-bets-participants .participant-info {
            font-size: 0.6rem;
            color: #6b7280;
            line-height: 1.2;
            margin-top: 2px;
        }

        /* Responsividade para mobile */
        @media (max-width: 768px) {
            .table-my-bets th:nth-child(1),
            .table-my-bets td:nth-child(1) {
                width: 35px;
                min-width: 35px;
                max-width: 35px;
            }
            
            /* Coluna de Participantes no tablet */
            .table-my-bets th:nth-child(2),
            .table-my-bets td:nth-child(2) {
                width: 140px;
                min-width: 140px;
                max-width: 140px;
                font-size: 0.75rem;
            }

            /* Coluna de Pontos no tablet */
            .table-my-bets th:nth-child(3),
            .table-my-bets td:nth-child(3) {
                width: 100px;
                min-width: 100px;
                max-width: 100px;
                font-size: 0.75rem;
                padding: 3px 6px;
            }

            /* Coluna de Números no tablet */
            .table-my-bets th:nth-child(4),
            .table-my-bets td:nth-child(4) {
                width: 200px;
                min-width: 200px;
                max-width: 200px;
                padding: 3px 4px;
            }

            /* Coluna de Status no tablet */
            .table-my-bets th:nth-child(5),
            .table-my-bets td:nth-child(5) {
                width: 70px;
                min-width: 70px;
                max-width: 70px;
                font-size: 0.7rem;
            }

            /* Coluna de Pago por no tablet */
            .table-my-bets th:nth-child(6),
            .table-my-bets td:nth-child(6) {
                width: 90px;
                min-width: 90px;
                max-width: 90px;
                font-size: 0.7rem;
            }

            /* Coluna de Ações no tablet */
            .table-my-bets th:nth-child(7),
            .table-my-bets td:nth-child(7) {
                width: 160px;
                min-width: 160px;
                max-width: 160px;
                font-size: 0.65rem;
            }

            .table-my-bets .btn-sm {
                padding: 1px 2px;
                font-size: 0.6rem;
            }

            .my-bets-number-ball {
                width: 16px;
                height: 16px;
                font-size: 0.55rem;
            }

            .my-bets-participants .participant-name {
                font-size: 0.75rem;
            }

            .my-bets-participants .participant-info {
                font-size: 0.55rem;
            }

            /* Coluna "Usuário" no tablet */
            .table-my-bets th:nth-last-child(2),
            .table-my-bets td:nth-last-child(2) {
                width: 90px;
                min-width: 90px;
                max-width: 90px;
                font-size: 0.7rem;
            }

            /* Coluna "Compra em" no tablet */
            .table-my-bets th:last-child,
            .table-my-bets td:last-child {
                width: 70px;
                min-width: 70px;
                max-width: 70px;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 576px) {
            .table-my-bets th:nth-child(1),
            .table-my-bets td:nth-child(1) {
                width: 30px;
                min-width: 30px;
                max-width: 30px;
            }
            
            /* Coluna de Participantes no mobile pequeno */
            .table-my-bets th:nth-child(2),
            .table-my-bets td:nth-child(2) {
                width: 120px;
                min-width: 120px;
                max-width: 120px;
                font-size: 0.7rem;
            }

            /* Coluna de Pontos no mobile pequeno */
            .table-my-bets th:nth-child(3),
            .table-my-bets td:nth-child(3) {
                width: 90px;
                min-width: 90px;
                max-width: 90px;
                font-size: 0.7rem;
                padding: 3px 4px;
            }

            /* Coluna de Números no mobile pequeno */
            .table-my-bets th:nth-child(4),
            .table-my-bets td:nth-child(4) {
                width: 190px;
                min-width: 190px;
                max-width: 190px;
                padding: 3px 4px;
            }

            /* Coluna de Status no mobile pequeno */
            .table-my-bets th:nth-child(5),
            .table-my-bets td:nth-child(5) {
                width: 60px;
                min-width: 60px;
                max-width: 60px;
                font-size: 0.65rem;
            }

            /* Coluna de Pago por no mobile pequeno */
            .table-my-bets th:nth-child(6),
            .table-my-bets td:nth-child(6) {
                width: 80px;
                min-width: 80px;
                max-width: 80px;
                font-size: 0.65rem;
            }

            /* Coluna de Ações no mobile pequeno */
            .table-my-bets th:nth-child(7),
            .table-my-bets td:nth-child(7) {
                width: 160px;
                min-width: 160px;
                max-width: 160px;
                font-size: 0.6rem;
            }
            
            .table-my-bets .btn-sm {
                padding: 1px 1px;
                font-size: 0.5rem;
            }

            .my-bets-number-ball {
                width: 14px;
                height: 14px;
                font-size: 0.5rem;
            }

            .my-bets-participants .participant-name {
                font-size: 0.7rem;
            }

            .my-bets-participants .participant-info {
                font-size: 0.5rem;
            }

            /* Coluna "Usuário" no mobile pequeno */
            .table-my-bets th:nth-last-child(2),
            .table-my-bets td:nth-last-child(2) {
                width: 80px;
                min-width: 80px;
                max-width: 80px;
                font-size: 0.65rem;
            }

            /* Coluna "Compra em" no mobile pequeno */
            .table-my-bets th:last-child,
            .table-my-bets td:last-child {
                width: 60px;
                min-width: 60px;
                max-width: 60px;
                font-size: 0.65rem;
            }
        }
    </style>

    @php
        $formatUserLabel = function ($user) {
            return $user->name;
        };
        $selectedUser = isset($users) ? $users->firstWhere('id', request('user')) : null;
        $selectedUserLabel = $selectedUser ? $formatUserLabel($selectedUser) : '';
    @endphp
    <!-- Formulário de Pesquisa e Filtro -->
    <form action="{{ url('/concursos/' . $game->id) }}" method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Pesquisar</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="Nome do concurso, números, telefone..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-search me-1"></i>Buscar</button>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    @foreach (['PAID', 'PENDING', 'CANCELED', 'FINISHED'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ __($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label d-flex justify-content-between">
                    <span>Usuário</span>
                </label>
                <input type="text" id="userFilterInput" class="form-control"
                    placeholder="Digite o nome do usuário"
                    list="userFilterOptions" autocomplete="off" value="{{ $selectedUserLabel }}">
                <input type="hidden" name="user" id="userFilterHidden" value="{{ request('user') }}">
                <datalist id="userFilterOptions">
                    @if (isset($users))
                        @foreach ($users as $user)
                            @php
                                $userLabel = $formatUserLabel($user);
                            @endphp
                            <option value="{{ $userLabel }}" data-id="{{ $user->id }}"></option>
                        @endforeach
                    @endif
                </datalist>
            </div>

            <div class="col-md-2">
                <button class="btn btn-info w-100" type="submit"><i class="bx bx-filter me-1"></i>Aplicar Filtros</button>
            </div>
        </div>
    </form>

    <!-- Botão de Download PDF -->
    <div class="mb-3">
        <a href="{{ route('game-my-bets-pdf', array_merge([$game->id], request()->query())) }}" 
           class="btn btn-danger">
            <i class="bx bx-download me-1"></i>Baixar PDF
        </a>
    </div>

    <div class="modal" tabindex="-1" id="modal_repeat_game">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- request()->all() -->
                <form action="{{ url()->query('/purchase/repeat', request()->all()) }}" method="POST" class="mb-4">
                    @csrf
                    @method('POST')

                    <div class="modal-header">
                        <h5 class="modal-title">Repetir jogo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="repeat_game_id">Jogo para repetir</label>
                                <select name="repeat_game_id" class="form-select">
                                    @foreach ($games->where('status', 'OPENED') as $game)
                                        <option value="{{ $game->id }}"
                                            {{ request('game_id') == $game->id ? 'selected' : '' }}>
                                            {{ $game->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="repeat_game_numbers">Números para repetir</label>
                                <input id="repeat_game_numbers_id" name="repeat_game_numbers" type="text" disabled
                                    class="form-control" value="">
                            </div>
                            <input id="repeat_game_purchase_id" name="repeat_game_purchase_id" type="hidden"
                                class="form-control" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button id="repeat_game_repeat_button_id" type="submit"
                            class="btn btn-primary btn-loadonclick ">Repetir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="modal_repeat_game_batch">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ url()->query('/purchase/repeat', request()->all()) }}" method="POST" class="mb-4">
                    @csrf
                    @method('POST')

                    <div class="modal-header">
                        <h5 class="modal-title">Repetir jogos em lote</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="repeat_game_id_batch">Jogo para repetir</label>
                                <select name="repeat_game_id" id="repeat_game_id_batch_tab" class="form-select" required>
                                    @foreach ($games->where('status', 'OPENED') as $game)
                                        <option value="{{ $game->id }}"
                                            {{ request('game_id') == $game->id ? 'selected' : '' }}>
                                            {{ $game->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mt-3">
                                <label>Apostas selecionadas: <span id="selected_count_batch_tab">0</span></label>
                                <div id="selected_purchases_list_tab" class="mt-2" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                                    <p class="text-muted">Nenhuma aposta selecionada</p>
                                </div>
                            </div>
                            
                            <div id="repeat_game_purchase_ids_container_tab"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button id="repeat_game_repeat_button_batch_id_tab" type="submit"
                            class="btn btn-primary btn-loadonclick" disabled>Repetir Selecionadas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="modal_delete_game">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- request()->all() -->
                <form action="{{ url()->query('/purchase/delete', request()->all()) }}" method="POST" class="mb-4">
                    @csrf
                    @method('POST')

                    <div class="modal-header">
                        <h5 class="modal-title">Deletar jogo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="delete_game_name">Jogo Concurso</label>
                                <input id="delete_game_name" name="delete_game_name" type="text" class="form-control"
                                    disabled>
                            </div>

                            <div class="form-group">
                                <label for="delete_game_gambler_name">Nome do apostador</label>
                                <input id="delete_game_gambler_name" name="delete_game_gambler_name" type="text"
                                    disabled class="form-control" value="">
                            </div>

                            <div class="form-group">
                                <label for="delete_game_gambler_phone">Telefone do apostador</label>
                                <input id="delete_game_gambler_phone" name="delete_game_gambler_phone" type="text"
                                    disabled class="form-control" value="">
                            </div>

                            <div class="form-group">
                                <label for="delete_game_numbers">Números</label>
                                <input id="delete_game_numbers" name="delete_game_numbers" type="text" disabled
                                    class="form-control" value="">
                            </div>

                            <input id="delete_game_purchase_id" name="delete_game_purchase_id" type="hidden"
                                class="form-control" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button id="delete_game_delete_button_id" type="submit"
                            class="btn btn-primary">Deletar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <style>
        .row-selected {
            background-color: #e0f0ff !important;
            /* azul clarinho */
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.addEventListener('click', () => {
                    // Remove classe de todas as linhas antes de aplicar
                    rows.forEach(r => r.classList.remove('row-selected'));
                    row.classList.add('row-selected');
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userInput = document.getElementById('userFilterInput');
            const userHiddenInput = document.getElementById('userFilterHidden');
            const userDatalist = document.getElementById('userFilterOptions');

            if (!userInput || !userHiddenInput || !userDatalist) {
                return;
            }

            const syncUserHiddenValue = () => {
                const inputValue = userInput.value.trim();
                if (!inputValue) {
                    userHiddenInput.value = '';
                    return;
                }

                const matchingOption = Array.from(userDatalist.options).find(option => option.value === inputValue);
                userHiddenInput.value = matchingOption ? (matchingOption.dataset.id || '') : '';
            };

            userInput.addEventListener('change', syncUserHiddenValue);
            userInput.addEventListener('blur', syncUserHiddenValue);
            userInput.addEventListener('input', () => {
                if (!userInput.value.trim()) {
                    userHiddenInput.value = '';
                }
            });
        });
    </script>


    @if (in_array(Auth::user()->role->level_id, ['admin']))
        <div class="modal fade" id="filtroVendedorModal" tabindex="-1" aria-labelledby="filtroVendedorModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form method="GET">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="filtroVendedorModalLabel">Filtrar por Vendedor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <select name="user" class="form-select">
                                <option value="">Todos</option>
                                @if (isset($users))
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ request('user') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Aplicar Filtro</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif


    @if (in_array(Auth::user()->role->level_id, ['admin', 'seller', 'gambler']))
        <div class="modal fade" id="filtroPointsModal" tabindex="-1" aria-labelledby="filtroPointsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form method="GET">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="filtroPointsModalLabel">Filtrar por Pontuação</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <select name="points" class="form-select">
                                <option value="">Todos</option>
                                @foreach (range(0, 11, 1) as $point)
                                    <option value="{{ $point }}"
                                        {{ request('points') == $point ? 'selected' : '' }}>
                                        {{ $point }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Aplicar Filtro</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif



    <!-- Tabela de Compras -->
    <div class="card">
        <div class="table-responsive text-nowrap" style="max-height: 70vh; overflow-y: auto;">
            <!-- Botão de Repetir em Lote -->
            <div class="mb-3" id="batch_repeat_container_tab" style="display: none;">
                <button type="button" id="btn_repeat_batch_tab" class="btn btn-info">
                    <i class="bx bx-repeat"></i> Repetir Selecionadas em Lote
                </button>
                <button type="button" id="btn_select_all_tab" class="btn btn-sm btn-outline-secondary ms-2">
                    Selecionar Todas
                </button>
                <button type="button" id="btn_deselect_all_tab" class="btn btn-sm btn-outline-secondary ms-2">
                    Desmarcar Todas
                </button>
            </div>

            <table class="table table-my-bets">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select_all_checkbox_tab" title="Selecionar todas">
                        </th>
                        <th>Participantes</th>
                        <th>Pts
                            {{-- @if (in_array(Auth::user()->role->level_id, ['admin'])) --}}
                            <button type="button" class="btn btn-sm btn-link p-0 ms-1" data-bs-toggle="modal"
                                data-bs-target="#filtroPointsModal">
                                <i class="bx bx-filter-alt"></i>
                            </button>
                            {{-- @endif --}}
                        </th>
                        <th>Números</th>
                        <th>Status</th>
                        <th>Pago por</th>
                        <th>Ações</th>
                        @if (in_array(Auth::user()->role->level_id, ['admin', 'seller']))
                            <!-- Usuario de quem Comprou -->
                            <th>Usuário</th>
                        @endif
                        <th>Compra em</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @if ($purchases->isEmpty())
                        <p class="text-muted">Não há apostas para esse jogo com os filtros especificados.</p>
                    @else
                        <!-- Aqui iteramos sobre as compras -->

                        <!-- A collection de compras como uma só -->
                        @foreach ($purchases as $purchase)
                            <tr>
                                <td>
                                    <input type="checkbox" class="purchase-checkbox-tab" 
                                        value="{{ $purchase->id }}"
                                        data-numbers="{{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}"
                                        data-game-name="{{ $purchase->game->name }}"
                                        data-gambler-name="{{ $purchase->gambler_name }}">
                                </td>
                                <td>
                                    <div class="my-bets-participants">
                                        <span class="participant-name">{{ Str::limit($purchase->gambler_name, 20) }}</span>
                                        @if (in_array(auth()->user()->role->level_id, ['admin', 'seller']))
                                            <small class="participant-info">
                                                @if (in_array($purchase->seller->role->level_id, ['seller']))
                                                    Vendedor: {{ Str::limit($purchase->seller->name, 20) }}
                                                @else
                                                    Vendedor: Banca Central
                                                @endif
                                            </small>
                                        @endif
                                        <small class="participant-info">Ticket: {{ $purchase->identifier }}</small>
                                    </div>
                                </td>

                                <td>
                                    
                                    @if($purchase->status == 'PAID')
                                        @php
                                            $badgeColor = 'secondary';
                                            $userAward = $purchase->userAwards->first();
                                            if ($userAward) {
                                                $gameAward = $userAward->game_award;
                                                $conditionType = $gameAward->condition_type;
                                                switch ($conditionType) {
                                                    case 'WINNER':
                                                        $badgeColor = 'danger';
                                                        break;
                                                    case 'SECONDARY_WINNER':
                                                        $badgeColor = 'primary';
                                                        break;
                                                    case 'EXACT_POINT':
                                                        $badgeColor = 'secondary';
                                                        break;
                                                }
                                            }

                                        @endphp
                                        <span class="badge bg-{{$badgeColor}} fw-bold fs-6">
                                            {{ $purchase->points }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="my-bets-numbers-container">
                                        @php
                                            // Prepare the arrays once to avoid calling them in the loop repeatedly
                                            $displayNumbers = explode(' ', $purchase->numbers);
                                            $matchedNumbers = $purchase->matched_numbers ?? [];
                                        @endphp
                                        
                                        @foreach ($displayNumbers as $number)
                                            @php
                                                $paddedNumber = str_pad($number, 2, '0', STR_PAD_LEFT);
                                                $isHit = in_array($number, $matchedNumbers) && $purchase->status == 'PAID';
                                            @endphp
                                            <div class="my-bets-number-ball {{ $isHit ? 'hit' : 'miss' }}">
                                                {{ $paddedNumber }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>


                                <td>
                                    @php
                                        $statusColors = [
                                            'PAID' => 'bg-success',
                                            'PENDING' => 'bg-warning text-dark',
                                            'CANCELED' => 'bg-danger',
                                            'FINISHED' => 'bg-info'
                                        ];
                                        $statusColor = $statusColors[$purchase->status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $statusColor }} me-1">
                                        <i class="bx {{ $purchase->status == 'PAID' ? 'bx-check-circle' : ($purchase->status == 'PENDING' ? 'bx-time' : ($purchase->status == 'CANCELED' ? 'bx-x-circle' : 'bx-check')) }} me-1"></i>
                                        {{ __($purchase->status) }}
                                    </span>
                                </td>

                                <!-- Quem é pagou ? -->
                                @if (isset($purchase->paid_by_user))
                                    <td>{{ $purchase->paid_by_user->name }}</td>
                                @else
                                    <td>{{ '-' }}</td>
                                @endif
                                <!-- -->

                                @php
                                    $is_imported = $purchase->imported;
                                    $is_admin = auth()->user()->role->level_id == 'admin';
                                @endphp
                                <td>
                                    <div style="display: flex; flex-wrap: wrap; gap: 2px;">
                                        <a href="{{ route('purchase-pay', array_merge([$purchase->id], request()->query())) }}"
                                            class="btn btn-sm btn-success btn-loadonclick {{ $purchase->status !== 'PENDING' || $purchase->game->status == 'CLOSED' || $purchase->game->status == 'FINISHED' ? 'disabled' : '' }}">
                                            Pagar
                                        </a>

                                        @if ($is_imported)
                                            <a href="{{ route('purchase-withdraw', array_merge([$purchase->id], request()->query())) }}"
                                                class="btn btn-sm btn-warning btn-loadonclick {{ !$is_admin || ($purchase->status !== 'PAID' || $purchase->game->status == 'CLOSED' || $purchase->game->status == 'FINISHED') ? 'disabled' : '' }}">
                                                Estornar
                                            </a>
                                        @else
                                            <a href="{{ route('purchase-withdraw', array_merge([$purchase->id], request()->query())) }}"
                                                class="btn btn-sm btn-warning btn-loadonclick {{ $purchase->status !== 'PAID' || $purchase->game->status == 'CLOSED' || $purchase->game->status == 'FINISHED' || $purchase->paid_by_user_id !== auth()->user()->id ? 'disabled' : '' }}">
                                                Estornar
                                            </a>
                                        @endif

                                        <a href="#" data-purchase_id="{{ $purchase->id }}"
                                            data-numbers="{{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}"
                                            data-purchase_id="{{ $purchase->id }}"
                                            data-game_name="{{ $purchase->game->name }}"
                                            data-gambler_name="{{ $purchase->gambler_name }}"
                                            data-gambler_phone="{{ $purchase->gambler_phone }}"
                                            class="btn btn-sm btn-danger delete_game_button {{ $purchase->status !== 'PENDING' || $purchase->game->status == 'CLOSED' || $purchase->game->status == 'FINISHED' ? 'disabled' : '' }}">
                                            Cancelar
                                        </a>

                                        <a href="#" data-purchase_id="{{ $purchase->id }}"
                                            data-game_name="{{ $purchase->game->name }}"
                                            data-numbers="{{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}"
                                            class="btn btn-sm btn-secondary repeat_game_button">
                                            Repetir
                                        </a>
                                    </div>
                                </td>

                                @if (in_array(auth()->user()->role->level_id, ['admin', 'seller']))
                                    <!-- Usuario de quem Comprou -->
                                    @if (in_array($purchase->user->role->level_id, ['seller', 'gambler']))
                                        <td>{{ $purchase->user->name }}</td>
                                    @else
                                        <td>Banca Central</td>
                                    @endif
                                    <!-- -->
                                @endif


                                <!-- Usar timestamp do próprio produto? -->
                                <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <!-- Controles de paginação -->
            <div class="d-flex justify-content-center mt-4">
                {{ $purchases->appends(request()->all())->links('pagination.custom') }}
            </div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Funcionalidade de repetir em lote
            const purchaseCheckboxes = document.querySelectorAll('.purchase-checkbox-tab');
            const selectAllCheckbox = document.getElementById('select_all_checkbox_tab');
            const batchRepeatContainer = document.getElementById('batch_repeat_container_tab');
            const btnRepeatBatch = document.getElementById('btn_repeat_batch_tab');
            const btnSelectAll = document.getElementById('btn_select_all_tab');
            const btnDeselectAll = document.getElementById('btn_deselect_all_tab');
            const selectedCountSpan = document.getElementById('selected_count_batch_tab');
            const selectedPurchasesList = document.getElementById('selected_purchases_list_tab');
            const repeatPurchaseIdsContainer = document.getElementById('repeat_game_purchase_ids_container_tab');
            const repeatButtonBatch = document.getElementById('repeat_game_repeat_button_batch_id_tab');
            const modalRepeatBatch = document.getElementById('modal_repeat_game_batch');

            function updateBatchUI() {
                // Verifica se os checkboxes existem
                if (!purchaseCheckboxes || purchaseCheckboxes.length === 0) {
                    return;
                }
                
                const selected = Array.from(purchaseCheckboxes).filter(cb => cb && cb.checked);
                const count = selected.length;
                
                // Atualiza contador
                if (selectedCountSpan) selectedCountSpan.textContent = count;

                // Mostra/oculta botão de repetir em lote
                if (batchRepeatContainer) {
                    if (count > 0) {
                        batchRepeatContainer.style.display = 'block';
                    } else {
                        batchRepeatContainer.style.display = 'none';
                    }
                }

                // Atualiza lista de apostas selecionadas
                if (count > 0 && selectedPurchasesList) {
                    selectedPurchasesList.innerHTML = selected.map(cb => {
                        const numbers = cb.getAttribute('data-numbers') || '';
                        const gameName = cb.getAttribute('data-game-name') || '';
                        const gamblerName = cb.getAttribute('data-gambler-name') || '';
                        return `<div class="mb-1"><small><strong>${gameName}</strong> - ${gamblerName}: ${numbers}</small></div>`;
                    }).join('');
                    
                    // Atualiza input hidden com IDs
                    const ids = selected.map(cb => cb ? cb.value : null).filter(id => id); // Remove valores vazios
                    
                    // Remove inputs antigos
                    if (repeatPurchaseIdsContainer) {
                        repeatPurchaseIdsContainer.innerHTML = '';
                        
                        // Adiciona novos inputs apenas com valores válidos
                        ids.forEach(id => {
                            if (id) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'repeat_game_purchase_ids[]';
                                input.value = id;
                                repeatPurchaseIdsContainer.appendChild(input);
                            }
                        });
                    }
                    
                    if (repeatButtonBatch) repeatButtonBatch.disabled = false;
                } else {
                    if (selectedPurchasesList) selectedPurchasesList.innerHTML = '<p class="text-muted">Nenhuma aposta selecionada</p>';
                    if (repeatPurchaseIdsContainer) repeatPurchaseIdsContainer.innerHTML = '';
                    if (repeatButtonBatch) repeatButtonBatch.disabled = true;
                }

                // Atualiza checkbox "selecionar todas"
                if (selectAllCheckbox && purchaseCheckboxes) {
                    const totalCheckboxes = purchaseCheckboxes.length;
                    if (count === totalCheckboxes && totalCheckboxes > 0) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else if (count > 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    }
                }
            }

            // Event listeners para checkboxes individuais
            purchaseCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBatchUI);
                // Previne que o checkbox dispare a seleção da linha
                checkbox.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            // Checkbox "selecionar todas"
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    purchaseCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateBatchUI();
                });
            }

            // Botão "Selecionar Todas"
            if (btnSelectAll) {
                btnSelectAll.addEventListener('click', function() {
                    purchaseCheckboxes.forEach(cb => cb.checked = true);
                    updateBatchUI();
                });
            }

            // Botão "Desmarcar Todas"
            if (btnDeselectAll) {
                btnDeselectAll.addEventListener('click', function() {
                    purchaseCheckboxes.forEach(cb => cb.checked = false);
                    updateBatchUI();
                });
            }

            // Botão "Repetir em Lote"
            if (btnRepeatBatch) {
                btnRepeatBatch.addEventListener('click', function() {
                    const selected = Array.from(purchaseCheckboxes).filter(cb => cb.checked);
                    if (selected.length === 0) {
                        alert('Selecione pelo menos uma aposta para repetir');
                        return;
                    }
                    
                    // Atualiza a UI antes de abrir o modal para garantir que os inputs estejam corretos
                    updateBatchUI();
                    
                    const myModal = new bootstrap.Modal(document.getElementById('modal_repeat_game_batch'), {
                        focus: true
                    });
                    myModal.show();
                });
            }
            
            // Atualiza os inputs quando o modal é aberto (evento show do Bootstrap)
            if (modalRepeatBatch) {
                modalRepeatBatch.addEventListener('show.bs.modal', function() {
                    // Garante que os inputs estejam atualizados quando o modal abrir
                    updateBatchUI();
                });
            }

            // Inicializa UI
            updateBatchUI();

            // Funcionalidade de repetir jogo individual
            const repeat_game_buttons = document.getElementsByClassName('repeat_game_button');
            for (var i = 0; i < repeat_game_buttons.length; i++) {
                (function(index) {
                    repeat_game_buttons[index].addEventListener("click", function(e) {
                        e.preventDefault();
                        var myModal = new bootstrap.Modal(document.getElementById('modal_repeat_game'), {
                            focus: true
                        });

                        const numbers = e.target.getAttribute('data-numbers');
                        const repeat_game_numbers_id = document.getElementById('repeat_game_numbers_id');
                        if (repeat_game_numbers_id) repeat_game_numbers_id.value = numbers;

                        const purchase_id = e.target.getAttribute('data-purchase_id');
                        const repeat_game_purchase_id = document.getElementById('repeat_game_purchase_id');
                        if (repeat_game_purchase_id) repeat_game_purchase_id.value = purchase_id;

                        myModal.show();
                    });
                })(i);
            }

            // Funcionalidade de deletar jogo
            const delete_game_buttons = document.getElementsByClassName('delete_game_button');
            for (var i = 0; i < delete_game_buttons.length; i++) {
                (function(index) {
                    delete_game_buttons[index].addEventListener("click", function(e) {
                        e.preventDefault();
                        var myModal = new bootstrap.Modal(document.getElementById('modal_delete_game'), {
                            focus: true
                        });

                        const numbers = e.target.getAttribute('data-numbers');
                        const delete_game_numbers = document.getElementById('delete_game_numbers');
                        if (delete_game_numbers) delete_game_numbers.value = numbers;

                        const game_name = e.target.getAttribute('data-game_name');
                        const delete_game_name = document.getElementById('delete_game_name');
                        if (delete_game_name) delete_game_name.value = game_name;

                        const gambler_name = e.target.getAttribute('data-gambler_name大师');
                        const delete_game_gambler_name = document.getElementById('delete_game_gambler_name');
                        if (delete_game_gambler_name) delete_game_gambler_name.value = gambler_name;

                        const gambler_phone = e.target.getAttribute('data-gambler_phone');
                        const delete_game_gambler_phone = document.getElementById('delete_game_gambler_phone');
                        if (delete_game_gambler_phone) delete_game_gambler_phone.value = gambler_phone;

                        const purchase_id = e.target.getAttribute('data-purchase_id');
                        const delete_game_purchase_id = document.getElementById('delete_game_purchase_id');
                        if (delete_game_purchase_id) delete_game_purchase_id.value = purchase_id;

                        myModal.show();
                    });
                })(i);
            }
        });
    </script>

</div>
