@extends('layouts.app')
@section('content')
    <div class="container-fluid p-3">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-sm caption-top">
                <caption class="border rounded-top p-2 fw-bold text-dark">
                    @include('dashboard.crm.tracking.search')
                </caption>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Código</th>
                        <th scope="col">Contacto</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Ultimo servicio realizado</th>
                        <th scope="col">Proximo seguimiento</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        @php
                            $last_contract = $customer->contracts->last();
                            $last_order = $customer->ordersPlaced()->get()->last();

                            $services = $last_order
                                ? $last_order->services->pluck('name')->implode(', ') .
                                    ' (' .
                                    $last_order->programmed_date .
                                    ')'
                                : 'Sin servicios';

                            $next_trackings = $customer->trackings
                                ->where('next_date', '>=', now()->toDateString())
                                ->sortBy('next_date')
                                ->values();
                        @endphp
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>
                                <a
                                    href="{{ route('customer.edit', ['id' => $customer->id, 'type' => $customer->service_type_id, 'section' => 1]) }}">({{ $customer->id }})
                                    {{ $customer->name }}</a>
                            </td>
                            <td>{{ $customer->code }}</td>

                            <td> {{ $customer->contact_medium ? $contact_medium[$customer->contact_medium] : '-' }} </td>
                            <td>{{ $customer->serviceType->name }}</td>
                            <td>
                                {{ $services }}
                            </td>
                            <td>
                                {{ $next_trackings->isNotEmpty() ? $next_trackings->first()->service->name . ' (' . $next_trackings->first()->next_date . ')' : 'Sin seguimientos' }}
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('crm.tracking.services', ['customerId' => $customer->id]) }}"
                                        class="btn btn-warning btn-sm">Analiticas</a>
                                    <button type="button" class="btn btn-primary btn-sm"
                                        onclick="openTrackingModal({{ $customer->id }})">
                                        Seguimientos </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $customers->links('pagination::bootstrap-5') }}
    </div>

    @include('dashboard.crm.modals.trackings')

    <script>
        const statusMap = {
            'active': 'Activo',
            'completed': 'Completado',
            'canceled': 'Cancelado'
        };

        function openTrackingModal(customerId) {
            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            formData.append('customer_id', customerId);

            $.ajax({
                url: "{{ route('crm.tracking.customer') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(data) {
                    console.log('Tracking data:', data.trackings);
                    //$('#trackingModal .modal-body').html(data);
                    showTrackings(data.customer, data.services, data.trackings, data.urls);
                    $('#trackingModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching tracking data:', error);
                }
            });
        }

        function showTrackings(customer, services, _trackings, urls) {
            var modalBody = $('#trackingModalBody');
            modalBody.empty(); // Limpiar contenido previo

            // Mostrar información del cliente
            var customerDiv = $('<div class="fw-bold fs-5 mb-4"></div>').text('Cliente: ' + customer.name);
            modalBody.append(customerDiv);

            if (services.length > 0) {
                // Crear contenedor principal del accordion
                var accordion = $('<div class="accordion" id="servicesAccordion"></div>');

                services.forEach(function(service, index) {
                    // Crear elemento del accordion para cada servicio
                    var trackings = _trackings[service.id]['data'];
                    var url = urls[service.id];
                    var accordionItem = $('<div class="accordion-item"></div>');

                    // Header del accordion (título del servicio)
                    var accordionHeader = $(`
                        <h2 class="accordion-header" id="heading${index}">
                            <button class="accordion-button collapsed" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse${index}" 
                                aria-expanded="false" aria-controls="collapse${index}">
                                ${service.name}
                            </button>
                        </h2>
                    `);

                    // Contenido del accordion (solo "hola")
                    var accordionCollapse = $(`
                        <div id="collapse${index}" class="accordion-collapse collapse" 
                            aria-labelledby="heading${index}" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <a href="${url}">
                                        Nuevo seguimiento
                                    </a>
                                </div>
                                ${trackings && trackings.length > 0 ? 
                                    `<ul class="list-group mb-3">
                                            ${ trackings.map(function(tracking) {
                                                var next_date = new Date(tracking.next_date).toISOString().split('T')[0];
                                                var today = new Date().toISOString().split('T')[0];
                                                var range = JSON.parse(tracking.range);
                                                return `
                                            <li class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                                                    <span class="fw-semibold">${tracking.title ?? 'Sin título'}</span>
                                                    <div class="d-flex gap-2">
                                                      <span class="badge ${today > next_date ? 'bg-danger text-white' : (today < next_date ? 'bg-success text-white' : 'bg-warning text-black')} rounded-pill text-nowrap">
                                                        ${new Date(next_date).toLocaleDateString('es-ES')}
                                                      </span>
                                                      <span class="badge bg-${tracking.status == 'active' ? 'success' : (tracking.status == 'completed' ? 'primary' : 'danger')} rounded-pill text-nowrap">
                                                        ${statusMap[tracking.status] || tracking.status}
                                                      </span>
                                                    </div>
                                                </div>
                                                ${range ? `<p class="mb-2">Frecuencia de ${range.frequency} ${range.frequency_type == 'days' ? 'Dias' : (range.frequency_type == 'months' ? 'Semanas' : 'Meses')}</p>` : `<p class="text-danger">No se tiene frecuencia</p>`}
                                                <div class="mb-3">${tracking.description ?? '-'}</div>
                                                <div class="">
                                                    ${range ? 
                                                        `<a href="${_trackings[service.id][tracking.id].url_auto}" class="btn btn-warning btn-sm" onclick="return confirm('La reprogramación se realiza entorno a la frecuencia configurada, ¿Estas seguro de continuar?')"> Reprogramar</a>`
                                                        : ``
                                                    }
                                                    <a href="${_trackings[service.id][tracking.id].url_complete}" class="btn btn-primary btn-sm"> Completar</a>
                                                    <a href="${_trackings[service.id][tracking.id].url_edit}" class="btn btn-secondary btn-sm"> Editar</a>
                                                    <a href="${_trackings[service.id][tracking.id].url_destroy}" class="btn btn-danger btn-sm" onclick="return confirm('¿Estas seguro de eliminarlo?')"> Eliminar</a>
                                                </div>
                                            </li>`;
                                                                                                                                                }).join('') }
                                                                                                                                            </ul>`
                                : `<p class="text-danger fw-bold mb-3"> No hay seguimientos </p>`}
                            </div>
                        </div>
                    `);

                    // Ensamblar el accordion item
                    accordionItem.append(accordionHeader);
                    accordionItem.append(accordionCollapse);

                    // Agregar al accordion principal
                    accordion.append(accordionItem);
                });

                modalBody.append(accordion);
            } else {
                modalBody.append('<div class="alert alert-info">No hay servicios disponibles</div>');
            }
        }
    </script>
@endsection
