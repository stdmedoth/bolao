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
        <h5 class="card-header">Minhas Compras</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Jogo</th>
                        <th>Data da Compra</th>
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
                                <!-- Mostrando o nome do jogo relacionado -->
                                <i class="bx bxl-game bx-md text-info me-4"></i>
                                <span>{{ $purchase->game->name }}</span>
                                <!-- Nome do jogo é o dia na semana em que se passa a aposta-->
                            </td>

                            <!-- Usar timestamp do próprio produto? -->

                            <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                            <td>
                                <!-- Mostrando o status da compra -->
                                <span class="badge bg-label-primary me-1">{{ $purchase->status }}</span>
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
