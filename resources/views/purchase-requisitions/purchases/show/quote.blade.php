<div class="row mb-3">
    <h5 class="fw-bold pb-1 border-bottom">Detalles</h5>
    <div class="col-6">
        <p class="card-text"><strong>Solicitante:</strong> {{ $requisition->user->name }}</p>
    </div>
    <div class="col-6">
        <p class="card-text"><strong>Empresa Destino:</strong> {{ $requisition->customer->name }}</p>
    </div>

    <div class="col-6">
        <p class="card-text"><strong>Departamento Solicitante:</strong>
            {{ $requisition->user->workDepartment->name }}</p>
    </div>
    <div class="col-6">
        <p class="card-text"><strong>Dirección Empresa Destino:</strong> {{ $requisition->customer->address }}
        </p>
    </div>

    <div class="col-6">
        <p class="card-text"><strong>Fecha de Solicitud:</strong> {{ $requisition->created_at }}</p>
    </div>
    <div class="col-6">
        <p class="card-text"><strong>Fecha a Requerir:</strong> {{ $requisition->request_date }}</p>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <label class="form-label">Observaciones</label>
        <textarea class="form-control">{{ $requisition->observations }}</textarea>
    </div>
</div>

<form class="mb-5" action="{{ route('purchase-requisition.quote.update', ['id' => $requisition->id]) }}" method="POST" >
    @csrf
    @method('PUT')
    <div class="row mb-3">
        <h5 class="fw-bold pb-1 border-bottom">Productos</h5>
        <div class="col my-3">

            <table class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Unidad</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad en almacén</th>
                        <th scope="col" id="header-supplier1">Proveedor 1</th>
                        <th scope="col" id="header-cost1">Costo 1</th>
                        <th scope="col" id="header-supplier2">Proveedor 2</th>
                        <th scope="col" id="header-cost2">Costo 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>
                                <span class="product-quantity" id="quantity">
                                    {{ $product->quantity }}</span>
                            </td>
                            <td>{{ $product->unit }}</td>
                            <td>{{ $product->description }}</td>
                            <td>
                                <span class="product-amount" id="amount" style="{{ $amounts[$product->description] > 0 ? 'color: green' : 'color: red' }}">
                                    {{ $amounts[$product->description] }}</span>
                            </td>
                            <td id="select-supplier1">
                                <select class="form-select supplier1-select" name="supplier1[{{ $product->id }}]">
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ $product->supplier1_id == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td id="input-cost1">
                                <input type="number" step="0.01" class="form-control cost1"
                                    name="cost1[{{ $product->id }}]"
                                    value="{{ old('cost1.' . $product->id, $product->supplier1_cost ?? 0.0) }}">
                            </td>
                            <td id="select-supplier2">
                                <select class="form-select supplier2-select" name="supplier2[{{ $product->id }}]">
                                    <option value="">Sin proveedor</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ $product->supplier2_id == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td id="input-cost2">
                                <input type="number" step="0.01" class="form-control cost2"
                                    name="cost2[{{ $product->id }}]"
                                    value="{{ old('cost2.' . $product->id, $product->supplier2_cost ?? 0.0) }}">
                            </td>
                        </tr>
                    @endforeach
            </table>

        </div>
    </div>

    <button type="submit" class="btn btn-primary me-3">{{ __('buttons.store') }}</button>
    <a href="javascript:history.back()" class="btn btn-danger"> {{ __('buttons.cancel') }}</a>
</form>
