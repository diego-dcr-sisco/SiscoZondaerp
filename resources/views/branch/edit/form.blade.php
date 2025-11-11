@extends('layouts.app')
@section('content')
    @php
        function formatPath($path)
        {
            return str_replace(['/', ' '], ['-', ''], $path);
        }
    @endphp

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('branch.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR SUCURSAL <span class="ms-2 fs-4"> {{ $branch->name }}</span>
            </span>
        </div>

        <form method="POST" action="{{ route('branch.update', ['id' => $branch->id]) }}" class="m-3"
            enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('modals.branch_data.name') }}:
                        </label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $branch->name }}"
                            required>
                    </div>
                    <div class="col-lg-2 col-12 mb-3">
                        <label for="code" class="form-label">Código: </label>
                        <input type="text" class="form-control" id="code" name="code"
                            value="{{ $branch->code }}">
                    </div>

                    <div class="col-lg-6 col-12 mb-3">
                        <label for="address" class="form-label is-required">{{ __('modals.branch_data.address') }}:
                        </label>
                        <input type="text" class="form-control" id="address" name="address"
                            placeholder="{{ __('modals.branch_data.address_specify') }}" value="{{ $branch->address }}"
                            required>
                    </div>
                    <div class="col-lg-2 col-12 mb-3">
                        <label for="zip_code" class="form-label is-required">{{ __('modals.branch_data.zip_code') }}:
                        </label>
                        <input type="number" class="form-control" id="zip_code" name="zip_code"
                            value="{{ $branch->zip_code }}" required>
                    </div>
                    <div class="col-lg-2 col-12 mb-3">
                        <label for="country" class="form-label">{{ __('modals.branch_data.country') }}: </label>
                        <select class="form-select  bg-secondary-subtle" id="country" name="country" required>
                            <option value="Mex">México</option>
                        </select>
                    </div>

                    <div class="col-lg-4 col-12 mb-3">
                        <label for="state" class="form-label is-required">{{ __('modals.branch_data.state') }}:
                        </label>
                        <select class="form-select " id="state" name="state" onchange="load_city()" required>
                            @foreach ($states as $state)
                                @if ($state['key'] === $branch->state)
                                    <option value="{{ $state['key'] }}" selected>{{ $state['name'] }}</option>
                                @else
                                    <option value="{{ $state['key'] }}">{{ $state['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="city" class="form-label is-required">{{ __('modals.branch_data.city') }}:
                        </label>
                        <select type="text" class="form-select " id="city" name="city" required>
                            @foreach ($states as $state)
                                @if ($state['key'] == $branch->state)
                                    @foreach ($cities[$state['key']] as $city)
                                        @if ($city == $branch->city)
                                            <option value="{{ $city }}" selected>{{ $city }}</option>
                                        @else
                                            <option value="{{ $city }}">{{ $city }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="license_number" class="form-label fw-bold is-required">NO. de licencia sanitaria
                            (COFEPRIS): </label>
                        <input type="text" class="form-control" id="license_number" name="license_number"
                            value="{{ $branch->license_number }}" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary my-3"> {{ __('buttons.store') }} </button>
        </form>
    </div>

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
@endsection
