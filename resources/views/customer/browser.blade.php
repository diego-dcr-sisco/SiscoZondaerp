<form class="input-group d-flex" method="GET"
    action="{{ route('customer.search', ['type' => $type, 'page' => 1]) }}" enctype="multipart/form-data">
    @csrf
    <input type="search" class="form-control rounded-0 rounded-start-2" id="search"
        name="search" placeholder="{{ __('pagination.search') }}" autocomplete="off">
    <button type="submit" class="btn btn-success rounded-0 rounded-end-2" data-bs-toggle="tooltip"
    data-bs-placement="bottom" data-bs-title=" {{ __('customer.tips.search') }}"> {{ __('buttons.search') }} </button>
</form>

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
</script>