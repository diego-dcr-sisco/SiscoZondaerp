<div class="row">
    <div class="col-12 mb-3">
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#productModal"
            onclick="cleanForm()"><i class="bi bi-plus-lg"></i> {{ __('buttons.add') }} Producto </button>
    </div>
    <div class="col-12">
        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Producto</th>
                    <th scope="col">Servicio usado</th>
                    <th scope="col">Método de aplicación</th>
                    <th scope="col">Cantidad usada</th>
                    <th scope="col">Cantidad por ltrs aplicados</th>
                    <th scope="col">Lote</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody id="products-table-body">
                @foreach ($order->products as $i => $order_product)
                    <tr>
                        <th scope="row">{{ $i + 1 }}</th>
                        <td>{{ $order_product->product->name ?? '-' }}</td>
                        <td>{{ $order_product->service->name ?? '-' }}</td>
                        <td>{{ $order_product->appMethod->name ?? '-' }}</td>
                        <td class="fw-bold">
                            {{ $order_product->amount ?? '0' }}<br>
                            <small
                                class="text-muted">{{ $order_product->metric?->value ?? ($order_product->product->metric?->value ?? '-') }}</small>
                        </td>
                        <td>{{ $order_product->dosage ?? ($order_product->product->dosage ?? '-') }}
                        </td>
                        <td>{{ $order_product->lot->registration_number ?? ($order_product->possible_lot ?? '-') }}</td>
                        <td>
                            {{-- <a href="#"
                                class="btn btn-warning btn-sm"
                                onclick="return confirm('{{ __('messages.are_you_sure') }}')">
                                <i class="bi bi-arrow-left-right"></i>
                                {{ __('buttons.propagate') }}
                            </a> --}}
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#productModal" data-product="{{ $order_product }}"
                                onclick="setProduct(this)"><i class="bi bi-pencil-square"></i></button>
                            <a href="{{ route('report.destroy.product', ['dataId' => $order_product->id]) }}"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
