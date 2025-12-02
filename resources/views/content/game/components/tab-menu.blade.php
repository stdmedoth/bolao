<div class="table-responsive">
    <ul class="nav nav-tabs flex-nowrap overflow-auto hide-scrollbar" id="gameTabs" role="tablist">

        <style>
            .hide-scrollbar {
                -ms-overflow-style: none;
                /* IE e Edge */
                scrollbar-width: none;
                /* Firefox */
            }

            .hide-scrollbar::-webkit-scrollbar {
                display: none;
                /* Chrome, Safari, Opera */
                scroll-behavior: smooth;
            }

            /* Responsividade para tabs */
            @media (max-width: 576px) {
                .nav-tabs .nav-link {
                    padding: 0.5rem 0.75rem;
                    font-size: 0.875rem;
                    white-space: nowrap;
                    min-width: auto;
                }
                
                .nav-tabs {
                    border-bottom: 1px solid #dee2e6;
                }
            }

            @media (max-width: 480px) {
                .nav-tabs .nav-link {
                    padding: 0.4rem 0.6rem;
                    font-size: 0.8rem;
                }
            }
        </style>
    @php
        $tabs = [
            'tab-classifications' => 'Classificação',
            'tab-bet-form' => 'Apostar',
            'tab-mybets' => 'Minhas apostas',
            'tab-results' => 'Resultados',
            'tab-winners' => 'Ganhadores',
            'tab-prizes' => 'Prêmios',
            'tab-rules' => 'Regras',
        ];
    @endphp

        @php
            $tabIcons = [
                'tab-classifications' => 'bx-trophy',
                'tab-bet-form' => 'bx-plus-circle',
                'tab-mybets' => 'bx-list-check',
                'tab-results' => 'bx-bar-chart',
                'tab-winners' => 'bx-award',
                'tab-prizes' => 'bx-gift',
                'tab-rules' => 'bx-book',
            ];
        @endphp
        @foreach ($tabs as $id => $label)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab == $id ? 'active text-primary fw-bold' : 'text-secondary' }}" id="{{ $id }}-tab" data-bs-toggle="tab"
                    href="#{{ str_replace('tab-', '', $id) }}" role="tab"
                    aria-controls="{{ str_replace('tab-', '', $id) }}" 
                    aria-selected="{{ $tab == $id ? 'true' : 'false' }}"
                    aria-label="{{ $label }}"
                >
                    <i class="bx {{ $tabIcons[$id] ?? 'bx-circle' }} me-1"></i>{{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
