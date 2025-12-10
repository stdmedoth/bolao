<style>
    .pagination-custom {
        font-size: 0.7rem;
    }
    .pagination-custom .page-link {
        padding: 0.25rem 0.4rem;
        font-size: 0.7rem;
        line-height: 1.2;
    }
    .pagination-custom .page-item:first-child .page-link,
    .pagination-custom .page-item:last-child .page-link {
        border-radius: 0.2rem;
    }
    .pagination-custom .page-item + .page-item .page-link {
        margin-left: 0.1rem;
    }
    .pagination-custom .page-link i {
        font-size: 0.6rem;
    }
</style>

@if ($paginator->hasPages())
    <nav>
        <ul class="pagination justify-content-center pagination-sm pagination-custom">
            {{-- First Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bx bx-chevrons-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}" rel="first">
                        <i class="bx bx-chevrons-left"></i>
                    </a>
                </li>
            @endif

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bx bx-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="bx bx-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                
                // Mostrar apenas 3 páginas máximo
                if ($last <= 3) {
                    $start = 1;
                    $end = $last;
                } else {
                    // Se estamos na primeira página
                    if ($current == 1) {
                        $start = 1;
                        $end = 3;
                    }
                    // Se estamos na última página
                    elseif ($current == $last) {
                        $start = $last - 2;
                        $end = $last;
                    }
                    // Páginas do meio
                    else {
                        $start = $current - 1;
                        $end = $current + 1;
                    }
                }
            @endphp

            {{-- Page Numbers --}}
            @for ($i = $start; $i <= $end; $i++)
                <li class="page-item {{ $i == $current ? 'active' : '' }}">
                    @if ($i == $current)
                        <span class="page-link">{{ $i }}</span>
                    @else
                        <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                    @endif
                </li>
            @endfor

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <i class="bx bx-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bx bx-chevron-right"></i>
                    </span>
                </li>
            @endif

            {{-- Last Page Link --}}
            @if ($paginator->currentPage() == $paginator->lastPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bx bx-chevrons-right"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" rel="last">
                        <i class="bx bx-chevrons-right"></i>
                    </a>
                </li>
            @endif
        </ul>
        
        {{-- Informação de quantidade de jogos --}}
        @if ($paginator->total() > 0)
            <div class="text-center mt-2">
                <small class="text-muted">
                    @if ($paginator->total() == 1)
                        Mostrando 1 jogo
                    @else
                        Mostrando {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} de {{ $paginator->total() }} jogos
                    @endif
                </small>
            </div>
        @endif
    </nav>
@endif
