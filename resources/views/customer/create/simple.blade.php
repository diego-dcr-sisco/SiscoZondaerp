@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR CLIENTE</span>
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('customer.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="name" class="form-label is-required"> {{ __('customer.data.name') }}: </label>
                        <input type="text" class="form-control " id="name" name="name" placeholder="Example"
                            maxlength="50" required>
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="email" class="form-label">{{ __('customer.data.email') }}: </label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="50"
                            placeholder="example@mail.com" autocomplete="off">
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="address" class="form-label is-required">{{ __('customer.data.address') }}</label>
                        <input type="text" class="form-control" id="address" name="address" maxlength="50"
                            placeholder="#00 Col. Example" required>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="phone" class="form-label is-required">{{ __('customer.data.phone') }}</label>
                        <input type="text" min=1 class="form-control" id="phone" placeholder="0000000000"
                            maxlength="25" name="phone" autocomplete="off" required>
                    </div>

                    <div class="col-lg-3 col-12 mb-3">
                        <label for="zip_code" class="form-label is-required">{{ __('customer.data.zip_code') }} :</label>
                        <input type="text" class="form-control" name="zip_code" placeholder="00000"
                            minlength="5" maxlength="5" id="zip_code" required>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="state" class="form-label is-required">{{ __('customer.data.state') }}: </label>
                        <select class="form-select " id="state" name="state" onchange="load_city()" required>
                            <option value="" selected>Selecciona un estado</option>
                            @foreach ($states as $state)
                                <option value="{{ $state['key'] }}">{{ $state['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="city" class="form-label is-required">{{ __('customer.data.city') }}: </label>
                        <select type="text" class="form-select " id="city" name="city" required>
                            <option value="" selected disabled hidden>Selecciona un municipio</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-12 mb-3">
                        <label for="serv" class="form-label is-required">{{ __('customer.data.type') }} :</label>
                        <select type="text" class="form-select " id="service-type" name="service_type_id" required>
                            @foreach ($service_types as $service)
                                <option value="{{ $service->id }}"
                                    {{ !empty($customer) && $customer->service_type_id == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-12 mb-3">
                        <label for="company_category_id"
                            class="form-label is-required">{{ __('customer.data.category') }}:</label>
                        <select type="text" class="form-select" name="company_category_id">
                            <option value="" selected>Sin Categoria</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ !empty($customer) && $customer->company_category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="branch" class="form-label is-required">{{ __('customer.data.branch') }}:</label>
                        <select type="text" class="form-select " name="branch_id" id="branch">
                            @foreach ($branches as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="branch" class="form-label is-required">Método de contacto:</label>
                        <select type="text" class="form-select " name="contact_medium" id="contact-medium">
                            @foreach ($contact_medium as $key => $medium)
                                <option value="{{ $key }}">{{ $medium }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="url" class="form-label">{{ __('customer.data.url_map') }}:</label>
                        <input type="text" class="form-control" id="map_location_url" name="map_location_url"
                            placeholder="https://www.google.com/maps?q=latitude,longitude&hl=en" />
                    </div>
                </div>
            </div>

            <input type="hidden" name="general_sedes" value="0">

            <button type="submit" class="btn btn-primary my-3">
                {{ __('buttons.store') }}
            </button>

        </form>
    </div>

    <script type="text/javascript">
        var states = @json($states);
        var cities = @json($cities);

        $(document).ready(function() {
        });

        function load_city() {
            var state = $("#state").val();
            var $selector_city = $("#city");

            $selector_city.empty(); // Clear previous options

            if (state) {
                var found_cities = cities[state] || [];
                $selector_city.append(found_cities.map(c => $('<option>', {
                    value: c,
                    text: c
                })));
            }
        }

        function convertToUppercase(id) {
            $("#" + id).val($("#" + id).val().toUpperCase());
        }
    </script>
@endsection
