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

<!-- Tabela de Compras -->
<div class="card">
  <h5 class="card-header">Jogos</h5>
  <a class="btn" href="/concursos/create_game_form">Criar novo jogo</a>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Jogo</th>
          <th>Aberto em</th>
          <th>Fecha em</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <!-- Aqui iteramos sobre as compras -->

        <!-- A collection de compras como uma só -->
        @foreach ($games as $game)
        <tr>
          <td>
            <!-- Mostrando o nome do jogo relacionado -->
            <a href="/concursos/{{$game->id}}">
              <i class="bx bxl-game bx-md text-info me-4"></i>
              <span>{{ $game->name }}</span>
              <!-- Nome do jogo é o dia na semana em que se passa a aposta-->
            </a>
          </td>

          <!-- Usar timestamp do próprio produto? -->

          <td>{{ date('d/m/Y', strtotime($game->open_at)) }}</td>
          <td>{{ date('d/m/Y', strtotime($game->close_at)) }}</td>
          <td>
            <!-- Mostrando o status da compra -->
            <span class="badge bg-label-primary me-1">{{ $game->status }}</span>
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"><i
                    class="bx bx-edit-alt me-1"></i> Editar </a>
                <a class="dropdown-item" href="javascript:void(0);"> <i
                    class="bx bx-trash me-1"></i> Excluir </a>
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection