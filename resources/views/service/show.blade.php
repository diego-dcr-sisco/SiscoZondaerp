@extends('layouts.app')
@section('content')
    @php
        $time_types = ['Segundo(s)', 'Minuto(s)', 'Hora(s)'];
        $i = 1;
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
    </style>

    <div class="row w-100 justify-content-between m-0 h-100">
        <div class="col-1 m-0" style="background-color: #343a40;">
            <div class="row">
                <a href="{{ Route('service.show', ['id' => $service->id, 'section' => 1]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Datos generales
                </a>
                <a href="{{ Route('service.show', ['id' => $service->id, 'section' => 2]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Plagas
                </a>
                <a href="{{ Route('service.show', ['id' => $service->id, 'section' => 3]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Métodos de aplicación
                </a>
            </div>
        </div>


        <div class="col-11">
            <div class="row p-3 border-bottom">
                <a href="{{ route('service.index', ['page' => 1]) }}" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 m-0">{{ __('service.title.show') }} <span class="fw-bold"> [{{ $service->name }}] </span></h1>
            </div>
            <div class="row p-5 pt-3">
                @switch($section)
                    @case(1)
                        <div class="row">
                            <span class="col fw-bold">{{ __('service.data.name') }}:</span>
                            <span class="col fw-normal">{{ $service->name }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('service.data.duration') }}:</span>
                            <span class="col fw-normal">
                                {{ $service->time }} @php echo($time_types[$service->time_unit - 1]) @endphp
                            </span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('service.data.type') }}:</span>
                            <span class="col fw-normal">
                                {{ $service->serviceType->name }}
                            </span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('service.data.business_line') }}:</span>
                            <span class="col fw-normal">
                                {{ $service->businessLine->name }}
                            </span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('service.data.description') }}:</span>
                            <p class="col fw-normal" style="text-align: justify;">{!! nl2br(e($service->description)) !!}</p>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('service.data.cost') }}:</span>
                            <span class="col fw-normal">${{ $service->cost }}</span>
                        </div>
                    @break

                    @case(2)
                        @if (!$service->pests->isEmpty())
                            @foreach ($service->pests as $i => $pest)
                                <div class="row">
                                    <span class="col fw-bold">{{ __('service.data.pest') }} {{ $i + 1 }}:</span>
                                    <span class="col fw-normal">{{ $pest->name }}</span>
                                </div>
                            @endforeach
                        @else
                            <span class="col fw-bold text-danger"> Sin plagas agregadas </span>
                        @endif
                    @break

                    @case(3)
                        @if (!$service->appMethods->isEmpty())
                            @foreach ($service->appMethods as $i => $appMethod)
                                <div class="row">
                                    <span class="col fw-bold">{{ __('service.data.app_method') }} {{ $i + 1 }}:</span>
                                    <span class="col fw-normal">{{ $appMethod->name }}</span>
                                </div>
                            @endforeach
                        @else
                            <span class="col fw-bold text-danger"> Sin metodos agregados agregadas </span>
                        @endif
                    @break
                @endswitch
            </div>
        </div>
    </div>
@endsection
