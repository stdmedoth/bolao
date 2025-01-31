@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Histórico de Jogo')

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
<div class="container mt-4">
  <div class="card">
    <div class="card-header">
      <h5>Editar Histórico de Jogo</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('edit-game-history', $gameHistory->id) }}" method="POST">
        @csrf
        @method('PUT')

    
        <div class="mb-3">
          <label for="description" class="form-label">Descrição</label>
          <textarea 
            class="form-control" 
            id="description" 
            name="description" 
            rows="3" 
            required>{{ old('description', $gameHistory->description) }}</textarea>
        </div>

        <div class="mb-3">
          <label for="type" class="form-label">Tipo</label>
          <select class="form-select" id="type" name="type" required>
            <option value="OPENED" {{ old('type', $gameHistory->type) == 'OPENED' ? 'selected' : '' }}>Aberto</option>
            <option value="ADDING_NUMBER" {{ old('type', $gameHistory->type) == 'ADDING_NUMBER' ? 'selected' : '' }}>Adicionando Números</option>
            <option value="CLOSED" {{ old('type', $gameHistory->type) == 'CLOSED' ? 'selected' : '' }}>Fechado</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="result_numbers" class="form-label">Números do Resultado</label>
          <input 
            type="text" 
            class="form-control" 
            id="result_numbers" 
            name="result_numbers" 
            value="{{ old('result_numbers', $gameHistory->result_numbers) }}"
          >
        </div>

        <div class="mb-3">
          <label for="numbers" class="form-label">Números</label>
          <input 
            type="text" 
            class="form-control" 
            id="numbers" 
            name="numbers" 
            disabled
            value="{{ old('numbers', $gameHistory->numbers) }}"
          >
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('show-game', ['id' => $gameHistory->game_id]) }}" class="btn btn-secondary">Cancelar</a>
      </form>
    </div>
  </div>
</div>
@endsection
