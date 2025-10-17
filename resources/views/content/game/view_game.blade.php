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
    <div>
        <?php $tab = session('tab') ?? (old('tab') ?? 'tab-details');
        ?>
        <div class="mx-4">
            <h1 class="my-4">Detalhes do Jogo</h1>
        </div>

        @if ($errors->has('error'))
            <div class="alert alert-danger">{{ $errors->first('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @include('content.game.components.tab-menu', ['tab' => $tab])
        <div class="tab-content mt-4" id="gameTabsContent">
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
            @include('content.game.components.tab-prizes', ['tab' => $tab, 'game' => $game])
            @include('content.game.components.tab-rules', ['tab' => $tab])
            @include('content.game.components.tab-winners', [
                'tab' => $tab,
                'winners' => $winners,
                'user_awards' => $user_awards,
            ])
        </div>
    </div>

    <script>
        window.backendSelectedTab = @json(session('tab'));
    </script>

@endsection
