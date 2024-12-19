@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Register -->
      <div class="card px-sm-6 px-0">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center">
            <a href="{{url('/')}}" class="app-brand-link gap-2">
              <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
              <span class="app-brand-text demo text-heading fw-bold">{{config('variables.templateName')}}</span>
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-1">Bem vindo à {{config('variables.templateName')}}! 👋</h4>
          <p class="mb-6">Por favor, faça login na sua conta e comece a aventura</p>

          <form id="formAuthentication" class="mb-6" action="{{url('/auth/login-basic')}}" method="POST">
            <div class="mb-6">
              <label for="email" class="form-label">Email</label>
              <input type="text" class="form-control" id="email" name="email" placeholder="Insira seu email" autofocus>
            </div>
            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password">Senha</label>
              <input type="hidden" name="_token" value="{{ csrf_token() }}" />
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password" placeholder="••••••••••••" aria-describedby="password" />
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

            @if(count($errors) > 0)
            @foreach( $errors->all() as $message )
            <div class="alert alert-danger display-hide">
              <span>{{ $message }}</span>
            </div>
            @endforeach
            @endif
            <div class="mb-8">
              <div class="d-flex justify-content-between mt-8">
                <a href="{{url('auth/forgot-password-basic')}}">
                  <span>Esqueceu sua senha?</span>
                </a>
              </div>
            </div>
            <div class="mb-6">
              <button class="btn btn-primary d-grid w-100" type="submit">Entrar</button>
            </div>
          </form>

          <p class="text-center">
            <span>Novo na plataforma?</span>
            <a href="{{url('auth/register-basic')}}">
              <span>Criar uma conta</span>
            </a>
          </p>
        </div>
      </div>
    </div>
    <!-- /Register -->
  </div>
</div>
@endsection