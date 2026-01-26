<style>
    .sidebar {
        color: white;
        text-decoration: none;
    }

    .sidebar-header {
        color: white;
        text-decoration: none;
        font-size: 1rem;
        text-align: center;
    }

    .sidebar:hover {
        background-color: #e9ecef;
        color: #212529;
    }

    .directory:hover {
        text-decoration: underline !important;
        color: #0d6efd !important;
    }
</style>

<div class="col-1 m-0" style="background-color: #343a40;">
    <div class="row sticky-top" style="font-size: small">
        <a href="{{ route('stock.index', ['is_active' => 1]) }}" class="sidebar my-1 py-2 w-100 rounded-start p-1 ">
            <i class="bi bi-box"></i> Almacenes
        </a>
        <a href="{{ route('lot.index') }}" class="sidebar w-100 p-1 rounded-start py-2 my-1">
            <i class="bi bi-boxes"></i> Lotes
        </a>
        <a href="{{ route('product.index') }}" class="sidebar w-100 p-1 rounded-start py-2 my-1">
            <i class="bi bi-box"></i> Productos
        </a>
        <a href="{{ route('stock.movements.all') }}" class="sidebar w-100 py-2 my-1 p-1 rounded-start">
            <i class="bi bi-arrow-left-right"></i> Movimientos
        </a>
        <a href="{{ route('comercial-zones.index') }}" class="sidebar w-100 p-1 rounded-start my-1 py-2">
            <i class="bi bi-geo-alt-fill"></i> Zonas comerciales
        </a>
        <div class="sidebar w-100 p-1 rounded-start my-1 py-2" data-bs-toggle="collapse" data-bs-target="#consumptionsCollapse" role="button" aria-expanded="false" aria-controls="consumptionsCollapse">
            <i class="bi bi-clipboard-data"></i> Consumos <i class="bi bi-chevron-down float-end"></i>
        </div>
        <div class="collapse" id="consumptionsCollapse">
            <div class="row sidebar w-100 rounded px-2 ml-3">
                <a href="{{ route('consumptions.index') }}" class="sidebar w-100" style="font-size: 0.8rem;">
                    Nuevos
                </a>
            </div>
            <div class="row sidebar w-100 rounded px-2 ml-3">
                <a href="{{ route('consumptions.index') }}" class="sidebar w-100" style="font-size: 0.8rem;">
                    Histórico
                </a>
            </div>
        </div>
        {{--<a href="{{ route('stock.product.orders') }}" class="sidebar w-100 p-1 rounded-start my-1 py-2">
            <i class="bi bi-file-earmark-ppt"></i> Productos en ordenes
        </a>--}}
        <a href="{{ route('stock.analytics') }}" class="sidebar w-100 p-1 rounded-start my-1 py-2">
            <i class="bi bi-bar-chart"></i> Estadisticas
        </a>

        @if (isset($indirect_warehouse_id))
            <span class="sidebar-header border-top pt-2 mt-4 disabled">Indirecto</span>

            <a href="{{ route('stock.indirect', ['id' => $indirect_warehouse_id]) }}"
                class="sidebar w-100 p-1 rounded-start my-1 py-2">
                <i class="bi bi-box"></i> SISCO-MRO
            </a>
            {{--<a href="{{ route('lot.index') }}" class="sidebar w-100 p-1 rounded py-2 my-1">
                <i class="bi bi-boxes"></i> Productos
            </a> --}}
        @endif

        <a href="{{ route('purchase-requisition.index') }}" class="sidebar w-100 p-1 rounded-start my-1 py-2">
            <i class="bi bi-file-earmark-text"></i> Compras
        </a>
    </div>
</div>
