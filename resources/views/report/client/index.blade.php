    @extends('layouts.app')

    @section('content')
    <div class="container">

        <h2 class="mb-4">Reporte de Clientes</h2>

        {{-- FORM FILTROS (solo para visualizar) --}}
        <div class="card p-3 mb-4" id="filterForm">
            <div class="row">

                <div class="col-md-3">
                    <label>Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-3">
                    <label>Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-3">
                    <label>Tipo de cliente</label>
                    <select name="type" class="form-control">
                        <option value="all">Todos</option>

                        <option value="new">Nuevos</option>
                        <option value="recurring">Recurrentes</option>
                    </select>
                </div>

            </div>

            <div class="mt-3">
                <label><strong>Información a incluir en el Excel</strong></label><br>

                {{-- values alineados con Controller y Export --}}
                <label>
                    <input type="checkbox" name="metrics[]" value="inc_orders_count">
                    Cantidad de órdenes de servicio
                </label>

                <br>


                <label class="ml-3">
                    <input type="checkbox" name="metrics[]" value="inc_has_devices">
                    ¿Cuenta con dispositivos?
                </label>

                                <br>


                <label class="ml-3">
                    <input type="checkbox" name="metrics[]" value="inc_devices_count">
                    Cantidad total de dispositivos
                </label>

                <br>

                <label>
                    <input type="checkbox" name="metrics[]" value="inc_device_types">
                    Tipos de dispositivos
                </label>

                <br>


                <label class="ml-3">
                    <input type="checkbox" name="metrics[]" value="inc_pests_count">
                    Cantidad de plagas
                </label>

                <br>


                <label class="ml-3">
                    <input type="checkbox" name="metrics[]" value="inc_pest_types">
                    Tipos de plagas
                </label>
            </div>

                <br>


            <div class="mt-3">
                <button type="button" class="btn btn-success" onclick="submitExport()">
                    Exportar a Excel
                </button>
            </div>
        </div>

        {{-- FORM OCULTO POST para exportar --}}
<form method="POST" action="{{ route('report.client.export') }}" id="exportForm">
    @csrf
</form>

    </div>

    <script>
    function submitExport() {
        const filterDiv  = document.getElementById('filterForm');
        const exportForm = document.getElementById('exportForm');

        // Limpiar form oculto excepto CSRF
        const csrf = exportForm.querySelector('input[name="_token"]');
        exportForm.innerHTML = '';
        exportForm.appendChild(csrf);

        // Copiar inputs de texto/date/select
        filterDiv.querySelectorAll('input[type=date], select').forEach(input => {
            if (!input.name || !input.value) return;
            const h = document.createElement('input');
            h.type  = 'hidden';
            h.name  = input.name;
            h.value = input.value;
            exportForm.appendChild(h);
        });

        // ✅ Solo checkboxes MARCADOS
        filterDiv.querySelectorAll('input[type=checkbox]:checked').forEach(cb => {
            const h = document.createElement('input');
            h.type  = 'hidden';
            h.name  = cb.name;
            h.value = cb.value;
            exportForm.appendChild(h);
        });

        exportForm.submit();
    }
    </script>

    @endsection