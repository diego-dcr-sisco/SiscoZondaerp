@php
    $offset = ($contracts->currentPage() - 1) * $contracts->perPage();
@endphp

<table class="table table-sm table-bordered table-striped caption-top">
    <thead>
        <tr>
            <th class="fw-bold" scope="col-1">#</th>
            <th class="fw-bold" scope="col"> Fecha de inicio
            </th>
            <th class="fw-bold" scope="col"> Fecha de termino
            </th>
            <th class="fw-bold" scope="col-1"> Técnicos
            </th>
            <th class="fw-bold" scope="col-1"> Estado
            </th>
            <th class="fw-bold" scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($contracts as $index => $contract)
            <tr id="contract-{{ $contract->id }}">
                <th scope="row">{{ $offset + $index + 1 }}</th>
                <td> {{ \Carbon\Carbon::parse($contract->startdate)->format('d/m/Y') }} </td>
                <td> {{ \Carbon\Carbon::parse($contract->enddate)->format('d/m/Y') }} </td>
                <td> 
                    <ul>
                        @foreach ($contract->technicianNames() as $technician)
                            <li>{{ $technician }}</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    <span
                        class="fw-bold {{ $contract->status == 1 ? 'text-success' : ($contract->status == 0 ? 'text-danger' : 'text-warning') }}">
                        {{ $contract->status == 1 ? __('contract.status.active') : ($contract->status == 0 ? __('contract.status.finalized') : __('contract.status.to_finalize')) }}
                    </span>
                </td>
                <td>
                    <div class="text-center" role="group" aria-label="Basic example">
                        <a class="btn btn-info btn-sm"
                            href="{{ route('contract.show', ['id' => $contract->id, 'section' => 1]) }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Ordenes de servicio">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        @can('write_order')
                            <a href="{{ route('contract.edit', ['id' => $contract->id]) }}" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Editar contrato" class="btn btn-secondary btn-sm">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <a class="btn btn-success btn-sm"
                                href="{{ route('contract.renew', ['id' => $contract->id]) }}" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Renovar contrato">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                            <a class="btn btn-dark btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Plan de rotación"
                                href="{{ $contract->hasRotationPlan() ? route('rotation.edit', ['id' => $contract->rotationPlan()->id]) : route('rotation.create', ['contractId' => $contract->id]) }}">
                                <i class="bi bi-arrow-repeat"></i>
                            </a>
                            {{-- <a class="btn btn-warning btn-sm"
                                href="{{ route('quality.opportunity-area', ['id' => $contract->customer->id]) }}">
                                    <i class="bi bi-lightbulb-fill"></i>
                                </a> --}}
                            <a href="{{ route('contract.destroy', ['id' => $contract->id]) }}"
                                class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar contrato"
                                onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        @endcan
                    </div>               
                </td>
            </tr>
        @empty
            <td colspan="7" class="text-center text-danger">No hay contratos por el momento.</td>
        @endforelse
    </tbody>
</table>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

</script>
