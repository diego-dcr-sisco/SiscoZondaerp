@php $index = 0; @endphp

<table class="table table-sm table-bordered table-striped caption-top">
    <thead>
        <tr>
            <th class="fw-bold" scope="col">#</th>
            <th class="fw-bold" scope="col">Calidad</th>
            <th class="fw-bold" scope="col">Empresa matriz</th>
            <th class="fw-bold" scope="col">Sedes</th>
            <th class="fw-bold" scope="col"></th>
        </tr>
    </thead>
    <tbody id="table-body">
        @foreach ($matrix as $m)
            @foreach ($quality_users as $quality_user)
                @if ($quality_user->id == $m->administrative_id)
                    <tr>
                        <th scope="row">{{ ++$index }}</th>
                        <td> {{ $m->administrative->name ?? 'S/A' }} </td>
                        <td>{{ $m->name }}</td>
                        <td>
                            <ol class="list-group list-group-flush list-group-numbered">
                                @foreach ($m->sedes as $sede)
                                    <li class="list-group-item bg-transparent">{{ $sede->name }}</li>
                                @endforeach
                            </ol>
                        </td>
                        <td>
                            <div class="text-center" role="group" aria-label="Basic example">
                                <a href="{{ route('quality.control.destroy', [$quality_user->id, $m->id]) }}" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar relación"
                                    onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"><i
                                    class="bi bi-trash-fill"></i>
                                </a>
                            </div>   
                            
                        </td>
                    </tr>
                @endif
            @endforeach
        @endforeach
    </tbody>
</table>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        trigger: 'hover',
    }));
</script>