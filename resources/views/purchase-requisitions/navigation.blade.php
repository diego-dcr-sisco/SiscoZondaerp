<style>
    .sidebar {
        color: white;
        text-decoration: none;
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

{{-- <div class="col-1 m-0 p-0 bg-dark shadow-sm">
    <nav class="nav flex-column text-start ps-2 text-wrap">
        <a class="side-bar rounded-start p-2 me-0 text-decoration-none text-white fw-bold"
            href="{{ route('purchase-requisition.index') }}">Requisiciones</a>
        <a class="side-bar rounded-start p-2 me-0 text-decoration-none text-white fw-bold"
            href="{{ route('supplier.index') }}">Proveedores</a>
    </nav>
</div> --}}

<!-- Sidebar -->
<div class="col-1 m-0" style="background-color: #343a40;">
    <div class="row sticky-top" style="font-size: small">   
        <a href="{{ route('purchase-requisition.dashboard') }}" class="sidebar w-100 p-1 rounded py-2 my-1">
            <i class="bi bi-house"></i> Inicio
        </a>
        <a href="{{ route('purchase-requisition.index') }}" class="sidebar w-100 py-2 my-1 p-1 rounded">
            <i class="bi bi-file-earmark-text"></i> Requisiciones
        </a>
        <a href="{{ route('supplier.index') }}" class="sidebar w-100 p-1 rounded my-1 py-2">
            <i class="bi bi-person-gear"></i> Proveedores
        </a>
        <a href="{{ route('consumptions.index') }}" class="sidebar w-100 p-1 rounded my-1 py-2">
            <i class="bi bi-clipboard-data"></i> Consumos
        </a>
        <a href="{{ route('stock.index', ['is_active' => 1]) }}" class="sidebar my-1 py-2 w-100 p-1 rounded">
            <i class="bi bi-box"></i> Almacén
        </a>
    </div>
</div>