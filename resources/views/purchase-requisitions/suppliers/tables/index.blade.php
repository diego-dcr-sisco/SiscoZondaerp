<table class="table text-center align-middle table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>RFC</th>
            <th>Teléfono</th>
            <th>Categoria</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($suppliers as $supplier)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->rfc }}</td>
                <td>{{ $supplier->phone }}</td>
                <td>{{ $supplier->category->name }}</td>
                <td>
                    <a href="{{ route('supplier.show', $supplier->id) }}" class="btn btn-info btn-sm">
                        <i class="bi bi-eye-fill"></i> ver
                    </a>
                    <a href="{{ route('supplier.edit', $supplier->id) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                    </a>
                    <form action="{{ route('supplier.destroy', $supplier->id) }}" method="POST"
                        style="display:inline-block;" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                            <i class="bi bi-trash-fill"></i> {{ __('buttons.delete') }}
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <h2 class="text-center">Aún no hay ningún proveedor registrado</h2>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
