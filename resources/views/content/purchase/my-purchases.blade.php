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
  @if (auth()->user()->role->level_id !== 'admin')
    <h5 class="card-header">Minhas Compras</h5>
  @endif
  @if (auth()->user()->role->level_id == 'admin')
    <h5 class="card-header">Compras realizadas</h5>
  @endif
  
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Jogo</th>
          @if (auth()->user()->role->level_id == 'admin')
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
          @if (auth()->user()->role->level_id == 'admin')
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
            <a href="{{ route('purchases.destroy', $purchase->id) }}" class="btn btn-danger {{$purchase->status == "PAID" ? "disabled" : ""}}">Deletar</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection