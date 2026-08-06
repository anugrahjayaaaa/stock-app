{{-- Showing X–Y of Z (works with any Laravel paginator) --}}
@if ($items->total() > 0)
    <p class="pagination-info mb-2">
        Showing {{ $items->firstItem() }}–{{ $items->lastItem() }}
        of {{ $items->total() }}
    </p>
@endif
