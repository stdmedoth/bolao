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

    <div>
        @if (!in_array(auth()->user()->role->level_id, ['admin', 'seller']))
            <h1 class="my-4">Minhas Compras</h1>
        @else
            <h1 class="my-4">Compras realizadas</h1>
        @endif

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

        <!-- Tabela de Compras -->
        <div class="card">

            <div class="table-responsive text-nowrap" style="max-height: 70vh; overflow-y: auto;">

                <!-- Formulário de Pesquisa e Filtro -->
                <form action="{{ url('/concursos' . $game->id) }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <!-- Campo de pesquisa -->
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Pesquisar por nome do concurso, numeros..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <!-- Select de filtro por role -->
                            <select name="game_id" class="form-select">
                                <option value="">Todos Concursos</option>
                                @foreach ($games as $game)
                                    <option value="{{ $game->id }}"
                                        {{ request('game_id') == $game->id ? 'selected' : '' }}>{{ $game->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <!-- Select de filtro por role -->
                            <select name="status" class="form-select">
                                <option value="">Todos os status</option>
                                @foreach (['PAID', 'PENDING', 'CANCELED', 'FINISHED'] as $status)
                                    <option value="{{ $status }}"
                                        {{ request('status') == $status ? 'selected' : '' }}>{{ __($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-secondary w-100" type="submit">Aplicar Filtros</button>
                        </div>
                    </div>
                </form>


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
                                        class="btn btn-danger delete_game_button {{ $purchase->status == 'PAID' || $purchase->game->status == 'CLOSED' || $purchase->game->status == 'FINISHED' ? 'disabled' : '' }}">
                                        Deletar
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
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            repeat_game_buttons = document.getElementsByClassName('repeat_game_button');
            repeat_game_repeat_button_id = document.getElementsByClassName('repeat_game_repeat_button_id');

            for (var i = 0; i < repeat_game_buttons.length; i++) {
                (function(index) {
                    repeat_game_buttons[index].addEventListener("click", function(e) {
                        var myModal = new bootstrap.Modal(document.getElementById(
                            'modal_repeat_game'), {
                            focus: true
                        });

                        numbers = e.target.getAttribute('data-numbers');
                        repeat_game_numbers_id = document.getElementById('repeat_game_numbers_id')
                        repeat_game_numbers_id.value = numbers;

                        purchase_id = e.target.getAttribute('data-purchase_id');
                        repeat_game_purchase_id = document.getElementById('repeat_game_purchase_id')
                        repeat_game_purchase_id.value = purchase_id;

                        myModal.show();

                    });
                })(i);
            }


            delete_game_buttons = document.getElementsByClassName('delete_game_button');
            delete_game_delete_button_id = document.getElementsByClassName('delete_game_delete_button_id');

            for (var i = 0; i < delete_game_buttons.length; i++) {
                (function(index) {
                    delete_game_buttons[index].addEventListener("click", function(e) {
                        var myModal = new bootstrap.Modal(document.getElementById(
                            'modal_delete_game'), {
                            focus: true
                        });

                        numbers = e.target.getAttribute('data-numbers');
                        delete_game_numbers = document.getElementById('delete_game_numbers')
                        delete_game_numbers.value = numbers;

                        game_name = e.target.getAttribute('data-game_name');
                        delete_game_name = document.getElementById('delete_game_name')
                        delete_game_name.value = game_name;

                        gambler_name = e.target.getAttribute('data-gambler_name');
                        delete_game_gambler_name = document.getElementById('delete_game_gambler_name')
                        delete_game_gambler_name.value = gambler_name;

                        gambler_phone = e.target.getAttribute('data-gambler_phone');
                        delete_game_gambler_phone = document.getElementById('delete_game_gambler_phone')
                        delete_game_gambler_phone.value = gambler_phone;

                        purchase_id = e.target.getAttribute('data-purchase_id');
                        delete_game_purchase_id = document.getElementById('delete_game_purchase_id')
                        delete_game_purchase_id.value = purchase_id;

                        myModal.show();

                    });
                })(i);
            }

        });
    </script>
@endsection
