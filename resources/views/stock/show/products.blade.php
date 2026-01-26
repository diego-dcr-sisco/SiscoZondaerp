<!-- resources/views/stock/products.blade.php -->
@extends('layouts.app') <!-- Asegúrate de extender tu layout principal -->

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('stock.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                    STOCK DE PRODUCTOS - ALMACEN <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $warehouse->name }}</span>
                </span>
            </div>
            <div class="m-3">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped table-sm align-middle caption-top">
                        <caption class="border rounded-top p-2 text-dark bg-light">
                            <div class="text-end">
                                <!-- Botón para exportar a Excel -->
                                <a href="{{ route('stock.exportStock', ['id' => $warehouse->id]) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Exportar a EXCEL
                                </a>
                            </div>
                        </caption>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Presentación</th>
                                <th>Lote</th>
                                <th>Cantidad</th>
                                <th>Caducidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products_data as $product_data)
                                <tr>
                                    <td>{{ $product_data['product'] }}</td>
                                    <td>{{ $product_data['presentation'] }}</td>
                                    <td>{{ $product_data['lot'] }}</td>
                                    <td>{{ $product_data['amount'] }} {{ $product_data['metric'] }}</td>
                                    <td>{{ $product_data['expiration_date'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endsection
