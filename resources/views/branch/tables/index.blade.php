<table class="table table-bordered table-striped table-sm">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">{{ __('branch.data.name') }}</th>
            <th scope="col">{{ __('branch.data.address') }}</th>
            <th scope="col">{{ __('branch.data.phone') }}</th>
            <th scope="col">{{ __('branch.data.city') }}</th>
            <th scope="col">{{ __('branch.data.license_number') }}</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($branches as $index => $branch)
            <tr>
                <th scope="row">{{ ++$index }}</th>
                <td>{{ $branch->name }}</td>
                <td>{{ $branch->address }}</td>
                <td>{{ $branch->phone }}</td>
                <td>{{ $branch->city }}</td>
                <td>{{ $branch->license_number }}</td>
                <td>
                    <a href="{{ route('branch.edit', ['id' => $branch->id, 'section' => 1]) }}"
                        class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Editar sucursal"><i class="bi bi-pencil-square"></i></a>
                    <a href="{{ route('branch.destroy', ['id' => $branch->id]) }}" class="btn btn-danger btn-sm"
                        onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Eliminar sucursal"><i class="bi bi-trash-fill"></i></a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
