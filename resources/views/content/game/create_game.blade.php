@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Minhas Compras')

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
  <h2>Criar Novo Jogo</h2>
  <form action="{{ route('create-game') }}" method="POST">
    @csrf
    <div class="form-group">
      <label for="name">Nome do Jogo:</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="price">Preço:</label>
      <input type="number" name="price" class="form-control" step="0.01" required>
    </div>

    <div class="form-group">
      <label for="open_at">Data de Abertura:</label>
      <input type="datetime-local" name="open_at" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="close_at">Data de Fechamento:</label>
      <input type="datetime-local" name="close_at" class="form-control" required>
    </div>

    <hr>
    <h3>Prêmios</h3>
    <div id="awards-container">
      <div class="award-item">
        <div class="form-group">
          <label for="awards[0][condition_type]">Tipo de Condição:</label>
          <select name="awards[0][condition_type]" class="form-control" required>
            <option value="MINIMUM_POINT">Ponto Mínimo</option>
            <option value="EXACT_POINT">Ponto Exato</option>
          </select>
        </div>

        <div class="form-group">
          <label for="awards[0][minimum_point_value]">Valor do Ponto Mínimo (se aplicável):</label>
          <input type="number" name="awards[0][minimum_point_value]" class="form-control">
        </div>

        <div class="form-group">
          <label for="awards[0][amount]">Valor do Prêmio:</label>
          <input type="number" name="awards[0][amount]" class="form-control" step="0.01" required>
        </div>
        <button type="button" class="btn btn-danger remove-award">Remover Prêmio</button>
      </div>
    </div>

    <button type="button" class="btn btn-secondary" id="add-award">Adicionar Prêmio</button>
    <br><br>

    <button type="submit" class="btn btn-primary">Criar Jogo</button>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    let awardIndex = 1;

    // Adicionar novo prêmio
    document.getElementById('add-award').addEventListener('click', function() {
      const container = document.getElementById('awards-container');
      const newAward = document.querySelector('.award-item').cloneNode(true);

      newAward.querySelectorAll('input, select').forEach(function(input) {
        const name = input.getAttribute('name').replace(/\[\d+\]/, `[${awardIndex}]`);
        input.setAttribute('name', name);
        input.value = '';
      });

      container.appendChild(newAward);
      awardIndex++;
    });

    // Remover prêmio
    document.getElementById('awards-container').addEventListener('click', function(e) {
      if (e.target.classList.contains('remove-award')) {
        e.target.closest('.award-item').remove();
      }
    });
  });
</script>
@endsection
