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
                            <div class="pix-container">
                                <div class="pix-header">
                                    <h5 class="mb-3">
                                        <i class="bx bx-qr-scan me-2"></i>
                                        Pagamento via PIX
                                    </h5>
                                    <p class="text-muted mb-4">Escaneie o QR Code ou copie o código PIX</p>
                                </div>
                                
                                <div class="qr-code-wrapper">
                                    <img src="data:image/jpeg;base64, {{ $pix }}" class="qr-code-image" alt="QR Code PIX" />
                                </div>
                                
                                <div class="pix-code-section">
                                    <label class="form-label fw-bold mb-2">Código PIX (Copiar e Colar)</label>
                                    <div class="input-group mb-3">
                                        <textarea class="form-control pix-code-text" id="pix_data_copy" readonly rows="3">{{ $copy_paste }}</textarea>
                                        <button class="btn btn-primary copy-btn" onclick="copyToClipboard()" type="button">
                                            <i class="bx bx-copy me-1"></i>
                                            Copiar
                                        </button>
                                    </div>
                                    <div class="copy-success-message" id="copySuccessMessage" style="display: none;">
                                        <i class="bx bx-check-circle text-success me-1"></i>
                                        <span class="text-success">Código PIX copiado com sucesso!</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col">
                        <div class="row">
                            @csrf
                            <div class="form-group">
                                <label for="amount" class="form-label">Valor do Depósito</label>
                                <input type="text" class="form-control" id="amount" name="amount"
                                    placeholder="Digite o valor" value="{{ session('amount', old('amount')) ?? '0,00' }}"
                                    required>
                                <small class="text-danger" id="error-message" style="display: none;">O valor deve ser no
                                    mínimo R$ 5,00.</small>
                            </div>

                            <script>
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
                const text = document.getElementById("pix_data_copy").value;
                const copyBtn = document.querySelector('.copy-btn');
                const successMessage = document.getElementById('copySuccessMessage');

                // Cria um elemento temporário para armazenar o texto
                const tempInput = document.createElement("textarea");
                tempInput.value = text;
                document.body.appendChild(tempInput);

                // Seleciona o texto no elemento temporário
                tempInput.select();
                tempInput.setSelectionRange(0, 99999); // Para compatibilidade com dispositivos móveis

                try {
                    // Copia o texto para a área de transferência
                    document.execCommand("copy");
                    
                    // Feedback visual
                    copyBtn.innerHTML = '<i class="bx bx-check me-1"></i>Copiado!';
                    copyBtn.classList.remove('btn-primary');
                    copyBtn.classList.add('btn-success');
                    
                    successMessage.style.display = 'block';
                    
                    // Volta ao estado original após 2 segundos
                    setTimeout(() => {
                        copyBtn.innerHTML = '<i class="bx bx-copy me-1"></i>Copiar';
                        copyBtn.classList.remove('btn-success');
                        copyBtn.classList.add('btn-primary');
                        successMessage.style.display = 'none';
                    }, 2000);
                    
                } catch (err) {
                    console.error('Erro ao copiar: ', err);
                    alert('Erro ao copiar o código PIX. Tente selecionar e copiar manualmente.');
                }

                // Remove o elemento temporário
                document.body.removeChild(tempInput);
            }

            document.querySelector('form').addEventListener('submit', (e) => {
                const rawValue = amountInput.value.replace(/[^\d,.-]/g, '').replace(',', '.');
                amountInput.value = rawValue; // Envia como float-friendly (e.g., "1234.56")
            });
        </script>

        <style>
            /* Estilos para a seção PIX */
            .pix-container {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 15px;
                padding: 25px;
                color: white;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                margin-bottom: 20px;
            }

            .pix-header h5 {
                color: white;
                font-weight: 600;
                margin-bottom: 10px;
            }

            .pix-header p {
                color: rgba(255, 255, 255, 0.8);
                font-size: 14px;
            }

            .qr-code-wrapper {
                text-align: center;
                margin: 20px 0;
                background: white;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .qr-code-image {
                max-width: 200px;
                width: 100%;
                height: auto;
                border-radius: 8px;
            }

            .pix-code-section {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 10px;
                padding: 20px;
                margin-top: 20px;
            }

            .pix-code-section .form-label {
                color: white;
                font-weight: 600;
                margin-bottom: 10px;
            }

            .pix-code-text {
                background: white;
                border: 2px solid rgba(255, 255, 255, 0.2);
                border-radius: 8px;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                resize: none;
                color: #333;
            }

            .pix-code-text:focus {
                border-color: #fff;
                box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
                outline: none;
            }

            .copy-btn {
                background: linear-gradient(45deg, #28a745, #20c997);
                border: none;
                border-radius: 8px;
                padding: 12px 20px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            }

            .copy-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
                background: linear-gradient(45deg, #218838, #1ea085);
            }

            .copy-btn:active {
                transform: translateY(0);
            }

            .copy-success-message {
                margin-top: 10px;
                font-size: 14px;
                font-weight: 500;
            }

            /* Estilos para cartão de crédito */
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

            /* Responsividade */
            @media (max-width: 768px) {
                .pix-container {
                    padding: 20px;
                }
                
                .qr-code-image {
                    max-width: 150px;
                }
                
                .copy-btn {
                    padding: 10px 16px;
                    font-size: 14px;
                }
            }
        </style>
    </div>
@endsection
