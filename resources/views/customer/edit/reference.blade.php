<div class="col-12 mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#referenceModal"
        onclick="resetForm()"> Crear
        referencia</button>
</div>
<div class="table-responsive">
    <table class="table text-center table-bordered table-striped">
        <thead>
            <tr>
                <th scope="col-1">#</th>
                <th scope="col-1">ID</th>
                <th scope="col-2">Nombre</th>
                <th scope="col-2">Tipo de referencia</th>
                <th scope="col-2">Correo electrónico</th>
                <th scope="col-2">Teléfono</th>
                <th scope="col-1">Departamento</th>
                <th scope="col-2">{{ __('buttons.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customer->references as $index => $reference)
                <tr>
                    <th scope="row">{{ $index + 1 }}</th>
                    <td>{{ $reference->id }}</td>
                    <td class="align-middle">{{ $reference->name }}</td>
                    <td class="align-middle">{{ $reference->referenceType->name }}</td>
                    <td class="align-middle">{{ $reference->email }}</td>
                    <td class="align-middle">{{ $reference->phone }}</td>
                    <td class="align-middle">{{ $reference->address }}</td>
                    <td class="align-middle">
                        <button type="button" class="btn btn-secondary btn-sm" data-reference="{{ $reference }}"
                            data-bs-toggle="modal" data-bs-target="#referenceModal" onclick="setData(this)">
                            <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                        </button>
                        <a href="{{ route('customer.reference.destroy', ['id' => $reference->id]) }}"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"><i
                                class="bi bi-trash-fill"></i>
                            {{ __('buttons.delete') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-danger fw-bold" colspan="7">Sin referencias</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
