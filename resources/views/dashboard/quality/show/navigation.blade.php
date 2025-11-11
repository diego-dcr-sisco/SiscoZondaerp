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

<div class="col-1 m-0" style="background-color: #343a40;">
    <div class="row">
        <a href="{{ route('quality.customer.details.general', ['customerId' => $customerId, 'section' => 1, 'status' => 1]) }}"
            class="sidebar col-12 p-2 text-center">
            Pendientes
        </a>
        <a href="{{ route('quality.customer.details.general', ['customerId' => $customerId, 'section' => 1, 'status' => 2]) }}"
            class="sidebar col-12 p-2 text-center">
            Aceptadas
        </a>
        <a href="{{ route('quality.customer.details.general', ['customerId' => $customerId, 'section' => 1, 'status' => 3]) }}"
            class="sidebar col-12 p-2 text-center">
            Finalizadas
        </a>
        <a href="{{ route('quality.customer.details.general', ['customerId' => $customerId, 'section' => 1, 'status' => 4]) }}"
            class="sidebar col-12 p-2 text-center">
            Verificadas
        </a>
        <a href="{{ route('quality.customer.details.general', ['customerId' => $customerId, 'section' => 1, 'status' => 5]) }}"
            class="sidebar col-12 p-2 text-center">
            Aprobadas
        </a>
        <a href="{{ route('quality.customer.details.general', ['customerId' => $customerId, 'section' => 1, 'status' => 6]) }}"
            class="sidebar col-12 p-2 text-center">
            Canceladas
        </a>
    </div>
</div>
