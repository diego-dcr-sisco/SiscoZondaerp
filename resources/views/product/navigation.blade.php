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
        <a href="{{ route('stock.index', ['is_active' => 1]) }}" class="sidebar my-1 py-2 w-100 p-1 rounded">
            <i class="bi bi-box"></i> Almacenes
        </a>
        <a href="{{ route('lot.index') }}" class="sidebar w-100 p-1 rounded py-2 my-1">
            <i class="bi bi-boxes"></i> Lotes
        </a>
        <a href="{{ route('product.index') }}" class="sidebar w-100 p-1 rounded py-2 my-1">
            <i class="bi bi-box"></i> Productos
        </a>
        <a href="{{ route('stock.movements.all') }}" class="sidebar w-100 py-2 my-1 p-1 rounded">
            <i class="bi bi-arrow-left-right"></i> Movimientos
        </a>
        <a href="{{ route('stock.analytics') }}" class="sidebar w-100 p-1 rounded my-1 py-2">
            <i class="bi bi-bar-chart"></i> Estadisticas
        </a>

    </div>
</div>
