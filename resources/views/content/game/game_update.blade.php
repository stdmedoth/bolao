@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Jogo')

@section('content')

    <div class="container">
        <h2>Editar Jogo: {{ $game->name }}</h2>

        <!-- Exibição da mensagem de erro geral -->
        @if ($errors->has('error'))
            <div class="alert alert-danger">
                {{ $errors->first('error') }}
            </div>
        @endif


        <form action="{{ route('game-update', $game->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Campos do Jogo -->
            <div class="form-group">
                <label for="name">Nome do Jogo:</label>
                <input type="text" name="name" class="form-control" value="{{ $game->name }}" required>
            </div>

            <!-- Temporada -->
            <div class="form-group">
                <label for="name">Temporada:</label>
                <input type="text" name="round" class="form-control" value="{{ $game->round }}" required>
            </div>

            <div class="form-group">
                <label for="price">Preço:</label>
                <input type="number" name="price" class="form-control" value="{{ $game->price }}" required
                    min="0" step="0.01">
                @error('price')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="open_at">Data de Abertura:</label>
                <input type="date" name="open_at" class="form-control" value="{{ $game->open_at }}" required>
                @error('open_at')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="close_at">Data de Fechamento:</label>
                <input type="date" name="close_at" class="form-control" value="{{ $game->close_at }}" required>
                @error('close_at')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select class="form-control" name="status" required>
                    @foreach (['OPENED', 'CLOSED', 'FINISHED'] as $status)
                        <option value="{{ $status }}" {{ $game->status == $status ? 'selected' : '' }}>
                            {{ __($status) }}</option>
                    @endforeach
                </select>
                @error('status')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>


            @if (isset($awards))
                <h3>Prêmios</h3>
                <div id="awards-container">
                    @foreach ($awards as $award)
                        <div class="award-item">
                            <div class="form-group">
                                <label for="awards[0][name]">Nome do prêmio:</label>
                                <input type="text" id="award_name" name="awards[0][name]" class="form-control" required
                                    value="{{ $award->name }}">
                            </div>

                            <div class="form-group" id="awards[0][condition_type_div]">
                                <label for="awards[0][condition_type]">Tipo de Condição:</label>
                                <select name="awards[0][condition_type]" class="form-control awards[0][condition_type]"
                                    required>
                                    <option {{ $award->type == 'EXACT_POINT' ? '' : 'selected' }} value="EXACT_POINT">Ponto
                                        Exato</option>
                                    <option {{ $award->type == 'LOWEST_POINT' ? '' : 'selected' }} value="LOWEST_POINT">Pé
                                        Frio</option>
                                    <option {{ $award->type == 'WINNER' ? '' : 'selected' }} value="WINNER">Vencedor
                                    </option>
                                </select>
                            </div>

                            <div class="form-group" id="awards[0][exact_point_div]">
                                <label for="awards[0][exact_point_value]">Quantos Pontos? (se aplicável):</label>
                                <input type="number" name="awards[0][exact_point_value]" class="form-control"
                                    value="{{ $award->exact_point_value }}">
                            </div>

                            <div class="form-group" id="awards[0][winner_point_div]">
                                <label for="awards[0][winner_point_value]">Quantos Pontos para ser vencedor (se
                                    aplicável):</label>
                                <input type="number" name="awards[0][winner_point_value]" class="form-control"
                                    value="{{ $award->winner_point_value }}">
                            </div>

                            <div class="form-group awards[0][value_point_div]">
                                <label for="awards[0][amount]">Valor do Prêmio:</label>
                                <input type="number" name="awards[0][amount]" class="form-control" step="0.01"
                                    value="{{ $award->amount }}" required>
                            </div>
                            <button type="button" class="btn btn-danger remove-award">Remover Prêmio</button>
                            <button type="button" class="btn btn-secondary" id="add-award">Adicionar Prêmio</button>
                            <br><br>
                        </div>
                    @endforeach
                </div>
            @endif

            <button type="submit" class="btn btn-primary">Atualizar Jogo</button>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let awardIndex = 1;

            // Função para atualizar a visibilidade das divs
            function updateDivVisibility(awardIndex) {
                const conditionTypeSelect = document.querySelector(
                    `[name="awards[${awardIndex}][condition_type]"]`);
                const exactPointDiv = document.querySelector(`#awards\\[${awardIndex}\\]\\[exact_point_div\\]`);
                const winnerPointDiv = document.querySelector(`#awards\\[${awardIndex}\\]\\[winner_point_div\\]`);

                // Verifica se os elementos existem para evitar erros
                if (conditionTypeSelect && exactPointDiv && winnerPointDiv) {
                    const selectedValue = conditionTypeSelect.value;

                    // Esconde todas as divs inicialmente
                    exactPointDiv.style.display = 'none';
                    winnerPointDiv.style.display = 'none';

                    // Mostra a div apropriada com base no valor selecionado
                    if (selectedValue === 'EXACT_POINT') {
                        exactPointDiv.style.display = 'block';
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
                newAward.querySelector(`select[name="awards[${awardIndex}][condition_type]"]`)
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
            document.querySelectorAll('[name^="awards[0][condition_type]"]').forEach(function(select, index) {
                select.addEventListener('change', function() {
                    updateDivVisibility(index);
                });
            });
        });
    </script>
@endsection
