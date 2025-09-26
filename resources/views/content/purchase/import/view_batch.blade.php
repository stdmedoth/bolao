@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Minhas Compras')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let savedTab = localStorage.getItem('activeBatchTab');
            let initialTab = savedTab || 'import-batch';

            const triggerEl = document.querySelector(`[data-bs-toggle="tab"][href="#${initialTab}"]`);
            console.log('Initial Tab:', initialTab); // Debugging line to check the initial tab
            console.log('Trigger Element:', triggerEl); // Debugging line to check if the trigger
            if (triggerEl) {
                new bootstrap.Tab(triggerEl).show();
                localStorage.setItem('activeBatchTab', initialTab);
            }

            // Atualiza o localStorage quando uma aba for clicada
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('href').replace('#', '');
                    localStorage.setItem('activeBatchTab', target);
                });
            });
        });
    </script>
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

    <script>
        window.backendSelectedTab = @json(session('tab'));
    </script>


@endsection
