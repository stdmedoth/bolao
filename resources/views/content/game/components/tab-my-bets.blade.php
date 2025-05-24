<div class="tab-pane fade {{ $tab == 'tab-mybets' ? 'show active' : '' }}" id="mybets" role="tabpanel"
    aria-labelledby="mybets-tab">

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
    @if ($purchases->isEmpty())
        <p class="text-muted">Não há apostas para esse jogo com os filtros especificados.</p>
    @else
        <div class="modal" tabindex="-1" id="modal_repeat_game">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- request()->all() -->
                    <form action="{{ url()->query('/purchase/repeat', request()->all()) }}" method="POST"
                        class="mb-4">
                        @csrf
                        @method('POST')

                        <div class="modal-header">
                            <h5 class="modal-title">Repetir jogo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
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
                                    <input id="repeat_game_numbers_id" name="repeat_game_numbers" type="text"
                                        disabled class="form-control" value="">
                                </div>
                                <input id="repeat_game_purchase_id" name="repeat_game_purchase_id" type="hidden"
                                    class="form-control" value="">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button id="repeat_game_repeat_button_id" type="submit"
                                class="btn btn-primary">Repetir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal" tabindex="-1" id="modal_delete_game">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- request()->all() -->
                    <form action="{{ url()->query('/purchase/delete', request()->all()) }}" method="POST"
                        class="mb-4">
                        @csrf
                        @method('POST')

                        <div class="modal-header">
                            <h5 class="modal-title">Deletar jogo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="delete_game_name">Jogo Concurso</label>
                                    <input id="delete_game_name" name="delete_game_name" type="text"
                                        class="form-control" disabled>
                                </div>

                                <div class="form-group">
                                    <label for="delete_game_gambler_name">Nome do apostador</label>
                                    <input id="delete_game_gambler_name" name="delete_game_gambler_name" type="text"
                                        disabled class="form-control" value="">
                                </div>

                                <div class="form-group">
                                    <label for="delete_game_gambler_phone">Telefone do apostador</label>
                                    <input id="delete_game_gambler_phone" name="delete_game_gambler_phone"
                                        type="text" disabled class="form-control" value="">
                                </div>

                                <div class="form-group">
                                    <label for="delete_game_numbers">Números</label>
                                    <input id="delete_game_numbers" name="delete_game_numbers" type="text"
                                        disabled class="form-control" value="">
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

        <!-- Tabela de Compras -->
        <div class="card">

            <div class="table-responsive text-nowrap" style="max-height: 70vh; overflow-y: auto;">




                <table class="table">
                    <thead>
                        <tr>
                            <th>Jogo</th>
                            <th>Apostador</th>
                            @if (in_array(Auth::user()->role->level_id, ['admin', 'seller']))
                                <!-- Usuario de quem Comprou -->
                                <th>Usuário</th>
                            @endif
                            @if (in_array(Auth::user()->role->level_id, ['admin', 'seller']))
                                <th>Vendedor</th>
                            @endif
                            <th>Compra em</th>
                            <th>Números</th>
                            <th>Status</th>
                            <th>Pago por</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <!-- Aqui iteramos sobre as compras -->

                        <!-- A collection de compras como uma só -->
                        @foreach ($purchases as $purchase)
                            <tr>
                                <td>
                                    <a href='/concursos/{{ $purchase->game->id }}'>
                                        <!-- Mostrando o nome do jogo relacionado -->
                                        <i class="bx bxl-game bx-md text-info me-4"></i>
                                        <span>{{ $purchase->game ? $purchase->game->name : '-' }}</span>
                                        <!-- Nome do jogo é o dia na semana em que se passa a aposta-->
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary me-1">{{ __($purchase->gambler_name) }}</span>
                                </td>

                                @if (in_array(auth()->user()->role->level_id, ['admin', 'seller']))
                                    <!-- Usuario de quem Comprou -->
                                    @if (in_array($purchase->user->role->level_id, ['seller', 'gambler']))
                                        <td>{{ $purchase->user->name }}</td>
                                    @else
                                        <td>Banca Central</td>
                                    @endif
                                    <!-- -->

                                    <!-- Quem é o Vendedor -->
                                    @if (in_array($purchase->seller->role->level_id, ['seller']))
                                        <td>{{ $purchase->seller->name }}</td>
                                    @else
                                        <!-- Se foi o admin que comprou, então a banca central é o vendedor -->
                                        <td>Banca Central</td>
                                    @endif
                                    <!-- -->
                                @endif

                                <!-- Usar timestamp do próprio produto? -->

                                <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                                <td> {{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}
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

                                <td>
                                    <a href="{{ route('purchase-pay', array_merge([$purchase->id], request()->query())) }}"
                                        class="btn btn-success {{ $purchase->status !== 'PENDING' || $purchase->game->status == 'CLOSED' || $purchase->game->status == 'FINISHED' ? 'disabled' : '' }}">
                                        Pagar
                                    </a>

                                    <a href="{{ route('purchase-withdraw', array_merge([$purchase->id], request()->query())) }}"
                                        class="btn btn-warning {{ $purchase->status !== 'PAID' || $purchase->game->status == 'CLOSED' || $purchase->game->status == 'FINISHED' || $purchase->paid_by_user_id !== auth()->user()->id ? 'disabled' : '' }}">
                                        Estornar
                                    </a>

                                    <a href="#" data-purchase_id="{{ $purchase->id }}"
                                        data-numbers="{{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}"
                                        data-purchase_id="{{ $purchase->id }}"
                                        data-game_name="{{ $purchase->game->name }}"
                                        data-gambler_name="{{ $purchase->gambler_name }}"
                                        data-gambler_phone="{{ $purchase->gambler_phone }}"
                                        class="btn btn-danger delete_game_button {{ $purchase->status !== 'PENDING' || $purchase->game->status == 'CLOSED' || $purchase->game->status == 'FINISHED' ? 'disabled' : '' }}">
                                        Cancelar
                                    </a>

                                    <a href="#" data-purchase_id="{{ $purchase->id }}"
                                        data-game_name="{{ $purchase->game->name }}"
                                        data-numbers="{{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}"
                                        class="btn btn-secondary repeat_game_button">
                                        Repetir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Controles de paginação -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $purchases->appends(request()->all())->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    @endif
</div>
