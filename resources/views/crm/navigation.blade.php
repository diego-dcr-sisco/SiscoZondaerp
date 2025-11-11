<style>
    .sidebar {
        color: white;
        text-decoration: none;  
    }

    .sidebar:hover {
        background-color: #e9ecef;
        color: #212529;
    }
</style>

<!-- Sidebar -->
<div class="col-1 m-0" style="background-color: #343a40;">
    <div class="row sticky-top" style="font-size: 85%">
        <span class="text-light border-top mb-2 pt-2">CRM</span>

        <a href="{{ Route('customer.index', ['type' => 1, 'page' => 1]) }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-person-check"></i> Clientes
        </a>
        <a href="{{ route('leads.index') }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-person"></i> Leads
        </a>
        <a href="{{ Route('customer.index', ['type' => 2, 'page' => 1]) }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-geo-alt"></i> Sedes
        </a>
        <a href="{{ route('crm.tracking', ['type' => 1]) }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-calendar"></i> Agenda
        </a>
        <a href="{{ route('invoices.index') }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-credit-card"></i> Pagos
        </a>
        <a href="#" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-envelope-at"></i> Marketing
        </a>
        <a href="{{ route('crm.chart.dashboard') }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-bar-chart"></i> <span style="font-size: smaller">Estadisticas</span>
        </a>
    </div>
</div>

