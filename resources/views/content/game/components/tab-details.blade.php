<div class="tab-pane fade {{ $tab == 'tab-details' ? 'show active' : '' }}" id="details" role="tabpanel"
    aria-labelledby="details-tab">
    <div class="card shadow-sm mb-4">
        @if (auth()->user()->role->level_id == 'admin')
            <a class="dropdown-item" href="/concursos/edit/{{ $game->id }}"><i class="bx bx-edit-alt me-1"></i>
                Editar</a>
            <a class="dropdown-item" href="/concursos/generate_pdf/{{ $game->id }}"><i class="bx bx-export me-1"></i>
                Gerar PDF</a>
            <a class="dropdown-item" href="/concursos/generate_csv/{{ $game->id }}"><i
                    class="bx bx-list-check me-1"></i> Gerar CSV</a>
        @endif
        <div class="card-body">
            <p><strong>Nome:</strong> {{ $game->name }}</p>
            <p><strong>Status:</strong>
                <span class="badge bg-label-primary">{{ __($game->status) }}</span>
            </p>
            <p><strong>Preço:</strong> R$ {{ number_format($game->price, 2, ',', '.') }}</p>
            <p><strong>Aberto em:</strong> {{ date('d/m/Y', strtotime($game->open_at)) }}</p>
            <p><strong>Fecha em:</strong> {{ date('d/m/Y', strtotime($game->close_at)) }}</p>
            <p><strong>Ativo:</strong> {{ $game->active ? 'Sim' : 'Não' }}</p>
        </div>
    </div>
</div>
