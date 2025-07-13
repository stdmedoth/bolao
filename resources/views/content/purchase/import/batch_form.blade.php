{{-- resources/views/content/game/components/tab-import-batch.blade.php --}}
<div class="tab-pane fade {{ $tab == 'tab-import-batch' ? 'show active' : '' }}" id="import-batch" role="tabpanel"
    aria-labelledby="import-batch-tab">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            {{-- Mensagens de sucesso --}}
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            {{-- Mensagens de erro --}}
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            {{-- Erros de validação do formulário --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('purchases.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group mb-3">
                    <label for="description" class="form-label">Descrição do Lote (Opcional)</label>
                    <input type="text" class="form-control" id="description" name="description"
                        value="{{ old('description') }}" placeholder="Ex: Compras do dia 04/07/2025">
                    @error('description')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <input type="hidden" name="game_id" value="{{ $game->id }}">

                <div class="form-group mb-4">
                    <label for="batch_file" class="form-label">Arquivo CSV de Compras</label>
                    <input type="file" class="form-control" id="batch_file" name="batch_file" accept=".csv, .txt"
                        required>
                    <small class="form-text text-muted">
                        Faça o upload de um arquivo CSV. As colunas esperadas (e na ordem que você quiser) são:
                        `gambler_name`, `gambler_phone`, `numbers` (ex: "11 22 33"), `quantity`, `price`, `identifier`,
                        `round`, `status`.
                        A primeira linha **deve** ser o cabeçalho.
                    </small>
                    @error('batch_file')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary me-2">Importar Lote</button>
                <a href="{{ route('show-game', $game->id) }}" class="btn btn-secondary">Voltar para o Jogo</a>

            </form>
        </div>
    </div>
</div>
