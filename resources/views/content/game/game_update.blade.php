@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Jogo')

@section('content')

<div class="container">
  <h2>Editar Jogo: {{ $game->name }}</h2>

  <!-- Exibição da mensagem de erro geral -->
  @if ($errors->has('error'))
  <div class="alert alert-danger">
    {{ $errors->first('error') }}
  </div>
  @endif


  <form action="{{ route('game-update', $game->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- Campos do Jogo -->
    <div class="form-group">
      <label for="name">Nome do Jogo:</label>
      <input type="text" name="name" class="form-control" value="{{ $game->name }}" required>
    </div>

    <div class="form-group">
      <label for="price">Preço:</label>
      <input type="number" name="price" class="form-control" value="{{ $game->price }}" required min="0" step="0.01">
      @error('price')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
    <div class="form-group">
      <label for="open_at">Data de Abertura:</label>
      <input type="date" name="open_at" class="form-control" value="{{ $game->open_at }}" required>
      @error('open_at')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
    <div class="form-group">
      <label for="close_at">Data de Fechamento:</label>
      <input type="date" name="close_at" class="form-control" value="{{ $game->close_at }}" required>
      @error('close_at')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
    <div class="form-group">
      <label for="status">Status:</label>
      <select class="form-control" name="status" required>
        <option value="OPENED" {{ $game->status == 'OPENED' ? 'selected' : '' }}>OPENED</option>
        <option value="CLOSED" {{ $game->status == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
      </select>
      @error('status')
      <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>



    <button type="submit" class="btn btn-primary">Atualizar Jogo</button>
  </form>

</div>

@endsection