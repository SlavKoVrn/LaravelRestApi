<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @if (isset($breadcrumbs))
            @foreach ($breadcrumbs as $label => $url)
                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                    @if (!$loop->last)
                        <a href="{{ $url }}">{{ $label }}</a>
                    @else
                        {{ $label }}
                    @endif
                </li>
            @endforeach
        @endif
    </ol>
</nav>