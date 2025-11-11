@extends('layouts.app')
@section('content')
    <div class="row m-0">

        <!-- Contenido principal -->
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            {{-- <a href="{{ route('invoices.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a> --}}
            <span class="text-black fw-bold fs-4">
                Complementos de Pago <span
                    class="badge text-bg-warning">{{ isset($invoice->folio) ? $invoice->serie . '-' . $invoice->folio : '' }}</span>
            </span>
        </div>


        <div class="my-3">
            <a class="button btn btn-primary btn-sm"
                href="{{ route('invoices.payments.create', ['invoice_id' => isset($invoice) ? $invoice->id : null]) }}">
                <i class="fas fa-plus"></i> Crear Complemento de Pago
            </a>
        </div>


        <!-- Tabla de facturas -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm caption-top">
                <caption class="border rounded-top p-2 text-dark bg-light">
                    <form action="{{ route('invoices.credit-notes.index') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-2 col-12">
                                <label for="note_folio" class="form-label">No. de nota</label>
                                <input type="text" class="form-control form-control-sm" id="note_folio" name="note_folio"
                                    value="{{ request('note_folio') }}" placeholder="Buscar por folio de notas credito">
                            </div>

                            <div class="col-lg-2 col-12">
                                <label for="invoice_folio" class="form-label">No. de Factura</label>
                                <input type="text" class="form-control form-control-sm" id="invoice_folio"
                                    name="invoice_folio" value="{{ request('invoice_folio') }}"
                                    placeholder="Buscar por folio de factura">
                            </div>

                            <div class="col-lg-4 col-12">
                                <label for="customer" class="form-label">Nombre comercial/cliente</label>
                                <input type="text" class="form-control form-control-sm" id="customer" name="customer"
                                    value="{{ request('customer') }}" placeholder="Buscar por cliente">
                            </div>

                            <div class="col-lg-4 col-12">
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
                            </div>
                        </div>
                    </form>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Folio Complemento</th>
                        <th scope="col">Razon social</th>
                        <th scope="col">RFC</th>
                        <th scope="col">Facturas ligas</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Timbrado en</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $index => $payment)
                        <tr>
                            <th scope="row">{{ $index + 1 }}</th>
                            <td>
                                <a class="fw-bold" href="#"># {{ $payment->serie }}-{{ $payment->folio }}
                                </a>
                            </td>
                            <td>{{ $payment->receiver_name }}</td>
                            <td>{{ $payment->receiver_rfc }}</td>
                            <td>
                                <table class="table table-sm table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Folio</th>
                                            <th scope="col">UUID</th>
                                            <th scope="col">No. Pago (Parcialidad)</th>
                                            <th scope="col">Monto previo</th>
                                            <th scope="col">Monto pagado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payment->items as $index => $items)
                                            @foreach ($items->relatedDocuments as $index => $doc)
                                                <tr>
                                                    <td>
                                                        <a class="fw-bold"
                                                            href="{{ route('invoices.show', ['id' => $doc->invoice->id]) }}>{{ $doc->invoice->serie }}-{{ $doc->invoice->folio }}">#
                                                            {{ $doc->invoice->serie }}-{{ $doc->invoice->folio }} </a>
                                                    </td>
                                                    <td>{{ $doc->cfdi_uuid }}</td>
                                                    <td>{{ $doc->partiality_number }}</td>
                                                    <td>${{ $doc->previous_balance_amount }}</td>
                                                    <td>${{ $doc->amount_paid }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                            <td>{{ $payment->getStatus() }}</td>

                            <td>{{ \Carbon\Carbon::parse($payment->stamped_at)->format('d/m/Y') }}</td>
                            <td class="text-center align-middle">
                                @if ($payment->status != 5)
                                    <a class="btn btn-secondary btn-sm"
                                        href="{{ route('invoices.payments.edit', ['id' => $payment->id]) }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="custom-tooltip" data-bs-title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <a class="btn btn-primary btn-sm"
                                        href="{{ route('invoices.stamp.payment', ['id' => $payment->id]) }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="custom-tooltip" data-bs-title="Timbrar">
                                        <i class="bi bi-bell-fill"></i>
                                    </a>
                                @else
                                    <a href="{{ route('invoices.download.zip-payment', ['id' => $payment->id]) }}"
                                        class="btn btn-dark btn-sm" data-bs-toggle="tooltip"
                                        title="Descargar ZIP - Archivo PDF y XML">
                                        <i class="bi bi-file-arrow-down-fill"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @isset($invoice)
            @include('invoices.credit-notes.create')
        @endisset
    </div>

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

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
