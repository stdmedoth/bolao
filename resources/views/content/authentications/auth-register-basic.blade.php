@extends('layouts/blankLayout')

@section('title', 'Register Basic - Pages')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection


@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register Card -->
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-6">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">@include('_partials.macros', [
                                    'width' => 25,
                                    'withbg' => 'var(--bs-primary)',
                                ])</span>
                                <span
                                    class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-1">A aventura começa aqui 🚀</h4>
                        <p class="mb-6">Torne o gerenciamento da suas apostas fácil e divertido!</p>

                        <form id="formAuthentication" class="mb-6" action="{{ route('register-validate') }}"
                            method="POST">
                            <div class="mb-6">
                                <label for="username" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Insira seu nome" autofocus>

                                <div class="mb-6">
                                    <label for="document" class="form-label">CPF</label>
                                    <input id="document" maxlength="14" type="text" class="form-control" id="document"
                                        oninput="cpf" name="document" placeholder="Insira seu documento" autofocus>
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


                                <div class="mb-6">
                                    <label for="phone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" id="phone" maxlength="15" name="phone"
                                        onkeyup="handlePhone(event)" placeholder="Insira seu telefone" autofocus>
                                </div>

                                <div class="mb-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" class="form-control" id="email" name="email"
                                        placeholder="Insira seu email">
                                </div>
                                <div class="mb-6 form-password-toggle">
                                    <label class="form-label" for="password">Senha</label>
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" class="form-control" name="password"
                                            placeholder="••••••••••••" aria-describedby="password" />
                                        <span id="hidepass" class="input-group-text cursor-pointer">
                                            <i class="bx bx-hide" id="icon"></i>
                                        </span>
                                    </div>
                                </div>

                                <script>
                                    const passwordInput = document.getElementById('password');
                                    const toggleButton = document.getElementById('hidepass');
                                    const icon = document.getElementById('icon');

                                    toggleButton.addEventListener('click', () => {
                                        if (passwordInput.type === 'password') {
                                            passwordInput.type = 'text';
                                            icon.classList.remove('bx-hide');
                                            icon.classList.add('bx-show');
                                        } else {
                                            passwordInput.type = 'password';
                                            icon.classList.remove('bx-show');
                                            icon.classList.add('bx-hide');
                                        }
                                    });
                                </script>

                                @if (count($errors) > 0)
                                    @foreach ($errors->all() as $message)
                                        <div class="alert alert-danger display-hide">
                                            <span>{{ $message }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                <!-- Exibição da mensagem de erro geral -->
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <input type="hidden" id="refered_by_id" class="form-control" name="refered_by_id"
                                    {{ isset($refered_by_id) ? 'disabled' : '' }}
                                    value="{{ isset($refered_by_id) ? $refered_by_id : '' }}" />

                                <button class="btn btn-primary d-grid w-100">
                                    Registrar
                                </button>
                        </form>

                        <p class="text-center">
                            <span>Já tem uma Conta?</span>
                            <a href="{{ url('auth/login-basic') }}">
                                <span>Se logue</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- Register Card -->
            </div>
        </div>
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
