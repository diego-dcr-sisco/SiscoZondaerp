@extends('layouts.app')
@section('content')
    <div class="row w-100 h-100 m-0">
        @include('dashboard.stock.navigation')

        <div class="col-11 p-3 m-o">
            

            <div class="row border-bottom p-1 mb-3">
                <a href="{{ route('consumption.show.past') }}" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 fw-bold m-0"> Consumo de {{ $product->name }}</h1>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <!-- Filtros -->
                    @include('stock.consumptions.filters.products')
                    
                </div>

            </div>

            <!-- Tabla de consumos  -->
            @isset($details)
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Historico de consumos</h5>
                        <p class="mb-0 px-2 bg-primary rounded text-white">Total: {{ $totalConsumption }}
                            {{ $product->metric->value }}</p>
                    </div>

                    <div class="card-body">
                        @if (!empty($details) && count($details) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" id="consumption-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Id Orden</th>
                                            <th>Fecha Programada</th>
                                            <th>Cliente</th>
                                            <th>Servicio</th>
                                            <th class="text-center">Cantidad Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($details as $detail)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ route('report.review', $detail['order_id']) }}" target="_blank">
                                                        {{ $detail['order_id'] }}
                                                    </a>
                                                </td>
                                                <td>{{ $detail['programmed_date'] }}</td>
                                                <td>{{ $detail['customer'] }}</td>
                                                <td>{{ $detail['service'] }}</td>
                                                <td class="text-end fw-bold">
                                                    {{ intval($detail['amount']) }} <span
                                                        style="font-size:0.6rem">{{ $product->metric->value }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $details->links('pagination::bootstrap-5') }}

                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> No se encontraron consumos en el período seleccionado
                            </div>
                        @endif
                    </div>
                </div>
            @endisset
        </div>
    </div>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                // Inicializar Select2 para búsqueda de clientes
                $('.select2').select2({
                    placeholder: "Seleccione un cliente",
                    allowClear: true
                });

                // Inicializar DataTables
                $('#consumption-table').DataTable({
                    "language": {
                        "url": "{{ asset('js/datatables.spanish.json') }}"
                    },
                    "order": [
                        [1, "desc"]
                    ],
                    "pageLength": 10,
                    "lengthMenu": [10, 25, 50, 100],
                    "responsive": true,
                    "autoWidth": false,
                    "dom": '<"top"f>rt<"bottom"lip><"clear">',
                    "columns": [
                        null,
                        {
                            "orderSequence": ["desc", "asc"]
                        },
                        {
                            "orderable": false,
                            "searchable": false
                        }
                    ]
                });

                // Efecto hover en tarjetas
                $(".card").hover(function() {
                    $(this).addClass("shadow");
                }, function() {
                    $(this).removeClass("shadow");
                });
            });
        </script>
@endsection
