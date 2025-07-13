{{-- resources/views/purchases/batch_details.blade.php --}}
@extends('layouts/contentNavbarLayout')

@section('title', 'Detalhes do Lote de Compra')

@section('vendor-style')
    {{-- Adicione estilos de vendor se necessário, como para tabelas ou ícones --}}
    {{-- @vite('resources/assets/vendor/libs/data-tables/data-tables.scss') --}}
@endsection

@section('vendor-script')
    {{-- Adicione scripts de vendor se necessário --}}
    {{-- @vite('resources/assets/vendor/libs/data-tables/data-tables.js') --}}
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seleciona todas as linhas principais dos itens da compra
            const itemRows = document.querySelectorAll('.purchase-item-row');

            itemRows.forEach(row => {
                row.addEventListener('click', function() {
                    // Encontra a próxima linha, que deve ser a linha de detalhes/mensagens
                    const detailRow = this.nextElementSibling;

                    // Verifica se a próxima linha existe e se ela é de detalhes
                    if (detailRow && detailRow.classList.contains('item-detail-row')) {
                        // Alterna a visibilidade da linha de detalhes
                        detailRow.classList.toggle(
                            'd-none'); // Usa a classe d-none do Bootstrap para esconder/mostrar
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div>
        <div class="mx-4">
            <h1 class="my-4">Detalhes do Lote de Compra #{{ $purchaseBatch->id }}</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações do Lote</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Descrição:</strong> {{ $purchaseBatch->description ?? 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Jogo:</strong> {{ $purchaseBatch->game->name ?? 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status do Lote:</strong>
                        @if ($purchaseBatch->status == 'pending')
                            <span class="badge bg-label-warning">Pendente</span>
                        @elseif ($purchaseBatch->status == 'approved')
                            <span class="badge bg-label-success">Aprovado</span>
                        @elseif ($purchaseBatch->status == 'error')
                            <span class="badge bg-label-danger">Erro</span>
                        @elseif ($purchaseBatch->status == 'imported')
                            <span class="badge bg-label-success">Importado</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Rodada:</strong> {{ $purchaseBatch->round }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Importado Por:</strong> {{ $purchaseBatch->user->name ?? 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Data de Importação:</strong> {{ $purchaseBatch->created_at->format('d/m/Y H:i') }}
                    </div>

                    <div>
                        @if ($purchaseBatch->status == 'pending')
                            <a href="{{ route('purchases.import.approve', $purchaseBatch->id) }}" class="btn btn-success">
                                <i class="menu-icon tf-icons bx bx-check"></i> Aprovar Importação
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Itens do Lote ({{ $purchaseBatch->items->count() }})</h5>
            </div>
            <div class="card-body">
                @if ($purchaseBatch->items->isEmpty())
                    <p class="text-muted">Nenhum item encontrado para este lote.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Apostador</th>
                                    <th>Telefone</th>
                                    <th>Vendedor</th>
                                    <th>Números</th>
                                    <th>Qtd.</th>
                                    <th>Preço</th>
                                    <th>Status</th>
                                    <th>Identificador</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr class="purchase-item-row" style="cursor: pointer;">
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->gambler_name }}</td>
                                        <td>{{ $item->gambler_phone ?? 'N/A' }}</td>
                                        <td>{{ $item->seller->name ?? 'N/A' }}</td>
                                        <td>{{ $item->numbers }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                                        <td>
                                            @if ($item->status == 'approved')
                                                <span class="badge bg-label-success">
                                                    {{ ucfirst($status_translate[$item->status]) }}
                                                </span>
                                            @endif
                                            @if ($item->status == 'pending')
                                                <span class="badge bg-label-warning">
                                                    {{ ucfirst($status_translate[$item->status]) }}
                                                </span>
                                            @endif
                                            @if ($item->status == 'error')
                                                <span class="badge bg-label-danger">
                                                    {{ ucfirst($status_translate[$item->status]) }}
                                                </span>
                                            @endif
                                            @if ($item->status == 'imported')
                                                <span class="badge bg-label-success">
                                                    {{ ucfirst($status_translate[$item->status]) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $item->identifier ?? 'N/A' }}</td>
                                    </tr>
                                    {{-- Linha de detalhes/mensagens (oculta por padrão) --}}
                                    <tr class="item-detail-row d-none">
                                        <td colspan="8"> {{-- span across all columns --}}
                                            <div class="p-3 bg-light border rounded">
                                                <h6>Mensagens:</h6>
                                                @if ($item->messages->isEmpty())
                                                    <p class="text-muted">Nenhuma mensagem para este item.</p>
                                                @else
                                                    <ul>
                                                        @foreach ($item->messages as $message)
                                                            <li
                                                                class="mb-1 text-{{ $message->type == 'error' ? 'danger' : 'success' }}">
                                                                <strong>{{ ucfirst($message->type) }}:</strong>
                                                                {{ $message->message }}
                                                                <br><small
                                                                    class="text-muted">{{ $message->created_at->format('d/m/Y H:i') }}</small>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- Controles de paginação -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <a href="{{ route('purchases.import.form', $purchaseBatch->game->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar para a lista
        </a>
    </div>
@endsection
