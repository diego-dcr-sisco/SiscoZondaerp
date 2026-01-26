<form method="POST" class="form" action="{{ route('warehouse.update', $warehouse->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-6 mb-3">
            <label for="name" class="form-label is-required"> Nombre del almacen </label>
            <input type="text" class="form-control border-secondary border-opacity-50" id="name" name="name" value="{{ $warehouse->name }}" required>
        </div>
        
        <div class="col-auto mb-3">
            <label for="receive_material" class="form-label is-required">Permitir recepciones de material </label>
            <select class="form-select border-secondary border-opacity-50" id="receive-material" name="receive_material">
                <option value="1" {{ $warehouse->receive_material ? 'selected' : '' }} >Sí</option>
                <option value="0" {{ $warehouse->receive_material ? '' : 'selected' }} >No</option>
            </select>
        </div>

        <div class="col-auto mb-3">
            <label for="receive_material" class="form-label is-required">Esta activo? </label>
            <select class="form-select border-secondary border-opacity-50" id="active" name="active">
                <option value="1" {{ $warehouse->active ? 'selected' : '' }} >Sí</option>
                <option value="0" {{ $warehouse->active ? '' : 'selected' }} >No</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-4 mb-3">
            <label for="phone" class="form-label is-required">{{ __('customer.data.phone') }}</label>
            <input type="number" class="form-control border-secondary border-opacity-50" id="phone" name="phone" value="{{ $warehouse->phone }}" placeholder="0000000000" autocomplete="off" min="0">
        </div>
        <div class="col-4 mb-3">
            <label for="address" class="form-label is-required">{{ __('customer.data.address') }}</label>
            <input type="text" class="form-control border-secondary border-opacity-50" id="address" name="address" value="{{ $warehouse->address }}" placeholder="#00 Col. Example" required>
        </div>
        <div class="col-2 mb-3">
            <label for="zip_code" class="form-label is-required">{{ __('customer.data.zip_code') }} </label>
            <input type="number" min=10000 max=99999 class="form-control border-secondary border-opacity-50" id="zip_code" name="zip_code" value="{{ $warehouse->zip_code}}" placeholder="00000" required>
        </div>
        <div class="col-2 mb-3">
            <label for="state" class="form-label is-required">{{ __('customer.data.state') }} </label>
            <select class="form-select border-secondary border-opacity-50" id="state" name="state" onchange="load_city()" required>
                <option value="" selected disabled hidden></option>
                @foreach ($states as $state)
                    @if ($state['key'] == $warehouse->state)
                        <option value="{{ $state['key'] }}" selected>{{ $state['name'] }}</option>
                    @else
                        <option value="{{ $state['key'] }}">{{ $state['name'] }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-2 mb-3">
            <label for="city" class="form-label is-required">{{ __('customer.data.city') }}</label>
            <select class="form-select border-secondary border-opacity-50" id="city" name="city" required>
                <option value="" selected disabled hidden>Selecciona un municipio</option>
                @foreach ($states as $state)
                    @if ($state['key'] == $warehouse->state)
                        @foreach ($cities[$state['key']] as $city)
                            @if ($city == $warehouse->city)
                                <option value="{{ $city }}" selected>{{ $city }}</option>
                            @else
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </select>
        </div>
        
        <div class="col-3 mb-3">
            <label for="branch" class="form-label is-required">{{ __('customer.data.branch') }}</label>
            <select type="text" class="form-select border-secondary border-opacity-50 " name="branch_id" id="branch">
                @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" {{ $branch->id == $warehouse->branch_id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-3">
            <label class="form-label" for="observations"> Observaciones </label>
            <textarea type="text" class="form-control border-secondary border-opacity-50" id="observations" name="observations" rows="4">{{ $warehouse->observations }}</textarea>
        </div>
    </div>
    <button type="submit" class="btn btn-primary mt-3">
        {{ __('buttons.update') }}
    </button>
</form>
<script src="{{ asset('js/user/validations.min.js') }}"></script>


<script type="text/javascript">
    function load_city() {
        var select_state = $("#state");
        var select_city = $("#city");
        var state = select_state.val();

        select_city.html('<option value="" selected disabled hidden>Selecciona un municipio</option>');

        if (state !== "") {
            var cities = @json($cities);
            var cityOptions = cities[state].map(city => `<option value="${city}">${city}</option>`);

            select_city.append(cityOptions.join(''));
        }
    }


    function convertToUppercase(id) {
        $("#" + id).val($("#" + id).val().toUpperCase());
    }
</script>
