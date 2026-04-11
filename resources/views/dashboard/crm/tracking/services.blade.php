@extends('layouts.app')
@section('content')
    @php
        $spanish_status = [
            'active' => 'Activo',
            'completed' => 'Completado',
            'canceled' => 'Cancelado',
        ];
    @endphp

    <div class="pb-2">
        <ul class="nav fs-4 border-bottom mb-3">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="javascript:history.back()"><i
                        class="bi bi-arrow-left"></i></a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled text-black fw-bold" aria-current="page" href="#">ANALITICAS DE CLIENTE POR
                    SERVICIOS </a>
            </li>
        </ul>

        <div class="container-fluid">
            <div class="card mb-3">
                <div class="card-header">
                    Servicios
                </div>
                <div class="row p-3">
                    <div class="col-4">
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-action bg-secondary-subtle" aria-current="true">
                                Cantidad de servicios
                            </li>
                            @foreach ($services as $service)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">{{ $service['name'] }}</div>
                                    </div>
                                    <span class="badge text-bg-primary rounded-pill">{{ $service['count'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="col-4">
                        <h6>Cantidad de servicios</h6>
                        <div class="border rounded shadow-sm" style="height: 400px;">
                            <canvas id="servicesChart"></canvas>
                        </div>
                    </div>

                    <div class="col-4">
                        <h6>Seguimientos por servicio</h6>
                        <div class="border rounded shadow-sm" style="height: 400px;">
                            <canvas id="trackingByServicesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    Seguimientos por mes
                </div>
                <div class="row">
                    <div class="col-12">
                        <canvas id="trackingByMonthsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    Lista de seguimientos
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <a href="{{ route('crm.tracking.create', ['customerId' => $customer->id, 'serviceId' => 0]) }}"
                            class="btn btn-primary btn-sm">Nuevo seguimiento</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Servicio</th>
                                <th scope="col">Titulo</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Proxima fecha</th>
                                <th scope="col">Status</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trackings as $tracking)
                                <tr>
                                    <th scope="row">{{ $tracking->service->name }}</th>
                                    <td>{{ $tracking->title }}</td>
                                    <td>{{ $tracking->description }}</td>
                                    <td>
                                        @php
                                            $today = \Carbon\Carbon::today();
                                            $nextDate = \Carbon\Carbon::parse($tracking->next_date);
                                            $colorClass = '';

                                            if ($nextDate->isToday()) {
                                                $colorClass = 'text-warning'; // Amarillo para hoy
                                            } elseif ($nextDate->isFuture()) {
                                                $colorClass = 'text-success'; // Verde para fechas futuras
                                            } else {
                                                $colorClass = 'text-danger'; // Rojo para fechas pasadas
                                            }
                                        @endphp

                                        <span class="fw-bold {{ $colorClass }}">
                                            {{ $tracking->next_date }}
                                        </span>
                                    </td>
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
                                                <a href="{{ route('crm.tracking.destroy', ['id' => $tracking->id]) }}" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Estas seguro de eliminarlo?')"><i class="bi bi-trash-fill"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('dashboard.crm.tracking.modals.complete')
    @include('dashboard.crm.tracking.modals.cancel')


    <script>
        $(document).ready(function() {
            const data = @json($data_charts);

            const ctx = document.getElementById('servicesChart').getContext('2d');
            const servicesChart = new Chart(ctx, {
                type: 'doughnut',
                data: data['services'],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });

            const ctx2 = document.getElementById('trackingByServicesChart').getContext('2d');
            const trackingByServicesChart = new Chart(ctx2, {
                type: 'doughnut',
                data: data['trackingByServices'],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });

            const ctx3 = document.getElementById('trackingByMonthsChart').getContext('2d');
            const trackingByMonthsChart = new Chart(ctx3, {
                type: 'line',
                data: data['trackingByMonths'],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        });
    </script>
@endsection
