<div class="tab-pane fade {{ $tab == 'tab-results' ? 'show active' : '' }}" id="results" role="tabpanel"
    aria-labelledby="results-tab">
    <h3 class="mb-4">Histórico de Resultados</h3>

    @if (auth()->user()->role->level_id == 'admin')
        <form id="result_form" action="{{ route('add-game-history', $game->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="description">Descrição para os novos números</label>
                <input type="text" class="form-control" id="description" name="description"
                    placeholder="Descrição para novo resultado">
            </div>
            <div class="form-group">
                <label for="result_numbers">Escolha suas dezenas (11 números por vez, separados por espaços)</label>
                <input type="text" class="form-control" id="result_numbers" name="result_numbers"
                    placeholder="Exemplo: 11 22 33 44 55 66 77 88 99 00 01" required>
                <small class="form-text text-muted">As dezenas devem ter dois dígitos cada e serem separadas por
                    espaços.</small>
                <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
            </div>
            <input type="hidden" name="game_id" value="{{ $game->id }}">
            <button type="submit" class="btn btn-primary">Adicionar novo resultado</button>
        </form>
    @endif

    <div class="card shadow-sm h-100 mt-4">
        <div class="card-body">
            @if ($histories->isEmpty())
                <p class="text-muted">Não há registros de resultados para este jogo.</p>
            @else
                <div class="row">
                    @foreach ($histories as $history)
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-lg h-100">
                                @if (auth()->user()->role->level_id == 'admin')
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item"
                                                href="/concursos/resultados/historico/edit/{{ $history->id }}">
                                                <i class="bx bx-edit-alt me-1"></i> Editar
                                            </a>
                                            <a class="dropdown-item"
                                                href="/concursos/resultados/historico/remove/{{ $history->id }}">
                                                <i class="bx bx-x me-1"></i> Remover
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h3 class="card-title">{{ $history->description }}</h3>
                                    <h5 class="card-text">
                                        {{ ucfirst(\Carbon\Carbon::parse($history->created_at)->translatedFormat('l, d/m/Y')) }}
                                    </h5>
                                    <h5 class="card-text">
                                        <strong>{{ collect(explode(' ', $history->result_numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}</strong>
                                    </h5>
                                    <p class="card-text">
                                        <small class="text-muted">Cadastrado:
                                            {{ $history->created_at->format('d/m/Y H:i') }}</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $histories->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
