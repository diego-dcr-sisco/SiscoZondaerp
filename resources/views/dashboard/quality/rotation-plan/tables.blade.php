<table class="table table-bordered table-striped table-sm caption-top">
    <div class="container-fluid p-3">
        <caption class="border rounded-top p-2 text-dark bg-light caption-top">
            <form action="{{ route('quality.rotation-plan.search', ['id' => $customer->id]) }}" method="GET">
                <div class="row g-3 mb-0">
                    <div class="col-lg-4 col-12">
                        <label for="version" class="form-label">Nombre</label>
                        <input type="text" class="form-control form-control-sm" name="name"value="{{ request('name') }}" />
                    </div>
                    <!-- Rango de Fechas -->
                            <div class="col-auto">
                                <label for="date_range" class="form-label">Rango de Fechas</label>
                                <input type="text" class="form-control form-control-sm date-range-picker" id="date-range"
                                    name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona un rango"
                                    autocomplete="off">
                            </div>
                    <!-- Botones -->
                            <div class="col-lg-12 d-flex justify-content-end m-0">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                                <a href="{{ route('quality.rotation-plan.index', ['id' => $customer->id]) }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>

                </div>
            </form>
                                            

        </caption>

    </div>

</table>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
    <thead>
        <tr>
            <th class="fw-bold" scope="col">#</th>
            <th class="fw-bold" scope="col">Código</th>
            <th class="fw-bold" scope="col">Nombre</th>
            <th class="fw-bold" scope="col">No.Revisión</th>
            <th class="fw-bold" scope="col">Fecha de inicio</th>
            <th class="fw-bold" scope="col">Fecha de termino</th>
            <th class="fw-bold" scope="col">Fecha de Autorización</th>
            <th class="fw-bold" scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @if($rotationPlans && count($rotationPlans) > 0)
            @foreach($rotationPlans as $index => $plan)
                <tr>
                    <th scope="row">{{ $index + 1 }}</th>
                    <td>{{ $plan->code }}</td>
                    <td>{{ $plan->name }}</td>
                    <td>{{ $plan->no_review }}</td>
                    <td>
                        @if($plan->contract && $plan->contract->startdate)
                            {{ \Carbon\Carbon::parse($plan->contract->startdate)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($plan->contract && $plan->contract->enddate)
                            {{ \Carbon\Carbon::parse($plan->contract->enddate)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($plan->authorizated_at)
                            {{ \Carbon\Carbon::parse($plan->authorizated_at)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <div class="text-center" role="group" aria-label="Basic example">
                            <a href="{{ route('rotation.edit', $plan->id) }}" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar plan de rotación">
                            <i class="bi bi-pencil-square"></i>
                            </a>        
                        </div>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="8" class="text-center text-danger">No se encontraron resultados.</td>
            </tr>
        @endif
    </tbody>
</table>
        
    </div>


</div>

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





