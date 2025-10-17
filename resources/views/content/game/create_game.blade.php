@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Minhas Compras')

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

    <div class="container">
        <h2>Criar Novo Jogo</h2>
        <form action="{{ route('create-game') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nome do Jogo:</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="price">Preço:</label>
                <input type="number" name="price" class="form-control" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="open_at">Data de Abertura:</label>
                <input type="datetime-local" name="open_at" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="close_at">Data de Fechamento:</label>
                <input type="datetime-local" name="close_at" class="form-control" required>
            </div>

            <hr>
            <h3>Prêmios</h3>
            <div id="awards-container">
                <div class="award-item">
                    <div class="form-group">
                        <label for="awards[0][name]">Nome do prêmio:</label>
                        <input type="text" id="award_name" name="awards[0][name]" class="form-control" required>
                    </div>

                    <div class="form-group" id="awards[0][condition_type_div]">
                        <label for="awards[0][condition_type]">Tipo de Condição:</label>
                        <select name="awards[0][type]" class="form-control awards[0][type]" required>
                            <option value="EXACT_POINT">Ponto Exato</option>
                            <option value="WINNER">Vencedor</option>
                        </select>
                    </div>

                    <div class="form-group" id="awards[0][exact_point_div]">
                        <label for="awards[0][exact_point_value]">Quantos Pontos? (se aplicável):</label>
                        <input type="number" name="awards[0][exact_point_value]" class="form-control">
                    </div>

                    <div class="form-group" id="awards[0][winner_point_div]">
                        <label for="awards[0][winner_point_value]">Quantos Pontos para ser vencedor (se aplicável):</label>
                        <input type="number" name="awards[0][winner_point_value]" class="form-control">
                    </div>

                    <div class="form-group" id="awards[0][only_when_finish_round_div]">
                        <label for="awards[0][only_when_finish_round]">Prêmio só é concedido quando o jogo terminar?:</label>
                        <select class="form-select" name="awards[0][only_when_finish_round]">
                            <option value="0">Não (prêmio imediato)</option>
                            <option value="1">Sim (prêmio só no final)</option>
                        </select>
                    </div>

                    <div class="form-group" id="awards[0][only_on_first_round_div]">
                        <label for="awards[0][only_on_first_round]">Apenas primeiro sorteio?:</label>
                        <select class="form-select" name="awards[0][only_on_first_round]">
                            <option value="0">Não</option>
                            <option value="1">Sim</option>
                        </select>
                    </div>

                    <div class="form-group awards[0][value_point_div]">
                        <label for="awards[0][amount]">Valor do Prêmio:</label>
                        <input type="number" name="awards[0][amount]" class="form-control" step="0.01" required>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-danger remove-award">Remover Prêmio</button>
            <button type="button" class="btn btn-secondary" id="add-award">+</button>
            <br><br>

            <button type="submit" class="btn btn-primary">Criar Jogo</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let awardIndex = 1;

            // Função para atualizar a visibilidade das divs
            function updateDivVisibility(awardIndex) {
                const conditionTypeSelect = document.querySelector(
                    `[name="awards[${awardIndex}][type]"]`);
                const exactPointDiv = document.querySelector(`#awards\\[${awardIndex}\\]\\[exact_point_div\\]`);
                const winnerPointDiv = document.querySelector(`#awards\\[${awardIndex}\\]\\[winner_point_div\\]`);
                const onlyOnFirstRoundDiv = document.querySelector(
                    `#awards\\[${awardIndex}\\]\\[only_on_first_round_div\\]`);

                // Verifica se os elementos existem para evitar erros
                if (conditionTypeSelect && exactPointDiv && winnerPointDiv) {
                    const selectedValue = conditionTypeSelect.value;

                    // Esconde todas as divs inicialmente
                    exactPointDiv.style.display = 'none';
                    winnerPointDiv.style.display = 'none';
                    onlyOnFirstRoundDiv.style.display = 'none';

                    // Mostra a div apropriada com base no valor selecionado
                    if (selectedValue === 'EXACT_POINT') {
                        exactPointDiv.style.display = 'block';
                        onlyOnFirstRoundDiv.style.display = 'block';

                    } else if (selectedValue === 'WINNER') {
                        winnerPointDiv.style.display = 'block';
                    }
                }
            }

            // Adicionar novo prêmio
            document.getElementById('add-award').addEventListener('click', function() {
                const container = document.getElementById('awards-container');
                const newAward = document.querySelector('.award-item').cloneNode(true);

                // Atualiza os atributos dos elementos clonados
                newAward.querySelectorAll('input, select').forEach(function(input) {
                    const name = input.getAttribute('name').replace(/\[\d+\]/, `[${awardIndex}]`);
                    input.setAttribute('name', name);
                    input.value = ''; // Limpa os valores para o novo campo
                });

                // Adiciona o evento de mudança para a nova seleção de tipo de condição
                newAward.querySelector(`select[name="awards[${awardIndex}][type]"]`)
                    .addEventListener('change', function() {
                        updateDivVisibility(awardIndex);
                    });

                container.appendChild(newAward);
                awardIndex++;
            });

            // Remover prêmio
            document.getElementById('awards-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-award')) {
                    e.target.closest('.award-item').remove();
                }
            });

            // Adiciona evento de mudança nos selects existentes (para o primeiro prêmio)
            document.querySelectorAll('[name^="awards[0][type]"]').forEach(function(select, index) {
                select.addEventListener('change', function() {
                    updateDivVisibility(index);
                });
            });
        });
    </script>

@endsection
