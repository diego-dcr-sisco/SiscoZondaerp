@extends('layouts.app')
@section('content')
    <div class="row m-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('payrolls.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                DETALLE DE NÓMINA
            </span>
            <div class="ms-auto me-4">
                @if ($payroll->status === 'stamped')
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-bell-fill"></i> TIMBRADA
                    </span>
                @elseif($payroll->status === 'active')
                    <span class="badge bg-primary fs-6">
                        <i class="fas fa-edit me-1"></i> ACTIVA
                    </span>
                @else
                    <span class="badge bg-secondary fs-6">
                        <i class="bi bi-bell-slash-fill"></i> NO TIMBRADA
                    </span>
                @endif
            </div>
        </div>

        <div class="container-fluid py-4">
            <!-- Información General -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-1">
                        <div class="card-header bg-primary text-white rounded-top-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Información General de la Nómina
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <strong>Folio:</strong><br>
                                    <span class="text-muted">N-{{ $payroll->folio }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Fecha de Pago:</strong><br>
                                    <span
                                        class="text-muted">{{ \Carbon\Carbon::parse($payroll->payment_date)->format('d/m/Y') }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Tipo de Nómina:</strong><br>
                                    <span class="text-muted">{{ $payroll->payroll_type }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Uso de CFDI:</strong><br>
                                    <span class="text-muted">{{ $payroll->cfdi_use }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Método de Pago:</strong><br>
                                    <span class="text-muted">{{ $payroll->payment_method }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Lugar de Expedición:</strong><br>
                                    <span class="text-muted">{{ $payroll->expedition_place }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Periodo:</strong><br>
                                    <span class="text-muted">{{ $payroll->date_range }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Días Pagados:</strong><br>
                                    <span class="text-muted">{{ $payroll->days_paid }}</span>
                                </div>
                                @if ($payroll->status === 'stamped' && $payroll->stamped_at)
                                    <div class="col-md-3 mb-3">
                                        <strong>Fecha de Timbrado:</strong><br>
                                        <span
                                            class="text-muted">{{ \Carbon\Carbon::parse($payroll->stamped_at)->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                                @if ($payroll->status === 'stamped' && $payroll->uuid)
                                    <div class="col-md-6 mb-3">
                                        <strong>UUID:</strong><br>
                                        <span class="text-muted small">{{ $payroll->uuid }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Empleado -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-1">
                        <div class="card-header bg-info text-white rounded-top-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>Información del Empleado
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <strong>Nombre Completo:</strong><br>
                                    <span class="text-muted">{{ $payroll->employee_name }}</span>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <strong>RFC:</strong><br>
                                    <span class="text-muted">{{ $payroll->employee_rfc }}</span>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <strong>CURP:</strong><br>
                                    <span class="text-muted">{{ $payroll->employee_curp }}</span>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <strong>NSS:</strong><br>
                                    <span class="text-muted">{{ $payroll->employee_nss }}</span>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <strong>Salario Diario:</strong><br>
                                    <span
                                        class="text-muted">${{ number_format($payroll->employee_daily_salary, 2) }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Régimen Fiscal:</strong><br>
                                    <span class="text-muted">{{ $payroll->tax_regime }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Tipo de Contrato:</strong><br>
                                    <span class="text-muted">{{ $payroll->contract_type }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Frecuencia de Pago:</strong><br>
                                    <span class="text-muted">{{ $payroll->frequency_payment }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <strong>Riesgo del Puesto:</strong><br>
                                    <span class="text-muted">{{ $payroll->position_risk }}</span>
                                </div>
                                @if ($payroll->employer_registration)
                                    <div class="col-md-3 mb-3">
                                        <strong>Número de Empleado:</strong><br>
                                        <span class="text-muted">{{ $payroll->employer_registration }}</span>
                                    </div>
                                @endif
                                @if ($payroll->department)
                                    <div class="col-md-3 mb-3">
                                        <strong>Departamento:</strong><br>
                                        <span class="text-muted">{{ $payroll->department }}</span>
                                    </div>
                                @endif
                                @if ($payroll->position)
                                    <div class="col-md-3 mb-3">
                                        <strong>Puesto:</strong><br>
                                        <span class="text-muted">{{ $payroll->position }}</span>
                                    </div>
                                @endif
                                @if ($payroll->start_date_labor_relations)
                                    <div class="col-md-3 mb-3">
                                        <strong>Fecha de Inicio:</strong><br>
                                        <span
                                            class="text-muted">{{ \Carbon\Carbon::parse($payroll->start_date_labor_relations)->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desglose de la Nómina -->
            <div class="row">
                <!-- Percepciones -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-success text-white rounded-top-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-arrow-up me-2"></i>Percepciones
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @php
                                // CORRECCIÓN: Calcular correctamente los totales de percepciones
                                $totalGravado = 0;
                                $totalExento = 0;
                                $totalPercepciones = 0;

                                foreach ($payroll->perceptions as $perception) {
                                    $gravado = floatval($perception->taxed_amount ?? 0);
                                    $exento = floatval($perception->exempt_amount ?? 0);
                                    $total = $gravado + $exento;

                                    $totalGravado += $gravado;
                                    $totalExento += $exento;
                                    $totalPercepciones += $total;
                                }
                            @endphp

                            @if (isset($payroll->perceptions) && count($payroll->perceptions) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="rounded-start">Codigo</th>
                                                <th>Descripción</th>
                                                <th class="text-end">Gravado</th>
                                                <th class="text-end">Exento</th>
                                                <th class="text-end rounded-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($payroll->perceptions as $perception)
                                                @php
                                                    $gravado = floatval($perception['taxed_amount'] ?? 0);
                                                    $exento = floatval($perception['exempt_amount'] ?? 0);
                                                    $total = floatval($perception['total'] ?? 0);
                                                @endphp
                                                <tr>
                                                    <td>{{ $perception->perception_type ?? '' }}</td>
                                                    <td>{{ $perception['description'] ?? '' }}</td>
                                                    <td class="text-end">${{ number_format($gravado, 2) }}</td>
                                                    <td class="text-end">${{ number_format($exento, 2) }}</td>
                                                    <td class="text-end fw-bold">${{ number_format($total, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-group-divider">
                                            <tr class="table-active">
                                                <th colspan="2" class="text-end rounded-start">Totales:</th>
                                                <th class="text-end">${{ number_format($totalGravado, 2) }}</th>
                                                <th class="text-end">${{ number_format($totalExento, 2) }}</th>
                                                <th class="text-end rounded-end">
                                                    ${{ number_format($totalPercepciones, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                                    <p class="text-muted">No hay percepciones registradas</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Deducciones -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-danger text-white rounded-top-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-arrow-down me-2"></i>Deducciones
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $totalDeducciones = 0;
                                foreach ($payroll->deductions as $deduction) {
                                    $totalDeducciones += floatval($deduction->amount ?? 0);
                                }
                            @endphp

                            @if (isset($payroll->deductions) && count($payroll->deductions) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="rounded-start">Codigo</th>
                                                <th>Descripción</th>
                                                <th class="text-end rounded-end">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($payroll->deductions as $deduction)
                                                @php
                                                    $monto = floatval($deduction['amount'] ?? 0);
                                                @endphp
                                                <tr>
                                                    <td>{{ $deduction->code ?? '' }}</td>
                                                    <td>{{ $deduction->description ?? '' }}</td>
                                                    <td class="text-end fw-bold">${{ number_format($monto, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-group-divider">
                                            <tr class="table-active">
                                                <th colspan="2" class="text-end rounded-start">Total:</th>
                                                <th class="text-end rounded-end">
                                                    ${{ number_format($totalDeducciones, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                                    <p class="text-muted">No hay deducciones registradas</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Otros Pagos -->
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-1">
                        <div class="card-header bg-warning text-dark rounded-top-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-money-bill-wave me-2"></i>Otros Pagos
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $totalOtrosPagos = 0;
                                $totalSubsidios = 0;
                                if (isset($payroll->other_payments) && is_array($payroll->other_payments)) {
                                    foreach ($payroll->other_payments as $otherPayment) {
                                        $totalOtrosPagos += floatval($otherPayment['amount'] ?? 0);
                                        $totalSubsidios += floatval($otherPayment['subsidy'] ?? 0);
                                    }
                                }
                            @endphp

                            @if (isset($payroll->other_payments) && count($payroll->other_payments) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="rounded-start">Tipo</th>
                                                <th>Concepto</th>
                                                <th class="text-end">Monto</th>
                                                <th class="text-end rounded-end">Subsidio</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($payroll->other_payments as $otherPayment)
                                                @php
                                                    $monto = floatval($otherPayment['amount'] ?? 0);
                                                    $subsidio = floatval($otherPayment['subsidy'] ?? 0);
                                                @endphp
                                                <tr>
                                                    <td>{{ $otherPayment['type'] ?? '' }}</td>
                                                    <td>{{ $otherPayment['concept'] ?? '' }}</td>
                                                    <td class="text-end">${{ number_format($monto, 2) }}</td>
                                                    <td class="text-end">${{ number_format($subsidio, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-group-divider">
                                            <tr class="table-active">
                                                <th colspan="2" class="text-end rounded-start">Totales:</th>
                                                <th class="text-end">${{ number_format($totalOtrosPagos, 2) }}</th>
                                                <th class="text-end rounded-end">${{ number_format($totalSubsidios, 2) }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                                    <p class="text-muted">No hay otros pagos registrados</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen de Totales -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-light rounded-1">
                        <div class="card-body rounded-1">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3">
                                    <h6 class="text-muted">Total Percepciones</h6>
                                    <h4 class="text-success">${{ number_format($totalPercepciones, 2) }}</h4>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6 class="text-muted">Total Deducciones</h6>
                                    <h4 class="text-danger">${{ number_format($totalDeducciones, 2) }}</h4>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6 class="text-muted">Total Otros Pagos</h6>
                                    <h4 class="text-info">${{ number_format($totalOtrosPagos, 2) }}</h4>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6 class="text-muted">Total Neto</h6>
                                    <h4 class="text-primary">
                                        ${{ number_format($totalPercepciones - $totalDeducciones + $totalOtrosPagos, 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="row mt-4">
                <div class="col-12 d-flex justify-content-end">
                    <a href="{{ route('payrolls.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>

                    @if ($payroll->status !== 'stamped')
                        <a href="{{ route('payrolls.edit', $payroll->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-1"></i> Editar
                        </a>
                        <button type="button" class="btn btn-success me-2" id="btnStampPayroll"
                            data-payroll-id="{{ $payroll->id }}">
                            <i class="fas fa-stamp me-1"></i> Timbrar Nómina
                        </button>
                        <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Imprimir
                        </button>
                    @else
                        <a href="{{ route('payrolls.download.pdf', $payroll->id) }}" class="btn btn-danger me-2">
                            <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                        </a>
                        <a href="{{ route('payrolls.download.xml', $payroll->id) }}" class="btn btn-info me-2">
                            <i class="fas fa-file-code me-1"></i> Descargar XML
                        </a>
                        <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Imprimir
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Timbrar -->
    @if ($payroll->status !== 'stamped')
        <div class="modal fade" id="stampConfirmModal" tabindex="-1" aria-labelledby="stampConfirmModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="stampConfirmModalLabel">Confirmar Timbrado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas timbrar esta nómina?</p>
                        <div class="alert alert-warning rounded-1">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Advertencia:</strong> Una vez timbrada, la nómina no podrá ser editada.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="confirmStamp">
                            <i class="fas fa-stamp me-1"></i> Sí, Timbrar Nómina
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        @media print {

            .btn,
            .d-flex.align-items-center.border-bottom {
                display: none !important;
            }
        }
    </style>

    <script>
        $(document).ready(function() {
            $('#btnStampPayroll').on('click', function() {
                $('#stampConfirmModal').modal('show');
            });

            $('#confirmStamp').on('click', function() {
                const payrollId = $('#btnStampPayroll').data('payroll-id');
                const button = $(this);

                button.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Timbrando...');

                $.ajax({
                    url: '{{ route('payrolls.stamp', ':id') }}'.replace(':id', payrollId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Nómina timbrada correctamente');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Error al timbrar la nómina');
                            button.prop('disabled', false).html(
                                '<i class="fas fa-stamp me-1"></i> Sí, Timbrar Nómina');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error al timbrar la nómina');
                        button.prop('disabled', false).html(
                            '<i class="fas fa-stamp me-1"></i> Sí, Timbrar Nómina');
                    }
                });
            });
        });
    </script>
@endsection
