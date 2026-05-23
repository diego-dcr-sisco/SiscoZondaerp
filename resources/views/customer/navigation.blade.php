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
    <div class="row sticky-top" style="font-size: 85%">
        <a href="{{ route('crm.agenda', ['is_active' => 1]) }}" class="sidebar my-1 py-2 w-100 rounded-start p-1 ">
            <i class="bi bi-house"></i> CRM
        </a>
        <a href="{{ Route('customer.index', ['type' => 1, 'page' => 1]) }}" class="sidebar w-100 p-1 rounded-start py-2 my-1">
            <i class="bi bi-person-check"></i> Clientes
        </a>
        <a href="{{ route('leads.index') }}" class="sidebar w-100 p-1 rounded-start py-2 my-1">
            <i class="bi bi-person"></i> Leads
        </a>
        <a href="{{ Route('customer.index', ['type' => 2, 'page' => 1]) }}" class="sidebar w-100 py-2 my-1 p-1 rounded-start">
            <i class="bi bi-geo-alt"></i> Sedes
        </a>
        <a href="{{ route('crm.chart.dashboard') }}" class="sidebar w-100 p-1 rounded-start my-1 py-2">
            <i class="bi bi-bar-chart"></i> Estadisticas
        </a>
    </div>
</div>
