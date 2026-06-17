@isset($productNavigation)
    <div class="container-fluid p-0">
        <div class="m-3 mb-0">
            <ul class="nav nav-tabs">
                @foreach ($productNavigation as $label => $route)
                    <li class="nav-item">
                        <a class="nav-link {{ url()->current() === $route ? 'active fw-bold' : '' }}"
                            href="{{ $route }}">
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endisset
