@extends('layouts.app')
@section('content')
    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }
    </style>
    
    @include('floorplan.create')
    @include('customer.edit.modals.area')
    @include('customer.modals.files')
    @include('customer.edit.modals.reference')

    <div class="row m-0 w-100 h-100">

        {{--<div class="col-1 m-0" style="background-color: #343a40;">
            <div class="row">
                <a class="sidebar col-12 p-2 text-center" data-bs-toggle="collapse" href="#collapseExample" role="button"
                    aria-expanded="false" aria-controls="collapseExample">
                    Especificaciones
                </a>
                <div class="collapse" id="collapseExample" style="background-color: #495057;">
                    <div class="row">
                        <a href="{{ route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 1]) }}"
                            class="sidebar col-12 p-2 text-center"> Basicas y fiscales
                        </a>
                        @if ($customer->service_type_id == 3)
                            <a href="{{ route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 2]) }}"
                                class="sidebar col-12 p-2 text-center"> Zonas de alcance
                            </a>
                            <a href="{{ route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 3]) }}"
                                class="sidebar col-12 p-2 text-center"> Portal
                            </a>
                        @endif
                    </div>
                </div>
                @if ($type != 0)
                    @if ($customer->general_sedes == 0 && $customer->properties->pluck('id')->contains(1))
                        <a href="{{ Route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 4]) }}"
                            class="sidebar col-12 p-2 text-center">
                            Sedes
                        </a>
                    @endif

                    @if ($customer->properties->pluck('id')->contains(2))
                        <a href="{{ Route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 5]) }}"
                            class="sidebar col-12 p-2 text-center">
                            Referencias
                        </a>

                       @include('customer.edit.modals.reference')
                    @endif

                    @if ($customer->general_sedes != 0)
                        @if ($customer->properties->pluck('id')->contains(5))
                            <a href="{{ Route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 6]) }}"
                                class="sidebar col-12 p-2 text-center">
                                Archivos
                            </a>

                            @include('customer.modals.files')
                        @endif

                        @if ($customer->properties->pluck('id')->contains(3))
                            <a href="{{ Route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 7]) }}"
                                class="sidebar col-12 p-2 text-center">
                                Zonas asss
                            </a>

                            @include('customer.create.modals.area')
                        @endif

                        @if ($customer->properties->pluck('id')->contains(4))
                            <a href="{{ Route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 8]) }}"
                                class="sidebar col-12 p-2 text-center">
                                Planos
                            </a>

                            @include('floorplan.create')
                        @endif
                    @endif
                @endif

                @if ($customer->service_type_id != 1)
                    <a href="{{ Route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 9]) }}"
                        class="sidebar col-12 p-2 text-center">
                        Configuración
                    </a>
                @endif
            </div>
        </div> --}} 
        @include('customer.edit.navigation')

        <div class="col-11">
            <div class="row p-3 border-bottom">
                <a href="{{ route('customer.index') }}" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 m-0">Editar cliente <span class="fw-bold">{{$customer->name}}</span></h1>
            </div>
            <div class="row p-3">
                @include('customer.edit.form')
            </div>
        </div>
    </div>
    
    <script src="{{ asset('js/user/validations.min.js') }}"></script>
@endsection
