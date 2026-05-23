<div class="row mb-3">
    
    <h5 class="fw-bold pb-1 border-bottom">Detalles de solicitud {{ $requisition->status }}</h5>
    <div class="col-6">
        <p class="card-text"><strong>Solicitante:</strong> {{ $requisition->user->name }}</p>
    </div>
    <div class="col-6">
        <p class="card-text"><strong>Empresa Destino:</strong> {{ $requisition->customer->name }}</p>
    </div>

    <div class="col-6">
        <p class="card-text"><strong>Departamento Solicitante:</strong>
            {{ $requisition->user->workDepartment->name }}</p>
    </div>
    <div class="col-6">
        <p class="card-text"><strong>Dirección Empresa Destino:</strong> {{ $requisition->customer->address }}
        </p>
    </div>

    <div class="col-6">
        <p class="card-text"><strong>Fecha de Solicitud:</strong> {{ $requisition->created_at }}</p>
    </div>
    <div class="col-6">
        <p class="card-text"><strong>Fecha a Requerir:</strong> {{ $requisition->request_date }}</p>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <label class="form-label">Observaciones</label>
        <textarea class="form-control">{{ $requisition->observations }}</textarea>
    </div>
</div>

<div class="row mb-3">
    <h5 class="fw-bold pb-1 border-bottom">Productos</h5>
    <div class="col my-3">
        @switch($requisition->status)
            @case('Pendiente')
                @include('purchase-requisitions.purchases.tables.pending')
            @break

            @case('Cotizada')
                @include('purchase-requisitions.purchases.tables.quoted')
            @break

            @case('Aprobada')
            @case('Finalizada')
                @include('purchase-requisitions.purchases.tables.approved')
            @break

            @case('Rechazada')
                @include('purchase-requisitions.purchases.tables.rejected')
            @break
        @endswitch
    </div>
</div>


@if (in_array($requisition->status, ['Pendiente', 'Cotizada']) &&
        in_array(auth()->user()->workDepartment->name, ['Direccion', 'Compras']))
    <a href="{{ route('purchase-requisition.quote', $requisition->id) }}" class="btn btn-warning">
        <i class="bi bi-currency-dollar"></i> {{ __('buttons.quote') }}
    </a>

    @if ($requisition->status == 'Cotizada' && auth()->user()->hasRole('AdministradorDireccion'))
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approvePurchaseModal">
            <i class="bi bi-check-lg"></i> {{ __('buttons.approve') }}
        </button>
    @endif

    <form action="{{ route('purchase-requisition.reject', $requisition->id) }}" method="POST"
        style="display:inline-block;">
        @csrf
        @method('PUT')
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPurchaseModal">
            <i class="bi bi-x-lg"></i> {{ __('buttons.reject') }}
        </button>
    </form>
@endif

{{-- <div class="row text-end">
    <div class="col">
        <a href="{{ route('purchase-requisition.index') }}" class="btn btn-secondary">
            Ver Solicitudes
        </a>
    </div>
</div> --}}

@if ($requisition->status == 'Cotizada')
    @include('purchase-requisitions.modals.approve_purchase_modal')
@endif

@include('purchase-requisitions.modals.reject_purchase_modal')
