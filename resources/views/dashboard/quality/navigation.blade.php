<style>
    .sidebar {
        color: white;
        text-decoration: none
    }

    .sidebar:hover {
        background-color: #e9ecef;
        color: #212529;
    }
</style>


<div class="row">
    @role('SupervisorCalidad|AdministradorDireccion')
        <a href="{{ route('quality.control') }}" class="sidebar col-12 p-2 text-center">
            Control de calidad
        </a>
    @endrole
    <a href="{{ route('quality.customers') }}" class="sidebar col-12 p-2 text-center">
        Clientes
    </a>
</div>
