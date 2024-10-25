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


@endsection