@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <!-- <a href="{{ route('order.index') }}" class="text-decoration-none pe-3">
                                                                <i class="bi bi-arrow-left fs-4"></i>
                                                            </a> -->
            <span class="text-black fw-bold fs-4">
                CONCEPTOS DE FACTURACIÓN
            </span>
        </div>

        <div class="px-3">
            <div class="py-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#createConceptModal">
                    <i class="fas fa-plus"></i> Crear Concepto
                </button>
            </div>

            

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm caption-top">
                    <caption class="border rounded-top p-2 text-dark bg-light">
                        <form action="{{ route('invoices.concepts') }}" method="GET">
                            @csrf
                            <div class="row g-3 mb-0">
                                <div class="col-lg-2 col-12">
                                    <label for="product_key" class="form-label">Clave del producto/servicio</label>
                                    <input type="text" class="form-control form-control-sm" id="product_key"
                                        name="product_key" value="{{ request('product_key') }}"
                                        placeholder="Buscar por la clave ante SAT">
                                </div>

                                <div class="col-lg-2 col-12">
                                    <label for="identificator" class="form-label">Identificador</label>
                                    <input type="text" class="form-control form-control-sm" id="identificator"
                                        name="identificator" value="{{ request('identificator') }}"
                                        placeholder="Identificador de concepto">
                                </div>

                                <div class="col-lg-4 col-12">
                                    <label for="customer_email" class="form-label">Nombre</label>
                                    <input type="text" class="form-control form-control-sm" id="customer"
                                        name="customer" value="{{ request('customer_email') }}"
                                        placeholder="Nombre del concepto">
                                </div>
                                <div class="col-lg-4 col-12">
                                    <label for="unit_code" class="form-label">Clave de unidad</label>
                                    <select class="form-select form-select-sm" id="unit_code" name="unit_code">
                                        <option value="">Todas las claves de unidad</option>
                                        @forelse ($unitCodes as $index => $unit)
                                            <option value="{{ $index }}">{{ $unit }}</option>
                                        @empty
                                            <option value="">Sin unidad</option>
                                        @endforelse
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label for="direction" class="form-label">Orden</label>
                                    <select class="form-select form-select-sm" id="direction" name="direction">
                                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                        </option>
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                        </option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label for="size" class="form-label">Total</label>
                                    <select class="form-select form-select-sm" id="size" name="size">
                                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                    </select>
                                </div>

                                <!-- Botones -->
                                <div class="col-12 d-flex justify-content-end m-0">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>
                                    <a href="{{ route('invoices.customers') }}" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Clave del servicio/producto</th>
                            <th scope="col">Identificador</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Objeto de impuesto</th>
                            <th scope="col">Precio/Monto</th>
                            <th scope="col">Tasa de impuesto</th>
                            <th scope="col">Clave de unidad</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($concepts as $index => $concept)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td> {{ $concept->product_key }} </td>
                                <td> {{ $concept->identification_number }} </td>
                                <td> {{ $concept->name }} </td>
                                <td> {{ $concept->description }} </td>
                                <td> {{ $concept->tax_object ?? ''}} - {{ $concept->tax_object ? $taxObjects[$concept->tax_object] : '-'}} </td>
                                <td> ${{ $concept->amount }} </td>
                                <td> {{ $concept->tax_rate * 100 }}% </td>
                                <td> {{ $concept->unit_name ? $unitCodes[$concept->unit_name] : '-' }} </td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                        data-bs-title="Editar concepto" data-concept="{{ $concept }}" onclick="editConcept(this)"><i class="bi bi-pencil-square"></i></button>
                                    <a href="#!" class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                        data-bs-title="Eliminar concepto"
                                        onclick="return confirm('Estas seguro de ELIMINAR EL CONCEPTO?')"><i
                                            class="bi bi-trash-fill"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-person-circle text-muted fs-1 mb-3"></i>
                                        <h5 class="text-muted">No hay clientes para mostrar</h5>
                                        <p class="text-muted mb-0">No se encontraron Contribuyentes en el sistema.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para crear cliente -->
    @include('invoices.concepts.modals.create')
    @include('invoices.concepts.modals.edit')
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
