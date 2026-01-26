<div class="modal fade" id="approvePurchaseModal" tabindex="-1" role="dialog" aria-labelledby="approvePurchaseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvePurchaseModalLabel">Aprobar Solicitud de Compra</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body container-fluid">
                <div class="row p-1">
                    <p>Seleccione los proveedores a elegir</p>
                </div>
                <form action="{{ route('purchase-requisition.approve', $requisition->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Cantidad</th>
                                        <th>Producto</th>
                                        <th>Proveedor 1</th>
                                        <th>Costo 1</th>
                                        <th>Proveedor 2</th>
                                        <th>Costo 2</th>
                                        <th>Proveedor Aprobado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>{{ $product->quantity }} - {{ $product->unit }}</td>
                                            <td>{{ $product->description }}</td>
                                            <td style="font-size: small; width: min-content">
                                                {{ $product->supplier1->name }}
                                            </td>
                                            <td>{{ $product->supplier1_cost }}</td>
                                            <td style="font-size: small; width: min-content">
                                                {{ $product->supplier2 ? $product->supplier2->name : 'Sin proveedor' }}
                                            </td>
                                            <td>{{ $product->supplier2_cost }}</td>
                                            <td>
                                                <!-- Inputs de selección para aprobar proveedor en línea -->
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="approved_supplier[{{ $product->id }}]"
                                                        id="supplier1_{{ $product->id }}"
                                                        value="{{ $product->supplier1_id }}" checked>
                                                    <label class="form-check-label"
                                                        for="supplier1_{{ $product->id }}">
                                                        Proveedor 1
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="approved_supplier[{{ $product->id }}]"
                                                        id="supplier2_{{ $product->id }}"
                                                        value="{{ $product->supplier2_id }}"
                                                        {{ $product->supplier2 ? '' : 'disabled' }}>
                                                    <label class="form-check-label"
                                                        for="supplier2_{{ $product->id }}">
                                                        Proveedor 2
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i>
                            {{ __('buttons.approve') }}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
