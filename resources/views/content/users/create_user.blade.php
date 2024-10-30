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
      <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Digite o nome">
      @error('name')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <!-- Email -->
    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="Digite o email">
      @error('email')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <!-- Senha -->
    <div class="form-group">
      <label for="password">Senha:</label>
      <input type="password" name="password" class="form-control" required placeholder="Digite a senha">
      @error('password')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <!-- Tipo de Usuário -->
    <div class="form-group">
      <label for="role_user_id">Tipo de Usuário:</label>
      <select class="form-control" name="role_user_id" required>
        <option value="" disabled selected>Selecione o tipo de usuário</option>
        @foreach ($roles as $role)
        <option value="{{ $role->id }}" {{ old('role_user_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
        @endforeach
      </select>
      @error('role_user_id')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <!-- Botão de Submissão -->
    <button type="submit" class="btn btn-primary mt-3">Criar Usuário</button>
  </form>
</div>
@endsection
