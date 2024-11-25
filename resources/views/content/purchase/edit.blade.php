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
    <h5 class="card-header">Aposta</h5>

    <div>
        <label>Numbers</label>
        <input type="text" name="numbers" value="{{ old('numbers', $purchase->numbers ?? '') }}">
    </div>
    <div>
        <label>Status</label>
        <select name="status">
            @foreach (['PAID', 'PENDING', 'CANCELED', 'FINISHED'] as $status)
            <option value="{{ $status }}" {{ (old('status', $purchase->status ?? '') == $status) ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Quantity</label>
        <input type="number" name="quantity" value="{{ old('quantity', $purchase->quantity ?? '') }}">
    </div>
    <div>
        <label>Price</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $purchase->price ?? '') }}">
    </div>
    <div>
        <label>Game</label>
        <select name="game_id">
            @foreach ($games as $game)
            <option value="{{ $game->id }}" {{ (old('game_id', $purchase->game_id ?? '') == $game->id) ? 'selected' : '' }}>{{ $game->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>User</label>
        <select name="user_id">
            @foreach ($users as $user)
            <option value="{{ $user->id }}" {{ (old('user_id', $purchase->user_id ?? '') == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
</div>

@endsection