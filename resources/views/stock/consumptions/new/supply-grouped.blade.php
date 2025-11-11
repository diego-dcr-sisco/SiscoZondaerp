@extends('layouts.app')

@section('content')
<div class="row w-100 h-100 m-0">
    @include('dashboard.stock.navigation')

    <div class="col-11 p-3 m-0">
        
        
        <div class="row mb-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <a href="{{ route('consumptions.show-grouped', [
                        'customer_id' => $groupedConsumption->customer->id,
                        'zone_id' => $groupedConsumption->zone->id,
                        'month' => $groupedConsumption->month,
                        'year' => $groupedConsumption->year
                    ]) }}" class="col-auto btn-primary p-0 fs-3">
                        <i class="bi bi-arrow-left m-3"></i>
                    </a>
                    <div>
                        <h1 class="h3 mb-0">Surtir Productos del Consumo</h1>
                        <small class="text-muted">{{ $groupedConsumption->customer->name }} - {{ $groupedConsumption->period_formatted }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Consumo -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold">Cliente:</td>
                                        <td>{{ $groupedConsumption->customer->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Zona:</td>
                                        <td>{{ $groupedConsumption->zone->name ?? 'Sin zona' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Período:</td>
                                        <td>{{ $groupedConsumption->period_formatted }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Total de productos:</td>
                                        <td>{{ $groupedConsumption->products_count }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <h5 class="mb-3">Observaciones:</h5>
                                <p class="mb-0">{{ $groupedConsumption->observation ?? 'Sin observaciones' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Surtido -->
        <form method="POST" action="{{ route('consumptions.update-supply') }}">
            @csrf
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Surtir Productos</h5>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad Solicitada</th>
                                    <th class="text-center">¿Surtido?</th>
                                    <th class="text-center">Cantidad Surtida</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedConsumption->products as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $product->product->name }}</div>
                                        <small class="text-muted">{{ $product->units }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-primary">{{ $product->amount }}</span>
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="supplies[{{ $loop->index }}][consumption_id]" value="{{ $product->id }}">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input supply-checkbox" 
                                                   type="checkbox" 
                                                   name="supplies[{{ $loop->index }}][is_supplied]" 
                                                   value="1"
                                                   id="supplied_{{ $product->id }}"
                                                   {{ $product->supply && $product->supply->is_supplied ? 'checked' : '' }}
                                                   data-index="{{ $loop->index }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               class="form-control text-center supply-amount" 
                                               name="supplies[{{ $loop->index }}][supplied_amount]" 
                                               step="0.01" 
                                               min="0" 
                                               max="{{ $product->amount }}"
                                               value="{{ $product->supply ? $product->supply->supplied_amount : 0 }}"
                                               data-index="{{ $loop->index }}"
                                               style="width: 120px; margin: 0 auto;"
                                               {{ $product->supply && $product->supply->is_supplied ? '' : 'disabled' }}>
                                    </td>
                                    <td>
                                        <textarea class="form-control supply-notes" 
                                                  name="supplies[{{ $loop->index }}][supply_notes]" 
                                                  rows="2" 
                                                  placeholder="Notas sobre el surtido..."
                                                  data-index="{{ $loop->index }}"
                                                  {{ $product->supply && $product->supply->is_supplied ? '' : 'disabled' }}>{{ $product->supply ? $product->supply->supply_notes : '' }}</textarea>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-6">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Marque la casilla "¿Surtido?" para habilitar los campos de cantidad y notas.
                            </small>
                        </div>
                        <div class="col-lg-6 text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar el cambio en los checkboxes de surtido
    document.querySelectorAll('.supply-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const index = this.dataset.index;
            const amountInput = document.querySelector(`input[name="supplies[${index}][supplied_amount]"]`);
            const notesTextarea = document.querySelector(`textarea[name="supplies[${index}][supply_notes]"]`);
            
            if (this.checked) {
                amountInput.disabled = false;
                notesTextarea.disabled = false;
                amountInput.focus();
            } else {
                amountInput.disabled = true;
                amountInput.value = 0;
                notesTextarea.disabled = true;
                notesTextarea.value = '';
            }
        });
    });
    
    // Validar que la cantidad surtida no exceda la solicitada
    document.querySelectorAll('.supply-amount').forEach(function(input) {
        input.addEventListener('input', function() {
            const max = parseFloat(this.max);
            const value = parseFloat(this.value);
            
            if (value > max) {
                this.value = max;
                alert('La cantidad surtida no puede ser mayor a la cantidad solicitada (' + max + ')');
            }
        });
    });
});
</script>

<style>
.supply-checkbox {
    transform: scale(1.2);
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.supply-amount:disabled,
.supply-notes:disabled {
    background-color: #f8f9fa;
    opacity: 0.6;
}
</style>
@endsection 