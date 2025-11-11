@extends('layouts.app')
@section('content')
    <style>
        .modal-blur {
            backdrop-filter: blur(5px);
            background-color: rgba(0, 0, 0, 0.3);
        }

        #fullscreen-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.2);
        }

        .spinner-overlay {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
    </style>

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <span class="text-black fw-bold fs-4">
                CONSUMOS
            </span>
        </div>

        <div id="fullscreen-spinner" class="d-none">
            <div class="spinner-overlay">
                <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-light mt-2">Procesando...</div>
            </div>
        </div>

        <div class="m-3">
            @can('write_user')
                <button type="button" class="btn btn-success btn-sm mb-3" onclick="generateExcel()">
                    <i class="bi bi-file-excel-fill"></i> Exportar a excel
                </button>
            @endcan
            <table class="table table-bordered table-striped table-sm align-middle caption-top">
                <caption class="border rounded-top p-2 text-dark bg-light">
                    <form action="{{ route('consumptions.index') }}" method="GET">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="comercial-zone" class="form-label">Zona comercial</label>
                                <select class="form-select form-select-sm" name="comercial_zone">
                                    <option value="" {{ $filters['comercial_zone'] == '' ? 'selected' : '' }}>Sin zona
                                        especificada</option>
                                    @foreach ($comercial_zones as $cz)
                                        <option value="{{ $cz->id }}"
                                            {{ $filters['comercial_zone'] == $cz->id ? 'selected' : '' }}>
                                            {{ $cz->name }} - {{ $cz->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-8 col-12 mb-3">
                                <label for="customer" class="form-label">Clientes</label>
                                <div class="input-group">
                                    <input class="form-control form-control-sm" name="customers"
                                        placeholder="Nombre 1, Nombre 2, Nombre 3 ..."
                                        value="{{ $filters['customers'] ?? '' }}" />
                                    <span class="input-group-text">
                                        <i class="bi bi-question-circle-fill" data-bs-toggle="tooltip"
                                            data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                            data-bs-title="Asegúrate de dividir los nombres de cada cliente usando comas."></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-3 col-12 mb-3">
                                <label for="lot" class="form-label">Fecha</label>
                                <input type="text" class="form-control form-control-sm" id="date-range" name="date"
                                    value="{{ $filters['date'] ?? '' }}" placeholder="Rango de fecha de los movimientos"
                                    autocomplete="off">
                            </div>
                            <div class="col-auto mb-3">
                                <label for="signature_status" class="form-label">Dirección</label>
                                <select class="form-select form-select-sm" id="direction" name="direction">
                                    <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                    </option>
                                    <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                    </option>
                                </select>
                            </div>

                            <div class="col-auto mb-3">
                                <label for="order_type" class="form-label">Total</label>
                                <select class="form-select form-select-sm" id="size" name="size">
                                    <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                    <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="col-12 d-flex justify-content-end m-0">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>

                            </div>
                        </div>
                    </form>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">
                            <input class="form-check-input p-0 m-0 border-secondary" type="checkbox"
                                onclick="setAllKeyConsumptions(this)">
                        </th>
                        <th scope="col">#</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Zonas comerciales</th>
                        <th scope="col">Periodo de tiempo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consumptions_data as $index => $data)
                        <tr>
                            <th>
                                <input class="form-check-input p-0 m-0 select-consumption border-secondary" type="checkbox"
                                    value="{{ $data['key'] }}" onclick="setKeyConsumptions(this)">
                            </th>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $data['product_name'] }}</td>
                            <td class="text-warning-emphasis fw-bold">{{ $data['amount'] }} <br> <small
                                    class="text-muted">{{ $data['product_metric'] }}</small></td>
                            <td>{{ $data['customer_name'] }}</td>
                            <td>
                                <ul style="margin: 0; padding-left: 20px;">
                                    @foreach ($data['comercial_zones'] as $code => $name)
                                        <li>{{ $name }} - {{ $code }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>{{ $data['timelapse'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- $wos->links('pagination::bootstrap-5') --}}
        </div>
    </div>

    <script>
        var consumptions = @json($consumptions_data);
        var array_keys = [];

        $(function() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                tooltipTriggerEl))

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
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'Últimos 2 meses': [moment().subtract(2, 'month').startOf('month'), moment().endOf(
                        'month')],
                    'Este año': [moment().startOf('year'), moment().endOf('year')],
                },
                showDropdowns: true,
                alwaysShowCalendars: true,
                autoUpdateInput: false
            };

            $('#date-range').daterangepicker(commonOptions);

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });

        function setAllKeyConsumptions(selectAllElement) {
            var isChecked = $(selectAllElement).prop('checked');

            $('.select-consumption')
                .prop('checked', isChecked)
                .each(function() {
                    setKeyConsumptions(this);
                });

            console.log('Todos los checkboxes ' + (isChecked ? 'seleccionados' : 'deseleccionados'));
            console.log('Array keys actualizado:', array_keys);
        }

        function setKeyConsumptions(element) {
            var key = element.value;
            var isChecked = element.checked;

            if (isChecked) {
                // Si está marcado y la key no existe, agregarla
                if (!array_keys.includes(key)) {
                    array_keys.push(key);
                }
            } else {
                // Si está desmarcado, remover la key si existe
                var index = array_keys.indexOf(key);
                if (index !== -1) {
                    array_keys.splice(index, 1);
                }
            }

            console.log('Keys seleccionados:', array_keys);
        }

        function generateExcel() {
            var data = consumptions.filter(c => array_keys.includes(c.key));

            if (data.length === 0) {
                alert('Por favor selecciona al menos un registro para exportar.');
                return;
            }

            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            var formData = new FormData();
            formData.append('data', JSON.stringify(data));

            showSpinner();
            $.ajax({
                url: "{{ route('consumptions.export') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    var blob = new Blob([response]);
                    var downloadUrl = URL.createObjectURL(blob);

                    var a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'consumptions_' + new Date().toISOString().slice(0, 10) + '.xlsx';
                    document.body.appendChild(a);
                    a.click();

                    setTimeout(function() {
                        document.body.removeChild(a);
                        URL.revokeObjectURL(downloadUrl);
                    }, 100);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error al generar el archivo Excel');
                },
                complete: function() {
                    hideSpinner()
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
