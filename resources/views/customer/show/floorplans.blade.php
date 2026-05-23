@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                PLANOS DE LA SEDE </span> <span class="ms-2 fs-4"> {{ $customer->name }}</span>
            </span>
        </div>

        <div class="p-3">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#createFloorplanModal">
                    Agregar plano </button>
            </div>
            <table class="table table-sm table-bordered table-striped mb-3">
                <thead>
                    <tr>
                        <th class="fw-bold">Imagen</th>
                        <th class="fw-bold">Nombre</th>
                        <th class="fw-bold">Servicio</th>
                        <th class="fw-bold">Version mas reciente</th>
                        <th class="fw-bold">Dispositivos</th>
                        <th class="fw-bold"></th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    @forelse ($customer->floorplans as $i => $floorplan)
                        <tr>
                            <td class="align-middle text-center">
                                <img src="{{ route('image.show', ['path' => $floorplan->path]) }}" class="img-fluid"
                                    style="max-height: 80px;">
                            </td>
                            <td class="">
                                {{ $floorplan->filename ? $floorplan->filename : 'Sin Nombre' }}
                            </td>
                            <td class="">
                                {{ $floorplan->service ? $floorplan->service->name : 'Sin servicio' }}
                            </td>
                            <td class="">
                                {{ $floorplan->lastVersion() ?? 'Sin versiones' }}
                            </td>
                            <td class="">
                                <span
                                    class="text-success fw-bold">{{ $floorplan->versions()->exists()
                                        ? $floorplan->devices($floorplan->versions()->latest()->first()->version)->get()->count()
                                        : 0 }}</span>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-secondary"
                                    href="{{ route('floorplan.edit', ['id' => $floorplan->id]) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" data-bs-title="Editar plano">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a class="btn btn-sm btn-primary"
                                    href="{{ route('floorplan.graphic.incidents', ['id' => $floorplan->id]) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" data-bs-title="Ver gráficas">
                                    <i class="bi bi-bar-chart-fill"></i>
                                </a>
                                {{--<a class="btn btn-sm btn-warning"
                                    href="{{ route('floorplan.print', ['id' => $floorplan->id, 'type' => 1]) }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Imprimir plano">
                                    <i class="bi bi-printer-fill"></i>
                                </a>--}}
                                <a class="btn btn-sm btn-dark"
                                    href="{{ route('floorplan.qr', ['id' => $floorplan->id]) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" data-bs-title="QRs">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                                <a class="btn btn-sm btn-danger"
                                    href="{{ route('floorplan.delete', ['id' => $floorplan->id]) }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar plano"
                                    onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </td>
                        </tr>
                    @empty  
                        <tr>
                            <td colspan="6" class="text-center text-danger">Sin planos agregados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>

    @include('floorplans.create')
@endsection
