@props(['paginator'])

@php
    $total = $paginator->total();

    if ($total === 0) {
        $summary = 'No rows found';
    } else {
        $from = ($paginator->currentPage() - 1) * $paginator->perPage() + 1;
        $to = min($from + $paginator->perPage() - 1, $total);
        $summary = "Showing {$from} to {$to} of {$total} results";
    }
@endphp

<span {{ $attributes }}>
    <span class="font-semibold">{{ $summary }}</span>
</span>