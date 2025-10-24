<div class="tab-pane fade {{ $tab == 'tab-classifications' ? 'show active' : '' }}" id="classifications" role="tabpanel"
    aria-labelledby="classifications-tab">

    <style>
        /* Tabela responsiva - usar toda largura disponível */
        .table-classifications {
            width: 100%;
            table-layout: fixed;
            font-size: 0.85rem;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .numbers-container {
            max-width: 350px;
        }

        /* Coluna do ticket - mínima absoluta */
        .table-classifications th:nth-child(1),
        .table-classifications td:nth-child(1) {
            width: 60px;
            min-width: 60px;
            max-width: 60px;
            text-align: center;
            font-size: 0.55rem;
            word-break: keep-all;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna de participantes - mínima absoluta */
        .table-classifications th:nth-child(2),
        .table-classifications td:nth-child(2) {
            width: 60px;
            min-width: 60px;
            max-width: 60px;
            font-size: 0.5rem;
            vertical-align: middle;
            padding: 3px 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Coluna de números - mínima absoluta */
        .table-classifications th:nth-child(3),
        .table-classifications td:nth-child(3) {
            width: 60px;
            min-width: 60px;
            max-width: 60px;
            text-align: center;
            font-size: 0.5rem;
            vertical-align: middle;
            padding: 3px 8px 3px 8px;
        }

        /* Coluna de pontos - mínima absoluta */
        .table-classifications th:nth-child(4),
        .table-classifications td:nth-child(4) {
            width: 30px;
            min-width: 30px;
            max-width: 30px;
            text-align: center;
            font-size: 0.55rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna de status - mínima absoluta */
        .table-classifications th:nth-child(5),
        .table-classifications td:nth-child(5) {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            text-align: center;
            font-size: 0.5rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Coluna de ações - mínima absoluta */
        .table-classifications th:nth-child(6),
        .table-classifications td:nth-child(6) {
            width: 30px;
            min-width: 30px;
            max-width: 30px;
            text-align: center;
            font-size: 0.55rem;
            vertical-align: middle;
            padding: 3px 1px;
        }

        /* Botão de ações ultra compacto */
        .table-classifications .btn-sm {
            padding: 2px 4px;
            font-size: 0.5rem;
            line-height: 1;
            border-radius: 2px;
        }

        .table-classifications .btn-sm i {
            font-size: 0.6rem;
        }

        /* Texto do vendedor ultra compacto */
        .table-classifications .text-muted {
            font-size: 0.3rem;
            line-height: 0.8;
            margin-top: 0px;
        }


        /* Container de números responsivo - linha horizontal única */
        .classification-numbers-container {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 2px;
            max-width: 130px;
            height: 18px;
            justify-content: center;
            align-items: center;
            overflow: visible;
        }

        .classification-number-ball {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 100%;
            font-size: 0.6rem;
            font-weight: 600;
            border: 1px solid;
            margin: 0;
            flex-shrink: 0;
            flex-grow: 0;
        }

        .classification-number-ball.hit {
            background-color: #fbbf24;
            color: #1a365d;
            border-color: #f59e0b;
        }

        .classification-number-ball.miss {
            background-color: #e5e7eb;
            color: #6b7280;
            border-color: #d1d5db;
        }

        /* Responsividade para mobile */
        @media (max-width: 768px) {
            .classification-numbers-container {
                max-width: 110px;
                height: 16px;
            }
            
            /* Ticket no tablet */
            .table-classifications th:nth-child(1),
            .table-classifications td:nth-child(1) {
                width: 50px;
                min-width: 50px;
                max-width: 50px;
                font-size: 0.5rem;
                word-break: keep-all;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                vertical-align: middle;
                padding: 2px 1px;
            }
            
            /* Coluna de participantes no tablet */
            .table-classifications th:nth-child(2),
            .table-classifications td:nth-child(2) {
                width: 50px;
                min-width: 50px;
                max-width: 50px;
                font-size: 0.45rem;
                padding: 2px 1px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            /* Coluna de números no tablet */
            .table-classifications th:nth-child(3),
            .table-classifications td:nth-child(3) {
                width: 50px;
                min-width: 50px;
                max-width: 50px;
                font-size: 0.45rem;
                padding: 2px 6px 2px 6px;
            }
            
            /* Pontos no tablet */
            .table-classifications th:nth-child(4),
            .table-classifications td:nth-child(4) {
                width: 25px;
                min-width: 25px;
                max-width: 25px;
                font-size: 0.5rem;
                padding: 2px 1px;
            }
            
            /* Status no tablet */
            .table-classifications th:nth-child(5),
            .table-classifications td:nth-child(5) {
                width: 35px;
                min-width: 35px;
                max-width: 35px;
                font-size: 0.45rem;
                padding: 2px 1px;
            }
            
            /* Ações no tablet */
            .table-classifications th:nth-child(6),
            .table-classifications td:nth-child(6) {
                width: 25px;
                min-width: 25px;
                max-width: 25px;
                font-size: 0.5rem;
                padding: 2px 1px;
            }
            
            /* Botão de ações no tablet */
            .table-classifications .btn-sm {
                padding: 1px 3px;
                font-size: 0.4rem;
            }
            
            .table-classifications .btn-sm i {
                font-size: 0.5rem;
            }
            
            /* Texto do vendedor no tablet */
            .table-classifications .text-muted {
                font-size: 0.25rem;
            }
            
            .classification-number-ball {
                width: 12px;
                height: 12px;
                font-size: 0.6rem;
            }
        }

        @media (max-width: 576px) {
            .classification-numbers-container {
                max-width: 105px;
                height: 14px;
            }
            
            .classification-number-ball {
                width: 10px;
                height: 10px;
                font-size: 0.30rem;
            }
            
            .prize-type-badge {
                font-size: 5px;
                padding: 1px 1px;
            }
            
            /* Ticket pequeno no mobile */
            .table-classifications th:nth-child(1),
            .table-classifications td:nth-child(1) {
                width: 30px;
                min-width: 30px;
                max-width: 30px;
                font-size: 0.3rem;
                padding: 2px 1px;
                word-break: keep-all;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                vertical-align: middle;
            }
            
            /* Participantes no mobile pequeno */
            .table-classifications th:nth-child(2),
            .table-classifications td:nth-child(2) {
                width: 35px;
                min-width: 35px;
                max-width: 35px;
                font-size: 0.3rem;
                padding: 2px 1px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            /* Números no mobile pequeno */
            .table-classifications th:nth-child(3),
            .table-classifications td:nth-child(3) {
                width: 70px;
                min-width: 70px;
                max-width: 70px;
                font-size: 0.3rem;
                padding: 2px 4px 2px 10px;
            }
            
            /* Pontos mínimo no mobile pequeno */
            .table-classifications th:nth-child(4),
            .table-classifications td:nth-child(4) {
                width: 20px;
                min-width: 20px;
                max-width: 20px;
                font-size: 0.3rem;
                padding: 2px 1px;
            }
            
            /* Status mínimo no mobile pequeno */
            .table-classifications th:nth-child(5),
            .table-classifications td:nth-child(5) {
                width: 20px;
                min-width: 20px;
                max-width: 20px;
                font-size: 0.3rem;
                padding: 2px 1px;
            }
            
            /* Ações mínimo no mobile pequeno */
            .table-classifications th:nth-child(6),
            .table-classifications td:nth-child(6) {
                width: 20px;
                min-width: 20px;
                max-width: 20px;
                font-size: 0.3rem;
                padding: 2px 1px;
            }
            
            /* Botão de ações no mobile pequeno */
            .table-classifications .btn-sm {
                padding: 1px 2px;
                font-size: 0.3rem;
            }
            
            .table-classifications .btn-sm i {
                font-size: 0.4rem;
            }
            
            /* Texto do vendedor no mobile pequeno */
            .table-classifications .text-muted {
                font-size: 0.2rem;
            }
        }
        /* Grid de dezenas - ultra compacto */
        .numbers-grid-compact {
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            padding: 8px;
            margin-bottom: 0;
        }

        .numbers-grid {
            display: grid;
            grid-template-columns: repeat(25, 1fr);
            gap: 1px;
            max-width: 100%;
            margin-bottom: 0;
            padding: 0;
            background-color: transparent;
            border: none;
        }

        .number-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 16px;
            border-radius: 2px;
            font-size: 0.6rem;
            font-weight: 500;
            border: 1px solid;
        }

        .number-cell.drawn {
            background-color: #fbbf24;
            color: #1a365d;
            border-color: #f59e0b;
        }

        .number-cell.not-drawn {
            background-color: #ffffff;
            color: #9ca3af;
            border-color: #e5e7eb;
        }

        .grid-title-compact {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            text-align: center;
        }

        /* Responsividade do grid */
        @media (max-width: 768px) {
            .numbers-grid {
                grid-template-columns: repeat(20, 1fr);
                gap: 1px;
            }
            
            .number-cell {
                height: 14px;
                font-size: 0.55rem;
            }
        }

        @media (max-width: 576px) {
            .numbers-grid {
                grid-template-columns: repeat(15, 1fr);
                gap: 1px;
            }
            
            .number-cell {
                height: 12px;
                font-size: 0.5rem;
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
                        placeholder="Pesquisar por nome, números..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </div>
            </div>
            <div class="col-md-8">
                <!-- Grid de Dezenas Sorteadas - Compacto -->
                <div class="numbers-grid-compact">
                    <div class="grid-title-compact">Dezenas Sorteadas</div>
                    <div class="numbers-grid">
                        @for($i = 0; $i <= 99; $i++)
                            @php
                                $paddedNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
                                $isDrawn = false;
                                
                                // Usar os números únicos do jogo (números sorteados)
                                if(isset($uniqueNumbers) && !empty($uniqueNumbers)) {
                                    $isDrawn = in_array($i, $uniqueNumbers);
                                }
                            @endphp
                            <div class="number-cell {{ $isDrawn ? 'drawn' : 'not-drawn' }}">
                                {{ $paddedNumber }}
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

  
        </div>
    </form>

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

    <!-- Tabela de Classificação -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-classifications">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Participantes</th>
                        <th>Números</th>
                        <th>Pontos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @if ($classifications->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-muted">Não há apostas para esse jogo com os filtros especificados.</td>
                        </tr>
                    @else
                        @foreach ($classifications as $index => $classification)
                            <tr>
                                <td>
                                    <strong>{{ $classification->identifier }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-primary">{{ $classification->gambler_name }}</span>
                                        @if (in_array($classification->seller->role->level_id, ['seller']))
                                            <small class="text-muted">Vendedor: {{ $classification->seller->name }}</small>
                                        @else
                                            <small class="text-muted">Vendedor: Banca Central</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="classification-numbers-container">
                                        @php
                                            $displayNumbers = explode(' ', $classification->numbers);
                                            $matchedNumbers = $classification->matched_numbers ?? [];
                                        @endphp
                                        @foreach ($displayNumbers as $number)
                                            @php
                                                $paddedNumber = str_pad($number, 2, '0', STR_PAD_LEFT);
                                                $isHit = in_array($number, $matchedNumbers) && $classification->status == 'PAID';
                                            @endphp
                                            <div class="classification-number-ball {{ $isHit ? 'hit' : 'miss' }}">
                                                {{ $paddedNumber }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">
                                        {{ $classification->status == 'PAID' ? $classification->points : '-' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusText = match($classification->status) {
                                            'PAID' => 'Pago',
                                            'PENDING' => 'Pendente',
                                            'CANCELED' => 'Cancelado',
                                            'FINISHED' => 'Finalizado',
                                            default => $classification->status
                                        };
                                    @endphp
                                    <span class="badge bg-label-{{ $classification->status == 'PAID' ? 'success' : ($classification->status == 'PENDING' ? 'warning' : 'secondary') }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="openClassificationModal('{{ $classification->id }}')"
                                            title="Ver detalhes">
                                        <i class="bx bx-info-circle"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            
            <!-- Controles de paginação -->
            <div class="d-flex justify-content-center mt-4">
                {{ $classifications->appends(request()->all())->links('pagination.custom') }}
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div class="modal fade" id="classificationModal" tabindex="-1" aria-labelledby="classificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="classificationModalLabel">Detalhes da Aposta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações da Aposta</h6>
                            <p><strong>Ticket:</strong> <span id="modal-ticket"></span></p>
                            <p><strong>Apostador:</strong> <span id="modal-gambler"></span></p>
                            <p><strong>Status:</strong> <span id="modal-status"></span></p>
                            <p><strong>Pontos:</strong> <span id="modal-points"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Números Jogados</h6>
                            <div id="modal-numbers" class="classification-numbers-container"></div>
                            <div class="mt-3">
                                <p><strong>Acertados:</strong> <span id="modal-hits"></span></p>
                                <p><strong>Errados:</strong> <span id="modal-misses"></span></p>
                                <p><strong>Total:</strong> 11</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openClassificationModal(classificationId) {
            try {
                // Buscar a linha que contém o ID específico
                const tableRows = document.querySelectorAll('tbody tr');
                let targetRow = null;
                
                // Procurar pela linha que contém o botão com o ID específico
                for (let row of tableRows) {
                    const button = row.querySelector('button[onclick*="' + classificationId + '"]');
                    if (button) {
                        targetRow = row;
                        break;
                    }
                }
                
                if (!targetRow) {
                    console.error('Linha não encontrada para o ID:', classificationId);
                    alert('Erro: Linha não encontrada');
                    return;
                }
                
                // Extrair dados da linha da tabela
                const cells = targetRow.querySelectorAll('td');
                
                if (cells.length < 6) {
                    console.error('Número insuficiente de células:', cells.length);
                    alert('Erro: Dados da linha incompletos');
                    return;
                }
                
                // Extrair dados com verificações de segurança
                const ticket = cells[0] ? cells[0].textContent.trim() : 'N/A';
                const gamblerElement = cells[1] ? cells[1].querySelector('.fw-bold') : null;
                const gamblerName = gamblerElement ? gamblerElement.textContent.trim() : 'N/A';
                const points = cells[3] ? cells[3].textContent.trim() : '0';
                
                // Extrair status do badge
                const statusBadge = cells[4] ? cells[4].querySelector('.badge') : null;
                const status = statusBadge ? statusBadge.textContent.trim() : 'N/A';
                
                // Extrair números da linha
                const numbersContainer = cells[2] ? cells[2].querySelector('.classification-numbers-container') : null;
                const numbers = [];
                const matchedNumbers = [];
                
                if (numbersContainer) {
                    const numberBalls = numbersContainer.querySelectorAll('.classification-number-ball');
                    numberBalls.forEach(ball => {
                        const number = ball.textContent.trim();
                        numbers.push(number);
                        if (ball.classList.contains('hit')) {
                            matchedNumbers.push(parseInt(number));
                        }
                    });
                }
                
                // Debug: verificar dados extraídos
                console.log('Dados extraídos para ID:', classificationId, {
                    ticket,
                    gamblerName,
                    points,
                    status,
                    numbers,
                    matchedNumbers
                });
                
                // Verificar se os elementos do modal existem
                const modalTicket = document.getElementById('modal-ticket');
                const modalGambler = document.getElementById('modal-gambler');
                const modalPoints = document.getElementById('modal-points');
                const modalStatus = document.getElementById('modal-status');
                const modalNumbers = document.getElementById('modal-numbers');
                const modalHits = document.getElementById('modal-hits');
                const modalMisses = document.getElementById('modal-misses');
                
                if (!modalTicket || !modalGambler || !modalPoints || !modalStatus || !modalNumbers || !modalHits || !modalMisses) {
                    console.error('Elementos do modal não encontrados');
                    alert('Erro: Elementos do modal não encontrados');
                    return;
                }
                
                // Preencher informações básicas
                modalTicket.textContent = ticket;
                modalGambler.textContent = gamblerName;
                modalPoints.textContent = points === '-' ? '-' : points;
                modalStatus.textContent = status;
                
                // Números
                modalNumbers.innerHTML = '';
                
                if (numbers.length > 0) {
                    numbers.forEach(number => {
                        // Verificar se o número está na lista de números acertados
                        const isHit = matchedNumbers.includes(parseInt(number)) && status !== 'Pendente';
                        
                        const numberBall = document.createElement('div');
                        numberBall.className = `classification-number-ball ${isHit ? 'hit' : 'miss'}`;
                        numberBall.textContent = number;
                        modalNumbers.appendChild(numberBall);
                    });
                }
                
                // Estatísticas
                const hits = points === '-' ? 0 : parseInt(points) || 0;
                const misses = 11 - hits;
                modalHits.textContent = hits;
                modalMisses.textContent = misses;
                
                // Mostrar modal
                const modalElement = document.getElementById('classificationModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    console.error('Modal não encontrado');
                    alert('Erro: Modal não encontrado');
                }
                
            } catch (error) {
                console.error('Erro ao abrir modal:', error);
                alert('Erro ao abrir detalhes: ' + error.message);
            }
        }
    </script>

</div>
