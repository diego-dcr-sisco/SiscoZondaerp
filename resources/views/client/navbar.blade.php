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
        <span class="text-light border-top mb-2 pt-2">Clientes</span>

        <a href="{{ route('client.system.index', ['path' => 'client_system']) }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-folder"></i> Carpetas
        </a>
        <a href="{{ route('client.reports') }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-file-earmark-pdf"></i> Reportes
        </a>
        @can('write_system_client')
        <a href="{{ route('client.mip.index', ['path' => 'mip_directory']) }}" class="sidebar rounded-start py-2 pl-0">
            <i class="bi bi-person-rolodex"></i> MIP
        </a>
        @endcan
    </div>
</div>

