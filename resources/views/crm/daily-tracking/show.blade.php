@extends('layouts.app')

@section('content')
    <div class="container-fluid p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Detalle de actividad diaria</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('crm.daily-tracking.edit', $dailyTracking) }}" class="btn btn-primary btn-sm">Editar</a>
                <a href="{{ route('crm.daily-tracking.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header">Datos generales</div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Servicio:</strong> {{ $dailyTracking->service->name ?? 'Sin servicio' }}</p>
                        <p class="mb-1"><strong>Cliente:</strong> {{ $dailyTracking->customer_name }}</p>
                        <p class="mb-1"><strong>Telefono:</strong> {{ $dailyTracking->phone ?? '-' }}</p>
                        <p class="mb-1"><strong>Tipo cliente:</strong> {{ $dailyTracking->customer_type?->label() ?? '-' }}</p>
                        <p class="mb-1"><strong>Metodo contacto:</strong> {{ $dailyTracking->contact_method?->label() ?? '-' }}</p>
                        <p class="mb-1"><strong>Estatus:</strong> {{ $dailyTracking->status?->label() ?? '-' }}</p>
                        <p class="mb-0"><strong>Tipo servicio:</strong> {{ $dailyTracking->service_type?->label() ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header">Datos comerciales</div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Cotizado:</strong> {{ $dailyTracking->quoted?->label() ?? '-' }}</p>
                        <p class="mb-1"><strong>Cerrado:</strong> {{ $dailyTracking->closed?->label() ?? '-' }}</p>
                        <p class="mb-1"><strong>Factura:</strong> {{ $dailyTracking->invoice?->label() ?? '-' }}</p>
                        <p class="mb-1"><strong>Metodo de pago:</strong>
                            {{ $dailyTracking->payment_method?->label() ?? 'No definido' }}</p>
                        <p class="mb-1"><strong>Monto cotizado:</strong>
                            {{ $dailyTracking->quoted_amount ? '$' . number_format((float) $dailyTracking->quoted_amount, 2) : '-' }}
                        </p>
                        <p class="mb-1"><strong>Monto facturado:</strong>
                            {{ $dailyTracking->billed_amount ? '$' . number_format((float) $dailyTracking->billed_amount, 2) : '-' }}
                        </p>
                        <p class="mb-0"><strong>Fecha servicio:</strong>
                            {{ optional($dailyTracking->service_date)->format('d/m/Y') ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header">Notas</div>
            <div class="card-body">
                {{ $dailyTracking->notes ?: 'Sin notas registradas.' }}
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">Historial de cambios (status, quoted, closed)</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Campo</th>
                            <th>Valor anterior</th>
                            <th>Valor nuevo</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailyTracking->logs as $log)
                            <tr>
                                <td>{{ $log->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $log->field }}</td>
                                <td>{{ $log->old_value ?? '-' }}</td>
                                <td>{{ $log->new_value ?? '-' }}</td>
                                <td>{{ $log->changed_by ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Sin historial de cambios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
