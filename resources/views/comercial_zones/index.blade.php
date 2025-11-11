@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="py-3">
            @can('write_user')
                <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createComercialZoneModal">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear zona comercial
                </a>
            @endcan
        </div>

        
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped caption-top">
                <caption class="border rounded-top p-2 text-dark bg-light">
                    {{-- <form action="{{ route('user.search') }}" method="GET">
                        @csrf
                    </form> --}}

                </caption>
                <thead>
                    <tr>
                        <th class="fw-bold" scope="col">#</th>
                        <th class="fw-bold" scope="col">Nombre de la zona comercial</th>
                        <th class="fw-bold" scope="col">Código</th>
                        <th class="fw-bold" scope="col">Clientes asociados</th>
                        <th class="fw-bold" scope="col">Descripción</th>
                        <th class="fw-bold" scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($comercial_zones as $index => $cz)
                        <tr>
                            <th scope="row">{{ ++$index }}</th>
                            <td> {{ $cz->name }} </td>
                            <td class="fw-bold"> {{ $cz->code ?? '-' }} </td>
                            <td>
                                <ul style="margin: 0; padding-left: 20px;">
                                    @foreach ($cz->customers as $customer)
                                        <li>{{ $customer->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td> {{ $cz->description ?? '-' }} </td>
                            <td>
                                <button class="btn btn-secondary btn-sm edit-comercial-zone" data-bs-toggle="modal"
                                    data-bs-target="#editComercialZoneModal" data-id="{{ $cz->id }}"
                                    data-name="{{ $cz->name }}" data-code="{{ $cz->code }}"
                                    data-description="{{ $cz->description }}"
                                    data-customers="{{ $cz->customers->pluck('id') }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        {{ $comercial_zones->links('pagination::bootstrap-5') }}
    </div>


    @include('comercial_zones.modals.create')
    @include('comercial_zones.modals.edit')
@endsection
