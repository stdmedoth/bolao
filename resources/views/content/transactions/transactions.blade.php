@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Extrato')

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
  <h1 class="my-4">Extrato</h1>

  <div class="card shadow-lg p-3 mb-5 bg-white rounded">
    <div class="row mt-3">
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Valor</th>
                @if (in_array(auth()->user()->role->level_id, ['admin' , 'seller']))
                <th>Usuário</th>
                @endif
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              @forelse($transactions as $transaction)
              <tr>
                <td>{{ $transaction->id }}</td>
                <td>{{ $typeTranslations[$transaction->type] ?? $transaction->type }}</td>
                <td>R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>

                @if (in_array(auth()->user()->role->level_id, ['admin' , 'seller']))
                <td>{{ $transaction->user->name }}</td>
                @endif

                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center">Nenhuma transação encontrada.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          <!-- Controles de paginação -->
          <div class="d-flex justify-content-center mt-4">
            {{ $transactions->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection