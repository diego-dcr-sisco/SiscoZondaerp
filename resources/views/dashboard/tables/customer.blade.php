@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif
    
    <div class="row p-3 border-bottom">
        <a href="{{ route('crm', ['status'=> 1,'page' => 1]) }}" class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
        @if($va == 1)
            <h1 class="col-auto fs-2 fw-bold m-0">Para seguimiento clientes</h1>
        @elseif($va == 2)
            <h1 class="col-auto fs-2 fw-bold m-0"> Por completar clientes</h1>
        @else
            <h1 class="col-auto fs-2 fw-bold m-0">Para seguimiento clientes potenciales</h1>
        @endif
        @if($va != 2)
            <div class="col" style="text-align: right;">
                <a class="btn btn-info" href="{{ route('customersexport.index' ,['va' => $va]) }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i> Exportar
                </a>
            </div>
        @endif
    </div>
    <div class="row m-0 p-3">
        <table class="table text-center table-bordered table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col"> {{ __('customer.data.name') }} </th>
                    <th scope="col"> {{ __('customer.data.phone') }} </th>
                    <th scope="col"> {{ __('customer.data.email') }} </th>
                    <th scope="col"> {{ __('customer.data.customer_type') }}</th>
                    <th scope="col"> {{ __('customer.data.created') }}</th>
                    <th scope="col"> {{ __('buttons.actions') }} </th>
                </tr>
            </thead>
            <tbody>
                @if($customers)
                    @foreach ($customers as $customer)
                        <tr>
                            <th scope="row">{{ $customer->id }}</th>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->serviceType->name }}</td>
                        
                            <td>{{ $customer->created_at }}</td>
                            <td>
                                <a href="{{ route('customer.show', ['id' => $customer->id, 'type' => 1, 'section' => 1]) }}"
                                    class="btn btn-info btn-sm">
                                    <i class="bi bi-eye-fill"></i> {{ __('buttons.show') }}
                                </a>
                                @can('write_customer')
                                    <a href="{{ route('customer.edit', ['id' => $customer->id, 'type' => 1, 'section' => 1]) }}"
                                        class="btn btn-secondary btn-sm">
                                        <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
@endsection