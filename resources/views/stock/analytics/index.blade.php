@extends('layouts.app')
@section('content')
@php
    use Carbon\Carbon;
@endphp
    <div class="row w-100 h-100 m-0">
        <div class="col p-0 m-0">
            <div class="d-flex align-items-center border-bottom ps-4 p-2 mb-3">
                <span class="text-black fw-bold fs-4">
                    ESTADISTICAS DE ALMACEN
                </span>
            </div>
            
            <div class="container-fluid">
                <!-- Primera fila: Gráficas principales -->
                <div class="d-flex flex-row mb-4 px-4">
                    <div class="col-6 p-0 me-2">
                        <div class="card shadow mb-3 h-100">
                            <div class="card-body">
                                <div class="card-title fw-bold d-flex justify-content-between align-items-center">
                                    <span>Uso de producto</span>
                                    <select class="form-select w-50 ms-2" onchange="updateProductChart(this.value)">
                                        @foreach ($products as $index => $product)
                                            <option value="{{ $product->id }}" {{ $index == 0 ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="chart-product-use">
                                    {!! $charts['product_use']->container() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 p-0 ms-2">
                        <div class="card shadow mb-3 h-100">
                            <div class="card-body">
                                <div class="card-title fw-bold d-flex justify-content-between align-items-center">
                                    <span>Productos Más Usados por Mes</span>
                                    <div class="d-flex gap-2">
                                        <select class="form-select" style="width: 90px;" onchange="updateMostUsedChart()">
                                            @for ($year = Carbon::now()->year; $year >= Carbon::now()->year - 3; $year--)
                                                <option value="{{ $year }}" {{ $year == Carbon::now()->year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endfor
                                        </select>
                                        <select class="form-select" style="width: 90px;" onchange="updateMostUsedChart()">
                                            <option value="3">Top 3</option>
                                            <option value="5" selected>Top 5</option>
                                            <option value="10">Top 10</option>
                                        </select>
                                        <select class="form-select" style="width: 120px;" onchange="updateMostUsedChart()">
                                            <option value="">Todos los almacenes</option>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ substr($warehouse->name, 0, 12) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div id="chart-most-used">
                                    {!! $charts['most_used_products']->container() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Segunda fila: Nuevas gráficas de almacén -->
                <div class="row">
                    <!-- Inventario por Almacén -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-body">
                                <div class="card-title fw-bold d-flex justify-content-between align-items-center">
                                    <span>Cantidad de productos distintos en almacén</span>
                                </div>
                                <div id="chart-inventory">
                                    {!! $charts['inventory_by_warehouse']->container() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Tercera fila: Productos más usados por mes -->
                <div class="row">
                    <div class="col-6 p-0 ms-2">
                        <div class="card shadow mb-3 h-100">
                            <div class="card-body">
                                <div class="card-title fw-bold d-flex justify-content-between align-items-center">
                                    <span>Movimientos del almacen</span>
                                    <select class="form-select w-50 ms-2" onchange="updateWarehouseChart(this.value)">
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $warehouse->id == 1 ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="chart-stock-movements">
                                    {!! $charts['stock_movements']->container() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://unpkg.com/vue"></script>
    <script>
        var chart = new Vue({
            el: '#chart-product-use, #chart-stock-movements, #chart-inventory, #chart-low-stock, #chart-rotation, #chart-most-used',
        });

        var product_route_api_url = '';
        var warehouse_route_api_url = '';
        var inventory_route_api_url = '';
        var mostused_route_api_url = '';

        function updateProductChart(value) {
            if (!product_route_api_url) {
                product_route_api_url = {{ $charts['product_use']->id }}_api_url;
            }

            {{ $charts['product_use']->id }}_refresh(product_route_api_url + "?product_id=" + value);
        }

        function updateWarehouseChart(value) {
            if (!warehouse_route_api_url) {
                warehouse_route_api_url = {{ $charts['stock_movements']->id }}_api_url;
            }

            {{ $charts['stock_movements']->id }}_refresh(warehouse_route_api_url + '/update' + "?warehouseId=" + value);
        }

        function updateInventoryChart(value) {
            if (!inventory_route_api_url) {
                inventory_route_api_url = {{ $charts['inventory_by_warehouse']->id }}_api_url;
            }

            {{ $charts['inventory_by_warehouse']->id }}_refresh(inventory_route_api_url + "?warehouse_id=" + value);
        }

        function updateMostUsedChart() {
            const selects = document.querySelectorAll('select[onchange="updateMostUsedChart()"]');
            const yearSelect = selects[0];
            const limitSelect = selects[1];
            const warehouseSelect = selects[2];
            
            const year = yearSelect.value;
            const limit = limitSelect.value;
            const warehouseId = warehouseSelect.value;
            
            if (!mostused_route_api_url) {
                mostused_route_api_url = {{ $charts['most_used_products']->id }}_api_url;
            }

            let params = "?year=" + year + "&limit=" + limit;
            if (warehouseId) {
                params += "&warehouse_id=" + warehouseId;
            }

            {{ $charts['most_used_products']->id }}_refresh(mostused_route_api_url + params);
        }
    </script>
    <script src=https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js charset=utf-8></script>
    {!! $charts['product_use']->script() !!}
    {!! $charts['stock_movements']->script() !!}
    {!! $charts['inventory_by_warehouse']->script() !!}
    {!! $charts['most_used_products']->script() !!}
@endsection
