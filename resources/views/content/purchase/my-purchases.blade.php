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