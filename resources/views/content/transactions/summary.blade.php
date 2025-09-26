@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Extrato')

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

@section('content')
    <div class="container">
        <h2>Resumo de Transações</h2>
        <form action="">
            @if (auth()->user()->role->level_id == 'admin')
                <div class="form-group">
                    <label for="user_id">Usuario</label>
                    <div class="input-group">
                        <select class="form-control" name="user_id">
                            <option value="">Todos</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label for="game_id">Concurso</label>
                <div class="input-group">
                    <select class="form-control" name="game_id">
                        @if (auth()->user()->role->level_id == 'admin')
                            <option value="all">Todos</option>
                        @endif
                        @php
                            if (!request('game_id')) {
                                $selected_game = $games->first()->id ?? null;
                            } else {
                                $selected_game = request('game_id');
                            }
                        @endphp
                        @foreach ($games as $game)
                            <option value="{{ $game->id }}" {{ $selected_game == $game->id ? 'selected' : '' }}>
                                {{ $game->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if (auth()->user()->role->level_id == 'admin')
                <div class="form-group">
                    <label for="start_date">Data Inicial</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                        value="{{ request('start_date') }}">
                </div>

                <div class="form-group">
                    <label for="end_date">Data Final</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                        value="{{ request('end_date') }}">
                </div>
            @endif

            <button class="btn btn-secondary mt-5" type="submit">Buscar</button>
        </form>

        <div class="row mb-4 mt-5">
            <div class="col-md-4 mt-5">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Saldo para Jogar</h5>

                        @if (auth()->user()->role->level_id !== 'admin' || !strlen(request('user_id')))
                            <p class="card-text h4">R$ {{ number_format(auth()->user()->game_credit, 2, ',', '.') }}</p>
                        @else
                            <p class="card-text h4">R$
                                {{ number_format($users->filter(fn($u) => $u->id == request('user_id'))->first()->game_credit, 2, ',', '.') }}
                            </p>
                        @endif

                    </div>
                </div>
            </div>
            <div class="col-md-4  mt-5">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Saldo para sacar</h5>
                        @if (auth()->user()->role->level_id !== 'admin' || !strlen(request('user_id')))
                            <p class="card-text h4">R$ {{ number_format(auth()->user()->balance, 2, ',', '.') }}</p>
                        @else
                            <p class="card-text h4">R$
                                {{ number_format($users->filter(fn($u) => $u->id == request('user_id'))->first()->balance, 2, ',', '.') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <h3>Detalhes por Tipo</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Quantidade</th>
                        <th>Total</th>
                        <th>Categoria</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($typeDetails as $type => $detail)
                        <tr>
                            <td>{{ $detail['name'] }}</td>
                            <td>{{ $detail['count'] }}</td>
                            <td class="{{ $detail['category'] === 'income' ? 'text-success' : 'text-danger' }}">
                                {{ number_format($detail['total'], 2, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $detail['category'] === 'income' ? 'success' : 'danger' }}">
                                    {{ $detail['category'] === 'income' ? 'Entrada' : 'Saída' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
