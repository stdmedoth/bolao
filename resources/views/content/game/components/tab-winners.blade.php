<div class="tab-pane fade {{ $tab == 'tab-winners' ? 'show active' : '' }}" id="winners" role="tabpanel"
    aria-labelledby="winners-tab">

    <style>
        .number-ball {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 10px;
            margin: 1px;
            border: 1px solid transparent;
            flex-shrink: 0;
        }

        .numbers-container {
            max-width: 350px;
        }

        /* Otimizar coluna do ticket - dinâmica */
        .table-winners th:nth-child(1),
        .table-winners td:nth-child(1) {
            width: 180px;
            min-width: 180px;
            max-width: 180px;
            text-align: center;
            font-size: 0.9rem;
            word-break: break-all;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            vertical-align: middle;
            padding: 12px 8px;
        }

        /* Otimizar coluna de pontos */
        .table-winners th:nth-child(4),
        .table-winners td:nth-child(4) {
            width: 70px;
            min-width: 70px;
            max-width: 70px;
            text-align: center;
            font-size: 0.8rem;
            vertical-align: middle;
            padding: 12px 8px;
        }

        /* Otimizar coluna de status */
        .table-winners th:nth-child(5),
        .table-winners td:nth-child(5) {
            width: 80px;
            min-width: 80px;
            max-width: 80px;
            text-align: center;
            font-size: 0.8rem;
            vertical-align: middle;
            padding: 12px 8px;
        }

        /* Solução elegante para mobile - números em múltiplas linhas */
        @media (max-width: 768px) {
            .numbers-container {
                max-width: 200px;
                flex-wrap: wrap;
                height: auto;
                min-height: 40px;
            }

            .number-ball {
                margin: 1px;
            }
        }

        @media (max-width: 576px) {
            .numbers-container {
                max-width: 150px;
                flex-wrap: wrap;
                height: auto;
                min-height: 50px;
            }
        }

        .number-ball.hit {
            background: #fbbf24;
            color: #1a365d;
            border-color: #f59e0b;
            box-shadow: 0 1px 3px rgba(251, 191, 36, 0.3);
        }

        .number-ball.miss {
            background: #e5e7eb;
            color: #6b7280;
            border-color: #d1d5db;
        }

        .status-badge {
            border-radius: 15px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-badge.paid {
            background: #10b981;
            color: white;
        }

        .status-badge.pending {
            background: #f59e0b;
            color: white;
        }

        .status-badge.revoked {
            background: #ef4444;
            color: white;
        }

        .prize-type-badge {
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 500;
        }

        .prize-type-badge.winner {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .prize-type-badge.secondary {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .prize-type-badge.exact {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .info-icon {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 12px;
        }

        .info-icon:hover {
            background: #e5e7eb;
            border-color: #9ca3af;
            color: #374151;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }

        .modal-body {
            padding: 30px;
        }

        /* Responsividade do modal */
        @media (max-width: 768px) {
            .modal-body {
                padding: 20px;
            }

            .modal-header {
                padding: 15px 20px;
            }

            .modal-title {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .modal-body {
                padding: 15px;
            }

            .modal-header {
                padding: 12px 15px;
            }

            .modal-title {
                font-size: 1rem;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .detail-label {
                font-weight: 600;
                color: #374151;
            }
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }

        .detail-value {
            color: #2d3748;
            font-weight: 500;
        }

        .table-winners-wrapper {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        table.table-winners {
            margin-bottom: 0;
        }

        .table-winners thead th {
            background: #f8fafc;
            color: #374151;
            border: none;
            font-weight: 600;
            padding: 15px 12px;
            border-bottom: 2px solid #e5e7eb;
        }

        .table-winners tbody tr {
            transition: background-color 0.2s ease;
        }

        .table-winners tbody tr:hover {
            background-color: #f9fafb;
        }

        .table-winners tbody td {
            padding: 12px;
            vertical-align: middle;
            border-color: #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
        }

        /* Responsividade para mobile */
        @media (max-width: 768px) {
            .table-winners-wrapper {
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
            }

            table.table-winners {
                min-width: 600px;
            }

            .table-winners thead th {
                padding: 10px 8px;
                font-size: 0.85rem;
            }

            .table-winners tbody td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }

            .number-ball {
                width: 20px;
                height: 20px;
                font-size: 9px;
            }

            .numbers-container {
                max-width: 280px;
            }

            /* Ticket intermediário no tablet */
            .table-winners th:nth-child(1),
            .table-winners td:nth-child(1) {
                width: 130px;
                min-width: 130px;
                max-width: 130px;
                font-size: 0.85rem;
                word-break: break-all;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                vertical-align: middle;
                padding: 10px 6px;
            }

            /* Pontos menor no mobile */
            .table-winners th:nth-child(4),
            .table-winners td:nth-child(4) {
                width: 60px;
                min-width: 60px;
                max-width: 60px;
                font-size: 0.75rem;
                vertical-align: middle;
                padding: 10px 6px;
            }

            /* Status menor no mobile */
            .table-winners th:nth-child(5),
            .table-winners td:nth-child(5) {
                width: 70px;
                min-width: 70px;
                max-width: 70px;
                font-size: 0.75rem;
                vertical-align: middle;
                padding: 10px 6px;
            }
        }

        @media (max-width: 576px) {
            table.table-winners {
                min-width: 500px;
            }

            .table-winners thead th {
                padding: 8px 6px;
                font-size: 0.8rem;
            }

            .table-winners tbody td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }

            .number-ball {
                width: 18px;
                height: 18px;
                font-size: 8px;
                margin: 0.5px;
            }

            .numbers-container {
                max-width: 220px;
            }

            .status-badge {
                font-size: 9px;
                padding: 2px 6px;
            }

            .prize-type-badge {
                font-size: 9px;
                padding: 2px 6px;
            }

            /* Ticket pequeno no mobile */
            .table-winners th:nth-child(1),
            .table-winners td:nth-child(1) {
                width: 80px;
                min-width: 80px;
                max-width: 80px;
                font-size: 0.75rem;
                padding: 8px 4px;
                word-break: break-all;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                vertical-align: middle;
            }

            /* Pontos mínimo no mobile pequeno */
            .table-winners th:nth-child(4),
            .table-winners td:nth-child(4) {
                width: 50px;
                min-width: 50px;
                max-width: 50px;
                font-size: 0.7rem;
                padding: 8px 4px;
                vertical-align: middle;
            }

            /* Status mínimo no mobile pequeno */
            .table-winners th:nth-child(5),
            .table-winners td:nth-child(5) {
                width: 60px;
                min-width: 60px;
                max-width: 60px;
                font-size: 0.7rem;
                padding: 8px 4px;
                vertical-align: middle;
            }
        }

        .winner-row.winner-first {
            background-color: #fffbeb;
        }

        .winner-row.winner-secondary {
            background-color: #f9fafb;
        }

        .winner-row.winner-exact {
            background-color: #fef2f2;
        }
    </style>

    @php
        $isAdmin = auth()->check() && optional(auth()->user()->role)->level_id === 'admin';
        $formatUserLabel = function ($user) {
            return $user->name;
        };
        $selectedUser = isset($users) ? $users->firstWhere('id', request('user')) : null;
        $selectedUserLabel = $selectedUser ? $formatUserLabel($selectedUser) : '';
    @endphp

    <!-- Formulário de Filtro -->
    <form action="{{ url('/concursos/' . $game->id) }}" method="GET" class="mb-4">
        <input type="hidden" name="tab" value="tab-winners">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label d-flex justify-content-between">
                    <span>Participantes</span>
                </label>
                <input type="text" id="userFilterInputWinners" class="form-control"
                    placeholder="Digite o nome do participante" list="userFilterOptionsWinners" autocomplete="off"
                    value="{{ $selectedUserLabel }}">
                <input type="hidden" name="user" id="userFilterHiddenWinners" value="{{ request('user') }}">
                <datalist id="userFilterOptionsWinners">
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
                <button class="btn btn-secondary w-100" type="submit">Aplicar Filtro</button>
            </div>
            @if (request('user'))
                <div class="col-md-2">
                    <a href="{{ url('/concursos/' . $game->id . '?tab=tab-winners') }}"
                        class="btn btn-outline-secondary w-100">Limpar Filtro</a>
                </div>
            @endif
        </div>
    </form>

    @if (!count($winners))
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bx bx-trophy" style="font-size: 4rem; color: #cbd5e0;"></i>
            </div>
            <h4 class="text-muted">Nenhum ganhador encontrado</h4>
            <p class="text-muted">
                @if (request('user'))
                    Não há ganhadores para o usuário selecionado. Tente outro filtro ou limpe o filtro para ver todos os
                    ganhadores.
                @else
                    Os prêmios serão distribuídos após o fechamento do jogo.
                @endif
            </p>
        </div>
    @else
        <div class="table-responsive">
            <div class="table-winners-wrapper">
                <table class="table table-winners mb-0">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Participantes</th>
                            <th>Prêmio</th>
                            <th>Pontos</th>
                            <th>Status</th>
                            @if ($isAdmin)
                                <th>Pago Por</th>
                            @endif
                            <th>Números</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($winners as $index => $winner)
                            @php
                                $rowClass = '';
                                if ($winner->game_award->condition_type == 'WINNER') {
                                    $rowClass = 'winner-first';
                                } elseif ($winner->game_award->condition_type == 'SECONDARY_WINNER') {
                                    $rowClass = 'winner-secondary';
                                } elseif ($winner->game_award->condition_type == 'EXACT_POINT') {
                                    $rowClass = 'winner-exact';
                                }

                                $purchaseNumbers = explode(' ', $winner->purchase->numbers);
                                $matchedNumbers = $winner->matched_numbers ?? [];

                                // Traduzir status
                                $statusText = '';
                                switch ($winner->status) {
                                    case 'PAID':
                                        $statusText = 'Pago';
                                        break;
                                    case 'PENDING':
                                        $statusText = 'Pendente';
                                        break;
                                    case 'REVOKED':
                                        $statusText = 'Revogado';
                                        break;
                                    default:
                                        $statusText = $winner->status;
                                }
                            @endphp

                            <tr class="winner-row {{ $rowClass }}">
                                <td>
                                    <strong>#{{ $winner->purchase->identifier }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <div class="mb-2">
                                            <div class="small text-muted mb-1">Apostador:</div>
                                            <div class="fw-bold text-primary">{{ $winner->purchase->gambler_name }}
                                            </div>
                                            @if ($isAdmin && $winner->purchase->gambler_phone)
                                                <small
                                                    class="text-muted">{{ $winner->purchase->gambler_phone }}</small>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="small text-muted mb-1">Vendedor:</div>
                                            <div class="fw-bold">
                                                {{ in_array($winner->purchase->seller->role->level_id, ['seller']) ? $winner->purchase->seller->name : 'Banca Central' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold text-success mb-1">
                                            R$ {{ number_format($winner->user_award->amount, 2, ',', '.') }}
                                        </div>
                                        <span
                                            class="prize-type-badge
                                            @if ($winner->game_award->condition_type == 'WINNER') winner
                                            @elseif($winner->game_award->condition_type == 'SECONDARY_WINNER') secondary
                                            @else exact @endif">
                                            @switch($winner->game_award->condition_type)
                                                @case('WINNER')
                                                    1º Lugar
                                                @break

                                                @case('SECONDARY_WINNER')
                                                    2º Lugar
                                                @break

                                                @case('EXACT_POINT')
                                                    {{ $winner->game_award->exact_point_value }} pts
                                                @break
                                            @endswitch
                                        </span>
                                        <div class="small text-muted mt-1">{{ $winner->game_award->name }}</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">{{ $winner->userPoint }} pts</span>
                                </td>
                                <td>
                                    <span class="status-badge {{ strtolower($winner->status) }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                @if ($isAdmin)
                                    <td>
                                        {{ $winner->purchase->paid_by_user->name ?? 'N/A' }}
                                    </td>
                                @endif

                                <td>
                                    <div class="d-flex flex-wrap numbers-container">
                                        @foreach ($purchaseNumbers as $number)
                                            @php
                                                $paddedNumber = str_pad($number, 2, '0', STR_PAD_LEFT);
                                                $isHit = in_array($number, $matchedNumbers);
                                            @endphp
                                            <span class="number-ball {{ $isHit ? 'hit' : 'miss' }}">
                                                {{ $paddedNumber }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="info-icon" data-bs-toggle="modal"
                                            data-bs-target="#winnerModal{{ $index }}" title="Ver detalhes">
                                            <i class="bx bx-info-circle"></i>
                                        </button>
                                        @if ($isAdmin)
                                            <a href="{{ route('user_award-pay', $winner->id) }}"
                                                class="btn btn-success btn-sm {{ $winner->status == 'PAID' ? 'disabled' : '' }}"
                                                title="Pagar prêmio">
                                                <i class="bx bx-check"></i>
                                            </a>
                                            @if ($winner->status == 'PAID')
                                                <a href="{{ route('user_award-withdraw', $winner->id) }}"
                                                    class="btn btn-warning btn-sm" title="Estornar prêmio">
                                                    <i class="bx bx-undo"></i>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de Detalhes -->
                            <div class="modal fade" id="winnerModal{{ $index }}" tabindex="-1"
                                aria-labelledby="winnerModalLabel{{ $index }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="winnerModalLabel{{ $index }}">
                                                <i class="bx bx-trophy me-2"></i>Detalhes do Bilhete Vencedor
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="detail-row">
                                                        <span class="detail-label">Número do Ticket:</span>
                                                        <span
                                                            class="detail-value fw-bold">#{{ $winner->purchase->identifier }}</span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Nome do Apostador:</span>
                                                        <span
                                                            class="detail-value">{{ $winner->purchase->gambler_name }}</span>
                                                    </div>
                                                    @if ($isAdmin)
                                                        <div class="detail-row">
                                                            <span class="detail-label">Telefone:</span>
                                                            <span
                                                                class="detail-value">{{ $winner->purchase->gambler_phone ?? 'Não informado' }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="detail-row">
                                                        <span class="detail-label">Vendedor:</span>
                                                        <span
                                                            class="detail-value">{{ in_array($winner->purchase->seller->role->level_id, ['seller']) ? $winner->purchase->seller->name : 'Banca Central' }}</span>
                                                    </div>
                                                    @if ($isAdmin)
                                                        <div class="detail-row">
                                                            <span class="detail-label">Pago Por:</span>
                                                            <span
                                                                class="detail-value">{{ $winner->purchase->paid_by_user->name ?? 'N/A' }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-row">
                                                        <span class="detail-label">Prêmio:</span>
                                                        <span class="detail-value fw-bold text-success">R$
                                                            {{ number_format($winner->user_award->amount, 2, ',', '.') }}</span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Pontuação:</span>
                                                        <span
                                                            class="detail-value fw-bold text-primary">{{ $winner->userPoint }}
                                                            pontos</span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Valor da Aposta:</span>
                                                        <span class="detail-value">R$
                                                            {{ number_format($winner->purchase->price, 2, ',', '.') }}</span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Data da Aposta:</span>
                                                        <span
                                                            class="detail-value">{{ $winner->purchase->created_at->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Status:</span>
                                                        <span
                                                            class="status-badge {{ strtolower($winner->status) }}">{{ $statusText }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-4">

                                            <div class="mb-3">
                                                <h6 class="fw-bold mb-3">Números Jogados</h6>
                                                @php
                                                    $modalPurchaseNumbers = explode(' ', $winner->purchase->numbers);
                                                    $modalMatchedNumbers = $winner->matched_numbers ?? [];
                                                @endphp
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($modalPurchaseNumbers as $number)
                                                        @php
                                                            $paddedNumber = str_pad($number, 2, '0', STR_PAD_LEFT);
                                                            $isHit = in_array($number, $modalMatchedNumbers);
                                                        @endphp
                                                        <span class="number-ball {{ $isHit ? 'hit' : 'miss' }}"
                                                            style="width: 40px; height: 40px; font-size: 16px;">
                                                            {{ $paddedNumber }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                                <div class="mt-3">
                                                    <small class="text-muted">
                                                        <span class="badge bg-warning text-dark me-2">Acertados:
                                                            {{ $winner->userPoint }}</span>
                                                        <span class="badge bg-secondary me-2">Errados:
                                                            {{ 11 - $winner->userPoint }}</span>
                                                        <span class="badge bg-primary">Total: 11</span>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <h6 class="fw-bold mb-2">Informações do Prêmio</h6>
                                                <div class="detail-row">
                                                    <span class="detail-label">Nome do Prêmio:</span>
                                                    <span class="detail-value">{{ $winner->game_award->name }}</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Tipo:</span>
                                                    <span class="detail-value">
                                                        @switch($winner->game_award->condition_type)
                                                            @case('WINNER')
                                                                <span class="badge bg-warning text-dark">1º Lugar</span>
                                                            @break

                                                            @case('SECONDARY_WINNER')
                                                                <span class="badge bg-secondary">2º Lugar</span>
                                                            @break

                                                            @case('EXACT_POINT')
                                                                <span class="badge bg-info">Ponto Exato
                                                                    ({{ $winner->game_award->exact_point_value }})
                                                                </span>
                                                            @break
                                                        @endswitch
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $user_awards->appends(request()->all())->links('pagination::bootstrap-5') }}
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userInput = document.getElementById('userFilterInputWinners');
            const userHiddenInput = document.getElementById('userFilterHiddenWinners');
            const userDatalist = document.getElementById('userFilterOptionsWinners');

            if (!userInput || !userHiddenInput || !userDatalist) {
                return;
            }

            const syncUserHiddenValue = () => {
                const inputValue = userInput.value.trim();
                if (!inputValue) {
                    userHiddenInput.value = '';
                    return;
                }

                const matchingOption = Array.from(userDatalist.options).find(option => option.value ===
                    inputValue);
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
</div>
