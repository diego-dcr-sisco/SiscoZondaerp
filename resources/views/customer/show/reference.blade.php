@extends('layouts.app')

@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <div class="w-100 m-0">
        <div class="row w-100 justify-content-between p-3 m-0 mb-3">
            <!-- Header -->
            <div class="col-12 border-bottom pb-2">
                <div class="row">
                    <a href="javascript:history.back()" class="col-auto btn-primary p-0 fs-3"><i
                            class="bi bi-arrow-left m-3"></i></a>
                    <h1 class="col-auto fs-2 fw-bold m-0">
                        Ver referencia
                    </h1>
                </div>
            </div>
            <div class="col-12">

                @if ($reference->reference_type_id == 2)
                    <h2 class="test-start fs-5 fw-blod mb-2 pb-2">{{ __('customer.title.infrefcom') }}</h2>
                @endif
                @if ($reference->reference_type_id == 3)
                    <h2 class="test-start fs-5 fw-blod mb-2 pb-2">{{ __('customer.title.infrefcuent') }}</h2>
                @endif
                @if ($reference->reference_type_id == 1)
                    <h2 class="test-start fs-5 fw-blod mb-2 pb-2">{{ __('customer.title.infrefserv') }}</h2>
                @endif
                <label for="type">Tipo de referencia:</label>
                <select class="form-select" id="type_{{ $reference->id }}" name="type" disabled>
                    @foreach ($reference_types as $type)
                        <option value="{{ $type->id }}"
                            {{ $type->id == $reference->reference_type_id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>

                <div class="row">
                    <span class="col fw-bold">Nombre:</span>
                    <span class="col"> {{ $reference->name }}</span>
                </div>
                <div class="row">
                    <span class="col fw-bold">Teléfono:</span>
                    <span class="col"> {{ $reference->phone }}</span>
                </div>
                <div class="row">
                    <span class="col fw-bold">Correo:</span>
                    <span class="col"> {{ $reference->email }}</span>
                </div>
                <div class="row">
                    <span class="col fw-bold">Departamento:</span>
                    <span class="col"> {{ $reference->department }}</span>
                </div>
                <div class="hide" id="hiddenDiv_{{ $reference->id }}">
                    <div class="row">
                        <span class="col fw-bold">Dirección:</span>
                        <span class="col"> {{ $reference->address }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">Código Postal:</span>
                        <span class="col"> {{ $reference->zip_code }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('customer.customer_table.state') }}:</span>
                        <span class="col"> {{ $reference->state }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('customer.customer_table.city') }}:</span>
                        <span class="col"> {{ $reference->city }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var citiesData = @json($cities);
        var statesData = @json($states);
    </script>

    <script src="{{ asset('js/customer/validations.min.js') }}"></script>
    <script src="{{ asset('js/customer/toggleCity.min.js') }}"></script>
    <script src="{{ asset('js/customer/togglediv.min.js') }}"></script>
@endsection
