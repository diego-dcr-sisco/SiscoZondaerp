<form class="input-group d-flex" method="GET" action="{{ route('service.search', ['page' => 1]) }}"
    enctype="multipart/form-data">
    @csrf
    <input class="form-control rounded-0 rounded-start-2" id="search" name="search"
        type="search" placeholder="{{ __('pagination.search') }}" aria-label="Search" autocomplete="off">
    <button type="submit" class="btn btn-success rounded-0 rounded-end-2" data-bs-toggle="tooltip"
        data-bs-placement="bottom" data-bs-title=" {{ __('service.tips.search') }}"> {{ __('buttons.search') }} </button>
</form>