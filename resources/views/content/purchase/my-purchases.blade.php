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
  @if (!in_array(auth()->user()->role->level_id, ['admin' , 'seller']))
  <h1 class="my-4">Minhas Compras</h1>
  @else
  <h1 class="my-4">Compras realizadas</h1>
  @endif

  <!-- Tabela de Compras -->
  <div class="card">

    <div class="table-responsive text-nowrap">

      <!-- Formulário de Pesquisa e Filtro -->
      <form action="{{ url('/minhas_compras') }}" method="GET" class="mb-4">
        <div class="row">
          <div class="col-md-6">
            <!-- Campo de pesquisa -->
            <div class="input-group">
              <input type="text" name="search" class="form-control" placeholder="Pesquisar por nome do concurso, numeros..." value="{{ request('search') }}">
              <button class="btn btn-primary" type="submit">Filtrar</button>
            </div>
          </div>

          <div class="col-md-4">
            <!-- Select de filtro por role -->
            <select name="status" class="form-select">
              <option value="">Todos os status</option>
              @foreach (['PAID', 'PENDING', 'CANCELED', 'FINISHED'] as $status)
              <option value="{{ $status }}" {{ (request('status') == $status) ? 'selected' : '' }}>{{ __($status) }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <button class="btn btn-secondary w-100" type="submit">Aplicar Filtros</button>
          </div>
        </div>
      </form>


      <table class="table">
        <thead>
          <tr>
            <th>Jogo</th>
            @if (in_array(Auth::user()->role->level_id, ['admin' , 'seller']))
            <th>Apostador</th>
            @endif
            <th>Data da Compra</th>
            <th>Números</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <!-- Aqui iteramos sobre as compras -->

          <!-- A collection de compras como uma só -->
          @foreach ($purchases as $purchase)
          <tr>
            <td>
              <a href='/concursos/{{$purchase->game->id}}'>
                <!-- Mostrando o nome do jogo relacionado -->
                <i class="bx bxl-game bx-md text-info me-4"></i>
                <span>{{ $purchase->game->name }}</span>
                <!-- Nome do jogo é o dia na semana em que se passa a aposta-->
              </a>
            </td>
            @if (in_array(auth()->user()->role->level_id, ['admin' , 'seller']))
            <td>{{ $purchase->user->name }}</td>

            @endif
            <!-- Usar timestamp do próprio produto? -->

            <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
            <td>{{ $purchase->numbers }}</td>
            <td>
              <!-- Mostrando o status da compra -->
              <span class="badge bg-label-primary me-1">{{ __($purchase->status) }}</span>
            </td>
            <td>
              <a href="{{ route('purchase-pay', $purchase->id) }}" class="btn btn-success {{$purchase->status !== "PENDING" ? "disabled" : ""}}">Pagar</a>
              <a href="{{ route('purchases.destroy', $purchase->id) }}" class="btn btn-danger {{($purchase->status == "PAID") && (auth()->user()->role->level_id !== 'admin') ? "disabled" : ""}}">Deletar</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <!-- Controles de paginação -->
      <div class="d-flex justify-content-center mt-4">
        {{ $purchases->links('pagination::bootstrap-5') }}
      </div>

    </div>
  </div>
</div>
@endsection