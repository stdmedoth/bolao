@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Usuários')

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

    <div>

        <a class="btn btn-primary" href="/usuarios/create_user">Criar novo usuário</a>

        <!-- Lista de Usuários -->
        <div class="row mt-4">
            @foreach ($usuarios as $usuario)
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">{{ $usuario->name }}</h5>
                            <p class="card-text">{{ $usuario->email }}</p>
                            <p class="card-text">{{ $usuario->role->name }}</p>
                            <a href="/usuarios/edit/{{ $usuario->id }}" class="btn btn-warning">Editar</a>
                            <form action="/usuarios/delete/{{ $usuario->id }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Tem certeza que deseja excluir este usuário?');">Excluir</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


@endsection
