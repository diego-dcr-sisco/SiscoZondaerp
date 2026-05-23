@extends('layouts.app')

@php
    $status = $customer->customer->status ?? null;
    $badgeClass = match($status) {
        '1' => 'success',
        '0' => 'danger',
        '2' => 'warning',
        default => 'secondary'
    };
    $statusText = match($status) {
        '1' => 'Activo',
        '0' => 'Inactivo',
        '2' => 'Facturable',
        default => 'Desconocido'
    };
    
    // Preparar días de pago para JavaScript
    $diasPago = is_array($customer->payment_days) ? $customer->payment_days : json_decode($customer->payment_days, true);
    $paymentDaysJS = $diasPago && is_array($diasPago) ? 
        array_map(function($dia) {
            return \Carbon\Carbon::parse($dia)->format('Y-m-d');
        }, $diasPago) : [];
@endphp

@section('content')
<div class="container-fluid">
     
    <div class="row border-bottom p-2 mb-3">
        <a href="{{ route('invoices.customers') }}"
            class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
        <h1 class="col-auto fs-2 fw-bold m-0"> Detalles de {{ $customer->customer->name }} </h1>
    </div>
    <div class="row mb-4">
        <div class="col-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span>Calendario de Días de Pago</span>
                </div>
                <div class="card-body">
                    <div id="calendar" class="fc"></div>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center border-bottom">
                    Información General
                    <div class="text-{{ $badgeClass }}">
                        {{ $statusText }}
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Información Personal -->
                        <div class="col-12">
                            <div class="p-3 shadow-sm bg-light rounded-3 mb-1">
                                <h6 class="fw-bold mb-3 text-secondary">
                                    <i class="bi bi-person me-2"></i>Datos Personales
                                </h6>
                                <div class="row g-1">
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">Nombre Fiscal</label>
                                        <span class="fw-medium">{{ $customer->customer->tax_name ?? '-' }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">Email</label>
                                        <span class="fw-medium">
                                            @if($customer->customer->email)
                                                <a href="mailto:{{ $customer->customer->email }}" class="text-decoration-none">
                                                    {{ $customer->customer->email }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">Teléfono</label>
                                        <span class="fw-medium">
                                            @if($customer->customer->phone)
                                                <a href="tel:{{ $customer->customer->phone }}" class="text-decoration-none">
                                                    <i class="bi bi-telephone me-1"></i>
                                                    {{ $customer->customer->phone }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">RFC</label>
                                        <span class="fw-medium">{{ $customer->customer->rfc ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Fiscal -->
                        <div class="col-12">    
                            <div class="p-3 shadow-sm bg-light rounded-3 mb-1">
                                <h6 class="fw-bold mb-3 text-secondary">
                                    <i class="bi bi-file-text me-2"></i>Información Fiscal
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">Saldo Actual</label>
                                        <span class="fw-medium">
                                            <i class="bi bi-currency-dollar"></i>
                                            {{ number_format($customer->credit->current_balance, 2) }}
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">Límite de Crédito</label>
                                        <span class="fw-medium text-success">
                                            <i class="bi bi-currency-dollar"></i>
                                            {{ number_format($customer->credit->limit_amount, 2) }}
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted d-block">Régimen Fiscal</label>
                                        <span class="fw-medium">{{ $customer->customer->taxRegime->name ?? '-' }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">Uso de CFDI</label>
                                        <span class="fw-medium">
                                            {{ $customer->cfdiUsage->code ?? '-' }} 
                                            <small class="text-muted">{{ $customer->cfdiUsage->description ?? '' }}</small>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas y Observaciones -->
                        <div class="col-12">
                            <div class="p-3 shadow-sm bg-light rounded-3">
                                <h6 class="fw-bold mb-3 text-secondary">
                                    <i class="bi bi-calendar-event me-2"></i>Fechas y Observaciones
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">Fecha de Inicio</label>
                                        <span class="fw-medium">
                                            @if($customer->payment_start_date)
                                                <i class="bi bi-calendar2-check me-1"></i>
                                                {{ \Carbon\Carbon::parse($customer->payment_start_date)->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted d-block">Fecha de Fin</label>
                                        <span class="fw-medium">
                                            @if($customer->payment_end_date)
                                                <i class="bi bi-calendar2-x me-1"></i>
                                                {{ \Carbon\Carbon::parse($customer->payment_end_date)->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted d-block">Observaciones</label>
                                        <p class="mb-0 fw-medium">
                                            @if($customer->credit->notes)
                                                <i class="bi bi-chat-left-text me-1"></i>
                                                {{ $customer->credit->notes }}
                                            @else
                                                <span class="text-muted fst-italic">Sin observaciones</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Tabla de facturas del cliente  --}}
    @include('invoices.tables.customer_invoices')
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pasar los días de pago a JavaScript
        const paymentDays = @json($paymentDaysJS);
        
        // Crear eventos para el calendario
        const events = paymentDays.map(day => {
            return {
                title: 'Día de pago',
                start: day,
                allDay: true,
                backgroundColor: '#28a745',
                borderColor: '#28a745'
            };
        });

        // Inicializar el calendario
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: events,
            eventDisplay: 'block',
            height: 'auto'
        });

        calendar.render();

        // Cambiar vista al hacer clic en el botón
        const toggleButton = document.getElementById('toggleView');
        let isMonthView = true;
        
        toggleButton.addEventListener('click', function() {
            if (isMonthView) {
                calendar.changeView('timeGridWeek');
                toggleButton.innerHTML = '<i class="bi bi-calendar-month"></i> Ver mensual';
            } else {
                calendar.changeView('dayGridMonth');
                toggleButton.innerHTML = '<i class="bi bi-calendar-week"></i> Ver semanal';
            }
            isMonthView = !isMonthView;
        });
    });
</script>
<style>
    #calendar {
        max-height: 400px;
    }
    .fc .fc-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }
    .fc .fc-toolbar-title {
        font-size: 1.2em;
        margin-bottom: 4px;
    }
    .fc-toolbar-chunk {
        margin-bottom: 4px;
    }
    .fc .fc-button {
        padding: 0.3em 0.6em;
        font-size: 0.9em;
    }
    .fc-event {
        cursor: default;
    }
    @media (min-width: 768px) {
        .fc .fc-toolbar {
            flex-direction: row;
            align-items: center;
        }
        .fc .fc-toolbar-title {
            margin-bottom: 0;
        }
    }
</style>
@endsection