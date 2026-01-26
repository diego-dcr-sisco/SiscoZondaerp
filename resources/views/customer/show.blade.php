@extends('layouts.app')
@section('content')
    @php
        function isPDF($filePath)
        {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            return $extension === 'pdf' || $extension == 'PDF';
        }
    @endphp

    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .flat-btn {
            background-color: #FF6B35;
        }
    </style>

    <div class="row w-100 justify-content-between m-0 h-100">
        <div class="col-1 m-0" style="background-color: #343a40;">
            <div class="row">
                <a class="sidebar col-12 p-2 text-center" data-bs-toggle="collapse" href="#collapseExample" role="button"
                    aria-expanded="false" aria-controls="collapseExample">
                    Datos
                </a>
                <div class="collapse" id="collapseExample" style="background-color: #495057;">
                    <div class="row">
                        <a href="{{ route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 1]) }}"
                            class="sidebar col-12 p-2 text-center">  Generales y fiscales
                        </a>
                        @if ($customer->service_type_id == 3)
                            <a href="{{ route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 2]) }}"
                                class="sidebar col-12 p-2 text-center"> Zonas de alcance
                            </a>
                            <a href="{{ route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 3]) }}"
                                class="sidebar col-12 p-2 text-center"> Portal
                            </a>
                        @endif
                    </div>
                </div>

                @if ($type != 0)
                    @if ($type == 1 && $customer->properties->pluck('id')->contains(1))
                        <a href="{{ Route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 4]) }}"
                            class="sidebar col-12 p-2 text-center">
                            Sedes
                        </a>
                    @endif

                    @if ($customer->properties->pluck('id')->contains(2))
                        <a href="{{ Route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 5]) }}"
                            class="sidebar col-12 p-2 text-center">
                            Referencias
                    @endif
                    </a>
                    @if ($customer->general_sedes != 0)
                        <a href="{{ Route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 6]) }}"
                            class="sidebar col-12 p-2 text-center">
                            Archivos
                        </a>
                        <a href="{{ Route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 7]) }}"
                            class="sidebar col-12 p-2 text-center">
                            Zonas
                        </a>
                        <a href="{{ Route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 8]) }}"
                            class="sidebar col-12 p-2 text-center">
                            Planos
                        </a>
                    @endif

                    @if ($customer->service_type_id == 1 || $customer->general_sedes != 0)
                        <a href="{{ Route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 9]) }}"
                            class="sidebar col-12 p-2 text-center">
                            Ordenes de servicio
                        </a>
                    @endif
                @endif
            </div>
        </div>

        <div class="col-11">
            <div class="row p-3 border-bottom">
                <a {{-- @if ($type == 2) href="javascript:history.back()" @else href="{{ route('customer.index', ['type' => 1, 'page' => 1]) }}" @endif --}}href="{{ route('customer.index', ['type' => 1, 'page' => 1]) }}"
                    class="col-auto btn-primary p-0 fs-3">
                    <i class="bi bi-arrow-left m-3"></i>
                </a>
                <h1 class="col-auto fs-2 m-0">{{ __('customer.title.show') }} <span class="fw-bold">[{{ $customer->name }}]</span> </h1>
            </div>

            <div class="row p-5 pt-3">
                @include('customer.show.general')
            </div>
        </div>
    </div>
@endsection
