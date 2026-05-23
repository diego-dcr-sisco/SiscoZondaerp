<table class="table table-bordered table-striped text-center">
    <form action="{{ route('purchase-requisition.index') }}" method="GET">
        <thead>
            <tr>
                <th>#</th>
                <th>Folio</th>
                <th>Departamento solicitante</th>
                <th>Empresa destino</th>
                <th>Dirección destino</th>
                <th>
                    Fecha de Solicitud
                    <div class="input-group input-group-sm">
                        <input type="text" id="date-range" name="date_range" class="form-control">
                        <button type="button" class="btn btn-primary sm" onclick="searchByDateRange()">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </th>
                <th>
                    Estado
                    <select id="state-filter" name="status" class="form-control form-select"
                        onchange="searchByState(this.value)">
                        @foreach ($states as $state)
                            <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>
                                {{ $state }}</option>
                        @endforeach
                    </select>
                </th>
                <th>{{ __('buttons.actions') }}</th>
            </tr>
        </thead>
    </form>
    <tbody>
        @foreach ($purchaseRequisitions as $requisition)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $requisition->folio }}</td>
                <td>{{ $requisition->user->workDepartment->name }}</td>
                <td>{{ $requisition->customer->name }}</td>
                <td>{{ $requisition->customer->address }}</td>
                <td>{{ $requisition->created_at->format('d-m-Y') }}</td>
                <td>{{ $requisition->status }}</td>
                <td>
                    <a href="{{ route('purchase-requisition.show', $requisition->id) }}" class="btn btn-info btn-sm">
                        <i class="bi bi-eye-fill"></i> Ver
                    </a>

                    {{-- <a href="{{ route('purchase-requisition.edit', $requisition->id) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>  --}}
                    <form action="{{ route('purchase-requisition.destroy', $requisition->id) }}" method="POST"
                        style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('¿Estás seguro de que deseas eliminar esta solicitud de compra?')">
                            <i class="bi bi-trash-fill"></i> Eliminar
                        </button>
                    </form>
                    @if ($requisition->state == 'Pendiente')
                        <a href="{{ route('purchase-requisition.quote', $requisition->id) }}"
                            class="btn btn-warning btn-sm">
                            <i class="bi bi-currency-dollar"></i> Cotizar
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="text-end mt-3">
    <a href="{{ route('purchase-requisition.export', request()->query()) }}" class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-excel-fill"></i> Exportar a Excel
    </a>
</div>

<script>
    $(function() {
        $('#date-range').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY'
            },
            ranges: {
                'Hoy': [moment(), moment()],
                'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este año': [moment().startOf('year'), moment().endOf('year')],
            },
            alwaysShowCalendars: true,
            autoUpdateInput: false,
        });

        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                'DD/MM/YYYY'));
        });
    });

    function searchByState(state) {
        console.log(state);

        const params = new URLSearchParams({
            state: state
        });

        window.location.href = `?${params.toString()}`;
        console.log(window.location.href);
    }

    function searchByDateRange() {
        const dateRange = document.getElementById('date-range').value;

        const params = new URLSearchParams({
            date_range: dateRange
        });

        window.location.href = `?${params.toString()}`;
    }
</script>