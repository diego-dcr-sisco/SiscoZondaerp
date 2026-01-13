@extends('layouts.app')
@section('content')
    @php
        function stockLevel($total, $current)
        {
            $perc = ($current / $total) * 100;
            if ($perc <= 20) {
                return 'text-danger'; // Critical (red)
            } elseif ($perc <= 50) {
                return 'text-warning'; // Warning (yellow/orange)
            } elseif ($perc <= 100) {
                return 'text-success'; // Normal (blue)
            } else {
                return 'text-primary'; // Good (green)
            }
        }
    @endphp

    {{-- @include('dashboard.stock.navigation') --}}

    <div class="container-fluid ">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <span class="text-black fw-bold fs-4">
                LISTA DE ALMACENES 
            </span>
        </div>
        <div class="d-flex flex-row justify-content-between align-items-center py-3">
            @can('write_warehouse')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear almacén
                </button>
            @endcan
                <div class="col-md-6 col-lg-4">
                    <input type="text" id="warehouseSearch" class="form-control border-success" placeholder="Buscar almacén por nombre...">
                </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th scope="col" class="" style="width: 50px;">#</th>
                        <th scope="col" class="">Ver</th>
                        <th scope="col" class="text-start">Nombre</th>
                        <th scope="col" class="">Sucursal</th>
                        <th scope="col" class="">Técnico</th>
                        <th scope="col" class="">Tipo</th>
                        <th scope="col" class="">Productos</th>
                        @if ($hasActionPermission)
                            <th scope="col" class=""></th>
                        @endif
                    </tr>
                </thead>
                <tbody>

                    @forelse ($warehouses as $index => $warehouse)
                        <tr class="warehouse-row" data-warehouse-id="{{ $warehouse->id }}">
                            <td class=" fw-bold text-muted">{{ $index + 1 }}</td>
                            <td class="">
                                <a href="{{ route('stock.show', ['id' => $warehouse->id]) }}" class="btn btn-info btn-sm"
                                    title="Ver almacén">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </td>
                            <td class="text-start">
                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="bg-primary bg-opacity-10 rounded-circle p-2 d-inline-flex align-items-center justify-content-center"
                                        style="width:32px;height:32px;">
                                        <i class="bi bi-building text-primary"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $warehouse->name }}</div>
                                        @if ($warehouse->is_active)
                                            <small class="text-success fw-semibold">Activo</small>
                                        @else
                                            <small class="text-danger fw-semibold">Inactivo</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-start">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-geo-alt text-info"></i>
                                    <span class="fw-semibold">{{ $warehouse->branch->name }}</span>
                                </span>
                            </td>
                            <td class="">
                                @if ($warehouse->technician)
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-person-badge text-success"></i>
                                        <span class="fw-semibold">{{ $warehouse->technician->user->name }}</span>
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                @if ($warehouse->is_matrix)
                                    <span class="fw-bold">Matriz</span>
                                @elseif($warehouse->technician)
                                    <span class="fw-bold">Técnico</span>
                                @else
                                    <span class="fw-bold">Regular</span>
                                @endif
                            </td>
                            <td class="">
                                {{ $warehouse->products_count }}
                            </td>
                            @if ($hasActionPermission)
                                <td>
                                    <a href="{{ route('stock.show', ['id' => $warehouse->id]) }}"
                                        class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Ver almacén">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    @if (auth()->user()->work_department_id == 1)
                                        <a href="{{ route('stock.edit', ['id' => $warehouse->id]) }}"
                                            class="btn btn-secondary btn-sm" data-bs-toggle="tooltip"
                                            title="Editar almacén">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif
                                    @if (auth()->user()->work_department_id == 1 ||
                                            auth()->user()->work_department_id == 5 ||
                                            auth()->user()->work_department_id == 6)
                                        <a href="{{ route('stock.entry', ['id' => $warehouse->id]) }}"
                                            class="btn btn-success btn-sm" data-bs-toggle="tooltip"
                                            title="Entradas">
                                            <i class="bi bi-box-arrow-in-down-right"></i>
                                        </a>
                                        <a href="{{ route('stock.exits', ['id' => $warehouse->id]) }}"
                                            class="btn btn-warning btn-sm" data-bs-toggle="tooltip"
                                            title="Salidas">
                                            <i class="bi bi-box-arrow-up-left"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('stock.movements.warehouse', ['id' => $warehouse->id]) }}"
                                        class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Movimientos">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </a>
                                    <a href="{{ route('stock.showProducts', ['id' => $warehouse->id]) }}"
                                        class="btn btn-dark btn-sm" data-bs-toggle="tooltip"
                                        title="Stock de productos">
                                        <i class="bi bi-boxes"></i>
                                    </a>
                                    @if (auth()->user()->work_department_id == 1)
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                                            title="Eliminar almacén" onclick="confirmDelete({{ $warehouse->id }})">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    @endif

                                    @if (auth()->user()->work_department_id == 1)
                                        <form id="delete-form-{{ $warehouse->id }}"
                                            action="{{ route('stock.destroy', ['id' => $warehouse->id]) }}"
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class=" py-5">
                                <div class="empty-state">
                                    <i class="bi bi-building text-muted fs-1 mb-3"></i>
                                    <h5 class="text-muted">No hay almacenes para mostrar</h5>
                                    <p class="text-muted mb-0">No se encontraron almacenes en el sistema.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('stock.create.modals.form')

    <!-- CSS inline -->
    <style>
        .table-responsive {
            overflow: visible !important;
        }

        .table {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            position: relative;
            z-index: 0;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            animation: fadeInUp 0.3s ease forwards;
            position: relative;
            z-index: 0;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05) !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .empty-state {
            padding: 3rem 1rem;
        }

        .empty-state i {
            display: block;
            margin: 0 auto;
        }

        .dropdown-menu {
            z-index: 1050 !important;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
                position: relative;
                z-index: 0;
            }

            .badge {
                font-size: 0.7rem !important;
                padding: 0.375rem 0.5rem !important;
            }

            .btn-group .btn,
            .dropdown-menu .dropdown-item {
                padding: 0.25rem 0.5rem;
                z-index: 1050 !important;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        function confirmDelete(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este almacén?')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
        // Animación de entrada para las filas
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.warehouse-row');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <script>
        const warehouses = @json($warehouses);
        const indirect_warehouse = warehouses.find(warehouse => warehouse.name === 'SISCOPLAGAS-MRO');
        const lots = @json($lots);
        const products = @json($products);
        const metrics = @json($metrics);

        function movementConfig(element) {
            limitProducts(element);
            setDestinationWarehouses(element);
        }

        function setDestinationWarehouses(element) {
            const assigned_products = JSON.parse(element.dataset.products);
            console.log(element.attributes[4].value);
            var warehouse_id = element.attributes[4].value;
            console.log(warehouse_id);
            var html = '';
            if (assigned_products) {
                var fetch_warehouses = warehouses.filter(warehouse => {
                    return warehouse.id != assigned_products[0].warehouse_id;
                });

                var html = '<option value="" selected>Sin almacen</option>';
                fetch_warehouses.forEach(warehouse => {
                    html += `<option value="${warehouse.id}">${warehouse.name}</option>`;
                });

                $('#input-destination-warehouse').html(html);
                $('#input-warehouse').val(assigned_products[0].warehouse_id);
                $('#input-warehouse-text').val(warehouses.find(warehouse => warehouse.id == assigned_products[0]
                    .warehouse_id).name);

                $('#output-destination-warehouse').html(html);
                $('#output-warehouse').val(assigned_products[0].warehouse_id);
                $('#output-warehouse-text').val(warehouses.find(warehouse => warehouse.id == assigned_products[0]
                    .warehouse_id).name);
            }

            $('#input-amount, #output-amount').attr('placeholder', '0');
            $('#input-amount, #output-amount').val('');
            $('#input-form select, #output-form select').each(function() {
                $(this).prop('selectedIndex', 0);
            });
        }

        function limitProducts(element) {
            const assigned_products = JSON.parse(element.dataset.products);
            var html = '';

            if (assigned_products) {

                $('#output-product').empty();

                var fetch_products = products.filter(product => {
                    return assigned_products.some(assigned_product => assigned_product.product_id == product.id &&
                        assigned_product.amount > 0);
                });

                var html = '<option value="" selected>Sin producto</option>';
                fetch_products.forEach(product => {
                    html += `<option value="${product.id}">${product.name}</option>`;
                });

                $('#output-product').html(html);
            }
        }

        function setProducts(element) {
            // console.log(element.dataset);
            const assigned_products = JSON.parse(element.dataset.products);
            console.log(assigned_products);
            var html = '';
            $('#stock-table-body').empty();

            if (assigned_products) {
                assigned_products.forEach((item, index) => {
                    const product = products.find(product => product.id == item.product_id);
                    const lot = lots.find(lot => lot.id == item.lot_id);
                    const metric = metrics.find(metric => metric.id == product.metric_id);
                    html += `
                        <tr>
                          <th scope="row">${index + 1}</th>
                          <td>${product.name}</td>
                          <td>${item.amount} ${metric.value}</td>
                          <td>${lot ? (lot.registration_number /*+ ' [' + lot.amount + ']'*/): '-'}</td>
                        </tr>
                    `;
                })
            }
            $('#stock-table-body').html(html);
        }

        function limitLots(product_id, type) {
            let id = `#${type}-lot`;
            var warehouse_id = $('#input-warehouse').val();
            var fetch_lots = warehouse_products = [];

            if (!product_id) {
                return $(id).html('<option value="" selected>Sin lote</option>');
            }

            if (type == 'input') {
                fetch_lots = lots.filter(lot => lot.product_id == product_id);
                remaining = lots_remaining[fetch_lots[0].id];
            } else {
                const warehouse = warehouses.find(warehouse => warehouse.id == warehouse_id);
                warehouse_products = warehouse.products.filter(item => item.product_id == product_id && item.amount >
                    0);
                fetch_lots = lots.filter(lot => warehouse_products.some(item => item.lot_id == lot.id));
            }

            const options = fetch_lots.map(lot => `<option value="${lot.id}">${lot.registration_number}</option>`).join('');
            const amount = type == 'input' ? remaining || 0 : warehouse_products[0]?.amount || 0;

            $(id).html(options);
            $('#input-amount, #output-amount').attr('max', amount);
            $('#input-amount, #output-amount').attr('placeholder', amount);
        }

        function limitAmount(lot_id, type) {
            var warehouse_id = $(`#${type}-warehouse`).val();
            var product_id = $(`#${type}-product`).val();
            var remaining = 0;
            const warehouse = warehouses.find(warehouse => warehouse.id == warehouse_id);
            var fetch_lot = warehouse.products.find(item => item.product_id == product_id &&
                item.lot_id == lot_id && item.amount > 0);

            if (!fetch_lot) {
                fetch_lot = lots.find(lot => lot.id == lot_id && lot.product_id == product_id && lot.amount > 0);
                remaining = lots_remaining[fetch_lot.id];
            } else {
                remaining = fetch_lot.amount;
            }

            if (fetch_lot) {
                $('#input-amount').attr('max', remaining);
                $('#output-amount').attr('max', remaining);

                $('#input-amount').attr('placeholder', remaining);
                $('#output-amount').attr('placeholder', remaining);
            } else {
                $('#input-amount').attr('max', 0);
                $('#output-amount').attr('max', 0);

                $('#input-amount').attr('placeholder', 0);
                $('#output-amount').attr('placeholder', 0);
            }
        }

        function checkMaxValue(element) {
            const max = parseFloat(element.getAttribute('max')); // Obtiene el valor máximo del atributo max
            const inputValue = parseFloat(element.value);

            if (inputValue > max) {
                element.value = parseFloat(max).toFixed(2);
                alert(`El valor no puede ser mayor a ${max}`);
                return;
            }
        }
    </script>

    <script> // Buscar almacén por nombre
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('warehouseSearch');
            searchInput.addEventListener('input', function () {
                const filter = this.value.toLowerCase();
                document.querySelectorAll('.warehouse-row').forEach(function(row) {
                    const nameCell = row.querySelector('.fw-semibold.text-dark');
                    if (nameCell) {
                        const name = nameCell.textContent.toLowerCase();
                        if (name.includes(filter)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>
@endsection