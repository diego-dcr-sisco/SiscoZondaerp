<div class="d-flex flex-row justify-content-between align-items-start py-2">
    <div class="table-responsive w-100 px-3">
        <table class="table table-hover table-bordered table-striped table-sm caption-top">
            <caption class="border rounded-top p-2 text-dark bg-light">
                <form action="{{ route('invoices.index') }}" method="GET">
                    @csrf
                    <div class="row g-3 mb-0">
                        <div class="col-lg-2 col-12">
                            <label for="folio" class="form-label">No. de Factura</label>
                            <input type="text" class="form-control form-control-sm" id="folio" name="folio"
                                value="{{ request('folio') }}" placeholder="Buscar por folio">
                        </div>

                        <div class="col-lg-5 col-12">
                            <label for="customer" class="form-label">Nombre comercial/cliente</label>
                            <input type="text" class="form-control form-control-sm" id="customer" name="customer"
                                value="{{ request('customer') }}" placeholder="Buscar por cliente">
                        </div>

                        <div class="col-lg-5 col-12">
                            <label for="social_reason" class="form-label">Razon social</label>
                            <input type="text" class="form-control form-control-sm" id="social_reason"
                                name="social_reason" value="{{ request('social_reason') }}"
                                placeholder="Buscar por la razon social ante SAT (Nombre fiscal)">
                        </div>

                        <div class="col-lg-4 col-12">
                            <label for="rfc" class="form-label">RFC</label>
                            <input type="text" class="form-control form-control-sm" id="rfc" name="rfc"
                                value="{{ request('rfc') }}" placeholder="Ingresa tu RFC (12-13 caracteres)">
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <label for="date_range" class="form-label">Rango de fecha</label>
                            <input type="text" class="form-control form-control-sm date-range-picker" id="date_range"
                                name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona un rango"
                                autocomplete="off">
                        </div>

                        <div class="col-lg-2 col-md-6 col-12">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">Todos los estados</option>
                                @foreach ($status as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6 col-12">
                            <label for="sort_by" class="form-label">Ordenar por</label>
                            <select class="form-select form-select-sm" id="sort_by" name="sort_by">
                                <option value="issue_date" {{ request('sort_by') == 'issue_date' ? 'selected' : '' }}>
                                    Fecha</option>
                                <option value="folio" {{ request('sort_by') == 'folio' ? 'selected' : '' }}>Folio
                                </option>
                                <option value="total" {{ request('sort_by') == 'total' ? 'selected' : '' }}>Total
                                </option>
                                <option value="customer_name"
                                    {{ request('sort_by') == 'customer_name' ? 'selected' : '' }}>Cliente</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6 col-12">
                            <label for="direction" class="form-label">Dirección</label>
                            <select class="form-select form-select-sm" id="direction" name="direction">
                                <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                </option>
                                <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-1 col-md-6 col-12">
                            <label for="size" class="form-label">Total</label>
                            <select class="form-select form-select-sm" id="size" name="size">
                                <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="col-12 d-flex justify-content-end m-0 gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </button>
                            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </caption>
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Folio</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Razon social</th>
                    <th scope="col">RFC</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Forma de pago</th>
                    <th scope="col">Método de Pago</th>
                    <th scope="col">Total</th>
                    <th scope="col">Estado</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $index => $invoice)
                    <tr class="invoice-row" data-invoice-id="{{ $invoice->id ?? '' }}">
                        <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                        <td class="text-start text-decoration-underline">
                            <a class="fw-bold" href="{{ route('invoices.show', ['id' => $invoice->id]) }}">
                                # {{ $invoice->serie }}-{{ $invoice->folio }}
                            </a>
                        </td>
                        <td>
                            <span class="d-inline-flex align-items-center gap-1">
                                <i class="bi bi-person-fill text-primary"></i>
                                <span class="fw-semibold">{{ $invoice->customer->comercial_name }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="">{{ $invoice->customer->social_reason }}</span>
                        </td>
                        <td class="text-info-emphasis fw-bold">
                            {{ $invoice->customer->rfc }}
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                        </td>
                        <td>
                            {{ $invoice->payment_form ?? '' }} -
                            {{ $invoice->payment_form ? $paymentForms[$invoice->payment_form] : '-' }}
                        </td>
                        <td>
                            {{ $invoice->payment_method ?? '' }} -
                            {{ $invoice->payment_method ? $paymentMethods[$invoice->payment_method] : '-' }}
                        </td>
                        <td class="fw-bold text-success">
                            ${{ number_format($invoice->total, 2) }}
                        </td>
                        <td>
                            {{ $status[$invoice->status] ?? 'Desconocido' }}
                        </td>
                        <td>
                            @if ($invoice->facturama_token)
                                <a href="{{-- --}}" class="btn btn-warning btn-sm"
                                    data-bs-toggle="tooltip" title="Traspaso(s) (T - Traspaso)">
                                    <i class="bi bi-arrow-left-right"></i>
                                </a>

                                <a href="{{-- --}}" class="btn btn-success btn-sm"
                                    data-bs-toggle="tooltip" title="Complemento(s) de pago (P - Pago)">
                                    <i class="bi bi-cash-coin"></i>
                                </a>
                                <a href="{{ route('invoices.credit-notes.index', ['invoice_id' => $invoice->id]) }}"
                                    class="btn btn-primary btn-sm" data-bs-toggle="tooltip"
                                    title="Nota(s) de credito (E - Egreso)">
                                    <i class="bi bi-journal-bookmark-fill"></i>
                                </a>
                                <a href="{{ route('invoices.download.zip-invoice', ['id' => $invoice->id]) }}"
                                    class="btn btn-dark btn-sm" data-bs-toggle="tooltip"
                                    title="Descargar ZIP - Archivo PDF y XML">
                                    <i class="bi bi-file-arrow-down-fill"></i>
                                </a>
                            @else
                                <a href="{{ route('invoices.edit', ['id' => $invoice->id]) }}"
                                    class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Editar factura">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-5 text-center">
                            <div class="empty-state">
                                <i class="bi bi-receipt text-muted fs-1 mb-3"></i>
                                <h5 class="text-muted">No hay facturas para mostrar</h5>
                                <p class="text-muted mb-0">No se encontraron facturas en el sistema.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Script para confirmar cancelación -->
<script>
    $(document).ready(function() {
        $('input[name="date_range"]').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
            },
            opens: 'left',
            autoUpdateInput: false
        });

        $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                'DD/MM/YYYY'));
        });
    });

    function confirmCancel(invoiceId) {
        if (confirm('¿Estás seguro de que deseas cancelar esta factura? Esta acción no se puede deshacer.')) {
            document.getElementById('cancel-form-' + invoiceId).submit();
        }
    }
</script>
</div>
