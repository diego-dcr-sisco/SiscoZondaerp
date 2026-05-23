<style>
    .dashboard-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dashboard-card:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        z-index: 2;
    }
    :root {
        --prussian-blue: #182a41ff;
        --charcoal: #304054ff;
        --jasper: #c3523eff;
        --marian-blue: #344290ff;
        --resolution-blue: #1d2d83ff;
    }
    .text-prussian { color: var(--prussian-blue); }
    .bg-prussian { background-color: var(--prussian-blue); }
    .border-prussian { border-color: var(--prussian-blue); }
    .text-charcoal { color: var(--charcoal); }
    .text-jasper { color: var(--jasper); }
    .bg-jasper { background-color: var(--jasper); }
    .border-jasper { border-color: var(--jasper); }
    .text-resolution { color: var(--resolution-blue); }
    .bg-resolution { background-color: var(--resolution-blue); }
    .border-resolution { border-color: var(--resolution-blue); }
    .text-marian { color: var(--marian-blue); }
    .bg-marian { background-color: var(--marian-blue); }
    .border-marian { border-color: var(--marian-blue); }
</style>
    
<div class="container-fluid shadow-lg p-3 rounded">
    <!-- Contenido del dashboard -->
    <div class="main-content">
        <!-- Tarjetas de Resumen -->
        <div class="row mb-4">
            <div class="col-lg-3">
                <a href="{{ route('invoices.index') }}" class="text-decoration-none">
                    <div class="card border-jasper h-100 dashboard-card">
                        <div class="card-body">
                            <h5 class="card-title fw-bolder text-jasper">Total Facturado {{ $currentMonth }}</h5>
                            <p class="card-text text-jasper">Total: <span class="fw-bold fs-4">${{ number_format($invoices['monthlyAmount'], 2) }}</span></p>
                            <small class="text-muted">{{ $invoices['total'] }} facturas totales</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3">
                <a href="{{ route('invoices.customers') }}" class="text-decoration-none">
                    <div class="card border-prussian h-100 dashboard-card">
                        <div class="card-body">
                            <h5 class="card-title fw-bolder text-prussian">Contribuyentes</h5>
                            <p class="card-text text-prussian">Total: <span class="fw-bold fs-4">{{ $customers['total'] }}</span></p>
                            <small class="text-success">{{ $customers['facturable'] }} activos</small> | 
                            <small class="text-danger">{{ $customers['moroso'] }} morosos</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3">
                <a href="{{ route('invoices.index', ['status' => 'paid']) }}" class="text-decoration-none">
                    <div class="card border-marian h-100 dashboard-card">
                        <div class="card-body">
                            <h5 class="card-title fw-bolder text-marian">Pagos de {{ $currentMonth }}</h5>
                            <p class="card-text text-marian">Total: <span class="fw-bold fs-4">${{ number_format($payments['total'], 2) }}</span></p>
                            <small class="text-warning">{{ $payments['pending'] }} pagos pendientes</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3">
                <a href="{{ route('invoices.index', ['status' => 'cancelled']) }}" class="text-decoration-none">
                    <div class="card border-resolution h-100 dashboard-card">
                        <div class="card-body">
                            <h5 class="card-title fw-bolder text-resolution">Facturas Canceladas</h5>
                            <p class="card-text text-resolution">Total: <span class="fw-bold fs-4">{{ $invoices['cancelled'] }}</span></p>
                            <small class="text-muted">{{ $invoices['overdue'] }} vencidas</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>  

        <hr class="my-4">
        
        <!-- Calendario y Gráficos en una sola fila -->
        <div class="row mb-4">
            <!-- Calendario a la izquierda -->
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Calendario de Pagos</h5>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
            <!-- Gráficos y datos a la derecha -->
            <div class="col-6">
                <div class="card mb-3">
                    <div class="card-body d-flex flex-column align-items-center justify-content-between">
                        <h5 class="card-title">Ingresos de los Últimos 7 Meses</h5>
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" id="last-payments">
                        <h5 class="card-title">Últimos Pagos Registrados</h5>
                        @if($recentPayments->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($recentPayments as $payment)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>{{ $payment->invoice->customer->name ?? 'Cliente no disponible' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            Factura: {{ $payment->invoice->folio ?? 'N/A' }} - 
                                            {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : 'Fecha no disponible' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-success rounded-pill">
                                        ${{ number_format($payment->amount ?? 0, 2) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center">No hay pagos recientes registrados.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>    
    </div>
</div>
