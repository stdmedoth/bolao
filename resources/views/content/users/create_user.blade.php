@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Usuários')

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
        <h2>Criar Novo Usuário</h2>

        <!-- Exibição da mensagem de erro geral -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Exibição da mensagem de erro geral -->
        @if ($errors->has('error'))
            <div class="alert alert-danger">
                {{ $errors->first('error') }}
            </div>
        @endif


        <form action="{{ route('create-user') }}" method="POST">
            @csrf

            <!-- Nome do Usuário -->
            <div class="form-group">
                <label for="name">Nome do Usuário:</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required
                    placeholder="Digite o nome">
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required
                    placeholder="Digite o email">
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Documento -->
            <div class="form-group">
                <label for="document">Documento:</label>
                <input type="text" id="document" maxlength="14" name="document" class="form-control"
                    value="{{ old('document') }}" required placeholder="Digite o documento">
                @error('document')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <script>
                const handlePhone = (event) => {
                    let input = event.target
                    input.value = phoneMask(input.value)
                }

                const phoneMask = (value) => {
                    if (!value) return ""
                    value = value.replace(/\D/g, '')
                    value = value.replace(/(\d{2})(\d)/, "($1) $2")
                    value = value.replace(/(\d)(\d{4})$/, "$1-$2")
                    return value
                }
            </script>


            <!-- Phone -->
            <div class="form-group">
                <label for="phone">Telefone:</label>
                <input type="text" maxlength="15" name="phone" onkeyup="handlePhone(event)" class="form-control"
                    value="{{ old('phone') }}" required placeholder="Digite o telefone">
                @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Senha -->
            <div class="form-group">
                <label for="password">Senha (mínimo 6 caracteres):</label>
                <input type="password" minlength="6" name="password" class="form-control" required
                    placeholder="Digite a senha">
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            @if (auth()->user()->role->level_id == 'admin')
                <!-- Tipo de Usuário -->
                <div class="form-group">
                    <label for="role_user_id">Tipo de Usuário:</label>
                    <select id="role_user_id" class="form-control" name="role_user_id" required>
                        <option value="" disabled selected>Selecione o tipo de usuário</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_user_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_user_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif
            @if (auth()->user()->role->level_id == 'seller')
                <input type="hidden" name="role_user_id" value="{{ $gambler_role->id }}">
            @endif


            <!-- Convidado por -->
            @if (auth()->user()->role->level_id == 'admin')
                <div class="form-group">
                    <label for="invited_by_id">Convidado por:</label>
                    <select class="form-control" name="invited_by_id">
                        <option value="" disabled selected>Selecione o vendedor que convidou</option>
                        @foreach ($sellers as $seller)
                            <option value="{{ $seller->id }}"
                                {{ old('invited_by_id') == $seller->id ? 'selected' : '' }}>{{ $seller->name }}</option>
                        @endforeach
                    </select>
                    @error('invited_by_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif
            @if (auth()->user()->role->level_id == 'seller')
                <input type="hidden" name="invited_by_id" value="{{ auth()->user()->id }}">
            @endif


            @if (auth()->user()->role->level_id == 'admin')
                <div class="form-group">
                    <label for="active_refer_earn">Possui Indique Ganhe?:</label>
                    <select class="form-control" name="active_refer_earn">
                        <option value="1" {{ old('active_refer_earn') == 1 ? 'selected' : '' }}>Sim</option>
                        <option value="0" {{ old('active_refer_earn') == 0 ? 'selected' : '' }}>Não</option>
                        @error('invited_by_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </select>
                </div>
            @endif

            @if (auth()->user()->role->level_id == 'admin')
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="game_credit" class="form-label">Credito Atual (para Jogar) </label>
                            <input type="text" edited="false" class="form-control" id="game_credit" inputmode="numeric"
                                name="game_credit" placeholder="Digite o valor"
                                value="{{ session('', old('game_credit')) }}" required>
                        </div>

                        <script>
                            const editedCredit = false;
                            const gameCreditInput = document.getElementById('game_credit');

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
                            gameCreditInput.addEventListener('input', () => {
                                let cursorPosition = gameCreditInput.selectionStart;
                                let formattedValue = formatToBRL(gameCreditInput.value);
                                gameCreditInput.value = formattedValue;
                                gameCreditInput.setSelectionRange(formattedValue.length, formattedValue.length);
                                gameCreditInput.setAttribute('edited', 'true');
                            });
                        </script>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="game_credit_limit" class="form-label">Limite Inicial de Credito (para Jogar)</label>
                            <input type="text" class="form-control" id="game_credit_limit" inputmode="numeric"
                                name="game_credit_limit" placeholder="Digite o valor"
                                value="{{ session('game_credit_limit', old('game_credit_limit')) }}">
                        </div>

                        <script>
                            const gameCreditLimitInput = document.getElementById('game_credit_limit');

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
                            gameCreditLimitInput.addEventListener('input', () => {
                                let cursorPosition = gameCreditLimitInput.selectionStart;
                                let formattedValue = formatToBRL(gameCreditLimitInput.value);
                                gameCreditLimitInput.value = formattedValue;
                                gameCreditLimitInput.setSelectionRange(formattedValue.length, formattedValue.length);

                                const editedCredit = gameCreditInput.getAttribute('edited');
                                console.log(editedCredit);
                                if (editedCredit == 'false') {
                                    gameCreditInput.value = formattedValue;
                                    gameCreditInput.setSelectionRange(formattedValue.length, formattedValue.length);
                                }
                            });
                        </script>
                    </div>

                </div>

                <div class="form-group">
                    <label for="balance" class="form-label">Saldo para sacar</label>
                    <input type="text" class="form-control" id="balance" inputmode="numeric" name="balance"
                        placeholder="Digite o valor" value="{{ session('balance', old('balance')) }}" required>
                </div>

                <script>
                    const balanceInput = document.getElementById('balance');

                    // Evento de input para aplicar a máscara ao digitar
                    balanceInput.addEventListener('input', () => {
                        let cursorPosition = balanceInput.selectionStart;
                        let formattedValue = formatToBRL(balanceInput.value);
                        balanceInput.value = formattedValue;
                        balanceInput.setSelectionRange(formattedValue.length, formattedValue.length);
                    });
                </script>

                <div class="form-group">
                    <label for="comission_percent" class="form-label">Porcentagem de Comissão</label>
                    <input type="text" class="form-control" id="comission_percent" inputmode="comission_percent"
                        name="comission_percent" placeholder="Digite o valor"
                        value="{{ session('comission_percent', old('comission_percent')) }}" required>
                </div>

                <script>
                    const comissionPercentInput = document.getElementById('comission_percent');

                    // Evento de input para aplicar a máscara ao digitar
                    comissionPercentInput.addEventListener('input', () => {
                        let cursorPosition = comissionPercentInput.selectionStart;
                        let formattedValue = formatToBRL(comissionPercentInput.value);
                        comissionPercentInput.value = formattedValue;
                        comissionPercentInput.setSelectionRange(formattedValue.length, formattedValue.length);
                    });
                </script>
            @endif



            <!-- Botão de Submissão -->
            <button type="submit" class="btn btn-primary mt-3">Criar Usuário</button>
        </form>
    </div>


    <script>
        document.getElementById('document').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove caracteres não numéricos
            if (value.length > 3) value = value.replace(/(\d{3})(\d)/, '$1.$2');
            if (value.length > 6) value = value.replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
            if (value.length > 9) value = value.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
            e.target.value = value;
        });
    </script>

@endsection
