@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Prêmio de Jogo')

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
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5>Editar Prêmio do Jogo</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('edit-game-award', $gameAward->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="game_name" class="form-label">Premio do concurso</label>
                        <textarea class="form-control" id="game_name" name="game_name" disabled rows="3" required>{{ old('game_name', $gameAward->game->name) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do prêmio</label>
                        <textarea class="form-control" id="name" name="name" rows="3" required>{{ old('name', $gameAward->name) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="condition_type" class="form-label">Tipo de Condição</label>
                        <select class="form-select" id="condition_type" name="condition_type" required>
                            <option
                                {{ old('condition_type', $gameAward->condition_type) == 'EXACT_POINT' ? 'selected' : '' }}
                                value="EXACT_POINT">Ponto Exato</option>

                            <option {{ old('condition_type', $gameAward->condition_type) == 'WINNER' ? 'selected' : '' }}
                                value="WINNER">Vencedor</option>
                            <option
                                {{ old('condition_type', $gameAward->condition_type) == 'SECONDARY_WINNER' ? 'selected' : '' }}
                                value="SECONDARY_WINNER">Vencedor Secundário</option>
                        </select>
                    </div>

                    <div class="mb-3" id="exact_point_div">
                        <label for="exact_point_value" class="form-label">Quantos pontos para o premio? (se
                            aplicável):</label>
                        <input type="number" class="form-control" id="exact_point_value" name="exact_point_value"
                            value="{{ old('exact_point_value', $gameAward->exact_point_value) }}">
                    </div>

                    <div class="mb-3" id="winner_point_div">
                        <label for="winner_point_value" class="form-label">Quantos pontos para o vencedor? (se
                            aplicável):</label>
                        <input type="number" class="form-control" id="winner_point_value" name="winner_point_value"
                            value="{{ old('winner_point_value', $gameAward->winner_point_value) }}">
                    </div>


                    <div class="mb-3" id="only_when_finish_round_div">
                        <label for="only_when_finish_round" class="form-label">Prêmio só é concedido quando o jogo terminar?</label>
                        <select class="form-select" id="only_when_finish_round" name="only_when_finish_round">
                            <option value="0"
                                {{ old('only_when_finish_round', $gameAward->only_when_finish_round == '0') ? 'selected' : '' }}>
                                Não (prêmio imediato)</option>
                            <option value="1"
                                {{ old('only_when_finish_round', $gameAward->only_when_finish_round == '1') ? 'selected' : '' }}>
                                Sim (prêmio só no final)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="only_on_first_round_div">
                        <label for="only_on_first_round" class="form-label">Apenas primeiro sorteio</label>
                        <select class="form-select" id="only_on_first_round" name="only_on_first_round">
                            <option value="1"
                                {{ old('only_on_first_round', $gameAward->only_on_first_round) ? 'selected' : '' }}>
                                Sim</option>
                            <option value="0"
                                {{ !old('only_on_first_round', $gameAward->only_on_first_round) ? 'selected' : '' }}>
                                Não</option>
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="amount" class="form-label">Valor do prêmio:</label>
                        <input class="form-control" type="text" inputmode="numeric" required id="amount"
                            name="amount" value="{{ old('amount', $gameAward->amount) }}">
                    </div>

                    <script>
                        const amountInput = document.getElementById('amount');
                        const errorMessage = document.getElementById('error-message');

                        // Função para aplicar a máscara de Real
                        function formatToBRL(value) {
                            let cleanValue = value.replace(/\D/g, ''); // Remove caracteres não numéricos
                            let formattedValue = (cleanValue / 100).toLocaleString('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                            });
                            return formattedValue.replace('R$', '').trim();
                        }

                        // Evento de input para aplicar a máscara ao digitar
                        amountInput.addEventListener('input', () => {
                            let cursorPosition = amountInput.selectionStart;
                            let formattedValue = formatToBRL(amountInput.value);
                            amountInput.value = formattedValue;
                            amountInput.setSelectionRange(cursorPosition, cursorPosition);
                        });

                        // Evento de blur para validar o valor
                        amountInput.addEventListener('blur', () => {
                            let numericValue = parseFloat(amountInput.value.replace('.', '').replace(',', '.')) || 0;
                            if (numericValue < 5) {
                                errorMessage.style.display = 'block';
                            } else {
                                errorMessage.style.display = 'none';
                            }
                        });
                    </script>

                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="{{ route('show-game', ['id' => $gameAward->game_id]) }}"
                        class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Função para atualizar a visibilidade das divs
            function updateDivVisibility() {
                const conditionTypeSelect = $("#condition_type");
                const exactPointDiv = $("#exact_point_div");
                const winnerPointDiv = $("#winner_point_div");

                // Verifica se os elementos existem para evitar erros
                if (conditionTypeSelect && exactPointDiv && winnerPointDiv) {
                    const selectedValue = conditionTypeSelect.val();

                    // Esconde todas as divs inicialmente
                    exactPointDiv.hide();
                    winnerPointDiv.hide();

                    // Mostra a div apropriada com base no valor selecionado
                    if (selectedValue === 'EXACT_POINT') {
                        exactPointDiv.show();
                    } else if (selectedValue === 'WINNER') {
                        winnerPointDiv.show();
                    }
                }
            }

            $("#condition_type").on('change', updateDivVisibility);
            updateDivVisibility();
        });
    </script>
@endsection
