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

    <!-- Documento -->
    <div class="form-group">
      <label for="document">Documento:</label>
      <input type="text" id="document" maxlength="14" name="document" class="form-control" value="{{ old('document') }}" required placeholder="Digite o documento">
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
      <input type="text" maxlength="11" name="phone" onkeyup="handlePhone(event)" class="form-control" value="{{ old('phone') }}" required placeholder="Digite o telefone">
      @error('phone')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <!-- Senha -->
    <div class="form-group">
      <label for="password">Senha (mínimo 6 caracteres):</label>
      <input type="password" minlength="6" name="password" class="form-control" required placeholder="Digite a senha">
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


    <!-- Convidado por -->
    <div class="form-group">
      <label for="invited_by_id">Convidado por:</label>
      <select class="form-control" name="invited_by_id">
        <option value="" disabled selected>Selecione o vendedor que convidou</option>
        @foreach ($sellers as $seller)
        <option value="{{ $seller->id }}" {{ old('invited_by_id') == $seller->id ? 'selected' : '' }}>{{ $seller->name }}</option>
        @endforeach
      </select>
      @error('invited_by_id')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

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