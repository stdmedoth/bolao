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
        <h2>Resumo de Transações</h2>
        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row mt-4">
            <!-- Filtros - Metade esquerda -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filtros</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('finances.summary') }}" method="GET">
                            @if (auth()->user()->role->level_id == 'admin')
                                <div class="form-group mb-3">
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

                            <div class="form-group mb-3">
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
                                <div class="form-group mb-3">
                                    <label for="month">Mês de movimentação</label>
                                    <div class="input-group">
                                        <select class="form-control" name="month" id="month">
                                            <option value="">Selecione o mês</option>
                                            @php
                                                $months = [
                                                    1 => 'Janeiro',
                                                    2 => 'Fevereiro',
                                                    3 => 'Março',
                                                    4 => 'Abril',
                                                    5 => 'Maio',
                                                    6 => 'Junho',
                                                    7 => 'Julho',
                                                    8 => 'Agosto',
                                                    9 => 'Setembro',
                                                    10 => 'Outubro',
                                                    11 => 'Novembro',
                                                    12 => 'Dezembro'
                                                ];
                                            // Usa o mês vindo do controller ou, se não houver, mantém nulo (sem seleção)
                                            $selectedMonthLocal = isset($selectedMonth) ? (int)$selectedMonth : null;
                                            @endphp
                                            @foreach ($months as $monthNum => $monthName)
                                            <option value="{{ $monthNum }}" {{ $selectedMonthLocal === $monthNum ? 'selected' : '' }}>
                                                    {{ $monthName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <button class="btn btn-secondary" type="submit">Buscar</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Formulário de Lançamentos - Metade direita -->
            @if (auth()->user()->role->level_id == 'admin')
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Novo Lançamento</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('finances.transaction.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="filter_user_id" value="{{ request('user_id', '') }}">
                            <input type="hidden" name="filter_game_id" value="{{ request('game_id', '') }}">
                            <input type="hidden" name="filter_month" value="{{ request('month', '') }}">

                            <div class="form-group mb-3">
                                <label for="transaction_user_id">Usuário</label>
                                <select class="form-control" name="user_id" id="transaction_user_id" required>
                                    <option value="">Selecione um usuário</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description">Descrição</label>
                                <textarea class="form-control" name="description" id="description" rows="3" required maxlength="500" placeholder="Digite a descrição do lançamento"></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="amount">Valor</label>
                                <input type="number" class="form-control" name="amount" id="amount" step="0.01" min="0.01" required placeholder="0.00">
                            </div>

                            <div class="form-group mb-3">
                                <label for="transaction_type">Tipo</label>
                                <select class="form-control" name="transaction_type" id="transaction_type" required>
                                    <option value="income">Entrada</option>
                                    <option value="outcome">Saída</option>
                                </select>
                            </div>

                            <button class="btn btn-primary" type="submit">Criar Lançamento</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>

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

        <!-- Informações do Usuário / Vendedor -->
        @if (isset($userInfo))
        <div class="card mt-5">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações do Usuário</h5>
            </div>
            <div class="card-body">
                <p><strong>Vendedor:</strong> {{ $userInfo['name'] }}</p>
                <p><strong>Porcentagem de comissão:</strong> {{ number_format($userInfo['comission_percent'], 2, ',', '.') }}%</p>
                <p><strong>Total de jogos:</strong> {{ $userInfo['total_games'] }}</p>

                {{-- Limite de crédito (aplicável para vendedores) --}}
                @php
                    $initialLimit = $userInfo['game_credit_limit'] ?? 0;
                    $currentCredit = $userInfo['game_credit'] ?? 0;
                    $creditDebt = $userInfo['credit_debt'] ?? 0;
                @endphp

                @if ($initialLimit > 0)
                    <hr>
                    <p><strong>Limite inicial de crédito:</strong> R$ {{ number_format($initialLimit, 2, ',', '.') }}</p>
                    <p><strong>Limite disponível (crédito atual para jogar):</strong> R$ {{ number_format($currentCredit, 2, ',', '.') }}</p>
                    <p><strong>Saldo devedor (limite utilizado):</strong> R$ {{ number_format($creditDebt, 2, ',', '.') }}</p>
                @endif
            </div>
        </div>
        @endif

        <h3 class="mt-5">Resumo Detalhado</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Concurso</th>
                        <th>Quantidade</th>
                        <th>Total</th>
                        <th>Categoria</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summaryRows as $row)
                        <tr>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ $row['game_name'] }}</td>
                            <td>{{ $row['quantity'] }}</td>
                            @php
                                $isIncome = $row['category'] === 'income';
                            @endphp
                            <td class="{{ $isIncome ? 'text-success' : 'text-danger' }}">
                                {{ $isIncome ? 'R$' : '-R$' }}
                                {{ number_format($row['total'], 2, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge {{ $isIncome ? 'bg-success' : 'bg-danger' }}">
                                    {{ $isIncome ? 'Entrada' : 'Saída' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhum registro encontrado para os filtros selecionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Controles de paginação -->
            <div class="d-flex justify-content-center mt-4">
                {{ $summaryRows->appends(request()->all())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
