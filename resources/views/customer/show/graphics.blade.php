@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.index.sedes') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                GRAFICAS DE LA SEDE </span> <span class="ms-2 fs-4"> {{ $customer->name }}</span>
            </span>
        </div>

        <div class="p-3">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="fw-bold" scope="col">#</th>
                            <th class="fw-bold" scope="col">Area</th>
                            <th class="fw-bold" scope="col">Dispositivo</th>
                            @foreach ($pests_headers as $pest_header)
                                <th class="fw-bold" scope="col">{{ $pest_header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $d)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $d['area_name'] }}</td>
                                <td>{{ $d['device_name'] }}</td>
                                @foreach ($pests_headers as $pest_header)
                                    <td>{{ $d['pest_total_detections'][$pest_header] }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
        }))
    </script>
@endsection
