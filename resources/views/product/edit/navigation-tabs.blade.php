@isset($productNavigation)
    <div class="container-fluid p-0">
        <div class="m-3 mb-0">
            {{-- Sombra nativa de Bootstrap y clase exclusiva para aislar el diseño --}}
            <ul class="nav nav-tabs shadow-sm tabs-product-edit">
                @foreach($productNavigation as $label => $route)
                    <li class="nav-item">
                        <a class="nav-link {{ url()->current() === $route ? 'active fw-bold' : '' }}" href="{{ $route }}">
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endisset

{{-- Bloque de estilos aislado para que no rompa nada global --}}
<style>
    /* Base de la barra de pestañas */
    ul.tabs-product-edit {
        background-color: #ffffff !important;
        border-radius: 8px 8px 0 0;
        padding: 6px 6px 0 6px;
        border-bottom: 2px solid #dee2e6;
    }

    /* Pestañas inactivas */
    ul.tabs-product-edit .nav-link {
        border: none !important;
        color: #6c757d !important;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        border-radius: 6px 6px 0 0;
    }

    /* Efecto Hover */
    ul.tabs-product-edit .nav-link:hover {
        background-color: #f8f9fa !important;
        color: #4e3575 !important;
    }

    /* Pestaña Activa (Violeta institucional de SiscoPlagas) */
    ul.tabs-product-edit .nav-link.active {
        background-color: #4e3575 !important;
        color: #ffffff !important;
        font-weight: 600;
        box-shadow: 0 -2px 6px rgba(78, 53, 117, 0.2) !important;
    }
</style>