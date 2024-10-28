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

<a class="btn" href="/usuarios/create_user">Criar novo usuario</a>
<!-- Lista de Usuarios -->
  <!-- Lista de Usuários -->
<table class="table table-bordered">
  <thead>
      <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Email</th>
          <th>Ações</th>
      </tr>
  </thead>
  <tbody>
      @forelse($users as $user)
      <tr>
          <td>{{ $user->id }}</td>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>
              <a href="/usuarios/edit/{{ $user->id }}" class="btn btn-warning">Editar</a>
              <form action="/usuarios/delete/{{ $user->id }}" method="POST" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">Excluir</button>
              </form>
          </td>
      </tr>
      @empty
      <tr>
          <td colspan="4" class="text-center">Nenhum usuário encontrado.</td>
      </tr>
      @endforelse
  </tbody>
</table>



@endsection
