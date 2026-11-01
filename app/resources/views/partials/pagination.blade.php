@if ($paginator->hasPages())
    <nav class="pager" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pager-disabled">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif

        <span class="pager-status">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="pager-disabled">Next</span>
        @endif
    </nav>
@endif
