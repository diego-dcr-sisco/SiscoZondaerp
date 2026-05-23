<div class="modal fade" id="createRefModal" tabindex="-1" aria-labelledby="createRefModalLabel" aria-hidden="true">
    <form class="modal-dialog" action="{{ route('customer.reference.store', ['id' => $id, 'type' => $type]) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="createRefModalLabel">Crear zona/área</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="row mb-3">
                    <div class="row mb-3">
                        <div class="col-3">
                            <label for="name" class="form-label is-required">{{ __('customer.customer_table.type_ref') }}</label>
                            <select class="form-select " name="reference_type_id" id="type" required
                                onchange="showLocation(this.value)">
                                @foreach ($reference_types as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-4 mb-2">
                        <label for="name" class="form-label is-required">{{ __('customer.customer_table.name') }}</label>
                        <input class="form-control" type="text" maxlength="255" name="name" required>
                    </div>
                    <div class="col-4 mb-2">
                        <label for="phone" class="form-label is-required">{{ __('customer.customer_table.phone') }}</label>
                        <input type="number" minlength="0" maxlength="10" class="form-control" name="phone"
                            required>
                    </div>
                    <div class="col-4 mb-2">
                        <label for="email" class="form-label is-required">{{ __('customer.customer_table.correo') }}</label>
                        <input type="text" class="form-control" name="email" required>
                    </div>
                    <div class="col-4 mb-2">
                        <label for="department"
                            class="form-label is-required">{{ __('customer.customer_table.department') }}</label>
                        <input type="text" class="form-control" name="department" required>
                    </div>
            
                    <div class="col-12 location mb-0">
                        <div class="row">
                            <div class="col-4">
                                <label for="address" class="form-label">{{ __('customer.customer_table.address') }}</label>
                                <input type="text" class="form-control" name="address">
                            </div>
                            <div class="col-2">
                                <label for="zip-code" class="form-label">{{ __('customer.customer_table.zipcode') }}</label>
                                <input type="number" minlength="0" maxlength="5" class="form-control"
                                    name="zip_code">
                            </div>
                            <div class="col-2">
                                <label for="state" class="form-label">{{ __('customer.data.state') }}: </label>
                                <select class="form-select " id="state" name="state" onchange="load_city()">
                                    <option value="" selected disabled hidden></option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state['key'] }}">{{ $state['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text" id="basic-addon4">{{ __('modals.branch_data.state_select') }}</div>
                            </div>
                            <div class="col-3">
                                <label for="city" class="form-label">{{ __('customer.data.city') }}: </label>
                                <select type="text" class="form-select " id="city" name="city">
                                    @foreach ($states as $state)
                                        @foreach ($cities[$state['key']] as $city)
                                            <option value="{{ $city }}">{{ $city }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                                <div class="form-text" id="basic-addon4">Selecciona primero el <b>Estado</b> y después la ciudad.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"> {{ __('buttons.store') }} </button>
            </div>
        </div>
    </form>
</div>

script>
    $(document).ready(function() {
        var select = $("#type").val();
        if (select != 1) {
            $(".location").show();
        } else {
            $(".location").hide();
        }

        $('.form').submit(function() {
            if (select.val() == 1) {
                item.show();
            }
        });
    });

    function showLocation(value) {
        var item = $(".location");
        if (value != 1) {
            item.show();
        } else {
            item.hide();
        }
    }
</script>

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
            var cities = {!! json_encode($cities) !!}
            var city = cities[state];
            city.forEach(function(c) {
                var option = document.createElement("option");
                option.text = c;
                option.value = c;
                select_city.appendChild(option);
            });
        }
    }

    function convertToUppercase(id) {
        $("#" + id).val($("#" + id).val().toUpperCase());
    }
</script>
