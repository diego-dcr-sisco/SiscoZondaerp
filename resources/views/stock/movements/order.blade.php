@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <span class="text-black fw-bold fs-4">
                CONSUMOS EN ORDENES
            </span>
        </div>
        <div class="m-3">
            <table class="table table-bordered table-striped table-sm align-middle caption-top">
                <caption class="border rounded-top p-2 text-dark bg-light">
                    <form action="{{ route('stock.movements.orders' ) }}" method="GET">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="warehouse" class="form-label">Orden</label>
                                <input type="text" class="form-control form-control-sm" id="order" name="order_folio"
                                    value="{{ request('order_folio') }}" placeholder="Folio de la orden." />
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="warehouse" class="form-label">Almacen</label>
                                <input type="text" class="form-control form-control-sm" id="warehouse" name="warehouse"
                                    value="{{ request('warehouse') }}" placeholder="Nombre del almacen." />
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="warehouse" class="form-label">Técnico</label>
                                <input type="text" class="form-control form-control-sm" id="technician" name="technician"
                                    value="{{ request('technician') }}" placeholder="Nombre del usuario/técnico." />
                            </div>
                            <div class="col-lg-3 col-12 mb-3">
                                <label for="movement" class="form-label">Producto</label>
                                <select class="form-select form-select-sm" id="product" name="product_id">
                                    <option value="">Todos los productos</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-12 mb-3">
                                <label for="lot" class="form-label">Lote</label>
                                <select class="form-select form-select-sm" id="lot" name="lot_id">
                                    <option value="">Todos los lotes</option>
                                    @foreach ($lots as $lot)
                                        <option value="{{ $lot->id }}"
                                            {{ request('lot_id') == $lot->id ? 'selected' : '' }}>
                                            {{ $lot->registration_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-12 mb-3">
                                <label for="lot" class="form-label">Fecha</label>
                                <input type="text" class="form-control form-control-sm" id="date-range" name="date_range"
                                    value="{{ request('date-range') }}" placeholder="Rango de fecha de los movimientos"
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
                        <th scope="col">ID</th>
                        <th scope="col">Orden</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Fecha de orden</th>
                        <th scope="col">Almacen</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Lote</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Usuario/Técnico</th>
                        <th scope="col">Fecha realizado</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($wos as $wo)
                        <tr>
                            <th scope="row">{{ $wo->id }}</th>
                            <td>
                                <a class="fw-bold" href="{{ route('order.edit', ['id' => $wo->order_id]) }}">{{ $wo->order->folio ?? '-' }}
                                    [{{ $wo->order_id ?? '-' }}]</a>
                            </td>
                            <td>{{ $wo->order->customer->name ?? '-' }}</td>
                            <td>{{ $wo->order->programmed_date ?? '-' }}</td>
                            <td>{{ $wo->warehouse->name ?? '-' }}</td>
                            <td>{{ $wo->product->name ?? '-' }}</td>
                            <td>{{ $wo->lot->registration_number ?? '-' }}</td>
                            <td class="text-danger fw-bold">
                                {{ $wo->amount ?? '-' }}<br>
                                <small class="text-muted">{{ $wo->product->metric->value }}</small>
                            </td>
                            <td>{{ $wo->user->name ?? '-' }}</td>
                            <td>{{ $wo->created_at->format('d-m-Y H:i:s') }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $wos->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
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

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });
    </script>
@endsection
