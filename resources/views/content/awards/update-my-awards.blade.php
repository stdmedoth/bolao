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
    <h1>Meus Prêmios</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID do Prêmio</th>
                <th>ID da Compra</th>
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
                    <td>{{ $award->purchase_id }}</td>
                    <td>{{ $award->amount ? number_format($award->amount, 2, ',', '.') : 'N/A' }}</td>
                    <td>
                        <span class="badge bg-label-{{ strtolower($award->status) }}">
                            {{ $award->status }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($award->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('awards.edit', $award->id) }}" class="btn btn-sm btn-primary">Editar</a>
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
