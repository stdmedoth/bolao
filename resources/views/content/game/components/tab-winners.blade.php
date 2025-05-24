<div class="tab-pane fade {{ $tab == 'tab-winners' ? 'show active' : '' }}" id="winners" role="tabpanel"
    aria-labelledby="winners-tab">
    @if (!count($winners))
        <p class="text-muted">Não há ganhadores ainda.</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nome do Usuário</th>
                        <th>Prêmio</th>
                        <th>Vlr. Prêmio</th>
                        <th>Pontuação</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($winners as $index => $winner)
                        <tr>
                            <td>{{ $winner->user->name }}</td>
                            <td>{{ $winner->game_award->name }}</td>
                            <td>R$ {{ number_format($winner->game_award->amount, 2, ',', '.') }}</td>
                            <td>{{ $winner->userPoint }}</td>
                            <td><span class="badge bg-label-primary me-1">{{ __($winner->status) }}</span></td>
                            <td>
                                <button class="btn btn-secondary btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#details-{{ $index }}" aria-expanded="false"
                                    aria-controls="details-{{ $index }}">
                                    Detalhes
                                </button>
                                @if (auth()->user()->role->level_id == 'admin')
                                    <a href="{{ route('user_award-pay', $winner->id) }}"
                                        class="btn btn-success {{ $winner->status == 'PAID' ? 'disabled' : '' }}">
                                        Pagar
                                    </a>
                                @endif
                                @if (auth()->user()->role->level_id == 'admin' && $winner->status == 'PAID')
                                    <a href="{{ route('user_award-withdraw', $winner->id) }}"
                                        class="btn btn-info {{ $winner->status !== 'PAID' ? 'disabled' : '' }}">
                                        Estornar
                                    </a>
                                @endif
                            </td>
                        </tr>
                        <tr class="collapse" id="details-{{ $index }}">
                            <td colspan="6">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Números</th>
                                            <th>Vlr. Aposta</th>
                                            <th>Data da Aposta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ collect(explode(' ', $winner->purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}
                                            </td>
                                            <td>R$ {{ number_format($winner->purchase->price, 2, ',', '.') }}</td>
                                            <td>{{ $winner->purchase->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $user_awards->links('pagination::bootstrap-5') }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
