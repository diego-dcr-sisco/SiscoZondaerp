@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CONTROL DE SEGUIMIENTOS <span class="ms-2 fs-4"> {{ $order->folio }} [{{ $order->id }}]</span>
            </span>
        </div>
        <form class="m-3" action="{{ route('tracking.handle') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-6 col-12 mb-3">
                    <label for="customer" class="form-label">Cliente</label>
                    <input type="text" class="form-control" value="{{ $order->customer->name }}" disabled>
                    <input type="hidden" id="trackable-input-id" name="trackable_id" value="{{ $order->customer->id }}">
                    <input type="hidden" id="trackable-input-type" name="trackable_type" value="customer">
                </div>
            </div>
            <table class="table table-bordered table-striped table-sm caption-top">
                <caption class="border rounded-top p-2 text-dark bg-light">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#trackingModal"><i class="bi bi-calendar-fill"></i> Programar fecha(s)</button>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Servicio</th>
                        <th scope="col">Frecuencia</th>
                        <th scope="col">Titulo</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Estado</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody id="tracking-table-body">
                </tbody>
            </table>

            <input type="hidden" id="trackings" name="trackings" />
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <button type="submit" class="btn btn-primary" onclick="setTrackings()">Guardar</button>
        </form>
    </div>

    @include('tracking.modals.create')
    @include('tracking.modals.edit')

    <script>
        const orders = @json($orders);
        const services = @json($services);
        var tracking_data = [];
        var edit_i = -1;
        var edit_j = -1;

        $(function() {
            tracking_data = @json($trackings);
            renderTrackings();
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        function renderTrackings() {
            var rows = ``
            var count = 0;
            const tbody = $('#tracking-table-body');
            tbody.empty();
            tracking_data.forEach((tracking, i) => {
                tracking.dates.forEach((d, j) => {
                    rows += `
                    <tr data-tracking-id="${tracking.id}">
                        <td>${++count}</td>
                        <td>${formatToDDMMYYYY(d.date)}</td>
                        <td>${tracking.service_name}</td>
                        <td>${tracking.frequency}</td>
                        <td>${d.title}</td>
                        <td>${d.description}</td>
                        <td class="${handleColorStatus(d.status)} fw-bold">${handleTranslate(d.status)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="editTracking(${i}, ${j})" data-bs-toggle="tooltip" data-bs-placement="top" title="Reprogramar Seguimiento">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-success" onclick="completedTracking(${i}, ${j})" data-bs-toggle="tooltip" data-bs-placement="top" title="Completar Seguimiento">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="canceledTracking(${i}, ${j})" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancelar Seguimiento">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTracking(${i}, ${j})" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar Seguimiento">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </td>
                    </tr>
                `
                })
            });
            tbody.html(rows)

            console.log(tracking_data)
        }

        function setTrackings() {
            $('#trackings').val(
                JSON.stringify(tracking_data)
            );
        }

        function completedTracking(i, j) {
            if (tracking_data[i] && tracking_data[i].dates && tracking_data[i].dates[j]) {
                tracking_data[i].dates[j].status = 'completed';
                renderTrackings();
            }
        }

        function canceledTracking(i, j) {
            if (tracking_data[i] && tracking_data[i].dates && tracking_data[i].dates[j]) {
                tracking_data[i].dates[j].status = 'canceled';
                renderTrackings();
            }
        }
    </script>
@endsection
