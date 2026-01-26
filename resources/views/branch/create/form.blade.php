<!-- Modal -->
<form class="form" method="POST" action="{{ route('branch.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-4 mb-3">
            <label for="name" class="form-label is-required">{{ __('modals.branch_data.name') }}: </label>
            <input type="text" class="form-control" id="name" name="name"
                required>
        </div>
        <div class="col-4 mb-3">
            <label for="email" class="form-label">{{ __('modals.branch_data.email') }}: </label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        <div class="col-4 mb-3">
            <label for="email" class="form-label ">Correo alternativo: </label>
            <input type="email" class="form-control" id="alt-email"
                name="alt_email">
        </div>
        <div class="col-3 mb-3">
            <label for="phone" class="form-label">{{ __('modals.branch_data.phone') }}: </label>
            <input type="number" class="form-control" id="phone" name="phone">
        </div>
        <div class="col-3 mb-3">
            <label for="alt_phone" class="form-label">Teléfono alternativo: </label>
            <input type="number" class="form-control" id="alt_phone"
                name="alt_phone">
        </div>
        <div class="col-2 mb-3">
            <label for="code" class="form-label">Código: </label>
            <input type="number" class="form-control" id="code" name="code">
        </div>
    </div>

    <div class="row">
        <div class="col-4 mb-3">
            <label for="address" class="form-label is-required">{{ __('modals.branch_data.address') }}: </label>
            <input type="text" class="form-control" id="address" name="address"
                placeholder="{{ __('modals.branch_data.address_specify') }}" required>
        </div>
        <div class="col-4 mb-3">
            <label for="colony" class="form-label is-required">{{ __('modals.branch_data.colony') }}: </label>
            <input type="text" class="form-control" id="colony" name="colony"
                required>
        </div>
        <div class="col-2 mb-3">
            <label for="zip_code" class="form-label is-required">{{ __('modals.branch_data.zip_code') }}: </label>
            <input type="number" class="form-control" id="zip_code" name="zip_code"
                required>
        </div>
        <div class="col-2 mb-3">
            <label for="country" class="form-label">{{ __('modals.branch_data.country') }}: </label>
            <select class="form-select  bg-secondary-subtle" id="country" name="country" required>
                <option value="Mex">México</option>
            </select>
        </div>

        <div class="col-3 mb-3">
            <label for="state" class="form-label is-required">{{ __('modals.branch_data.state') }}: </label>
            <select class="form-select " id="state" name="state"
                onchange="load_city()" required>
                <option value="" selected disabled hidden>{{ __('modals.branch_data.state_select') }}</option>
                @foreach ($states as $state)
                    <option value="{{ $state['key'] }}">{{ $state['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-3 mb-3">
            <label for="city" class="form-label is-required">{{ __('modals.branch_data.city') }}: </label>
            <select type="text" class="form-select " id="city" name="city"
                required>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-6 mb-3">
            <label for="license_number" class="form-label fw-bold is-required">NO. de licencia sanitaria (COFEPRIS): </label>
            <input type="text" class="form-control" id="license_number"
                name="license_number" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"> Crear </button>
</form>

<script type="text/javascript">
    function load_city() {
        var select_state = document.getElementById("state");
        var select_city = document.getElementById("city");
        var state = select_state.value;
        // Borra las opciones actuales de select_city
        select_city.innerHTML =
            '<option value="" selected disabled hidden>Selecciona un municipio</option>';
        if (state != "") {
            // Obtén los municipios correspondientes al city seleccionado del JSON
            var cities = {!! json_encode($cities) !!};
            var city = cities[state];
            city.forEach(function(c) {
                var option = document.createElement("option");
                option.text = c;
                option.value = c;
                select_city.appendChild(option);
            });
        }
    }
</script>

<script src="js/actions/forms/create_branch.min.js"></script>
