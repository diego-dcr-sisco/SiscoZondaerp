@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <span class="text-black fw-bold fs-4">
                ADMINISTRACIÓN DE CONTRIBUYENTES
            </span>
        </div>

        <div class="px-3">
            <div class="py-3">
                <a class="btn btn-primary btn-sm" href="{{ route('invoices.customer.create') }}"
                    onclick="return confirm('TEN A LA MANO LOS SIGUIENTES DOCUMENTOS DEL CLIENTE:\n \n \t RFC. \n \t Nombre Fiscal.\n \t Régimen Fiscal. \n \t Correo Electrónico \n \t Celular. \n \n Toma en cuenta que si es un CLIENTE NUEVO, tiene que ser \u0332R\u0332E\u0332G\u0332I\u0332S\u0332T\u0332R\u0332A\u0332D\u0332O\u0332 \u0332A\u0332N\u0332T\u0332E\u0332R\u0332I\u0332O\u0332R\u0332M\u0332E\u0332N\u0332T\u0332E\u0332 en el módulo de clientes')">
                    <i class="fas fa-plus"></i> Crear Contribuyente
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm caption-top">
                    <caption class="border rounded-top p-2 text-dark bg-light">
                        <form action="{{ route('invoices.customers') }}" method="GET">
                            @csrf
                            <div class="row g-3 mb-0">
                                <div class="col-lg-4 col-12">
                                    <label for="customer_name" class="form-label">Nombre del Cliente</label>
                                    <input type="text" class="form-control form-control-sm" id="customer_name"
                                        name="customer_name" value="{{ request('customer_name') }}"
                                        placeholder="Buscar por nombre del cliente">
                                </div>

                                <div class="col-lg-4 col-12">
                                    <label for="customer_email" class="form-label">Email</label>
                                    <input type="text" class="form-control form-control-sm" id="customer_email"
                                        name="customer_email" value="{{ request('customer_email') }}"
                                        placeholder="Buscar por email">
                                </div>

                                <div class="col-lg-4 col-12">
                                    <label for="customer_status" class="form-label">Estado</label>
                                    <select class="form-select form-select-sm" id="customer_status" name="customer_status">
                                        <option value="">Todos los estados</option>
                                        <option value="1" {{ request('customer_status') == '1' ? 'selected' : '' }}>
                                            Activo</option>
                                        <option value="0" {{ request('customer_status') == '0' ? 'selected' : '' }}>
                                            Inactivo</option>
                                        <option value="2" {{ request('customer_status') == '2' ? 'selected' : '' }}>
                                            Facturable</option>
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
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
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
                            <th scope="col">Nombre</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Persona</th>
                            <th scope="col">Razon social</th>
                            <th scope="col">RFC</th>
                            <th scope="col">Email</th>
                            <th scope="col">Regimen Fiscal</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Limite de credito</th>
                            <th scope="col">Dias de credito</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="align-middle  ">
                        @forelse ($invoice_customers as $index => $ic)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>{{ $ic->name }}</td>
                                <td>{{ $ic->type_name }}</td>
                                <td>{{ $ic->getTaxpayerNameAttribute()}}</td>
                                <td>{{ $ic->social_reason ?? '-' }}</td>
                                <td>{{ $ic->rfc }}</td>
                                <td>{{ $ic->email }}</td>
                                <td>{{ $ic->tax_system }}</td>
                                <td class="{{ $ic->getStatusTextColorAttribute()['color'] }} fw-bold"> {{ $ic->status }} </td>
                                <td> ${{ $ic->credit_limit ?? '0' }} </td>
                                <td> {{ $ic->credit_days ?? '-' }} </td>
                                <td>
                                    <a href="{{  route('invoices.customer.edit', ['id' => $ic->id])  }}" class="btn btn-secondary btn-sm"><i class="bi bi-pencil-square"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                        data-bs-title="Eliminar concepto"><i class="bi bi-trash-fill"></i></button>
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
    {{-- @include('invoices.clients.modals.create') --}}    
    {{-- @include('invoices.clients.modals.edit') --}}


    <script>
        $(document).ready(function() {
            // Función para toggle de secciones
            function toggleSections() {
                const selectedType = $('#type').val();

                // Ocultar todas las secciones primero
                $('.section-toggle').hide();
                $('.client-field, .worker-field').prop('required', false);

                // Mostrar la sección correspondiente y hacer requeridos sus campos
                if (selectedType === 'customer') {
                    $('#clientSection').show();
                    $('.client-field').prop('required', true);
                } else if (selectedType === 'worker') {
                    $('#workerSection').show();
                    $('.worker-field').prop('required', true);
                }
            }

            // Evento cuando cambia el select
            $('#type').change(function() {
                toggleSections();
            });

            // Ejecutar al cargar la página
            toggleSections();

            // También ejecutar cuando se abre el modal (por si acaso)
            $('#createClientModal').on('show.bs.modal', function() {
                toggleSections();
            });

            // Limpiar campos cuando se cierra el modal
            $('#createClientModal').on('hidden.bs.modal', function() {
                // Resetear a cliente por defecto
                $('#type').val('customer');
                toggleSections();

                // Limpiar campos
                $('input, select').val('');
                $('.client-field, .worker-field').prop('required', false);
            });
        });
    </script>
@endsection
