@extends('layouts/contentNavbarLayout')

@section('title', 'Criar Novo Concurso')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Concurso /</span> Criar Novo
            </h4>
        </div>
    </div>

    <!-- Mensagens de erro/sucesso -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('create-game') }}" method="POST" id="createGameForm">
        @csrf

        <!-- Informações do Jogo -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="bx bx-info-circle me-2"></i>Informações do Concurso
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Nome do Jogo -->
                    <div class="col-md-12">
                        <label for="name" class="form-label">Nome do Concurso <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" 
                               placeholder="Digite o nome do concurso" required
                               value="{{ old('name') }}">
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preço -->
                    <div class="col-md-6">
                        <label for="price" class="form-label">Preço por Aposta <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" name="price" id="price" class="form-control" 
                                   step="0.01" min="0" placeholder="0,00" required
                                   value="{{ old('price') }}">
                        </div>
                        @error('price')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Data de Abertura -->
                    <div class="col-md-6">
                        <label for="open_at" class="form-label">Data de Abertura <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="open_at" id="open_at" class="form-control" required
                               value="{{ old('open_at') }}">
                        @error('open_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Data de Fechamento -->
                    <div class="col-md-6">
                        <label for="close_at" class="form-label">Data de Fechamento <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="close_at" id="close_at" class="form-control" required
                               value="{{ old('close_at') }}">
                        @error('close_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Prêmios -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="bx bx-trophy me-2"></i>Prêmios do Concurso
                </h5>
                <button type="button" class="btn btn-sm btn-primary" id="add-award">
                    <i class="bx bx-plus me-1"></i>Adicionar Prêmio
                </button>
            </div>
            <div class="card-body">
                <div id="awards-container">
                    <!-- Prêmio Template (inicialmente visível) -->
                    <div class="award-item card border mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bx bx-gift me-2"></i>Prêmio <span class="award-number">1</span>
                            </h6>
                            <button type="button" class="btn btn-sm btn-danger remove-award">
                                <i class="bx bx-trash me-1"></i>Remover
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Nome do Prêmio -->
                                <div class="col-md-12">
                                    <label for="awards[0][name]" class="form-label">Nome do Prêmio <span class="text-danger">*</span></label>
                                    <input type="text" name="awards[0][name]" id="awards[0][name]" 
                                           class="form-control" placeholder="Ex: Prêmio Principal" required
                                           value="{{ old('awards.0.name') }}">
                                </div>

                                <!-- Tipo de Condição -->
                                <div class="col-md-6" id="awards[0][condition_type_div]">
                                    <label for="awards[0][condition_type]" class="form-label">Tipo de Condição <span class="text-danger">*</span></label>
                                    <select name="awards[0][condition_type]" id="awards[0][condition_type]" 
                                            class="form-select award-condition-type" required>
                                        <option value="">Selecione...</option>
                                        <option value="EXACT_POINT" {{ old('awards.0.condition_type') == 'EXACT_POINT' ? 'selected' : '' }}>Ponto Exato</option>
                                        <option value="WINNER" {{ old('awards.0.condition_type') == 'WINNER' ? 'selected' : '' }}>Vencedor</option>
                                        <option value="SECONDARY_WINNER" {{ old('awards.0.condition_type') == 'SECONDARY_WINNER' ? 'selected' : '' }}>Vencedor Secundário</option>
                                    </select>
                                </div>

                                <!-- Valor do Prêmio -->
                                <div class="col-md-6">
                                    <label for="awards[0][amount]" class="form-label">Valor do Prêmio <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" name="awards[0][amount]" id="awards[0][amount]" 
                                               class="form-control" step="0.01" min="5" placeholder="0,00" required
                                               value="{{ old('awards.0.amount') }}">
                                    </div>
                                </div>

                                <!-- Pontos Exatos (condicional) -->
                                <div class="col-md-6" id="awards[0][exact_point_div]" style="display: none;">
                                    <label for="awards[0][exact_point_value]" class="form-label">Quantos Pontos? <span class="text-muted">(Ponto Exato)</span></label>
                                    <input type="number" name="awards[0][exact_point_value]" id="awards[0][exact_point_value]" 
                                           class="form-control" min="0" placeholder="Ex: 10"
                                           value="{{ old('awards.0.exact_point_value') }}">
                                </div>

                                <!-- Pontos Vencedor (condicional) -->
                                <div class="col-md-6" id="awards[0][winner_point_div]" style="display: none;">
                                    <label for="awards[0][winner_point_value]" class="form-label">Pontos para ser Vencedor <span class="text-muted">(Vencedor/Vencedor Secundário)</span></label>
                                    <input type="number" name="awards[0][winner_point_value]" id="awards[0][winner_point_value]" 
                                           class="form-control" min="0" placeholder="Ex: 10"
                                           value="{{ old('awards.0.winner_point_value') }}">
                                </div>

                                <!-- Apenas Primeiro Sorteio (condicional para Ponto Exato) -->
                                <div class="col-md-6" id="awards[0][only_on_first_round_div]" style="display: none;">
                                    <label for="awards[0][only_on_first_round]" class="form-label">Apenas Primeiro Sorteio?</label>
                                    <select name="awards[0][only_on_first_round]" id="awards[0][only_on_first_round]" class="form-select">
                                        <option value="0" {{ old('awards.0.only_on_first_round') == '0' ? 'selected' : '' }}>Não</option>
                                        <option value="1" {{ old('awards.0.only_on_first_round') == '1' ? 'selected' : '' }}>Sim</option>
                                    </select>
                                </div>

                                <!-- Prêmio no Final -->
                                <div class="col-md-6">
                                    <label for="awards[0][only_when_finish_round]" class="form-label">Quando Conceder o Prêmio?</label>
                                    <select name="awards[0][only_when_finish_round]" id="awards[0][only_when_finish_round]" class="form-select">
                                        <option value="0" {{ old('awards.0.only_when_finish_round') == '0' ? 'selected' : '' }}>Imediato (assim que atingir)</option>
                                        <option value="1" {{ old('awards.0.only_when_finish_round') == '1' ? 'selected' : '' }}>Somente no Final do Jogo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ url()->previous() }}" class="btn btn-label-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Criar Concurso
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let awardIndex = 1;

    // Função para atualizar a visibilidade dos campos condicionais
    function updateDivVisibility(index) {
        const conditionTypeSelect = document.querySelector(
            `[name="awards[${index}][condition_type]"]`
        );
        const exactPointDiv = document.querySelector(`#awards\\[${index}\\]\\[exact_point_div\\]`);
        const winnerPointDiv = document.querySelector(`#awards\\[${index}\\]\\[winner_point_div\\]`);
        const onlyOnFirstRoundDiv = document.querySelector(
            `#awards\\[${index}\\]\\[only_on_first_round_div\\]`
        );

        if (conditionTypeSelect && exactPointDiv && winnerPointDiv) {
            const selectedValue = conditionTypeSelect.value;

            // Esconde todas as divs condicionais
            exactPointDiv.style.display = 'none';
            winnerPointDiv.style.display = 'none';
            onlyOnFirstRoundDiv.style.display = 'none';

            // Mostra os campos apropriados baseado no tipo selecionado
            if (selectedValue === 'EXACT_POINT') {
                exactPointDiv.style.display = 'block';
                if (onlyOnFirstRoundDiv) {
                    onlyOnFirstRoundDiv.style.display = 'block';
                }
                // Limpa o campo de vencedor
                if (winnerPointDiv.querySelector('input')) {
                    winnerPointDiv.querySelector('input').value = '';
                }
            } else if (selectedValue === 'WINNER' || selectedValue === 'SECONDARY_WINNER') {
                winnerPointDiv.style.display = 'block';
                // Limpa os campos de ponto exato
                if (exactPointDiv.querySelector('input')) {
                    exactPointDiv.querySelector('input').value = '';
                }
                if (onlyOnFirstRoundDiv && onlyOnFirstRoundDiv.querySelector('select')) {
                    onlyOnFirstRoundDiv.querySelector('select').value = '0';
                    onlyOnFirstRoundDiv.style.display = 'none';
                }
            }
        }
    }

    // Adicionar novo prêmio
    document.getElementById('add-award').addEventListener('click', function() {
        const container = document.getElementById('awards-container');
        const template = document.querySelector('.award-item');
        const newAward = template.cloneNode(true);

        // Atualiza os atributos dos elementos clonados
        newAward.querySelectorAll('input, select, label, div[id], button').forEach(function(element) {
            // Atualiza IDs
            if (element.id) {
                element.id = element.id.replace(/\[\d+\]/g, `[${awardIndex}]`);
            }

            // Atualiza names
            if (element.name) {
                element.name = element.name.replace(/\[\d+\]/g, `[${awardIndex}]`);
            }

            // Atualiza for dos labels
            if (element.htmlFor) {
                element.htmlFor = element.htmlFor.replace(/\[\d+\]/g, `[${awardIndex}]`);
            }

            // Limpa valores de inputs
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.value = '';
            } else if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
            }
        });

        // Atualiza o número do prêmio
        const awardNumberSpan = newAward.querySelector('.award-number');
        if (awardNumberSpan) {
            awardNumberSpan.textContent = awardIndex + 1;
        }

        // Adiciona o evento de mudança para a nova seleção de tipo de condição
        const newConditionSelect = newAward.querySelector(`select[name="awards[${awardIndex}][condition_type]"]`);
        if (newConditionSelect) {
            newConditionSelect.addEventListener('change', function() {
                updateDivVisibility(awardIndex);
            });
        }

        // Esconde campos condicionais inicialmente
        updateDivVisibility(awardIndex);

        container.appendChild(newAward);
        awardIndex++;

        // Scroll suave para o novo prêmio
        newAward.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    // Remover prêmio (delegation de eventos)
    document.getElementById('awards-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-award')) {
            const awardItem = e.target.closest('.award-item');
            if (document.querySelectorAll('.award-item').length > 1) {
                if (confirm('Tem certeza que deseja remover este prêmio?')) {
                    awardItem.remove();
                    // Atualiza números dos prêmios
                    document.querySelectorAll('.award-item').forEach((item, index) => {
                        const numberSpan = item.querySelector('.award-number');
                        if (numberSpan) {
                            numberSpan.textContent = index + 1;
                        }
                    });
                }
            } else {
                alert('Você precisa ter pelo menos um prêmio!');
            }
        }
    });

    // Adiciona evento de mudança nos selects existentes
    document.querySelectorAll('.award-condition-type').forEach(function(select, index) {
        select.addEventListener('change', function() {
            // Extrai o índice do name do select
            const match = this.name.match(/\[(\d+)\]/);
            if (match) {
                updateDivVisibility(parseInt(match[1]));
            }
        });

        // Inicializa a visibilidade dos campos
        const match = select.name.match(/\[(\d+)\]/);
        if (match) {
            updateDivVisibility(parseInt(match[1]));
        }
    });

    // Validação do formulário antes de enviar
    document.getElementById('createGameForm').addEventListener('submit', function(e) {
        const awardItems = document.querySelectorAll('.award-item');
        let hasError = false;

        awardItems.forEach(function(item, index) {
            const conditionType = item.querySelector('[name*="[condition_type]"]').value;
            const amount = item.querySelector('[name*="[amount]"]').value;

            if (!conditionType) {
                alert(`Por favor, selecione o tipo de condição para o prêmio ${index + 1}.`);
                hasError = true;
            }

            if (!amount || parseFloat(amount) < 5) {
                alert(`O valor do prêmio ${index + 1} deve ser de pelo menos R$ 5,00.`);
                hasError = true;
            }
        });

        if (hasError) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<style>
.award-item {
    transition: all 0.3s ease;
}

.award-item:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-label .text-danger {
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .card-header .btn {
        margin-top: 0.5rem;
    }
}
</style>
@endsection
