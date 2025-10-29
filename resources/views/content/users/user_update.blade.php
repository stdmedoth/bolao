@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Usuário')

@section('content')

    <div class="container">
        <h2>Editar Usuário: {{ $user->name }}</h2>


        <!-- Exibição da mensagem de erro geral -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Exibição de erros de validação -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Exibição da mensagem de erro geral -->
        @if ($errors->has('error'))
            <div class="alert alert-danger">
                {{ $errors->first('error') }}
            </div>
        @endif

        <form action="{{ route('user-update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')


            <div class="form-group">
                <label for="name">Nome:</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="document">CPF:</label>
                <input id="document" maxlength="14" type="document" name="document" class="form-control"
                    value="{{ $user->document }}" required>
                @error('document')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>


            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                @error('email')
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
                    value="{{ $user->phone }}" required placeholder="Digite o telefone">
                @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>


            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" name="password" class="form-control"
                    placeholder="Deixe em branco se não quiser alterar">
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            @if (auth()->user()->role->level_id == 'admin')
                <div class="form-group">
                    <label for="role">Tipo de usuário:</label>
                    <select class="form-control" name="role_user_id" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ $user->role_user_id == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_user_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            <!-- Convidado por -->
            @if (auth()->user()->role->level_id == 'admin')
                <div class="form-group">
                    <label for="invited_by_id">Convidado por:</label>
                    <select class="form-control" name="invited_by_id">
                        <option value="" disabled selected>Selecione o vendedor que convidou</option>
                        @foreach ($sellers as $seller)
                            <option value="{{ $seller->id }}"
                                {{ $user->invited_by_id == $seller->id ? 'selected' : '' }}>{{ $seller->name }}</option>
                        @endforeach
                    </select>
                    @error('invited_by_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            @if (auth()->user()->role->level_id == 'admin')
                <div class="form-group">
                    <label for="active_refer_earn">Possui Indique Ganhe?:</label>
                    <select class="form-control" name="active_refer_earn">
                        <option value="1" {{ $user->active_refer_earn == 1 ? 'selected' : '' }}>Sim</option>
                        <option value="0" {{ $user->active_refer_earn == 0 ? 'selected' : '' }}>Não</option>
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
                            <label for="game_credit" class="form-label">Limite atual de Credito (para Jogar)</label>
                            <input type="text" class="form-control" id="game_credit" inputmode="numeric"
                                name="game_credit" placeholder="Digite o valor"
                                value="{{ number_format($user->game_credit, 2, '.', '') }}" required>
                        </div>
                    </div>

                    <script>
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
                        });
                    </script>

                    <div class="col">
                        <div class="form-group">
                            <label for="game_credit_limit" class="form-label">Limite Inicial de Credito (para Jogar)</label>
                            <i id="edit_limit" class="bx bx-edit-alt me-1"></i>
                            <input type="text" class="form-control" disabled id="game_credit_limit" inputmode="numeric"
                                name="game_credit_limit" placeholder="Digite o valor"
                                value="{{ number_format($user->game_credit_limit, 2, '.', '') }}" required>
                        </div>

                        <script>
                            const gameCreditLimitInput = document.getElementById('game_credit_limit');
                            const editLimitElement = document.getElementById('edit_limit');

                            // Função para aplicar a máscara de Real
                            function formatToBRL(value) {
                                let cleanValue = value.replace(/\D/g, ''); // Remove caracteres não numéricos
                                let formattedValue = (cleanValue / 100).toLocaleString('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL',
                                });
                                return formattedValue.replace('R$', '').trim();
                            }

                            editLimitElement.addEventListener('click', () => {
                                gameCreditLimitInput.removeAttribute('disabled');
                            });

                            // Evento de input para aplicar a máscara ao digitar
                            gameCreditLimitInput.addEventListener('input', () => {
                                let cursorPosition = gameCreditLimitInput.selectionStart;
                                let formattedValue = formatToBRL(gameCreditLimitInput.value);
                                gameCreditLimitInput.value = formattedValue;
                                gameCreditLimitInput.setSelectionRange(formattedValue.length, formattedValue.length);
                            });
                        </script>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="limit_restart_button" class="form-label">Vendedor pagou o crédito?</label>
                            <a href="{{ route('user_limit_credit_restart', $user->id) }}" name="limit_restart_button"
                                class="btn btn-success form-control">Reiniciar Limite</a>
                        </div>

                    </div>

                    <div class="form-group">
                        <label for="balance" class="form-label">Saldo para sacar</label>
                        <input type="text" class="form-control" id="balance" inputmode="numeric" name="balance"
                            placeholder="Digite o valor" value="{{ number_format($user->balance, 2, '.', '') }}"
                            required>
                    </div>

                    <script>
                        var event = document.createEvent('Event');
                        event.initEvent('input', true, false);

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
                            value="{{ number_format($user->comission_percent, 2, '.', '') }}" required>
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



                        balanceInput.value = balanceInput.value.replace(",", ".").replace(".", ",");
                        comissionPercentInput.value = comissionPercentInput.value.replace(",", ".").replace(".", ",");
                        gameCreditInput.value = gameCreditInput.value.replace(",", ".").replace(".", ",");

                        //balanceInput.dispatchEvent(event);
                        //gameCreditInput.dispatchEvent(event);
                        //comissionPercentInput.dispatchEvent(event);
                    </script>
            @endif


            <button type="submit" class="btn btn-primary">Atualizar Usuário</button>
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
