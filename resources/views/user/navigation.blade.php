<style>
    .side-bar:hover {
        white-space: wrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #212529 !important;
        background-color: #fff;
    }

</style>

<div class="col-1 m-0 p-0 bg-dark shadow-sm">
    <nav class="nav flex-column text-center ps-2 text-wrap">
        <a class="side-bar rounded-start p-2 me-0 text-decoration-none text-white fw-bold"
            href="{{ route('user.index', ['type' => 1]) }}">Equipo interno</a>
        <a class="side-bar rounded-start p-2 me-0 text-decoration-none text-white fw-bold"
            href="{{ route('user.index', ['type' => 2]) }}">Clientes</a>
    </nav>
</div>
