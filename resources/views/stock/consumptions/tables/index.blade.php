<table class="table table-hover table-bordered" id="consumption-table">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Planta</th>
            <th>Zona</th>
            <th>Período</th>
            <th class="text-center">Productos</th>
            <th class="text-center">Estado</th>
            <th>Fecha Registro</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($consumptions as $consumption)
            <tr>
                <td>{{ $loop->iteration + ($consumptions->currentPage() - 1) * $consumptions->perPage() }}</td>
                <td>
                    <div class="fw-bold">{{ $consumption->customer->name }}</div>
                    <small class="text-muted">{{ $consumption->customer->address ?? 'Sin dirección' }}</small>
                </td>
                <td>
                    <span class="text-center">{{ $consumption->zone->name ?? 'Sin zona' }}</span>
                </td>
                <td>
                    <div class="fw-bold text-center">{{ $consumption->month_spanish }} {{ $consumption->year }}</div>
                </td>
                <td>
                    <div class="fw-bold text-primary">{{ $consumption->products_count }} productos</div>
                    <small class="text-muted d-block" title="{{ $consumption->products_summary }}">
                        {{ Str::limit($consumption->products_summary, 50) }}
                    </small>
                </td>
                <td>
                    @switch($consumption->status)
                        @case('pending')
                            <span class="badge bg-warning">{{ $consumption->status_formatted }}</span>
                            @break
                        @case('approved')
                            <span class="badge bg-success">{{ $consumption->status_formatted }}</span>
                            @break
                        @case('rejected')
                            <span class="badge bg-danger">{{ $consumption->status_formatted }}</span>
                            @break
                        @default
                            <span class="badge bg-secondary">Sin estado</span>
                    @endswitch
                </td>
                <td>
                    <div>{{ $consumption->created_at->format('d/m/Y') }}</div>
                    <small class="text-muted">{{ $consumption->created_at->format('H:i') }}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <!-- Ver detalles del consumo agrupado -->
                        <a href="{{ route('consumptions.show-grouped', [
                            'customer_id' => $consumption->customer->id,
                            'zone_id' => $consumption->zone->id,
                            'month' => $consumption->month,
                            'year' => $consumption->year
                        ]) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        
                        <!-- Editar el grupo de consumos -->
                        <a href="{{ route('consumptions.edit', $consumption->id) }}" 
                           class="btn btn-sm btn-outline-secondary"
                           title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        
                        <!-- Eliminar grupo completo -->
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger" 
                                onclick="confirmDelete('{{ $consumption->group_key }}')"
                                title="Eliminar consumo">
                            <i class="bi bi-trash"></i>
                        </button>

                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No se encontraron consumos registrados
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $consumptions->links('pagination::bootstrap-5') }}

<script>
function confirmDelete(groupKey) {
    if (confirm('¿Estás seguro de que deseas eliminar este consumo completo? Esta acción eliminará todos los productos asociados.')) {
        // Extraer los parámetros del groupKey
        const parts = groupKey.split('-');
        const customerId = parts[0];
        const zoneId = parts[1];
        const month = parts[2];
        const year = parts[3];
        
        // Crear un formulario para enviar la petición DELETE
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('consumptions.destroy-group') }}';
        form.style.display = 'none';
        
        // Token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Method DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Parámetros
        const customerInput = document.createElement('input');
        customerInput.type = 'hidden';
        customerInput.name = 'customer_id';
        customerInput.value = customerId;
        form.appendChild(customerInput);
        
        const zoneInput = document.createElement('input');
        zoneInput.type = 'hidden';
        zoneInput.name = 'zone_id';
        zoneInput.value = zoneId;
        form.appendChild(zoneInput);
        
        const monthInput = document.createElement('input');
        monthInput.type = 'hidden';
        monthInput.name = 'month';
        monthInput.value = month;
        form.appendChild(monthInput);
        
        const yearInput = document.createElement('input');
        yearInput.type = 'hidden';
        yearInput.name = 'year';
        yearInput.value = year;
        form.appendChild(yearInput);
        
        // Agregar el formulario al body y enviarlo
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
