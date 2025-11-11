@extends('layouts.app')
@section('content')
    <div class="row w-100 justify-content-between m-0 h-100">
        @include('crm.schedule.navigation')
        <div class="col-11">
            <div class="container-fluid pt-3">
                <div class="row justify-content-end">
                    <div type="browser" class="col-4 mb-3">
                        @include('user.browser')
                    </div>
                </div>

                @include('messages.alert')
                <div class="table-responsive">
                    @include('crm.schedule.tables.customers')
                </div>
            </div>
        </div>
    </div>

    @include('crm.schedule.modals.tracking')

    <script>
        //const customer_services = @json($customer_services);
        const services = @json($services);
    </script>
@endsection
