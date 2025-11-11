@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-4">
            <a href="{{ route('quality.customers') }}" class="col-auto btn-primary p-0"><i
                    class="bi bi-arrow-left m-3 fs-4"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0">{{ $customer->name }}</h1>

        </div>

         <!-- comienza  -->
             <div class="d-flex">
            <div class="mb-3">
                @can('write_order')
                    <a class="btn btn-primary btn-sm me-2" href="{{ route('order.create') }}" onclick="sessionStorage.setItem('prev_url', window.location.href);">
                        <i class="bi bi-plus-lg fw-bold"></i> {{ __('order.title.create') }}
                    </a>
                @endcan
            </div>

            <div class="dropdown">
                <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Funciones especiales
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a style="cursor: pointer;" class="dropdown-item" onclick="setCheckboxOrder()">Generar ZIP de
                            ordenes</a>
                    </li>
                    <li>
                        <a style="cursor: pointer;" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#technicianModal">Asignar técnicos en rango</a>
                    </li>
                </ul>
            </div>
            
        </div>
            <!-- termina -->
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
    <!-- Ordenes de servicio -->
    <div class="position-relative" style="width: 120px;">
        <a href="{{ route ('quality.customer', ['id' => $customer->id]) }}" 
           class="d-block p-2 text-center text-decoration-none border rounded bg-white shadow-sm hover-effect">
            <div class="d-flex justify-content-center gap-1 mb-1">
                <span class="badge rounded-circle bg-warning p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $customer->countOrdersbyStatus(1) }}
                </span>
                <span class="badge rounded-circle bg-primary p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $customer->countOrdersbyStatus(3) }}
                </span>
                <span class="badge rounded-circle bg-success p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $customer->countOrdersbyStatus(5) }}
                </span>
            </div>
            <span class="small fw-semibold text-dark">Órdenes</span>
        </a>
    </div>

    <!-- Analíticas -->
    <!--div class="position-relative" style="width: 100px;">
        <a href="{{ route('quality.analytics', ['id' => $customer->id]) }}" 
           class="d-block p-2 text-center text-decoration-none border rounded bg-white shadow-sm hover-effect">
            <div class="mb-1">
                <span class="badge rounded-circle bg-success p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $customer->orders()->where('status_id', 5)->count() }}
                </span>
            </div>
            <span class="small fw-semibold text-dark">Analíticas</span>
        </a>
    </div-->

    <!-- Contratos -->
    <div class="position-relative" style="width: 100px;">
        <a href="{{ route('quality.contracts', ['id' => $customer->id]) }}" 
           class="d-block p-2 text-center text-decoration-none border rounded bg-white shadow-sm hover-effect">
            <div class="mb-1">
                <span class="badge rounded-circle bg-secondary p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $customer->contracts->count() }}
                </span>
            </div>
            <span class="small fw-semibold text-dark">Contratos</span>
        </a>
    </div>

    <!-- Planos -->
    <div class="position-relative" style="width: 90px;">
        <a href="{{ route('customer.show.sede.floorplans', ['id' => $customer->id, 'type' => 2 ]) }}" 
           class="d-block p-2 text-center text-decoration-none border rounded bg-white shadow-sm hover-effect">
            <div class="mb-1">
                <span class="badge rounded-circle bg-secondary p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $customer->floorplans->count() }}
                </span>
            </div>
            <span class="small fw-semibold text-dark">Planos</span>
        </a>
    </div>

    <!-- Áreas de aplicación -->
    <div class="position-relative d-inline-block">
        <a href="{{ route('quality.application-areas', ['id' => $customer->id]) }}" 
           class="d-block p-2 text-center text-decoration-none border rounded bg-white shadow-sm hover-effect">
            <div class="mb-1">
                <span class="badge rounded-circle bg-secondary p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $customer->applicationAreas->count() }}
                </span>
            </div>
            <span class="small fw-semibold text-dark">Áreas de aplicación</span>
        </a>
    </div>

    <!-- Dispositivos -->
    <div class="position-relative" style="width: 100px;">
        <a href="{{ route('quality.devices', ['id' => $customer->id]) }}" 
           class="d-block p-2 text-center text-decoration-none border rounded bg-white shadow-sm hover-effect">
            <div class="mb-1">
                <span class="badge rounded-circle bg-secondary p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $count_devices }}
                </span>
            </div>
            <span class="small fw-semibold text-dark">Dispositivos</span>
        </a>
    </div>

    <!-- Archivos -->
    <div class="position-relative" style="width: 90px;">
        <a href="{{ route('quality.files', ['id' => $customer->id]) }}" 
           class="d-block p-2 text-center text-decoration-none border rounded bg-white shadow-sm hover-effect">
            <div class="mb-1">
                <span class="badge rounded-circle bg-secondary p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $customer->files->where('path', '!=', null)->count() }}
                </span>
            </div>
            <span class="small fw-semibold text-dark">Archivos</span>
        </a>
    </div>

    <!-- Planes de rotación -->
    <div class="position-relative d-inline-block" >
        <a href="{{ route('quality.rotation-plan.index', ['id' => $customer->id]) }}"
           class="d-block p-2 text-center text-decoration-none border rounded bg-white shadow-sm hover-effect">
            <div class="mb-1">
                <span class="badge rounded-circle bg-secondary p-1" style="width: 22px; height: 22px; font-size: 0.65rem;">
                    {{ $rotation_plans->count() }}
                </span>
            </div>
            <span class="small fw-semibold text-dark">Planes de rotación</span>
        </a>
    </div>
