@if ($paginator->hasPages())
<nav class="pager" role="navigation" aria-label="Paginación">
    @if ($paginator->onFirstPage())
        <span class="btn secondary pager-off">‹ Anterior</span>
    @else
        <a class="btn secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Anterior</a>
    @endif
    <span class="muted pager-info">Página {{ $paginator->currentPage() }}</span>
    @if ($paginator->hasMorePages())
        <a class="btn secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente ›</a>
    @else
        <span class="btn secondary pager-off">Siguiente ›</span>
    @endif
</nav>
@endif
