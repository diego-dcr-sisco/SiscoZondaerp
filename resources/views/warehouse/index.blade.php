@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    @include('warehouse.create.modals.form')
    @include('warehouse.create.modals.input')
    @include('warehouse.create.modals.output')
    @include('warehouse.create.modals.stock')

    <div class="row w-100 h-100 m-0">
        <div class="col-1 m-0" style="background-color: #343a40;">
            @include('dashboard.stock.navigation')
        </div>
        <div class="col-11 p-0 m-0">
            @include('warehouse.tables.index')
        </div>
    </div>

    

    <script>
        const warehouses = @json($warehouses);
        const stocks = @json($stocks);

        function setData(button, warehouseId, type) {
            const warehouse_data = JSON.parse(button.dataset.warehouse);
            const today = new Date();
            const filtered_warehouses = warehouses.filter(warehouse => warehouse.id != warehouseId);
            
            $(`#${type}-destination-warehouse`).empty();
            $(`#${type}-destination-warehouse`).append(new Option('Sin almacen', ''));
            filtered_warehouses.forEach(option => {
                $(`#${type}-destination-warehouse`).append(new Option(option.name, option.id));
            });

            $(`#${type}-date`).val(
                `${today.getFullYear()}-${('0' + (today.getMonth() + 1)).slice(-2)}-${('0' + today.getDate()).slice(-2)}`
            );
            $(`#${type}-warehouse`).val(warehouse_data.id);
            $(`#${type}-warehouse-text`).val(warehouse_data.name);
        }

        function setStock(warehouseId) {
            const found_stock = stocks.find(stock => stock.warehouse_id == warehouseId);
            const found_warehouse = warehouses.find(warehouse => warehouse.id == warehouseId);
            var html = '';
            $('#stock-table-body').empty();
            if(found_stock) {
                found_stock.stock.forEach((item, index) => {
                    html += `
                        <tr>
                          <th scope="row">${index + 1}</th>
                          <td>${item.product_name}</td>
                          <td>${item.product_metric}</td>
                          <td>${item.cant}</td>
                          <td>${0}</td>
                        </tr>
                    `;
                })
            } 
            $('#stock-table-body').html(html);
        }
    </script>
@endsection
