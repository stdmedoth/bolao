@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Meus Prêmios')

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
  @if (auth()->user()->role->level_id !== 'admin')

  <h1>Meus Prêmios</h1>
  @endif
  @if (auth()->user()->role->level_id == 'admin')

  <h1>Prêmios</h1>
  @endif

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID do Prêmio</th>
        <th>Valor</th>
        <th>Status</th>
        <th>Data de Criação</th>
        <th>Ações</th>
      </tr>
    </thead>

    <tbody>
      @foreach($user_awards as $award)
      <tr>
        <td>{{ $award->id }}</td>
        <td>{{ $award->amount ? number_format($award->amount, 2, ',', '.') : 'N/A' }}</td>
        <td>
          <span class="badge bg-label-primary">
            {{ __($award->status) }}
          </span>
        </td>
        <td>{{ \Carbon\Carbon::parse($award->created_at)->format('Y-m-d H:i') }}</td>
        <td>
          <button class="btn btn-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#details-{{ $index }}" aria-expanded="false" aria-controls="details-{{ $index }}">
            Detalhes
          </button>
          @if (auth()->user()->role->level_id == 'admin')
          <a href="{{ route('user_award-pay', $award->id) }}" class="btn btn-success {{$award->status == "PAID" ? "disabled" : ""}}">Pagar</a>
          @endif
          @if (auth()->user()->role->level_id == 'admin' && ($award->status == "PAID"))
          <a href="{{ route('user_award-withdraw', $award->id) }}" class="btn btn-info {{$award->status !== "PAID" ? "disabled" : ""}}">Estornar</a>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  @if($user_awards->isEmpty())
  <div class="alert alert-info">
    Você ainda não possui prêmios.
  </div>
  @endif
</div>
@endsection
