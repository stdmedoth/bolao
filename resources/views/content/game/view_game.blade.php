@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Minhas Compras')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
    <script src="{{ asset('js/game-tabs.js') }}"></script>
    <script src="{{ asset('js/my-bets-tab.js') }}"></script>
@endsection


@section('content')
    <div class="container-fluid px-0">
        <?php $tab = session('tab') ?? (old('tab') ?? 'tab-classifications');
        ?>
        <div class="row px-2">
            <div class="col-12">
                <h1 class="my-2 my-md-3 h4 h3-md">{{ $game->name }}</h1>
            </div>
        </div>

        @if ($errors->has('error'))
            <div class="alert alert-danger mx-2">{{ $errors->first('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success mx-2">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                @include('content.game.components.tab-menu', ['tab' => $tab])
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="tab-content mt-2 mt-md-3 px-2" id="gameTabsContent">
                    {{-- @include('content.game.components.tab-details', ['tab' => $tab, 'game' => $game]) --}}
                    @include('content.game.components.tab-bet-form', [
                        'tab' => $tab,
                        'game' => $game,
                        'sellers' => $sellers,
                    ])
                    @include('content.game.components.tab-results', [
                        'tab' => $tab,
                        'game' => $game,
                        'histories' => $histories,
                    ])
                    @include('content.game.components.tab-my-bets', ['tab' => $tab, 'purchases' => $purchases])
                    @include('content.game.components.tab-classifications', ['tab' => $tab, 'purchases' => $purchases, 'game' => $game, 'sellers' => $sellers])
                    @include('content.game.components.tab-prizes', ['tab' => $tab, 'game' => $game])
                    @include('content.game.components.tab-rules', ['tab' => $tab])
                    @include('content.game.components.tab-winners', [
                        'tab' => $tab,
                        'winners' => $winners,
                        'user_awards' => $user_awards,
                    ])
                </div>
            </div>
        </div>
    </div>

    <script>
        window.backendSelectedTab = @json(session('tab'));
    </script>

@endsection
