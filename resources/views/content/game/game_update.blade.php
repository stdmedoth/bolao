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

    <div id="awards-container">
        @foreach ($game->awards as $index => $award)
            <div class="award-item">
                <input type="hidden" name="awards[{{ $index }}][id]" value="{{ $award->id }}">
                <label>Tipo de Condição:</label>
                <select name="awards[{{ $index }}][condition_type]" class="form-control">
                    <option value="MINIMUM_POINT" {{ $award->condition_type == 'MINIMUM_POINT' ? 'selected' : '' }}>Ponto Mínimo</option>
                    <option value="EXACT_POINT" {{ $award->condition_type == 'EXACT_POINT' ? 'selected' : '' }}>Ponto Exato</option>
                </select>
                <label>Valor do Ponto Mínimo:</label>
                <input type="number" name="awards[{{ $index }}][minimum_point_value]" value="{{ $award->minimum_point_value }}">
                <label>Valor do Prêmio:</label>
                <input type="number" name="awards[{{ $index }}][amount]" value="{{ $award->amount }}" required>
                <button type="button" class="remove-award">Remover</button>
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-primary">Atualizar Jogo</button>
</form>

</div>

@endsection


