@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Minhas Compras')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
@endsection


@section('content')


    <?php $tab = session('tab') ?? (old('tab') ?? 'tab-import-batch');
    ?>

    @include('content.purchase.import.tabs-menu', ['tab' => $tab])

    <div class="tab-content mt-4" id="gameTabsContent">
        @include('content.purchase.import.batch_form', [
            'tab' => $tab,
        ])
        @include('content.purchase.import.list', [
            'tab' => $tab,
            'purchaseBatches' => $purchaseBatches,
        ])
    </div>


@endsection
