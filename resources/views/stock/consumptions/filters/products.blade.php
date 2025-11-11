<div class="card shadow-sm mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0">Filtros de Búsqueda</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('consumption.product.detail', $product->id) }}" method="GET" class="row g-3">
            <div class="col-lg-4">
                <label for="date_range" class="form-label">Rango de Fechas</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-calendar3"></i>
                    </span>
                    <input type="text" class="form-control" id="date_range" name="date_range"
                        value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }} - {{ request('end_date', now()->format('Y-m-d')) }}"
                        autocomplete="off" placeholder="Selecciona el rango de fechas">
                    <input type="hidden" name="start_date" id="start_date"
                        value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}">
                    <input type="hidden" name="end_date" id="end_date"
                        value="{{ request('end_date', now()->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="col-lg-4 d-flex align-items-end justify-content-end">
                <button type="submit" class="btn btn-primary mx-1">
                    <i class="bi bi-search"></i> Filtrar
                </button>

                @if (isset($details) && count($details) > 0)
                    <a href="{{ route('consumption.product.export', [
                        'product_id' => $product->id,
                        'start_date' => request('start_date', now()->subMonth()->format('Y-m-d')),
                        'end_date' => request('end_date', now()->format('Y-m-d')),
                    ]) }}"
                        class="btn btn-success mx-1">
                        <i class="bi bi-file-excel"></i> Exportar a Excel
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
    $(function() {
        $('#date_range').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Rango Específico',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                firstDay: 1
            },
            startDate: "{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}",
            endDate: "{{ request('end_date', now()->format('Y-m-d')) }}",
            ranges: {
                'Este Mes': [
                    moment().startOf('month'),
                    moment().endOf('month')
                ],
                'Mes Pasado': [
                    moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month')
                ],
                'Año en Curso': [
                    moment().startOf('year'),
                    moment().endOf('year')
                ],
                'Año Anterior': [
                    moment().subtract(1, 'year').startOf('year'),
                    moment().subtract(1, 'year').endOf('year')
                ],
                'Rango Específico': [
                    moment().subtract(1, 'month').startOf('day'),
                    moment().endOf('day')
                ]
            }
        }, function(start, end) {
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        });
    });
</script>
