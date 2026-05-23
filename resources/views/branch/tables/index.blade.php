<table class="table table-bordered table-striped table-hover table-sm mb-0 align-middle">
    <caption class="px-3 py-2 text-dark bg-white border-bottom caption-top">
        Lista de sucursales registradas
    </caption>
    <thead>
        <tr class="table-light">
            <th scope="col">#</th>
            <th scope="col">{{ __('branch.data.name') }}</th>
            <th scope="col">{{ __('branch.data.address') }}</th>
            <th scope="col">{{ __('branch.data.phone') }}</th>
            <th scope="col">{{ __('branch.data.city') }}</th>
            <th scope="col">{{ __('branch.data.license_number') }}</th>
            <th scope="col" class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($branches as $index => $branch)
            <tr>
                <th scope="row">{{ ($offset ?? 0) + $index + 1 }}</th>
                <td>{{ $branch->name }}</td>
                <td>{{ $branch->address }}</td>
                <td>{{ $branch->phone ?? 'S/A' }}</td>
                <td>{{ $branch->city }}</td>
                <td>{{ $branch->license_number }}</td>
                <td class="text-center">
                    <a href="{{ route('branch.edit', ['id' => $branch->id, 'section' => 1]) }}"
                        class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Editar sucursal"><i class="bi bi-pencil-square"></i></a>
                    <a href="{{ route('branch.destroy', ['id' => $branch->id]) }}" class="btn btn-danger btn-sm"
                        onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Eliminar sucursal"><i class="bi bi-trash-fill"></i></a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-danger fw-bold py-4">No hay sucursales para mostrar.</td>
            </tr>
        @endforelse
    </tbody>
</table>
