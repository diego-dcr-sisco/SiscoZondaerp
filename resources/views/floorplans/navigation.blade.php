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
            href="{{ route('floorplan.edit', ['id' => $floorplan->id, 'type' => $type, 'section' => 1]) }}">Datos generales</a>
        <a class="side-bar rounded-start p-2 me-0 text-decoration-none text-white fw-bold"
            href="{{ route('floorplan.edit', ['id' => $floorplan->id, 'type' => $type, 'section' => 2]) }}">Puntos de control</a>
    </nav>
</div>
