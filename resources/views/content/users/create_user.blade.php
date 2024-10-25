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
  <h2>Criar Novo Usuario</h2>
  <form action="{{ route('create-user') }}" method="POST">
    @csrf
    <div class="form-group">
      <label for="name">Nome do Usuario:</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="email">Email:</label>
      <input type="text" name="email" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="password">Senha:</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="role">Tipo de usuário:</label>
      <select class="form-control" name="role_user_id">
        @foreach ($roles as $role)
          <option value="$role->id">{{$role->name}}</option>
        @endforeach
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Criar Usuario</button>
  </form>
</div>
@endsection