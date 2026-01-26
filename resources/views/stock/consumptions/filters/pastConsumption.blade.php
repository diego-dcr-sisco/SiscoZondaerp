<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Filtros de Búsqueda</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('consumptions.consumptions.filter') }}" class="row justify-content-between g-3" method="GET">
            @csrf

            @if(isset($customers) && count($customers) > 0)
                <div class="col-lg-3">
                    <label class="form-label">Seleccionar Cliente:</label>
                    <select name="customer_id" id="customer_select" class="form-select">
                        <option value="">Todos los clientes</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(isset($products) && count($products) > 0)
                <div class="col-lg-3">
                    <label class="form-label">Seleccionar Producto:</label>
                    <select name="product_id" id="product_select" class="form-select">
                        <option value="">Todos los productos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-lg-3">
                        <label for="date_range" class="form-label">Rango de Fechas</label>
                        <input type="text" class="form-control date-range-picker" id="date_range" name="date_range"
                            value="{{ request('date_range') }}" placeholder="Selecciona un rango">
                    </div>

            <div class="col-lg-3 d-flex align-items-end justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter"></i> Filtrar
                </button>

                @if (isset($consumptions) && count($consumptions) > 0)
                    <a href="{{ route('consumptions.export', [
                        'start_date' => request('start_date', now()->subMonth()->format('Y-m-d')),
                        'end_date' => request('end_date', now()->format('Y-m-d')),
                        'customer_id' => request('customer_id', ''),
                        'product_id' => request('product_id', ''),
                    ]) }}"
                        class="btn btn-success">
                        <i class="bi bi-file-excel"></i> Generar Excel
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('input[name="date_range"]').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
            },
            opens: 'left',
            autoUpdateInput: false
        });

        $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                'DD/MM/YYYY'));
        });
    });
</script>

<style>
    .customer-item {
        padding: 0.5rem 1rem;
        transition: background-color 0.2s;
    }

    .customer-item:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

    #customer_results {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
    }
</style>