@php
    $offset = ($zones->currentPage() - 1) * $zones->perPage();
@endphp

<table class="table table-sm table-bordered table-striped caption-top">
    <thead>
        <tr>
            <th class="fw-bold" scope="col">#</th>
            <th class="fw-bold" scope="col"> Nombre
            </th>
            <th class="fw-bold" scope="col"> Tipo
            </th>
            <th class="fw-bold" scope="col-1"> Area (m²)
            </th>
            <th class="fw-bold" scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($zones as $index => $zone)
            <tr id="zone-{{ $zone->id }}">
                <th scope="row"> {{ $offset + $index + 1 }} </th>
                <td> {{ $zone->name }} </td>

                <td> {{ $zone->zone_type_id ? $zone->zoneType->name : 'No aplica (N/A)' }} </td>
                <td> {{ $zone->m2 }} </td>
                <td>
                    <div class="text-center" role="group" aria-label="Basic example">
                        @can('write_order')
                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Editar área">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-area="{{ $zone }}"
                                    data-bs-target="#areaEditModal" onclick="setInputs(this)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </span>
                            <a href="{{ route('area.destroy', ['id' => $zone->id]) }}" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar área" onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        @endcan

                    </div>                
                </td>
            </tr>
        @empty
            <td colspan="6" class="text-center text-danger" >No hay zonas por el momento.</td>
        @endforelse
    </tbody>
</table>

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
        }))
</script>

