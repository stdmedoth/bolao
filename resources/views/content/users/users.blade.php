@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Usuarios')

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

    <div class="contatiner">
        <h1 class="my-4">Usuários</h1>
        @if (auth()->user()->role->level_id == 'admin')
            <a class="btn btn-secondary" href="/usuarios/create_user">Criar novo usuario</a>
        @endif

        @if (auth()->user()->role->level_id == 'seller')
            <a class="btn btn-secondary" href="/usuarios/create_user">Trazer novo apostador</a>
        @endif

        <!-- Formulário de Pesquisa -->
        <!-- Formulário de Pesquisa e Filtro -->
        <form action="{{ url('/usuarios') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <!-- Campo de pesquisa -->
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                            placeholder="Pesquisar por nome, email ou tipo..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Filtrar</button>
                    </div>
                </div>

                @if (auth()->user()->role->level_id == 'admin')
                    <div class="col-md-4">
                        <!-- Select de filtro por role -->
                        <select name="role_user_id" class="form-select">
                            <option value="">Todos os Tipos</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ request('role_user_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" type="submit">Aplicar Filtros</button>
                </div>
            </div>
        </form>

        <!-- Lista de Usuários -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    @if (auth()->user()->role->level_id == 'admin')
                        <th>Ações</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role->name }}</td>
                        @if (auth()->user()->role->level_id == 'admin')
                            <td>
                                <a href="/usuarios/edit/{{ $user->id }}" class="btn btn-warning">Editar</a>
                                <form action="/usuarios/delete/{{ $user->id }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        {{ $user->id === auth()->user()->id ? 'disabled' : '' }}
                                        onclick="return confirm('Tem certeza que deseja excluir este usuário?');">Excluir</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Nenhum usuário encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Controles de paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $users->appends(request()->all())->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
