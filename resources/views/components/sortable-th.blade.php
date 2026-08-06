@php
    $field  = $field ?? null;
    $label  = $label ?? '';
    $current = request('sort');
    $dir    = request('direction', 'asc') === 'desc' ? 'desc' : 'asc';
    $active = $current === $field;
    $nextDir = ($active && $dir === 'asc') ? 'desc' : 'asc';
    $params = array_merge(request()->except(['page']), ['sort' => $field, 'direction' => $nextDir]);
    $url = request()->url() . '?' . http_build_query($params);
@endphp
<th {{ $attributes->merge(['class' => 'sortable']) }}>
    <a href="{{ $url }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
        {{ $label }}
        @if ($active)
            <i class="fas fa-arrow-{{ $dir === 'asc' ? 'up' : 'down' }} text-primary"></i>
        @else
            <i class="fas fa-sort text-muted opacity-50"></i>
        @endif
    </a>
</th>
