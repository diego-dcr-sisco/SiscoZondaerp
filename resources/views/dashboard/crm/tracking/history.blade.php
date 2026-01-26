@extends('layouts.app')
@section('content')
    @php
        $spanish_status = [
            'active' => 'Activo',
            'completed' => 'Completado',
            'canceled' => 'Cancelado',
        ];

        $spanish_timetypes = [
            'days' => 'Dias',
            'weeks' => 'Semanas',
            'months' => 'Meses',
        ];
    @endphp

    <style>
        .modal-blur {
            backdrop-filter: blur(5px);
            background-color: rgba(0, 0, 0, 0.3);
        }
    </style>

    <div class="container-fluid p-3">
        @include('dashboard.crm.tracking.search')

        <div class="mb-2">
            <a href="{{ route('crm.tracking.create', ['customerId' => 0, 'serviceId' => 0]) }}" class="btn btn-primary btn-sm">Nuevo seguimiento</a>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Orden</th>
                        <th>Servicio</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Próxima Fecha</th>
                        <th>Rango</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trackings as $tracking)
                        @php
                            $range = json_decode($tracking->range);
                        @endphp
                        <tr>
                            <td>{{ $tracking->trackable->name ?? '-' }}</td>
                            <td>{{ $tracking->order->folio ?? '-' }}</td>
                            <td>{{ $tracking->service->name ?? '-' }}</td>
                            <td>{{ $tracking->title }}</td>
                            <td>{{ $tracking->description ? Str::limit($tracking->description, 100) : '-' }}</td>
                            <td>{{ $tracking->next_date }}</td>
                            <td> {{$range && $range->frequency_type ? 'Cada ' . $range->frequency . ' ' . $spanish_timetypes[$range->frequency_type] : '-' }}</td>
                            <td
                                class="fw-bold
                                    {{ $tracking->status == 'active'
                                        ? 'text-success'
                                        : ($tracking->status == 'completed'
                                            ? 'text-primary'
                                            : ($tracking->status == 'canceled'
                                                ? 'text-danger'
                                                : 'text-secondary')) }}">
                                {{ $spanish_status[$tracking->status] }}
                            </td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <a href="{{ route('crm.tracking.edit', ['id' => $tracking->id]) }}"
                                        class="btn btn-sm btn-secondary">
                                        Editar
                                    </a>
                                    @if ($tracking->status != 'canceled')
                                        <a href="{{ route('crm.tracking.auto', ['id' => $tracking->id]) }}"
                                            class="btn btn-warning btn-sm"
                                            onclick="return confirm('La reprogramación se realiza entorno a la frecuencia configurada, ¿Estas seguro de continuar?')">
                                            Reprogramar</a>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="completedModal({{ $tracking->id }})">
                                            Completar
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="cancelModal({{ $tracking->id }})">
                                            Cancelar
                                        </button>
                                    @endif
                                    <a href="{{ route('crm.tracking.destroy', ['id' => $tracking->id]) }}" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Estas seguro de eliminarlo?')"><i class="bi bi-trash-fill"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $trackings->links('pagination::bootstrap-5') }}

        @include('dashboard.crm.tracking.modals.complete')
        @include('dashboard.crm.tracking.modals.cancel')
    </div>
@endsection
