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


    <div class="row w-100 justify-content-between m-0 h-100">
        <div class="col-1 m-0" style="background-color: #343a40;">
            <div class="row">
                <a href="{{ Route('order.show', ['id' => $order->id, 'section' => 1]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Datos generales
                </a>
                <a href="{{ Route('order.show', ['id' => $order->id, 'section' => 2]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Servicios
                </a>
                <a href="{{ Route('order.show', ['id' => $order->id, 'section' => 3]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Técnicos
                </a>
                <a href="{{ Route('order.show', ['id' => $order->id, 'section' => 4]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Detalles técnicos adicionales
                </a>
                <a href="{{ Route('order.show', ['id' => $order->id, 'section' => 5]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Tabla de cambios
                </a>
            </div>
        </div>

        <div class="col-11">
            <div class="row p-3 border-bottom">
                <a href="{{ route('order.index', ['page' => 1]) }}" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 fw-bold m-0">Ver orden de servicio {{ $order->id }}
                </h1>
            </div>

            <div class="row p-5 pt-3">
                @if ($section == 1)
                    <div class="row ">
                        <span class="col fw-bold">{{ __('order.data.managed_by') }}:</span>
                        <span class="col"> {{ $order->administrative->user->name }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.customer') }}:</span>
                        <span class="col"> {{ $order->customer->name }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.start_time') }}:</span>
                        <span class="col">{{ $order->start_time }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.end_time') }}:</span>
                        <span class="col">{{ $order->end_time ?? 'S/A' }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.created_at') }}:</span>
                        <span class="col"> {{ $order->created_at }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.programmed_date') }}:</span>
                        <span class="col"> {{ $order->programmed_date }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.completed_date') }}:</span>
                        <span class="col"> {{ $order->completed_date ?? 'S/A' }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.status') }}:</span>
                        <span
                            class="col fw-bold 
                                    {{ $order->status_id == 1
                                        ? 'text-warning'
                                        : ($order->status_id == 2 || $order->status_id == 3
                                            ? 'text-primary'
                                            : ($order->status_id == 4 || $order->status_id == 5
                                                ? 'text-success'
                                                : 'text-danger')) }}">
                            {{ $order->status->name }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.cost') }}:</span>
                        <span class="col">{{ $order->price ?? 'S/A' }}</span>
                    </div>
                @endif

                @if ($section == 2)
                    @foreach ($order->services as $i => $service)
                        <span class="col fw-bold">{{ __('order.data.service') . ' ' . ($i + 1) }}
                            <span
                                class="{{ $order->status_id == 1
                                    ? 'text-warning'
                                    : ($order->status_id == 2 || $order->status_id == 3
                                        ? 'text-primary'
                                        : ($order->status_id == 4 || $order->status_id == 5
                                            ? 'text-success'
                                            : 'text-danger')) }}">
                                ({{ $order->status->name }})</span>
                        </span>
                        <span class="col"> {{ $service->name }}</span>
            </div>
            @endforeach
            @endif

            @if ($section == 3)
                @foreach ($order->technicians as $i => $technician)
                    <div class="row">
                        <span class="col fw-bold">{{ __('order.data.technician') . ' ' . ($i + 1) }}:</span>
                        <span class="col"> {{ $technician->user->name }}</span>
                    </div>
                @endforeach
            @endif

            @if ($section == 4)
                <div class="row mb-2">
                    <span class="col fw-bold">{{ __('order.data.execution') }}:</span>
                    <span class="col"> {!! nl2br(e($order->execution)) !!}</span>
                </div>
                <div class="row mb-2">
                    <span class="col fw-bold">{{ __('order.data.areas') }}:</span>
                    <span class="col"> {!! nl2br(e($order->areas)) !!}</span>
                </div>
                <div class="row mb-2">
                    <span class="col fw-bold">{{ __('order.data.comments') }}:</span>
                    <span class="col"> {!! nl2br(e($order->additional_comments)) !!}</span>
                </div>
            @endif

            @if ($section == 5)
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Usuario</th>
                            <th scope="col">Tipo de Cambio</th>
                            <th scope="col">Cambio</th>
                            <th scope="col">Dia y Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tablelog as $log)
                            <tr>
                                <th>{{ $log->user->name }} </th>
                                <td>
                                    @if ($log->changetype == 'store')
                                        CREADA
                                    @elseif($log->changetype == 'update')
                                        ACTUALIZADA
                                    @else
                                        CANCELADA
                                    @endif
                                </td>
                                <td>{{ $log->change }}</td>
                                <td>{{ Carbon\Carbon::parse($log->created_at, 'UTC')->setTimezone('America/Mexico_City')->format('Y-m-d H:i:s') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    </div>
@endsection
