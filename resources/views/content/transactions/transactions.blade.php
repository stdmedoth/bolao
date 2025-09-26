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

    <div class="container">
        <h1 class="my-4">Extrato</h1>

        <div class="card shadow-lg p-3 mb-5 bg-white rounded">
            <div class="row mt-3">
                <div class="col-12">

                    <form action="">
                        @if (auth()->user()->role->level_id == 'admin')
                            <div class="form-group">
                                <label for="user_id">Usuario</label>
                                <div class="input-group">
                                    <select class="form-control" name="user_id">
                                        <option value="">Todos</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ request('user_id') == $user->id ? 'selected' : '' }}>
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
                                        <option value="{{ $game->id }}"
                                            {{ $selected_game == $game->id ? 'selected' : '' }}>
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

                    <div class="table-responsive mt-5">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Concurso</th>
                                    <th>Valor</th>
                                    @if (in_array(auth()->user()->role->level_id, ['admin', 'seller']))
                                        <th>Usuário</th>
                                    @endif
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ $typeTranslations[$transaction->type] ?? $transaction->type }}</td>

                                        <td>{{ isset($transaction->game) ? $transaction->game->name : '-' }}</td>

                                        <td>R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>

                                        @if (in_array(auth()->user()->role->level_id, ['admin', 'seller']))
                                            <td>{{ $transaction->user->name }}</td>
                                        @endif

                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhuma transação encontrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <!-- Controles de paginação -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $transactions->appends(request()->all())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
