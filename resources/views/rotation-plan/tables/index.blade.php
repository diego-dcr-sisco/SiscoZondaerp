<table class="table table-bordered table-striped text-center align-middle">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Id</th>
            <th scope="col">Nombre</th>
            <th scope="col">No. de revisión</th>
            <th scope="col">Contrato asignado</th>
            <th scope="col">Código</th>
            <th scope="col">Fecha de inicio</th>
            <th scope="col">Fecha de fin</th>
            <th scope="col">Fecha de autorización</th>
            <th scope="col">{{ __('buttons.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rotation_plans as $index => $rotation_plan)
            <tr>
                <th scope="row">{{ ++$index }}</th>
                <th class="text-primary" scope="row">{{ $rotation_plan->id }}</th>
                <td>{{ $rotation_plan->name }}</td>
                <td>{{ $rotation_plan->no_review }}</td>
                <td>Contrato [{{ $rotation_plan->contract->id }}] {{ $rotation_plan->customer->name }}</td>
                <td>{{ $rotation_plan->code }}</td>
                <td>{{ $rotation_plan->contract->startdate }}</td>
                <td>{{ $rotation_plan->contract->enddate }}</td>
                <td class="text-success fw-bold">{{ $rotation_plan->authorizated_at }}</td>
                <td>
                    @can('write_order')
                        <a href="{{ route('rotation.print', ['id' => $rotation_plan->id]) }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-file-pdf-fill"></i> {{ __('buttons.file') }}</a>
                        </a>
                        <a href="{{ route('rotation.edit', ['id' => $rotation_plan->id]) }}"
                            class="btn btn-secondary btn-sm my-1">
                            <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                        </a>
                        <a href="{{ route('rotation.destroy', ['id' => $rotation_plan->id]) }}"
                            class="btn btn-danger btn-sm my-1"
                            onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                            <i class="bi bi-trash-fill"></i> {{ __('buttons.delete') }}
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
