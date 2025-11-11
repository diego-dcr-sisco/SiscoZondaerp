<div class="modal fade" id="showClientModal{{ $client->id }}" tabindex="-1" aria-labelledby="showClientModalLabel{{ $client->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showClientModalLabel{{ $client->id }}">Detalles del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row g-3">
                        <!-- Columna: Datos del Cliente -->
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="fas fa-user me-2"></i>Datos del Cliente</h6>
                            <div class="mb-2">
                                <small class="text-muted">Nombre</small>
                                <div class="">{{ $client->customer->name }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Email</small>
                                <div class="">{{ $client->customer->email }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Teléfono</small>
                                <div class="">{{ $client->customer->phone }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Estado</small>
                                <div>
                                    @php
                                        $status = $client->customer->status;
                                        $badgeClass = match($status) {
                                            '1' => 'success',
                                            '0' => 'danger',
                                            '2' => 'primary',
                                            default => 'secondary'
                                        };
                                        $statusText = match($status) {
                                            '1' => 'Activo',
                                            '0' => 'Inactivo',
                                            '2' => 'Facturable',
                                            default => 'Desconocido'
                                        };
                                    @endphp
                                    <span class=" text-{{ $badgeClass }}">{{ $statusText }}</span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Fecha de Alta</small>
                                <div class="">{{ $client->customer->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <!-- Columna: Datos Facturables -->
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Datos Facturables</h6>
                            <div class="mb-2">
                                <small class="text-muted">RFC</small>
                                <div class="">{{ $client->customer->rfc }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Nombre Fiscal</small>
                                <div class="">{{ $client->customer->tax_name }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Régimen Fiscal</small>
                                <div class="">{{ $client->customer->taxRegime->name }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Uso de CFDI</small>
                                <div class="">{{ $client->cfdiUsage->code }} {{ $client->cfdiUsage->description }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Limite de credito</small>
                                <div class="">$ {{ $client->credit->limit_amount }}</div>
                            </div>
                        </div>
                        <div class="col-12 mt-3 btn-block text-center w-100">
                            <a href="{{ route('invoices.customer.show', $client->id) }}" 
                               class="btn btn-outline-primary btn-sm"
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="ver facturas y detalles">
                                Ver más
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>