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
      <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
      @error('name')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <div class="form-group">
      <label for="document">CPF:</label>
      <input id="document" maxlength="14" type="document" name="document" class="form-control" value="{{ $user->document }}" required>
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
      <input type="text" maxlength="15" name="phone" onkeyup="handlePhone(event)" class="form-control" value="{{ $user->phone }}" required placeholder="Digite o telefone">
      @error('phone')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>


    <div class="form-group">
      <label for="password">Senha:</label>
      <input type="password" name="password" class="form-control" placeholder="Deixe em branco se não quiser alterar">
      @error('password')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    @if (auth()->user()->role->level_id == 'admin')
    <div class="form-group">
      <label for="role">Tipo de usuário:</label>
      <select class="form-control" name="role_user_id" required>
        @foreach ($roles as $role)
        <option value="{{ $role->id }}" {{ $user->role_user_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
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
        <option value="{{ $seller->id }}" {{ old('invited_by_id') == $seller->id ? 'selected' : '' }}>{{ $seller->name }}</option>
        @endforeach
      </select>
      @error('invited_by_id')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
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