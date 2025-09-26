@extends('layouts/blankLayout')

@section('title', 'Forgot Password Basic - Pages')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">

                <!-- Forgot Password -->
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
                        <h4 class="mb-1">Esqueceu sua senha? 🔒</h4>
                        <form id="formAuthentication" class="mb-6" action="{{ url('/auth/reset-password') }}"
                            method="POST">
                            <input type="hidden" value="{{ $token }}" name="token">
                            <div class="mb-6">
                                <label for="email" class="form-label">Email</label>
                                <input readonly type="text" class="form-control" id="email" name="email"
                                    placeholder="Email" value="{{ $email }}" autofocus>
                            </div>
                            <div class="mb-6">
                                <label for="password" class="form-label">Senha</label>
                                <input type="text" class="form-control" id="password" name="password"
                                    placeholder="Insira sua senha" autofocus>
                            </div>
                            <div class="mb-6">
                                <label for="password_confirmation" class="form-label">Confirme a Senha</label>
                                <input type="text" class="form-control" id="password_confirmation"
                                    name="password_confirmation" placeholder="Insira sua senha" autofocus>
                            </div>
                            @if (isset($errors) && count($errors) > 0)
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
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <button class="btn btn-primary d-grid w-100">Recuperar senha</button>

                        </form>
                        <div class="text-center">
                            <a href="{{ url('auth/login-basic') }}" class="d-flex justify-content-center">
                                <i class="bx bx-chevron-left scaleX-n1-rtl me-1"></i>
                                Voltar para o login
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Forgot Password -->
            </div>
        </div>
    </div>
@endsection
