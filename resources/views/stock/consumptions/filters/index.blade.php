<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Filtros de Búsqueda</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('consumptions.index') }}" class="row justify-content-between g-3">
            @csrf
            
            <div class="col-lg-2">
                <label class="form-label">Zona</label>
                <select name="zone_id" id="zone_select" class="form-select">
                    <option value="">Todas las zonas</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" 
                            {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                            {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            @if(isset($customers) && count($customers) > 0)
                <div class="col-lg-4">
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

            <div class="col-lg-3">
                <label class="form-label">Rango de Fechas:</label>
                <input type="text" name="date_range" id="date_range" class="form-control"
                    value="{{ isset($start) && isset($end) ? $start . ' - ' . $end : now()->subMonth()->format('d-m-Y') . ' - ' . now()->format('d-m-Y') }}"
                    autocomplete="off" required>
                <input type="hidden" name="start_date" id="start_date"
                    value="{{ $start ?? now()->subMonth()->format('d-m-Y') }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ $end ?? now()->format('d-m-Y') }}">
            </div>

            <div class="col-lg-3 d-flex align-items-end justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter"></i> Filtrar
                </button>

                @if (isset($consumptions) && count($consumptions) > 0)
                    <a href="{{ route('consumptions.export', [
                        'start_date' => $start ?? now()->subMonth()->format('d-m-Y'),
                        'end_date' => $end ?? now()->format('d-m-Y'),
                        'customer_id' => $customerId ?? '',
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
    $(function() {
        // Inicialización del daterangepicker
        $('#date_range').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                weekLabel: 'S',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                firstDay: 1
            },
            startDate: "{{ $start ?? now()->subMonth()->format('Y-m-d') }}",
            endDate: "{{ $end ?? now()->format('Y-m-d') }}"
        }, function(start, end) {
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        });

        // Set initial hidden fields on load
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
        });

        // Filtrado dinámico de clientes por zona
        $('#zone_select').on('change', function() {
            const zoneId = $(this).val();
            const customerSelect = $('#customer_select');
            const selectedCustomerId = '{{ request('customer_id') }}';
            
            // Limpiar el select de clientes
            customerSelect.empty();
            customerSelect.append('<option value="">Todos los clientes</option>');
            
            if (zoneId) {
                // Hacer petición AJAX para obtener clientes de la zona
                $.ajax({
                    url: '{{ route('consumptions.customers-by-zone') }}',
                    method: 'GET',
                    data: { zone_id: zoneId },
                    success: function(customers) {
                        customers.forEach(function(customer) {
                            const selected = selectedCustomerId == customer.id ? 'selected' : '';
                            customerSelect.append(
                                `<option value="${customer.id}" ${selected}>${customer.name}</option>`
                            );
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar clientes:', error);
                        alert('Error al cargar los clientes. Por favor, intente de nuevo.');
                    }
                });
            } else {
                // Si no hay zona seleccionada, cargar todos los clientes
                const allCustomers = @json($customers ?? []);
                allCustomers.forEach(function(customer) {
                    const selected = selectedCustomerId == customer.id ? 'selected' : '';
                    customerSelect.append(
                        `<option value="${customer.id}" ${selected}>${customer.name}</option>`
                    );
                });
            }
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
