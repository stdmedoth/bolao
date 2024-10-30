@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Jogo')

@section('content')

<div class="container">
  <h2>Editar Jogo: {{ $game->name }}</h2>
  <form action="{{ route('update-game', $game->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
      <label for="name">Nome do Jogo:</label>
      <input type="text" name="name" class="form-control" value="{{ $game->name }}" required>
    </div>

    <div class="form-group">
      <label for="price">Preço:</label>
      <input type="number" name="price" class="form-control" value="{{ $game->price }}" required min="0" step="0.01">
    </div>

    <div class="form-group">
      <label for="open_at">Data de Abertura:</label>
      <input type="date" name="open_at" class="form-control" value="{{ $game->open_at->format('Y-m-d') }}" required>
    </div>

    <div class="form-group">
      <label for="closed_at">Data de Fechamento:</label>
      <input type="date" name="closed_at" class="form-control" value="{{ $game->closed_at->format('Y-m-d') }}" required>
    </div>

    <div class="form-group">
      <label for="status">Status:</label>
      <select class="form-control" name="status" required>
        <option value="active" {{ $game->status == 'active' ? 'selected' : '' }}>Active</option>
        <option value="pending" {{ $game->status == 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="closed" {{ $game->status == 'closed' ? 'selected' : '' }}>Closed</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Atualizar Jogo</button>
  </form>
</div>

@endsection
