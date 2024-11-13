@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Usuário')

@section('content')

<div class="container">
  <h2>Editar Usuário: {{ $user->name }}</h2>

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
      <label for="email">Email:</label>
      <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
      @error('email')
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

    <button type="submit" class="btn btn-primary">Atualizar Usuário</button>
  </form>
</div>

@endsection