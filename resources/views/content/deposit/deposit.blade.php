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
        <h1 class="my-4">Depositar</h1>
        <?php
        $payment_method = session('payment_method') ?? 'pix';
        $pix = session('pix') ?? null;
        $copy_paste = session('copy_paste') ?? null;
        ?>

        <!-- Exibição da mensagem de erro geral -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Exibição da mensagem de erro geral -->
        @if (count($errors->all()))
            @foreach ($errors->all() as $error)
                <div class="alert alert-danger">
                    {{ $error }}
                </div>
            @endforeach
        @endif

        <!-- Basic Bootstrap Table -->
        <div class="card">

            <div class="card-body">
                @if ($payment_method == 'credit_card')
                    <form id="depositForm" action="{{ route('transactions.deposit-create-credit-card') }}" method="POST">
                @endif
                @if ($payment_method == 'pix')
                    <form id="depositForm" action="{{ route('deposit-create-pix') }}" method="POST">
                @endif
                <div class="row">
                    @if (isset($pix))
                        <div class="col" id="pix_qrcode_image">
                            <img src="data:image/jpeg;base64, {{ $pix }}" />
                            <p id="pix_data_copy">{{ $copy_paste }}</p>
                            <button onclick="copyToClipboard()">Copiar</button>
                        </div>
                    @endif

                    <div class="col">
                        <div class="row">
                            @csrf
                            <div class="form-group">
                                <label for="amount" class="form-label">Valor do Depósito</label>
                                <input type="text" class="form-control" id="amount" name="amount"
                                    placeholder="Digite o valor" value="{{ session('amount', old('amount')) }}" required>
                                <small class="text-danger" id="error-message" style="display: none;">O valor deve ser no
                                    mínimo R$ 5,00.</small>
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


                            <div class="form-group">
                                <label for="payment_method" class="form-label">Forma de Pagamento</label>
                                <select class="form-control" name="payment_method" id="payment_method">
                                    <option {{ $payment_method == 'credit_card' ? 'selected' : '' }} value="credit_card">
                                        Cartão Credito</option>
                                    <option {{ $payment_method == 'pix' ? 'selected' : '' }} value="pix">Pix</option>
                                </select>
                                @error('payment_method')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div id="credit_card_address_info" class="card">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Celular (DDD)</label>
                                    <input class="form-control" type="text" name="phone"
                                        value="{{ auth()->user()->phone }}">
                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="postal_code" class="form-label">CEP</label>
                                    <input class="form-control" type="text" name="postal_code"
                                        value="{{ auth()->user()->postal_code }}">
                                    @error('postal_code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="address_number" class="form-label">Número (Endereço)</label>
                                    <input class="form-control" type="text" name="address_number"
                                        value="{{ auth()->user()->address_number }}">
                                    @error('address_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col">
                        <div id="credit_card_infos" class="card">
                            <div class="form-group">
                                <label for="cc_name" class="form-label">Nome no cartão</label>
                                <input class="form-control" type="text" name="cc_name"
                                    value="{{ auth()->user()->cc_name }}">
                                @error('cc_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="cc_number" class="form-label">Número no cartão</label>
                                <input class="form-control" type="text" name="cc_number"
                                    value="{{ auth()->user()->cc_number }}">
                                @error('cc_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="cc_expiry_month" class="form-label">Mês de expiração</label>
                                <input class="form-control" type="number" name="cc_expiry_month"
                                    value="{{ auth()->user()->cc_expiry_month }}">
                                @error('cc_expirity_month')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="cc_expiry_year" class="form-label">Ano de expiração</label>
                                <input class="form-control" type="number" name="cc_expiry_year"
                                    value="{{ auth()->user()->cc_expiry_year }}">
                                @error('cc_expiry_year')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="cc_ccv" class="form-label">Código de Segurança</label>
                                <input class="form-control" type="number" name="cc_ccv"
                                    value="{{ auth()->user()->cc_ccv }}">
                                @error('cc_ccv')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Depositar</button>
                </form>
            </div>
        </div>

        <script>
            function update_paymethod_form() {
                const form = document.getElementById('depositForm');
                const selectedMethod = document.getElementById('payment_method').value;

                if (selectedMethod === 'pix') {
                    form.action = "{{ route('deposit-create-pix') }}";
                    if (document.getElementById("pix_qrcode_image")) {
                        document.getElementById("pix_qrcode_image").style.display = 'block';
                    }
                    document.getElementById("credit_card_infos").style.display = 'none';
                    document.getElementById("credit_card_address_info").style.display = 'none';

                } else if (selectedMethod === 'credit_card') {
                    form.action = "{{ route('transactions.deposit-create-credit-card') }}";
                    if (document.getElementById("pix_qrcode_image")) {
                        document.getElementById("pix_qrcode_image").style.display = 'none';
                    }
                    document.getElementById("credit_card_infos").style.display = 'block';
                    document.getElementById("credit_card_address_info").style.display = 'block';
                }
            }
            update_paymethod_form();

            document.getElementById('payment_method').addEventListener('change', update_paymethod_form);

            function copyToClipboard() {

                text = document.getElementById("pix_data_copy").innerHTML;

                // Cria um elemento temporário para armazenar o texto
                const tempInput = document.createElement("textarea");
                tempInput.value = text;
                document.body.appendChild(tempInput);

                // Seleciona o texto no elemento temporário
                tempInput.select();
                tempInput.setSelectionRange(0, 99999); // Para compatibilidade com dispositivos móveis

                // Copia o texto para a área de transferência
                document.execCommand("copy");

                // Remove o elemento temporário
                document.body.removeChild(tempInput);

                // Exibe uma mensagem de sucesso no console
                console.log("Texto copiado: " + text);
            }

            document.querySelector('form').addEventListener('submit', (e) => {
                const rawValue = amountInput.value.replace(/[^\d,.-]/g, '').replace(',', '.');
                amountInput.value = rawValue; // Envia como float-friendly (e.g., "1234.56")
            });
        </script>

        <style>
            #credit_card_infos.card {
                background-color: #f9f9f9;
                /* Cor de fundo neutra */
                border: 1px solid #ddd;
                /* Borda sutil */
                border-radius: 8px;
                /* Cantos arredondados */
                padding: 16px;
                /* Espaçamento interno */
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                /* Sombra para dar um efeito 3D */
                max-width: 400px;
                /* Largura máxima para centralizar a atenção */
                margin: 16px auto;
                /* Centraliza horizontalmente na página */
            }

            #credit_card_infos .form-group {
                margin-bottom: 16px;
                /* Espaçamento entre os campos */
            }

            #credit_card_infos .form-label {
                font-weight: bold;
                /* Dá destaque aos labels */
                display: block;
                /* Garante que os labels fiquem acima dos inputs */
                margin-bottom: 8px;
                /* Espaçamento abaixo dos labels */
            }

            #credit_card_infos .form-control {
                width: 100%;
                /* Campo ocupa toda a largura disponível */
                padding: 8px;
                /* Espaçamento interno dos inputs */
                font-size: 14px;
                /* Tamanho de fonte confortável */
                border: 1px solid #ccc;
                /* Borda leve */
                border-radius: 4px;
                /* Cantos arredondados */
            }

            #credit_card_infos .form-control:focus {
                border-color: #007bff;
                /* Cor azul ao focar no campo */
                box-shadow: 0 0 4px rgba(0, 123, 255, 0.25);
                /* Efeito de foco */
                outline: none;
                /* Remove o outline padrão */
            }
        </style>
    </div>
@endsection
