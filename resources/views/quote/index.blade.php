@extends('layouts.app')

@section('content')
    <style>
        .text-orange {
            color: #e67e22 !important;
        }
    </style>

    @php
        $count = 0;
        function formatCurrency(?float $amount): string
        {
            $formatter = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

            return $formatter->format($amount ?? 0);
        }
    @endphp

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ $customer['type'] == 'App/Models/Customer' ? route('customer.index') : route('customer.index.leads') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">COTIZACIONES DEL CLIENTE <span class="ms-2 fs-4">
                    {{ $customer['name'] }}</span></span>
        </div>

        <div class="p-3">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#quoteModal">
                    Agregar cotización
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm caption-top">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Servicio</th>
                            <th>Inicio</th>
                            <th>Fin estimada</th>
                            <th>Válido hasta</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Valor</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="table-body-quote">
                        @foreach ($quotes as $i => $quote)
                            @php
                                $count += $quote->value;
                            @endphp
                            <tr>
                                <th>{{ $i + 1 }}</th>
                                <td>{{ $quote->service->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($quote->start_date)->translatedFormat('d-m-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($quote->end_date)->translatedFormat('d-m-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($quote->valid_until)->translatedFormat('d-m-Y') }}</td>
                                <td class="{{ $quote->priority->class() }} fw-bold">
                                    <i class="bi {{ $quote->priority->icon() }}"></i>
                                    {{ $quote->priority->label() }}
                                </td>
                                <td>
                                    <span class="{{ $quote->status->class() }} fw-bold">
                                        {{ $quote->status->label() }}
                                    </span>
                                </td>
                                <td class="fw-bold">{{ formatCurrency($quote->value) }}</td>
                                <td>
                                    @if (!$quote->file)
                                        <span class="text-danger fw-bold">Sin archivo PDF</span>
                                    @else
                                        <a href="{{ route('customer.quote.download', ['id' => $quote->id]) }}"
                                            class="btn btn-sm btn-link" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Archivo PDF cotización">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i> Archivo PDF
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('customer.quote.edit', ['id' => $quote->id]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Editar cotización">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="{{ route('customer.quote.destroy', ['id' => $quote->id]) }}"
                                        class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Eliminar cotización">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <td colspan="7" class="text-end">Total</td>
                            <td colspan="3" class="text-start" id="table-count">{{ formatCurrency($count) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @include('quote.modals.create')

    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
@endsection
