<ul class="nav nav-tabs flex-nowrap overflow-auto hide-scrollbar" id="gameTabs" role="tablist">
    @php
        $tabs = [
            'tab-import-batch' => 'Importar em Lote',
            'tab-batch-list' => 'Lista de Lotes',
        ];
    @endphp

    @foreach ($tabs as $id => $label)
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab == $id ? 'active' : '' }}" id="{{ $id }}-tab" data-bs-toggle="tab"
                href="#{{ str_replace('tab-', '', $id) }}" role="tab"
                aria-controls="{{ str_replace('tab-', '', $id) }}" aria-selected="{{ $tab == $id ? 'true' : 'false' }}">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>
