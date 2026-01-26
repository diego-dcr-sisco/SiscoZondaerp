@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR SEDE</span>
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('customer.store.sede') }}" enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="name" class="form-label is-required">Matriz: </label>
                        @isset($customer_matrix)
                            <input type="hidden" id="customer-matrix" name="customer_matrix"
                                value="{{ $customer_matrix?->id }}" />
                            <input type="text" class="form-control" value="{{ $customer_matrix?->name }}" disabled  >
                        @else
                            <select class="form-select" id="customer-matrix" name="customer_matrix">
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}"
                                        {{ $customer_matrix && $customer_matrix?->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endisset
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="name" class="form-label is-required"> {{ __('customer.data.name') }}: </label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="Sede - {{ $customer_matrix?->name }}" maxlength="50">
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="email" class="form-label">{{ __('customer.data.email') }}: </label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="{{ $customer_matrix?->email }}" maxlength="50" placeholder="example@mail.com"
                            autocomplete="off">
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="address" class="form-label is-required">{{ __('customer.data.address') }}</label>
                        <input type="text" class="form-control" id="address" name="address"
                            value="{{ $customer_matrix?->address }}" maxlength="50" placeholder="#00 Col. Example"
                            required>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="phone" class="form-label is-required">{{ __('customer.data.phone') }}</label>
                        <input type="text" min=1 class="form-control" id="phone"
                            value="{{ $customer_matrix?->phone }} " placeholder="0000000000" maxlength="25" name="phone"
                            autocomplete="off" required>
                    </div>

                    <div class="col-lg-3 col-12 mb-3">
                        <label for="zip_code" class="form-label is-required">{{ __('customer.data.zip_code') }} :</label>
                        <input type="text" class="form-control" name="zip_code"
                            value="{{ $customer_matrix?->zip_code }}" placeholder="00000" minlength="5" maxlength="5"
                            id="zip_code" required>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="state" class="form-label is-required">{{ __('customer.data.state') }}: </label>
                        <select class="form-select " id="state" name="state" onchange="load_city()" required>
                            <option value="" selected>Selecciona un estado</option>
                            @foreach ($states as $state)
                                <option value="{{ $state['key'] }}"
                                    {{ $customer_matrix?->state == $state['key'] ? 'selected' : '' }}>{{ $state['name'] }}
                                </option>
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
                                    {{ !empty($customer_matrix) && $customer_matrix?->service_type_id == $service->id ? 'selected' : '' }}>
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
                                    {{ !empty($customer_matrix) && $customer_matrix?->company_category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="branch" class="form-label is-required">{{ __('customer.data.branch') }}:</label>
                        <select type="text" class="form-select " name="branch_id" id="branch">
                            @foreach ($branches as $item)
                                <option value="{{ $item->id }}"
                                    {{ $customer_matrix?->branch_id == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="branch" class="form-label is-required">Método de contacto:</label>
                        <select type="text" class="form-select " name="contact_medium" id="contact-medium">
                            @foreach ($contact_medium as $key => $medium)
                                <option value="{{ $key }}"
                                    {{ $customer_matrix?->contact_medium == $key ? 'selected' : '' }}>{{ $medium }}
                                </option>
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

            <button type="submit" class="btn btn-primary my-3">
                {{ __('buttons.store') }}
            </button>

        </form>
    </div>

    <script type="text/javascript">
        var states = @json($states);
        var cities = @json($cities);

        $(document).ready(function() {
            load_city();
            $('#city').val('{{ $customer_matrix?->city }}');
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
