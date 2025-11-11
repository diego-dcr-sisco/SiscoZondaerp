@extends('layouts.app')

@section('content')
    <div class="row w-100 h-100 m-0">

        @include('crm.navigation')

        <div class="col-11 p-3 m-0">
            <div class="row">
                <div class="d-flex justify-content-start mb-4">
                    <div>
                        <a class="btn btn-primary" href="{{ route('customer.create', ['id' => 0, 'type' => 0]) }}">
                            <i class="bi bi-plus-lg fw-bold"></i>
                            {{ __('customer.title.create_lead') }}
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('leads.import.form') }}" class="btn btn-success mx-3">
                            <i class="bi bi-upload"></i> Importar Leads
                        </a>
                    </div>
                </div>

                

                <div class="table-responsive" style="font-size: 85%">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ver</th>
                                <th>Nombre</th>
                                <th>Razón</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                {{-- <th>Fecha Creación</th> --}}
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leads as $lead)
                                <tr>
                                    <td>{{ $lead->id }}</td>
                                    <td style="justify-content: center; align-items: center;">
                                        <a href="{{ route('customer.show', ['id' => $lead->id, 'type' => 0, 'section' => 1]) }}"
                                            class="btn btn-info btn-sm">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                    </td>
                                    <td>{{ $lead->name }}</td>
                                    <td>{{ $lead->reason }}</td>
                                    <td>{{ $lead->phone }}</td>
                                    <td>{{ $lead->email }}</td>
                                    <td>{{ $lead->serviceType->name ?? 'N/A' }}</td>
                                    <td>{{ $lead->state }}</td>
                                    {{-- <td>{{ $lead->created_at->format('d/m/Y H:i') }}</td> --}}
                                    <td>
                                        @can('write_customer')
                                            <a href="{{ route('customer.edit', ['id' => $lead->id, 'type' => 0, 'section' => 1]) }}"
                                                class="btn btn-secondary btn-sm">
                                                <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                                            </a>
                                            <a href="{{ route('customer.convert', ['id' => $lead->id]) }}"
                                                class="btn btn-success btn-sm"
                                                onclick="return confirm('{{ __('messages.lead_to_customer') }}')">
                                                <i class="bi bi-person-fill-add"></i> convertir
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $leads->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
@endsection
