<form class="input-group d-flex" method="GET" action="{{ route('lot.search') }}" enctype="multipart/form-data">
    @csrf
    <input class="form-control rounded-0 rounded-start-2" id="search" name="search"
           type="search" placeholder="Buscar lotes por nombre" aria-label="Search" autocomplete="off"
           value="{{ request('search') }}">
    <button type="submit" class="btn btn-success rounded-0 rounded-end-2" data-bs-toggle="tooltip"
            data-bs-placement="bottom" title="Buscar por nombre">
        <i class="bi bi-search"></i> Buscar
    </button>
</form>

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
