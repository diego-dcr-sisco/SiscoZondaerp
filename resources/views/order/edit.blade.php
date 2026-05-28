@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR ORDEN DE SERVICIO <span class="ms-2 fs-4"> {{ $order->folio }} [{{ $order->id }}]</span>
            </span>
        </div>
        @include('order.edit.form')
        @can('write_order')
            @if ($order->status_id != 6)
                @include('components.danger-action', [
                    'actionRoute' => route('order.destroy', ['id' => $order->id]),
                    'title' => 'Zona de peligro',
                    'description' => 'Cancela esta orden desde su pantalla de edición.',
                    'buttonText' => 'Cancelar orden',
                    'icon' => 'bi-x-lg',
                    'confirmMessage' => __('messages.are_you_sure'),
                ])
            @endif
        @endcan
        @include('order.modals.service')
        @include('order.modals.configure-service')
    </div>

    <script>
        let services_configuration = @json($services_configuration);
        let contract_configurations = [];
        const contain_selected_services = @json($selected_services);
        const new_client_account = false;
        const view = @json($view);
    </script>

    <script src="{{ asset('js/customer.min.js') }}"></script>
    <script src="{{ asset('js/service.min.js') }}"></script>
    <script src="{{ asset('js/technician.min.js') }}"></script>
    <script src="{{ asset('js/order/functions.min.js') }}"></script>
@endsection
