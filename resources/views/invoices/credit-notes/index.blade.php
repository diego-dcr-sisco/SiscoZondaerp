@extends('layouts.app')
@section('content')
    <div class="row m-0">

        <!-- Contenido principal -->
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            {{-- <a href="{{ route('invoices.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a> --}}
            <span class="text-black fw-bold fs-4">
                NOTAS DE CREDITO <span
                    class="badge text-bg-warning">{{ isset($invoice->folio) ? $invoice->serie . '-' . $invoice->folio : '' }}</span>
            </span>
        </div>


        <div class="my-3">
            <a class="button btn btn-primary btn-sm" href="{{ route('invoices.credit-notes.create', ['invoice_id' => isset($invoice) ? $invoice->id : null])  }}">
                <i class="fas fa-plus"></i> Crear Nota de Credito
            </a>
        </div>


        <!-- Tabla de facturas -->
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped table-sm caption-top">
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
                        <th scope="col">Folio Nota</th>
                        <th scope="col">Factura ligada</th>
                        <th scope="col">Nombre comercial/cliente</th>
                        <th scope="col">Razon social</th>
                        <th scope="col">RFC</th>
                        <th scope="col">Forma de pago</th>
                        <th scope="col">Método de Pago</th>
                        <th scope="col">Total</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Timbrado en</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($credit_notes as $index => $note)
                        <tr>
                            <th scope="row">{{ $index + 1 }}</th>
                            <td>
                                <a class="fw-bold" href="#"> {{ $note->serie }}-{{ $note->folio }}
                                </a>
                            </td>
                            <td><a href="{{ route('invoices.show', ['id' => $note->invoice_id]) }}"
                                    class="fw-bold text-primary">{{ $note->invoice->serie }}-{{ $note->invoice->folio }}</a>
                            </td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-person-fill text-primary"></i>
                                    <span class="fw-semibold">{{ $note->invoice->customer->comercial_name }}</span>
                                </span>
                            </td>
                            <td>{{ $note->receiver_name }}</td>
                            <td>{{ $note->receiver_rfc }}</td>
                            <td>{{ $note->payment_form }}</td>
                            <td>{{ $note->payment_method }}</td>
                            <td>${{ $note->getFormattedTotalAttribute() }}</td>
                            <td>{{ $note->getStatus() }}</td>
                            <td>{{ \Carbon\Carbon::parse($note->stamped_at)->format('d/m/Y') }}</td>
                            <td>
                                @if ($note->status != 5)
                                    <a class="btn btn-primary btn-sm"
                                        href="{{ route('invoices.stamp.credit-note', ['id' => $note->id]) }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-custom-class="custom-tooltip" data-bs-title="Timbrar">
                                        <i class="bi bi-bell-fill"></i>
                                    </a>
                                @else
                                    <a href="{{ route('invoices.download.zip-credit-note', ['id' => $note->id]) }}"
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
    </script>
@endsection
