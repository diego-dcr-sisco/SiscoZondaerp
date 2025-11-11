@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <!-- <a href="{{ route('order.index') }}" class="text-decoration-none pe-3">
                                                                                        <i class="bi bi-arrow-left fs-4"></i>
                                                                                    </a> -->
            <span class="text-black fw-bold fs-4">
                NOMINA
            </span>
        </div>

        <div class="px-3">
            <div class="py-3">
                <a href="{{ route('payrolls.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Crear Nomina
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm caption-top">
                    <caption class="border rounded-top p-2 text-dark bg-light">

                    </caption>
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Empleado</th>
                            <th>RFC</th>
                            <th>Fecha Pago</th>
                            <th>Tipo Nómina</th>
                            <th>Salario Diario</th>
                            <th>Días Pagados</th>
                            <th>Departamento</th>
                            <th>Puesto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payrolls as $payroll)
                            <tr>
                                <td>N-{{ $payroll->folio }}</td>
                                <td>{{ $payroll->employee_name }}</td>
                                <td>{{ $payroll->employee_rfc }}</td>
                                <td>{{ \Carbon\Carbon::parse($payroll->payment_date)->format('d/m/Y') }}</td>
                                <td class="text-center text-primary fw-bold">
                                    {{ $payroll->getPayrollTypeLabelAttribute() }}
                                </td>
                                <td>${{ number_format($payroll->employee_daily_salary, 2) }}</td>
                                <td>{{ $payroll->days_paid }} días</td>
                                <td>{{ $payroll->department ?? 'N/A' }}</td>
                                <td>{{ $payroll->position ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('payrolls.show', ['id' => $payroll->id]) }}"
                                        class="btn btn-sm btn-info"><i class="bi bi-eye-fill"></i></a>
                                    <a href="{{ route('payrolls.edit', ['id' => $payroll->id]) }}"
                                        class="btn btn-sm btn-secondary"><i class="bi bi-pencil-square"></i></a>
                                    <a href="{{ route('payrolls.stamp', ['id' => $payroll->id]) }}"
                                        class="btn btn-sm btn-success"><i class="bi bi-file-earmark-check-fill"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
