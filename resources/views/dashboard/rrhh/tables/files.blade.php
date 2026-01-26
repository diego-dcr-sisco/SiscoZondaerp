<table class="table text-center table-bordered table-striped">
    <thead>
        <tr>
            <th scope="col">Nombre del archivo</th>
            @if ($section == 3)
                <th scope="col">Fecha de expiración</th>
            @endif
            <th scope="col">Usuario</th>
            <th scope="col">Rol</th>
            <th scope="col">Departamento</th>
            <th scope="col">{{ __('buttons.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($files as $file)
            <tr>
                <td class="align-middle">{{ $file->filename->name }}</td>
                @if ($section == 3)
                    <td class="align-middle text-danger fw-bold">{{ $file->expirated_at }}</td>
                @endif
                <td class="align-middle">
                    {{ $file->user->name }}
                </td>
                <td class="align-middle">{{ $file->user->simpleRole->name }}</td>
                <td class="align-middle">{{ $file->user->workDepartment->name }}</td>
                <td class="align-middle">
                    <a href="{{ route('user.edit', ['id' => $file->user_id, 'section' => 3]) }}"
                        class="btn btn-secondary btn-sm">
                        <i class="bi bi-pencil-square"></i> Editar
                    </a>
                </td>
            </tr>
        @endforeach

    </tbody>
</table>
