@if ($paginator->hasPages())
    @php
        // A five-page window around the current page, with first and last
        // always reachable. Long result sets stay narrow at small widths.
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max(1, $current - 2);
        $end = min($last, $current + 2);
    @endphp
    <nav class="pager" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pager-disabled" aria-disabled="true">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif

        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}" aria-label="Page 1">1</a>
            @if ($start > 2)
                <span class="pager-gap" aria-hidden="true">…</span>
            @endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page === $current)
                <span class="pager-current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
            @endif
        @endfor

        @if ($end < $last)
            @if ($end < $last - 1)
                <span class="pager-gap" aria-hidden="true">…</span>
            @endif
            <a href="{{ $paginator->url($last) }}" aria-label="Page {{ $last }}">{{ $last }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="pager-disabled" aria-disabled="true">Next</span>
        @endif

        <span class="pager-status">Page {{ $current }} of {{ $last }}</span>
    </nav>
@endif
