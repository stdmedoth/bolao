{{-- resources/views/content/game/components/tab-batch-list.blade.php --}}
<div class="tab-pane fade {{ $tab == 'tab-batch-list' ? 'show active' : '' }}" id="batch-list" role="tabpanel"
    aria-labelledby="batch-list-tab">
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Lotes de Compras Importados</h5>
        </div>
        <div class="card-body">
            @if ($purchaseBatches->isEmpty())
                <p class="text-muted">Nenhum lote de compra importado ainda.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID do Lote</th>
                                <th>Descrição</th>
                                <th>Jogo</th>
                                <th>Vendedor</th>
                                <th>Status</th>
                                <th>Rodada</th>
                                <th>Itens</th>
                                <th>Importado Por</th>
                                <th>Data de Importação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseBatches as $batch)
                                <tr>
                                    <td>{{ $batch->id }}</td>
                                    <td>{{ $batch->description ?? 'N/A' }}</td>
                                    <td>{{ $batch->game->name ?? 'N/A' }}</td>
                                    <td>{{ $batch->seller->name ?? 'N/A' }}</td>
                                    <td><span
                                            class="badge bg-label-{{ $batch->status == 'pending' ? 'warning' : 'success' }}">{{ ucfirst($batch->status) }}</span>
                                    </td>
                                    <td>{{ $batch->round }}</td>
                                    <td>{{ $batch->items->count() }}</td> {{-- Conta o número de itens no lote --}}
                                    <td>{{ $batch->user->name ?? 'N/A' }}</td>
                                    <td>{{ $batch->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{-- Adicione botões de ação aqui, por exemplo, para ver detalhes do lote --}}
                                        <a href="{{ route('purchases.import.show', $batch->id) }}"
                                            class="btn btn-sm btn-info" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        {{-- Exemplo de botão para aprovar/processar o lote --}}
                                        {{-- <button type="button" class="btn btn-sm btn-success" title="Processar Lote">Processar</button> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Controles de paginação -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $purchaseBatches->appends(request()->all())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
