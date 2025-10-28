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
        
        /* Coluna de ID/Ticket - primeira coluna */
        .table-my-bets th:nth-child(1),
        .table-my-bets td:nth-child(1) {
            width: 80px;
            min-width: 80px;
            max-width: 80px;
            font-size: 0.8rem;
            vertical-align: middle;
            padding: 3px 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Coluna de Apostador - segunda coluna */
        .table-my-bets th:nth-child(2),
        .table-my-bets td:nth-child(2) {
            width: 120px;
            min-width: 120px;
            max-width: 120px;
            font-size: 0.8rem;
            vertical-align: middle;
            padding: 3px 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Coluna de Vendedor - terceira coluna (se houver) */
        .table-my-bets th:nth-child(3),
        .table-my-bets td:nth-child(3) {
            width: 100px;
            min-width: 100px;
            max-width: 100px;
            font-size: 0.8rem;
            vertical-align: middle;
            padding: 3px 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Coluna de Pontos */
        .table-my-bets th:nth-child(4),
        .table-my-bets td:nth-child(4) {
            width: 50px;
            min-width: 50px;
            max-width: 50px;
            text-align: center;
            font-size: 0.85rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna de Números */
        .table-my-bets th:nth-child(5),
        .table-my-bets td:nth-child(5) {
            width: 180px;
            min-width: 180px;
            max-width: 180px;
            text-align: center;
            font-size: 0.75rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna de Status */
        .table-my-bets th:nth-child(6),
        .table-my-bets td:nth-child(6) {
            width: 80px;
            min-width: 80px;
            max-width: 80px;
            text-align: center;
            font-size: 0.75rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna de Pago por */
        .table-my-bets th:nth-child(7),
        .table-my-bets td:nth-child(7) {
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

        /* Coluna de Ações */
        .table-my-bets th:nth-child(8),
        .table-my-bets td:nth-child(8) {
            width: 200px;
            min-width: 200px;
            max-width: 200px;
            font-size: 0.7rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Botões de ações compactos */
        .table-my-bets .btn-sm {
            padding: 2px 4px;
            font-size: 0.7rem;
            line-height: 1;
            border-radius: 2px;
            margin: 1px;
        }

        /* Container de números no estilo de classificações */
        .my-bets-numbers-container {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
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
            width: 22px;
            height: 22px;
            border-radius: 100%;
            font-size: 0.7rem;
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

        /* Coluna "Usuário" (penúltima coluna antes de "Compra em") */
        .table-my-bets th:nth-last-child(2),
        .table-my-bets td:nth-last-child(2) {
            width: 120px;
            min-width: 120px;
            max-width: 120px;
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

        /* Responsividade para mobile */
        @media (max-width: 768px) {
            .table-my-bets th:nth-child(1),
            .table-my-bets td:nth-child(1) {
                width: 60px;
                min-width: 60px;
                max-width: 60px;
                font-size: 0.7rem;
            }
            
            .table-my-bets th:nth-child(2),
            .table-my-bets td:nth-child(2) {
                width: 100px;
                min-width: 100px;
                max-width: 100px;
                font-size: 0.7rem;
            }

            .table-my-bets .btn-sm {
                padding: 1px 2px;
                font-size: 0.6rem;
            }

            .my-bets-number-ball {
                width: 18px;
                height: 18px;
                font-size: 0.6rem;
            }

            /* Coluna "Usuário" no tablet */
            .table-my-bets th:nth-last-child(2),
            .table-my-bets td:nth-last-child(2) {
                width: 100px;
                min-width: 100px;
                max-width: 100px;
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
                width: 50px;
                min-width: 50px;
                max-width: 50px;
                font-size: 0.6rem;
            }
            
            .table-my-bets .btn-sm {
                padding: 1px 1px;
                font-size: 0.5rem;
            }

            .my-bets-number-ball {
                width: 15px;
                height: 15px;
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

    <!-- Formulário de Pesquisa e Filtro -->
    <form action="{{ url('/concursos/' . $game->id) }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <!-- Campo de pesquisa -->
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="Pesquisar por nome do concurso, numeros..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </div>
            </div>

            <div class="col-md-3">
                <!-- Select de filtro por role -->
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    @foreach (['PAID', 'PENDING', 'CANCELED', 'FINISHED'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ __($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-secondary w-100" type="submit">Aplicar Filtros</button>
            </div>
        </div>
    </form>
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
                            <select name="seller" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller->id }}"
                                        {{ request('seller') == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->name }}
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
            <table class="table table-my-bets">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Apostador</th>
                        @if (in_array(Auth::user()->role->level_id, ['admin', 'seller']))
                            <th>
                                Vendedor
                                @if (in_array(Auth::user()->role->level_id, ['admin']))
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1"
                                        data-bs-toggle="modal" data-bs-target="#filtroVendedorModal">
                                        <i class="bx bx-filter-alt"></i>
                                    </button>
                                @endif
                            </th>
                        @endif

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
                                    <strong>{{ $purchase->identifier }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary me-1">{{ __($purchase->gambler_name) }}</span>
                                </td>

                                @if (in_array(auth()->user()->role->level_id, ['admin', 'seller']))
                                    <!-- Quem é o Vendedor -->
                                    @if (in_array($purchase->seller->role->level_id, ['seller']))
                                        <td>{{ $purchase->seller->name }}</td>
                                    @else
                                        <!-- Se foi o admin que comprou, então a banca central é o vendedor -->
                                        <td>Banca Central</td>
                                    @endif
                                    <!-- -->
                                @endif

                                <td><span
                                        class="fw-bold text-primary">{{ $purchase->status == 'PAID' ? $purchase->points : '' }}</span>
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
                                    <span class="badge bg-label-primary me-1">{{ __($purchase->status) }}</span>
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
                {{ $purchases->appends(request()->all())->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

</div>