</div>
 <!-- termina seccion de indicadores -->
     
     @include('dashboard.quality.tables.orders')

        <!-- Modal -->
        <div class="modal fade" id="technicalModal" tabindex="-1" aria-labelledby="technicalModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <form class="modal-content" method="POST"
                    action="{{ route('quality.update.technician', ['id' => $customer->id]) }}">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="technicalModalLabel">Reasignación de técnicos</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="date" class="form-label is-required">Rango de fechas</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="date-range" name="date_range" value=""
                                    required />
                                <button class="btn btn-success" type="button" onclick="searchOrders()"> <i
                                        class="bi bi-search"></i> </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <ul class="list-group" id="technicians-list">
                                <li class="list-group-item text-danger">Sin coincidencias</li>
                            </ul>
                        </div>

                        <input type="hidden" id="technicians" name="technicians" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"
                            onclick="submitForm()">{{ __('buttons.accept') }}</button>
                        <button type="button" class="btn btn-danger"
                            data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>

    <style>
        .hover-effect {
            transition: all 0.2s ease;
        }
        .hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
            background-color: #f8f9fa !important;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
        <script>
            var technician_to_change = [];

            $(document).ready(function() {
                $(".card").hover(function() {
                    $(this).addClass("animate__animated animate__pulse");
                }, function() {
                    $(this).removeClass("animate__animated animate__pulse");
                });
            });

            function submitForm() {
                $('#technicians').val(
                    JSON.stringify(technician_to_change)
                );
            }

            function searchOrders() {
                const date_range = $('#date-range').val();
                var formData = new FormData();
                var csrfToken = $('meta[name="csrf-token"]').attr("content");
                var html = '';
                formData.append('date_range', date_range);

                $.ajax({
                    url: "{{ route('quality.ajax.search.technicians', ['id' => $customer->id]) }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    success: function(response) {
                        console.log(response);
                        const technicians = response.data.technicians;
                        const all_technicians = response.data.all_technicians;

                        if (all_technicians.length == 0) {
                            html = `<li class="list-group-item text-danger">Sin coincidencias</li>`;
                            return;
                        } else {
                            technician_to_change = technicians.map(technician => technician.id);
                            all_technicians.forEach(technician => {
                                html += `
                                    <li class="list-group-item">
                                        <input class="form-check-input border-secondary me-1" type="checkbox" value="${technician.id}" ${technician_to_change.includes(technician.id) ? 'checked' : ''}  onchange="setTechnician(this)">
                                        <label class="form-check-label">${technician.name}</label>
                                    </li>
                                `;
                            });
                        }
                        $('#technicians-list').html(html);
                    },
                    error: function(error) {},
                });
            }

            function setTechnician(element) {
                value = parseInt(element.value);
                isChecked = element.checked;
                if (isChecked) {
                    technician_to_change.includes(value) || technician_to_change.push(value);
                } else {
                    technician_to_change = technician_to_change.filter(id => id != value);
                }
            }
        </script>

        <script>
        var order_ids = [];

        $(document).ready(function() {
            $('.form-check-input').prop('checked', false);
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        $(function() {
            // Configuración común para ambos datepickers
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
            $('#date-range-technician').daterangepicker(commonOptions);

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            $('#date-range-technician').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });

        function createTracking() {
            $('#trackingModal').modal('show');
        }

        function selectAllOrders() {
            const isChecked = $('#all-checkboxes').is(':checked');
            $('.checkbox-order').prop('checked', isChecked);
        }

        function setCheckboxOrder() {
            const checkboxes = $('.checkbox-order:checked');

            if (checkboxes.length == 0) {
                alert('Por favor, selecciona al menos una orden.');
                return;
            }

            var selected_orders = [];
            checkboxes.each(function() {
                selected_orders.push(parseInt($(this).val()));
            });

            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            formData.append('selectedOrders', JSON.stringify(selected_orders));

            showSpinner();

            $.ajax({
                url: "{{ route('report.bulk') }}",
                type: 'POST',
                data: formData,
                processData: false, // Importante para FormData
                contentType: false, // Importante para FormData
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        if (confirm('ZIP listo, ¿Deseas descargar los reportes?')) {
                            window.location.href = response.download_url;
                        } else {
                            window.location.href = response.delete_url;
                        }
                    }
                },
                complete: function() {
                    hideSpinner();
                },
                error: function(xhr, status, error) {
                    console.error('Error al descargar los reportes:', error);
                }
            });
        }

        function showSpinner() {
            $("#fullscreen-spinner").removeClass("d-none");
        }

        function hideSpinner() {
            $("#fullscreen-spinner").addClass("d-none");
        }
    </script>
    @endsection
