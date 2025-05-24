<div class="tab-pane fade {{ $tab == 'tab-prizes' ? 'show active' : '' }}" id="prizes" role="tabpanel"
    aria-labelledby="prizes-tab">
    <h3 class="mb-0">Prêmios Disponíveis</h3>

    @if (auth()->user()->role->level_id == 'admin')
        <a href="{{ route('create-game-award-form', $game->id) }}" class="btn btn-primary my-3">Criar novo prêmio</a>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            @if ($game->awards->isEmpty())
                <p class="text-muted">Nenhum prêmio foi configurado para este jogo.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($game->awards as $award)
                        <li class="list-group-item">
                            @if (auth()->user()->role->level_id == 'admin')
                                <div class="dropdown float-end">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="/concursos/premios/edit/{{ $award->id }}">
                                            <i class="bx bx-edit-alt me-1"></i> Editar
                                        </a>
                                        <a class="dropdown-item" href="/concursos/premios/remove/{{ $award->id }}">
                                            <i class="bx bx-x me-1"></i> Remover
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <h4>{{ $award->name }}</h4>
                            @if ($award->condition_type === 'EXACT_POINT')
                                Quem fizer <strong>{{ $award->exact_point_value }}</strong> pontos ganha<br>
                            @endif
                            @if ($award->condition_type === 'WINNER')
                                Quem fizer <strong>{{ $award->winner_point_value }}</strong> pontos vence o torneio em
                                primeiro<br>
                            @endif
                            @if ($award->condition_type === 'LOWEST_POINT')
                                Quem fizer <strong>menos</strong> pontos ganha<br>
                            @endif
                            <strong>Valor do prêmio:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
