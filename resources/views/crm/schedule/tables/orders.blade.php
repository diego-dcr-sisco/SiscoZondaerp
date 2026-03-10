@extends('layouts.app')
@section('content')

@php
    $offset = ($orders->currentPage() - 1) * $orders->perPage();
@endphp

    <div class="row w-100 justify-content-between m-0 h-100">
        @include('crm.schedule.navigation')
        <div class="col-11">
            <div class="container-fluid pt-3">
                <div class="row justify-content-end">
                    <div type="browser" class="col-4 mb-3">
                    </div>
                </div>

                @include('messages.alert')
                <div class="table-responsive">
                    <table class="table text-center table-bordered table-striped">
                        <thead>
                            <tr>
                                <th scope="col-1" class="align-middle">#</th>
                                <th scope="col-1" class="align-middle">ID</th>
                                <th scope="col-2" class="align-middle">Hora y fecha programada</th>
                                <th scope="col-2" class="align-middle">Fecha realizada</th>
                                <th scope="col-2" class="align-middle">Encargado</th>
                                <th scope="col-2" class="align-middle">
                                    Cliente
                                    <div class="input-group input-group-sm">
                                        <input type="hidden" id="search-customer-url"
                                            value="{{ route('ajax.quality.search.customer') }}">
                                        <input type="text" class="form-control" id="search-customer"
                                            placeholder="Filtrar cliente" aria-label="Recipient's username"
                                            aria-describedby="button-addon2" name="search">
                                        <button class="btn btn-primary" type="button" id="button-addon2"
                                            onclick="getOrdersByCustomer()"><i class="bi bi-search"></i></button>
                                    </div>
                                </th>
                                <th scope="col-2" class="align-middle">Técnico</th>
                                <th scope="col-1" class="align-middle">Status</th>
                                <th scope="col-2" class="align-middle">{{ __('buttons.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            @foreach ($orders as $index => $order)
                                <tr id="order-{{ $order->id }}">
                                    <th scope="row"> {{ $offset + $index + 1 }} </th>
                                    <td class="align-middle">{{ $order->id }}</td>
                                    <td class="align-middle"> {{ $order->start_time }} {{ $order->programmed_date }}</td>
                                    <td class="align-middle {{ empty($order->completed_date) ? 'text-danger' : '' }}">
                                        {{ empty($order->completed_date) ? 'S/N' : $order->completed_date }}</td>
                                    <td class="align-middle">
                                        {{ $order->customer->user->name ?? 'S/A' }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $order->customer->name }}
                                    </td>
                                    <td class="align-middle">
                                        @foreach ($order->technicians as $technician)
                                            <div class="col-12">{{ $technician->user->name }}</div>
                                        @endforeach
                                    </td>
                                    <td
                                        class="fw-bold align-middle
                                            @if ($order->status_id == 1) text-warning
                                            @elseif ($order->status_id == 2 || $order->status_id == 3) text-primary
                                            @elseif ($order->status_id == 4 || $order->status_id == 5) text-success
                                            @else text-danger @endif 
                                        ">
                                        {{ $order->status->name }}
                                    </td>
                                    <td class="align-middle">
                                        <a class="btn btn-info btn-sm"
                                            href="{{ route('order.show', ['id' => $order->id, 'section' => 1]) }}">
                                            <i class="bi bi-eye-fill"></i> {{ __('buttons.show') }}
                                        </a>
                                        @can('write_order')
                                            <a class="btn btn-secondary btn-sm"
                                                href="{{ route('order.edit', ['id' => $order->id]) }}">
                                                <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                                            </a>
                                        @endcan
                                        @if ($status == 3)
                                            <a class="btn btn-warning btn-sm"
                                                href="{{ route('report.review', ['id' => $order->id]) }}">
                                                <i class="bi bi-file-pdf-fill"></i> Reporte
                                            </a>
                                        @endif
                                        @if ($status == 5)
                                            {{-- <a class="btn btn-dark btn-sm"
                                                    href="{{ route('order.report.create', ['id' => $order->id]) }}">
                                                    <i class="bi bi-download"></i> Descargar
                                                </a> --}}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <script>
        const orders = @json($orders);

        function getOrdersByCustomer() {
            const formData = new FormData();
            const csrfToken = $('meta[name="csrf-token"]').attr("content");
            const search = $("#search-customer").val();
            var url = $("#search-customer-url").val();
            var html = '';

            formData.append("search", search);

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    const ordersFound = response.orders;
                    console.log(ordersFound);
                    if (ordersFound.length > 0) {
                        orders.forEach(order => {
                            if (ordersFound.indexOf(order.id) == -1) {
                                $('#order-' + order.id).hide();
                            } else {
                                if ($('#order-' + order.id).is(':hidden')) {
                                    $('#order-' + order.id).show()
                                }
                            }
                        });
                    } else {
                        orders.forEach(order => {
                            if ($('#order-' + order.id).is(':hidden')) {
                                $('#order-' + order.id).show()
                            }
                        });
                    }
                },
                error: function(error) {
                    //console.error(error);
                },
            });
        }
    </script>
@endsection
