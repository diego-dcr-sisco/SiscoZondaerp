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
        <span class="text-light border-top mb-2 pt-2">Planificacion</span>
        <a href="{{ Route('planning.schedule') }}" class="sidebar rounded-start py-2 pl-0 text-center  ">
            Cronograma
        </a>
        <a href="{{ Route('planning.activities') }}" class="sidebar rounded-start py-2 pl-0 text-center ">
            Asignacion de actividades
        </a>
    </div>
</div>

