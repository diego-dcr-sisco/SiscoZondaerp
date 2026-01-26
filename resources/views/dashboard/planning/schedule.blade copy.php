@extends('layouts.app')
@section('content')
    <div class="container-fluid py-3">
        <!-- Encabezado -->
        <div class="d-flex align-items-center mb-4">
            <h5 class="text-dark mb-0">
                <i class="bi bi-calendar-event me-2"></i>Cronograma de Actividades
            </h5>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('planning.schedule') }}" method="GET">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="date_range" class="form-label small mb-1">Rango de Fechas</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="text" class="form-control date-range-picker" id="date-range"
                                    name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona rango"
                                    autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="col-md-9 text-end pt-2">
                            <button type="submit" class="btn btn-primary btn-sm me-1">
                                <i class="bi bi-funnel me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('order.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cronograma -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" style="width: 100px;">Hora</th>
                                <th>Órdenes Programadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($timelapse as $hrs)
                                @php
                                    $data = $schedule_data[$hrs] ?? [];
                                @endphp
                                <tr>
                                    <td class="ps-3 fw-medium bg-light">{{ $hrs }}</td>
                                    <td class="p-2">
                                        @if(count($data) > 0)
                                            <div class="row g-2">
                                                @foreach ($data as $schedule)
                                                    @php
                                                        // Determinar color según el estado
                                                        $statusColor = 'secondary';
                                                        if ($schedule['status'] == 'Pendiente') $statusColor = 'warning';
                                                        elseif ($schedule['status'] == 'Completado') $statusColor = 'success';
                                                        elseif ($schedule['status'] == 'Cancelado') $statusColor = 'danger';
                                                        elseif ($schedule['status'] == 'En proceso') $statusColor = 'info';
                                                        
                                                        // Determinar tipo de cliente
                                                        $customerType = 'Doméstico';
                                                        if (strpos(strtolower($schedule['customer']), 'comercial') !== false || 
                                                            strpos(strtolower($schedule['customer']), 'empresa') !== false) {
                                                            $customerType = 'Comercial';
                                                        } elseif (strpos(strtolower($schedule['customer']), 'industrial') !== false) {
                                                            $customerType = 'Industrial';
                                                        }
                                                        
                                                        // Asignar color según tipo de cliente
                                                        $customerColor = 'domestico';
                                                        if ($customerType == 'Comercial') $customerColor = 'comercial';
                                                        elseif ($customerType == 'Industrial') $customerColor = 'industrial';
                                                    @endphp
                                                    <div class="col-12 col-md-6 col-xl-4">
                                                        <div class="card border-1 shadow border-secondary-subtle mb-2 order-card order-{{ $customerColor }}">
                                                            <div class="card-body py-2">
                                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                                    <span class="fw-bold">#{{ $schedule['order_folio'] }}</span>
                                                                    <span class="badge bg-{{ $statusColor }}">{{ $schedule['status'] }}</span>
                                                                </div>
                                                                
                                                                <div class="mb-2">
                                                                    <small class="text-muted">Cliente:</small>
                                                                    <p class="mb-0 small fw-medium">{{ $schedule['customer'] }}</p>
                                                                </div>
                                                                
                                                                <div class="row mb-2">
                                                                    <div class="col-6">
                                                                        <small class="text-muted">Fecha:</small>
                                                                        <p class="mb-0 small">{{ $schedule['date'] }}</p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <small class="text-muted">Hora:</small>
                                                                        <p class="mb-0 small">{{ $schedule['time'] }}</p>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="mb-2">
                                                                    <small class="text-muted">Servicio:</small>
                                                                    <p class="mb-0 small">{{ $schedule['service'] }} ({{ $schedule['type'] }})</p>
                                                                </div>
                                                                
                                                                <div class="mb-2">
                                                                    <small class="text-muted">Técnicos:</small>
                                                                    <div class="mt-1">
                                                                        @foreach ($schedule['technicians'] as $technician)
                                                                            <span class="badge bg-light text-dark me-1 mb-1">{{ $technician }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                
                                                                @can('write_order')
                                                                <div class="d-flex justify-content-end pt-2 border-top">
                                                                    <a href="{{ $schedule['links']['tracking'] }}"
                                                                        class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="tooltip"
                                                                        data-bs-placement="top" data-bs-title="Seguimiento">
                                                                        <i class="bi bi-person-gear"></i>
                                                                    </a>
                                                                    <a class="btn btn-sm btn-outline-secondary me-1"
                                                                        href="{{ $schedule['links']['edit'] }}" data-bs-toggle="tooltip"
                                                                        data-bs-placement="top" data-bs-title="Editar">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </a>
                                                                    <a class="btn btn-sm btn-outline-dark me-1"
                                                                        href="{{ $schedule['links']['report'] }}" data-bs-toggle="tooltip"
                                                                        data-bs-placement="top" data-bs-title="Reporte">
                                                                        <i class="bi bi-file-pdf"></i>
                                                                    </a>
                                                                    <a href="{{ $schedule['links']['destroy'] }}"
                                                                        class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                                                                        data-bs-placement="top" data-bs-title="Cancelar"
                                                                        onclick="return confirm('{{ __('messages.are_you_sure') }}')">
                                                                        <i class="bi bi-x"></i>
                                                                    </a>
                                                                </div>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-3 text-muted">
                                                <i class="bi bi-calendar-x display-6 d-block mb-2"></i>
                                                <p class="mb-0">No hay órdenes programadas para esta hora</p>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .order-card {
            transition: all 0.2s ease;
            border-left: 4px solid #6c757d;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .order-domestico {
            border-left-color: #fd7e14;
        }
        
        .order-comercial {
            border-left-color: #198754;
        }
        
        .order-industrial {
            border-left-color: #0d6efd;
        }
        
        .badge {
            font-size: 0.7rem;
        }
        
        .table th, .table td {
            border-color: #dee2e6;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Inicializar daterangepicker
            $(function() {
                const commonOptions = {
                    opens: 'left',
                    locale: {
                        format: 'DD/MM/YYYY'
                    },
                    ranges: {
                        'Hoy': [moment(), moment()],
                        'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                        'Este mes': [moment().startOf('month'), moment().endOf('month')],
                        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                        'Este año': [moment().startOf('year'), moment().endOf('year')],
                    },
                    showDropdowns: true,
                    alwaysShowCalendars: true,
                    autoUpdateInput: false
                };

                $('#date-range').daterangepicker(commonOptions);

                $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                });
            });
        });
    </script>
@endsection