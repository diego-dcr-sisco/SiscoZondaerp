<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Clientes - ZondaERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        .card { border-radius: 15px; }
        .card-header { border-top-left-radius: 15px !important; border-top-right-radius: 15px !important; }
        .form-switch .form-check-input { width: 2.5em; height: 1.25em; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            {{-- Botón de regreso simulado para estética del ERP --}}
            <div class="mb-4">
                <span class="text-muted fw-bold text-uppercase" style="letter-spacing: 1px;">ZondaERP / Módulo de Reportes</span>
            </div>

            <div class="card shadow border-0">
                {{-- Encabezado Principal --}}
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-file-excel me-2"></i> Reporte de Clientes Nuevos y Recurrentes
                    </h5>
                </div>
                
                <div class="card-body p-5">
                    
                    {{-- Formulario que viaja al controlador --}}
                    <form action="{{ route('report.clients.export') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            {{-- 1. Rango de Fechas Obligatorio --}}
                            <div class="col-md-6">
                                <label for="date_range" class="form-label fw-bold text-secondary">Rango de Fechas (Formato Estricto):</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" name="date_range" id="date_range" class="form-control" placeholder="AAAA-MM-DD - AAAA-MM-DD" required autocomplete="off">
                                </div>
                                <small class="text-muted d-block mt-1">Ejemplo: <span class="badge bg-secondary">2026-05-01 - 2026-05-25</span></small>
                            </div>

                            {{-- 2. Tipo de Cliente Obligatorio --}}
                            <div class="col-md-6">
                                <label for="client_type" class="form-label fw-bold text-secondary">Tipo de Cliente:</label>
                                <select name="client_type" id="client_type" class="form-select shadow-sm" required>
                                    <option value="all">Todos los Clientes Activos</option>
                                    <option value="new">Clientes Nuevos (Primera orden en el rango)</option>
                                    <option value="recurrent">Clientes Recurrentes (Con historial previo)</option>
                                </select>
                            </div>
                        </div>

                        {{-- 3. Checkboxes Dinámicos de Métricas (Sección 2 del PDF) --}}
                        <div class="card mt-5 bg-light border-0 rounded-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-dark">
                                    <i class="fas fa-tasks me-2 text-primary"></i> Columnas Adicionales a Incluir en el Excel:
                                </h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="metrics[]" value="inc_orders_count" id="m1" checked>
                                            <label class="form-check-label fw-semibold" for="m1">Cantidad de órdenes de servicio</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="metrics[]" value="inc_has_devices" id="m2">
                                            <label class="form-check-label fw-semibold" for="m2">Saber si cuenta con dispositivos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="metrics[]" value="inc_devices_count" id="m3">
                                            <label class="form-check-label fw-semibold" for="m3">Cantidad total de dispositivos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="metrics[]" value="inc_device_types" id="m4">
                                            <label class="form-check-label fw-semibold" for="m4">Tipos de dispositivos asignados</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="metrics[]" value="inc_pests_count" id="m5">
                                            <label class="form-check-label fw-semibold" for="m5">Cantidad de plagas asociadas (e)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="metrics[]" value="inc_pest_types" id="m6">
                                            <label class="form-check-label fw-semibold" for="m6">Tipos de plagas detectadas (f)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botón de Acción Principal --}}
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow">
                                <i class="fas fa-file-excel me-2"></i> Generar y Exportar Reporte
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(function() {
    $('#date_range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'YYYY-MM-DD',
            separator: ' - ',
            applyLabel: 'Aplicar',
            cancelLabel: 'Limpiar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Personalizado',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            firstDay: 1
        }
    });

    // Al aplicar las fechas, se escribe el string esperado por el controlador
    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    // Al limpiar, se vacía el campo
    $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
</body>
</html>