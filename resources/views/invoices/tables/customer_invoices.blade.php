<div class="card mb-4 shadow-sm">
    <div class="card-header">
        Facturas del Cliente
    </div>
    <div class="card-body p-0">
        @if(!$invoices)
            <div class="p-3">No hay facturas para este cliente.</div>
        @else
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ver</th>
                        <th>Fecha</th>
                        <th>Folio</th>
                        <th>Generado desde</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody class="text-start align-middle">
                    @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            @if($invoice->order)
                                <td>
                                    <a href="{{ route('invoices.show', ['id' => $invoice->order->id, 'type' => 'order']) }}"
                                        class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Ver factura">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            @elseif($invoice->contract)
                                <td>
                                    <a href="{{ route('invoices.show', ['id' => $invoice->contract->id, 'type' => 'contract']) }}"
                                        class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Ver factura">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            @endif
                            <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                            <td>{{ $invoice->folio }}</td>
                            <td>
                                @if($invoice->order)
                                    <a href="{{ route('invoices.show', ['id' => $invoice->order->id, 'type' => 'order']) }}">
                                        Orden #{{ $invoice->order->id ?? 'N/A' }}
                                    </a>
                                @elseif($invoice->contract)
                                    <a href="{{ route('invoices.show', ['id' => $invoice->contract->id, 'type' => 'contract']) }}">
                                        Contrato #{{ $invoice->contract->id ?? 'N/A' }}
                                    </a>
                                @endif
                            </td>
                            <td>$ {{ number_format($invoice->total, 2) }}</td>
                            <td>
                                <span class="">{{ ($invoice->getStatus()) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>