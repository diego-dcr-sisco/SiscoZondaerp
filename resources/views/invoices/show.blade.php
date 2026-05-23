@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2 no-print">
            <a href="{{ route('invoices.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                DETALLE DE FACTURA
            </span>
        </div>

        <div class="row p-4">
            @if (!isset($invoice))
                <div class="container no-print">
                    <div class="alert alert-info alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <h4>Vista Previa de Factura</h4>
                        <p>
                            Esta es una vista previa de cómo se verá la factura. Revise cuidadosamente todos los datos antes
                            de generar la factura definitiva.
                            Si necesita generar la factura desde otra orden o contrato, cancele esta vista previa y regrese
                            a la orden o contrato correspondiente.
                        </p>
                    </div>
                </div>
            @endif

            <div class="col-md-8">
                @include('invoices.pdf_preview')
            </div>

            <div class="col-md-4 no-print">
                <div class=" p-2">
                    <div class="col-12">
                        {{-- Datos del Cliente --}}
                        <div class="p-3 shadow border border-secondary-subtle bg-light rounded-3 mb-4">
                            <h6 class="fw-bold mb-3 text-secondary">
                                <i class="bi bi-person me-2"></i>Datos del cliente
                            </h6>
                            <div class="row g-1">
                                <div class="col-auto-6">
                                    <span class="fw-bold">{{ $invoice->customer->social_reason ?? '-' }}</span>
                                </div>
                                <div class="col-auto-6">
                                    {{-- <label class="small text-muted d-block">Email</label> --}}
                                    <span class="fw-medium">
                                        @if ($invoice->customer->email)
                                            <a href="mailto:{{ $invoice->customer->email }}" class="text-decoration-none">
                                                <i class="bi bi-envelope me-1"></i>
                                                {{ $invoice->customer->email }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                                <div class="col-auto-6">
                                    {{-- <label class="small text-muted d-block">Teléfono</label> --}}
                                    <span class="fw-medium">
                                        @if ($invoice->customer->phone)
                                            <a href="tel:{{ $invoice->customer->phone }}" class="text-decoration-none">
                                                <i class="bi bi-telephone me-1"></i>
                                                {{ $invoice->customer->phone }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Datos de la factura --}}
                        <div class="p-3 shadow border border-secondary-subtle bg-light rounded-3 mb-4">
                            <h6 class="fw-bold mb-3 text-secondary">
                                <i class="bi bi-file-earmark-text me-2"></i>Datos de la factura
                            </h6>
                            <div class="row g-1">
                                @if($invoice->order)
                                    <div class="col-auto-6">
                                        <span class="fw-bold">Origen: <a href="{{ route('order.edit', $invoice->order_id) }}">Orden:
                                            {{ $invoice->order->folio }}</a></span>
                                        <hr>
                                    </div>
                                @else
                                    <div class="col-auto-6">
                                        <span> Creada el {{ $invoice->created_at->format('d/m/Y') }}</span>
                                        <hr>
                                    </div>
                                @endif
                                <div class="col-auto-6">
                                    <span class="fw-medium">
                                        Total de la factura: <strong>$ {{ $invoice->total }} </strong>
                                        {{ $invoice->currency }}
                                    </span>
                                </div>
                                <div class="col-auto-6">
                                    <span class="fw-medium">
                                        N° de factura: <strong> {{ $invoice->folio }} </strong>
                                    </span>
                                </div>
                                <div class="col-auto-6">
                                    <span class="fw-medium">
                                        Estado: <strong> {{ $invoice->getStatus() }} </strong>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- PROCESOS DE LA FACTURA --}}
                        <div class="p-3 shadow border border-secondary-subtle bg-light rounded-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-success mb-0">
                                    1. Factura Generada
                                </h6>
                                <a href="{{ route('invoices.show.pdf', ['id' => $invoice->id]) }}"
                                    target="_blank" class="btn btn-outline-primary ms-3">
                                    <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                                </a>
                            </div>
                            {{-- Si la factura esta en estado pendiente se puede editar --}}
                            @if($invoice->status == 'pendiente' || $invoice->status == 0)
                                <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-secondary btn-sm mt-2">
                                    <i class="fas fa-edit"></i> Editar Factura
                                </a>
                            @endif
                        </div>


                            {{-- Si ya se genero el XML  --}}
                        {{--<div
                            class="p-3 shadow border border-secondary-subtle bg-light rounded-3 mb-4 d-flex justify-content-between align-items-center">
                            @if ($invoice->xml_file != null)
                                <h6 class="fw-bold text-success">
                                    2. XML Generado.
                                </h6>
                                <a href="{{ route('invoices.show.xml', ['id' => $invoice->id]) }}"
                                    class="btn btn-outline-primary ms-3" target="_blank">
                                    <i class="bi bi-file-earmark-code"></i> Ver XML
                                </a>
                            @else
                                <h6 class="fw-bold text-secondary">
                                    2. Generar XML
                                </h6>
                                <form
                                    action="{{ route('invoices.generate.xml', ['id' => $invoice->id ]) }}"
                                    method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-file-earmark-code me-2"></i> Generar XML
                                    </button>
                                </form>
                            @endif
                        </div>--}}

                            {{-- Si ya se timbro la factura --}}
                            <div
                                class="p-3 shadow border border-secondary-subtle bg-light rounded-3 mb-4 d-flex justify-content-between align-items-center">
                                @if ( $invoice->status == 5 || $invoice->status == 'Timbrada' )
                                    <h6 class="fw-bold text-success">
                                        2. Factura Timbrada
                                    </h6>
                                @else
                                    <h6 class="fw-bold text-secondary">
                                        2. Timbrar Factura
                                    </h6>
                                    <a class="btn btn-outline-primary"
                                        href="{{ route('invoices.stamp.invoice', ['id' => $invoice->id]) }}">
                                        <i class="bi bi-bell"></i> Timbrar
                                    </a>
                                @endif
                            </div>

                            {{-- Si ya se envio la factura --}}
                            <div
                                class="p-3 shadow border border-secondary-subtle bg-light rounded-3 mb-4 d-flex justify-content-between align-items-center">
                                @if ($invoice->status == 7)
                                    <h6 class="fw-bold text-success">
                                        3. Factura Enviada
                                    </h6>
                                @else
                                    <h6 class="fw-bold text-secondary">
                                        3. Enviar Factura
                                    </h6>
                                    @if ($invoice->status == 5 || $invoice->status == 'Timbrada')
                                        <form
                                            action="{{ route('invoices.send.email', [$invoice->id]) }}"
                                            method="POST" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{ $invoice->order->id ?? '' }}">
                                            <button type="submit" class="btn btn-outline-primary"
                                                onclick="return confirm('¿Está seguro de enviar la factura al cliente?')">
                                                <i class="bi bi-envelope"></i> Enviar Factura
                                            </button>
                                        </form>
                                    @else
                                        <small class="text-muted mb-0 w-50">
                                            La factura debe estar Timbrada para poder enviarla.
                                        </small>
                                    @endif
                                @endif
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
