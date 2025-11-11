@php
    use Carbon\Carbon;
    function hasPassedWeek($trackingDate)
    {
        if ($trackingDate) {
            $trackingDate = Carbon::parse($trackingDate);
            $now = Carbon::now();
            return $trackingDate->diffInDays($now) >= 7;
        }
        return false;
    }

@endphp
<table class="table table-bordered table-striped text-center">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">ID</th>
            <th scope="col"> {{ __('customer.data.name') }} </th>
            <th scope="col"> {{ __('customer.data.phone') }} </th>
            <th scope="col"> {{ __('customer.data.email') }} </th>
            @if ($type != 0)
                <th scope="col"> {{ __('customer.data.type') }}</th>
                <th scope="col">Ultimo servicio</th>
            @else
                <th class="col-3" scope="col">{{ __('customer.data.reason') }}</th>
            @endif
            <th scope="col">Ultimo seguimiento</th>
            <th scope="col"> {{ __('buttons.actions') }} </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($customers as $index => $customer)
            @php
                $last_tracking = $customer->trackings()->get()->last();
            @endphp
            <tr>
                <th scope="row">{{ ++$index }}</th>
                <td>{{ $customer->id }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->phone }}</td>
                <td>{{ $customer->email ?? 'S/A' }}</td>
                @if ($type != 0)
                    @php
                        $last_order = $customer->ordersPlaced()->first();
                    @endphp
                    <td>{{ $customer->serviceType->name }}</td>
                    <td>{{ $last_order && $last_order->completed_date ? $last_order->completed_date : 'S/A' }}</td>
                @else
                    <td>{{ $customer->reason ?? 'S/A' }}</td>
                @endif

                @if ($last_tracking && $last_tracking->tracking_date)
                    <td
                        class="fw-bold {{ hasPassedWeek($last_tracking->tracking_date) ? 'text-warning' : 'text-success' }}">
                        ({{ $last_tracking->tracking_date }}) {{ $last_tracking->service->name }}</td>
                @else
                    <td>S/A</td>
                @endif
                <td>
                    <a href="{{ route('customer.show', ['id' => $customer->id, 'type' => $type, 'section' => 1]) }}"
                        class="btn btn-info btn-sm mb-1">
                        <i class="bi bi-eye-fill"></i> {{ __('buttons.show') }}
                    </a>
                    @can('write_customer')
                        <a href="{{ route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 1]) }}"
                            class="btn btn-secondary btn-sm mb-1">
                            <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                        </a>

                        <button class="btn btn-primary btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#trackingModal"
                            data-customer="{{ $customer }}" onclick="setCustomer(this)">
                            <i class="bi bi-hand-index-fill"></i> {{ __('buttons.tracking') }}
                        </button>

                        @if ($type == 0)
                            <a href="{{ route('customer.convert', ['id' => $customer->id]) }}"
                                class="btn btn-success btn-sm mb-1"
                                onclick="return confirm('{{ __('messages.lead_to_customer') }}')">
                                <i class="bi bi-person-fill-add"></i> {{ __('buttons.convert') }}
                            </a>
                        @endif
                    @endcan
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
