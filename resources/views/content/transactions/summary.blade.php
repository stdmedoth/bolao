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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary mb-0"><i class="bx bx-receipt me-2"></i>Resumo de Transações</h2>
            @if (in_array(auth()->user()->role->level_id, ['admin', 'seller']))
                <a href="{{ route('finances.summary.pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
                    <i class="bx bx-file-pdf me-2"></i>Gerar PDF
                </a>
            @endif
        </div>
        
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
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="bx bx-filter-alt me-2"></i>Filtros</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('finances.summary') }}" method="GET">
                            <div class="form-group mb-3">
                                <label for="game_id">Concurso</label>
                                <div class="input-group">
                                    <select class="form-control" name="game_id">
                                        <option value="all" {{ request('game_id') == 'all' || !request('game_id') ? 'selected' : '' }}>Todos</option>
                                        @php
                                            if (!request('game_id') || request('game_id') == 'all') {
                                                $selected_game = null;
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

                            @if (auth()->user()->role->level_id == 'admin')
                                @php
                                    $formatUserLabel = function ($user) {
                                        return $user->name;
                                    };
                                    $selectedUser = isset($users) ? $users->firstWhere('id', request('user_id')) : null;
                                    $selectedUserLabel = $selectedUser ? $formatUserLabel($selectedUser) : '';
                                @endphp
                                <div class="form-group mb-3">
                                    <label class="form-label d-flex justify-content-between">
                                        <span>Usuário</span>
                                    </label>
                                    <input type="text" id="userFilterInput" class="form-control"
                                        placeholder="Digite o nome do usuário"
                                        list="userFilterOptions" autocomplete="off" value="{{ $selectedUserLabel }}">
                                    <input type="hidden" name="user_id" id="userFilterHidden" value="{{ request('user_id') }}">
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
                            @endif

                            <button class="btn btn-primary" type="submit"><i class="bx bx-search me-2"></i>Buscar</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Formulário de Lançamentos - Metade direita -->
            @if (auth()->user()->role->level_id == 'admin')
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0"><i class="bx bx-plus-circle me-2"></i>Novo Lançamento</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('finances.transaction.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="filter_game_id" value="{{ request('game_id', '') }}">
                            <input type="hidden" name="filter_month" value="{{ request('month', '') }}">
                            <input type="hidden" name="filter_user_id" value="{{ request('user_id', '') }}">

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

                            <button class="btn btn-success" type="submit"><i class="bx bx-check me-2"></i>Criar Lançamento</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="row mb-4 mt-5">
            @php
                // Determina qual usuário mostrar
                // Se houver filtro de seller, usa o seller; se houver filtro de apostador, usa o apostador; caso contrário, usa o usuário logado
                if (auth()->user()->role->level_id === 'admin' && request('seller')) {
                    $displayUser = isset($sellers) ? $sellers->firstWhere('id', request('seller')) : auth()->user();
                } elseif (auth()->user()->role->level_id === 'admin' && request('gambler')) {
                    $displayUser = isset($gamblers) ? $gamblers->firstWhere('id', request('gambler')) : auth()->user();
                } else {
                    $displayUser = auth()->user();
                }
                
                // Para apostadores, mostra apenas o saldo (game_credit) sem considerar limite
                if (auth()->user()->role->level_id === 'gambler') {
                    $currentBalance = $displayUser ? ($displayUser->game_credit ?? 0) : 0;
                    $hasDebt = false;
                } else {
                    // Para vendedores e admin: calcula valores do saldo considerando limite
                    // Dívida = quanto está devendo (game_credit_limit - game_credit)
                    $creditDebt = $displayUser ? max(0, ($displayUser->game_credit_limit ?? 0) - ($displayUser->game_credit ?? 0)) : 0;
                    // Saldo disponível = apenas o que está acima do limite inicial (game_credit - game_credit_limit)
                    $availableBalance = $displayUser ? max(0, ($displayUser->game_credit ?? 0) - ($displayUser->game_credit_limit ?? 0)) : 0;
                    $hasDebt = $creditDebt > 0;
                    $currentBalance = $hasDebt ? $creditDebt : $availableBalance;
                }
            @endphp
            
            <div class="col-md-4 mt-5">
                <div class="card {{ $hasDebt ? 'border-danger' : 'border-success' }} shadow-sm">
                    <div class="card-body text-center {{ $hasDebt ? 'bg-danger' : 'bg-success' }} text-white">
                        <h5 class="card-title mb-3"><i class="bx bx-wallet me-2"></i>Saldo Atual</h5>
                        <p class="card-text h2 mb-2 fw-bold">R$ {{ number_format($currentBalance, 2, ',', '.') }}</p>
                        @if($hasDebt)
                            <small class="badge bg-warning text-dark"><i class="bx bx-error-circle me-1"></i>Dívida de crédito</small>
                        @else
                            <small class="badge bg-light text-success"><i class="bx bx-check-circle me-1"></i>Disponível para saque</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Usuário / Vendedor (apenas para vendedores e admin) -->
        @if (isset($userInfo) && auth()->user()->role->level_id !== 'gambler')
        <div class="card mt-5 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h5 class="card-title mb-0 text-dark">Informações do Usuário</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bx bx-user-circle text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">Vendedor</small>
                                <div class="fw-semibold text-dark">{{ $userInfo['name'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bx bx-percent text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">Comissão</small>
                                <div class="fw-semibold text-dark">{{ number_format($userInfo['comission_percent'], 2, ',', '.') }}%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bx bx-trophy text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">Total de Jogos</small>
                                <div class="fw-semibold text-dark">{{ $userInfo['total_games'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Limite de crédito (aplicável para vendedores) --}}
                @php
                    $initialLimit = $userInfo['game_credit_limit'] ?? 0;
                    $currentCredit = $userInfo['game_credit'] ?? 0;
                    $creditDebt = $userInfo['credit_debt'] ?? 0;
                @endphp

                @if ($initialLimit > 0)
                    <hr class="my-4">
                    <h6 class="text-muted mb-3">Informações de Crédito</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <small class="text-muted d-block mb-1">Limite Inicial</small>
                            <div class="fw-semibold text-dark">R$ {{ number_format($initialLimit, 2, ',', '.') }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <small class="text-muted d-block mb-1">Crédito Disponível</small>
                            <div class="fw-semibold text-success">R$ {{ number_format($currentCredit, 2, ',', '.') }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <small class="text-muted d-block mb-1">Saldo Devedor</small>
                            <div class="fw-semibold {{ $creditDebt > 0 ? 'text-danger' : 'text-dark' }}">R$ {{ number_format($creditDebt, 2, ',', '.') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <h3 class="mt-5 text-primary"><i class="bx bx-list-ul me-2"></i>Resumo Detalhado</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th><i class="bx bx-file me-1"></i>Descrição</th>
                        <th><i class="bx bx-trophy me-1"></i>Concurso</th>
                        <th><i class="bx bx-hash me-1"></i>Quantidade</th>
                        <th><i class="bx bx-money me-1"></i>Total</th>
                        <th><i class="bx bx-user me-1"></i>Usuário</th>
                        <th><i class="bx bx-category me-1"></i>Categoria</th>
                        @if (auth()->user()->role->level_id == 'admin')
                            <th><i class="bx bx-cog me-1"></i>Ações</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summaryRows as $row)
                        @php
                            $isIncome = $row['category'] === 'income';
                            $isCustom = isset($row['is_custom']) && $row['is_custom'];
                        @endphp
                        <tr>
                            <td><strong class="text-dark">{{ $row['type'] }}</strong></td>
                            <td><span class="text-info">{{ $row['game_name'] }}</span></td>
                            <td><span class="badge bg-secondary">{{ $row['quantity'] }}</span></td>
                            <td class="{{ $isIncome ? 'text-success' : 'text-danger' }} fw-bold">
                                <i class="bx {{ $isIncome ? 'bx-arrow-down text-success' : 'bx-arrow-up text-danger' }} me-1"></i>
                                @if($row['total'] > 0)
                                    {{ $isIncome ? 'R$' : '-R$' }}
                                    {{ number_format($row['total'], 2, ',', '.') }}
                                @else
                                    -
                                @endif
                                
                            </td>
                            <td>
                                @if(isset($row['user_name']) && $row['user_name'])
                                    <span class="text-muted"><i class="bx bx-user me-1"></i>{{ $row['user_name'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $isIncome ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                    <i class="bx {{ $isIncome ? 'bx-trending-up' : 'bx-trending-down' }} me-1"></i>
                                    {{ $isIncome ? 'Entrada' : 'Saída' }}
                                </span>
                            </td>
                            @if (auth()->user()->role->level_id == 'admin')
                                <td>
                                    @if ($isCustom && isset($row['transaction_id']))
                                        <form action="{{ route('finances.transaction.destroy', $row['transaction_id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este lançamento personalizado? Esta ação irá ajustar o saldo do usuário.');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="filter_game_id" value="{{ request('game_id', '') }}">
                                            <input type="hidden" name="filter_month" value="{{ request('month', '') }}">
                                            <input type="hidden" name="filter_user_id" value="{{ request('user_id', '') }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role->level_id == 'admin' ? 7 : 6 }}" class="text-center text-muted py-4">
                                <i class="bx bx-info-circle me-2"></i>Nenhum registro encontrado para os filtros selecionados.
                            </td>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Campo de lançamento
            const transactionUserIdSelect = document.getElementById('transaction_user_id');

            // Função para atualizar o campo de lançamento com o ID do usuário
            const updateTransactionUserField = (userId) => {
                if (transactionUserIdSelect && userId) {
                    // Verifica se o ID existe nas opções do select
                    const option = Array.from(transactionUserIdSelect.options).find(opt => opt.value == userId);
                    if (option) {
                        transactionUserIdSelect.value = userId;
                    }
                } else if (transactionUserIdSelect && !userId) {
                    // Se não houver userId, limpa a seleção
                    transactionUserIdSelect.value = '';
                }
            };

            // Filtro de usuário
            const userInput = document.getElementById('userFilterInput');
            const userHiddenInput = document.getElementById('userFilterHidden');
            const userDatalist = document.getElementById('userFilterOptions');

            if (userInput && userHiddenInput && userDatalist) {
                const syncUserHiddenValue = () => {
                    const inputValue = userInput.value.trim();
                    if (!inputValue) {
                        userHiddenInput.value = '';
                        updateTransactionUserField('');
                        return;
                    }

                    const matchingOption = Array.from(userDatalist.options).find(option => option.value === inputValue);
                    const userId = matchingOption ? (matchingOption.dataset.id || '') : '';
                    userHiddenInput.value = userId;
                    
                    // Atualiza o campo de lançamento quando o usuário é selecionado
                    if (userId) {
                        updateTransactionUserField(userId);
                    }
                };

                userInput.addEventListener('change', syncUserHiddenValue);
                userInput.addEventListener('blur', syncUserHiddenValue);
                userInput.addEventListener('input', () => {
                    if (!userInput.value.trim()) {
                        userHiddenInput.value = '';
                        updateTransactionUserField('');
                    }
                });
            }

            // Inicializa o campo de lançamento com o valor do filtro se já houver um selecionado
            if (userHiddenInput && userHiddenInput.value) {
                updateTransactionUserField(userHiddenInput.value);
            }
        });
    </script>
@endsection
