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

    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mt-2 mb-3">
            <h1 class="mb-3 mb-md-0">Usuários</h1>
            @if (auth()->user()->role->level_id == 'admin')
                <a class="btn btn-primary" href="{{ url('usuarios/create_user') }}">Criar novo usuário</a>
            @endif

            @if (auth()->user()->role->level_id == 'seller')
                <a class="btn btn-primary" href="{{ url('usuarios/create_user') }}">Trazer novo apostador</a>
            @endif
        </div>

        <!-- Filtros -->
        <form action="{{ url('/usuarios') }}" method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Pesquisar por nome, email ou tipo..." value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit">Buscar</button>
                    </div>
                </div>

                @if (auth()->user()->role->level_id == 'admin')
                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="role_user_id" class="form-select">
                            <option value="">Todos os Tipos</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_user_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-sm-6 col-md-3">
                    <button class="btn btn-secondary w-100" type="submit">Aplicar Filtros</button>
                </div>
            </div>
        </form>

        <!-- Lista de Usuários - Desktop/Tablets -->
        <div class="card d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            @if (auth()->user()->role->level_id == 'admin')
                                <th class="text-end">Ações</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-medium">{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="text-muted small">Criado em {{ optional($user->created_at)->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $user->email }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $user->role->level_id == 'admin' ? 'bg-label-danger' : ($user->role->level_id == 'seller' ? 'bg-label-info' : 'bg-label-secondary') }}">
                                        {{ $user->role->name }}
                                    </span>
                                </td>
                                @if (auth()->user()->role->level_id == 'admin')
                                    <td class="text-end">
                                        <div class="d-flex gap-2 align-items-stretch" role="group">
                                            <a href="{{ url('usuarios/edit/'.$user->id) }}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center px-3 h-100">Editar</a>
                                            <form action="{{ url('usuarios/delete/'.$user->id) }}" method="POST" class="m-0 d-flex align-items-stretch" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center px-3 h-100" {{ $user->id === auth()->user()->id ? 'disabled' : '' }}>Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Nenhum usuário encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lista de Usuários - Mobile (cards) -->
        <div class="d-md-none">
            @forelse($users as $user)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 p-0 overflow-hidden"
                                 style="width:40px;height:40px;border-radius:50%;">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                            <div class="w-100">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->email }}</div>
                                    </div>
                                    <span class="badge {{ $user->role->level_id == 'admin' ? 'bg-label-danger' : ($user->role->level_id == 'seller' ? 'bg-label-info' : 'bg-label-secondary') }}">
                                        {{ $user->role->name }}
                                    </span>
                                </div>

                                @if (auth()->user()->role->level_id == 'admin')
                                    <div class="d-flex gap-2 mt-3 align-items-stretch">
                                        <div class="flex-fill d-flex">
                                            <form action="{{ url('usuarios/edit/'.$user->id) }}" method="GET" class="w-100 d-flex m-0 align-items-stretch">
                                                <button type="submit" class="btn btn-sm btn-outline-primary w-100 d-flex align-items-center justify-content-center py-2 h-100 lh-1">Editar</button>
                                            </form>
                                        </div>
                                        <div class="flex-fill d-flex">
                                            <form action="{{ url('usuarios/delete/'.$user->id) }}" method="POST" class="w-100 d-flex m-0 align-items-stretch" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center py-2 h-100 lh-1" {{ $user->id === auth()->user()->id ? 'disabled' : '' }}>Excluir</button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-light text-center" role="alert">Nenhum usuário encontrado.</div>
            @endforelse
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $users->appends(request()->all())->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
