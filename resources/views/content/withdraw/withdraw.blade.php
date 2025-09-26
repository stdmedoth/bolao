@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

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
        <h1 class="my-4">Saque</h1>


        <!-- Exibição da mensagem de erro geral -->
        @if ($errors->has('error'))
            <div class="alert alert-danger">
                {{ $errors->first('error') }}
            </div>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const amountInput = document.getElementById('amount');
                const errorMessage = document.getElementById('error-message');

                // Função para aplicar a máscara de Real
                function formatToBRL(value) {
                    // Uma pequena melhoria: se o valor estiver vazio, retorne uma string vazia.
                    if (!value) {
                        return '';
                    }

                    let cleanValue = value.replace(/\D/g, ''); // Remove caracteres não numéricos
                    let formattedValue = (cleanValue / 100).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                    });
                    return formattedValue.replace('R$', '').trim();
                }

                // Evento de input para aplicar a máscara ao digitar
                amountInput.addEventListener('input', () => {
                    let formattedValue = formatToBRL(amountInput.value);
                    amountInput.value = formattedValue;

                    // CORREÇÃO: Mova o cursor para o final do novo valor formatado
                    amountInput.setSelectionRange(formattedValue.length, formattedValue.length);
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

                document.querySelector('form').addEventListener('submit', (e) => {
                    const rawValue = amountInput.value.replace(/[^\d,.-]/g, '').replace(',', '.');
                    amountInput.value = rawValue; // Envia como float-friendly (e.g., "1234.56")
                });
            });
        </script>
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('transactions.saque') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="amount" class="form-label">Valor do Saque</label>
                        <input class="form-control" id="amount" name="amount" type="text"
                            placeholder="Digite o valor" value="{{ session('amount', old('amount')) ?? '0,00' }}" required>
                        <small class="text-danger" id="error-message" style="display: none;">O valor deve ser no
                            mínimo R$ 5,00.</small>
                    </div>
                    @error('amount')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <div class="form-group">
                        <label for="payment_method" class="form-label">Forma de Pagamento</label>
                        <select class="form-control" name="payment_method" id="payment_method">
                            <option value="pix">Pix</option>
                        </select>
                        @error('payment_method')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                    </div>

                    <div class="form-group">
                        <label for="pix_key" class="form-label">Chave Pix</label>
                        <input type="text" class="form-control" name="pix_key" placeholder="Digite sua Chave PIX">
                    </div>
                    @error('pix_key')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                    <div class="form-group">
                        <label for="pix_key_type" class="form-label">Tipo de Pix</label>
                        <select class="form-control" name="pix_key_type" id="pix_key_type">
                            <option value="CPF">CPF</option>
                            <option value="CNPJ">CNPJ</option>
                            <option value="EMAIL">E-mail</option>
                            <option value="PHONE">Telefone</option>
                            <option value="EVP">Chave Aleatória</option>
                        </select>
                    </div>
                    @error('pix_key_type')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <!-- Botão separado -->
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">Sacar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
