@extends('layouts.app')

@section('content')
    <div class="row h-100 w-100 m-0 p-0">
        @include('dashboard.stock.navigation')
        <div class="col-11">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2 p-0">
                <div>
                    <h1 class="h3 mb-0">
                        <a href="{{ route('comercial-zones.index') }}" class="col-auto btn-primary p-0 fs-3"><i
                            class="bi bi-arrow-left m-3"></i></a>
                        Editar Zona
                    </h1>
                </div>
            </div>

            

            <!-- Formulario -->
            <div class="row justify-content-center w-100">
                <div class="col">
                    <div class="card">
                        <div class="card-header bg-light text-dark">
                            <h5 class="mb-0">
                                <i class="bi bi-form"></i> Editar Información de la Zona
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('customer-zones.update', $customerZone->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <!-- Cliente -->
                                    <div class="col-lg-6 mb-3">
                                        <label for="customer_id" class="form-label">
                                            <i class="bi bi-building"></i> Cliente *
                                        </label>
                                        <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                            <option value="">Seleccionar cliente...</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" 
                                                        {{ (old('customer_id', $customerZone->customer_id) == $customer->id) ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Zona -->
                                    <div class="col-lg-3 mb-3">
                                        <label for="zone_id" class="form-label" required>
                                            <i class="bi bi-geo-alt"></i> Zona 
                                        </label>
                                        <select name="zone_id" id="zone_id" class="form-select @error('zone_id') is-invalid @enderror" required>
                                            <option value="">Seleccionar zona...</option>
                                            @foreach($zones as $zone)
                                                <option value="{{ $zone->id }}" {{ (old('zone_id', $customerZone->zone_id) == $zone->id) ? 'selected' : '' }}>
                                                    {{ $zone->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('zone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Estado -->
                                    <div class="col-lg-3 mb-3">
                                        <label for="status" class="form-label">
                                            <i class="bi bi-toggle2-on"></i> Estado
                                        </label>
                                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="active" {{ old('status', $customerZone->status) == 'active' ? 'selected' : '' }}>Activo</option>
                                            <option value="inactive" {{ old('status', $customerZone->status) == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Observaciones -->
                                <div class="mb-3">
                                    <label for="observation" class="form-label">
                                        <i class="bi bi-chat-text"></i> Observaciones
                                    </label>
                                    <textarea name="observation" id="observation" 
                                              class="form-control @error('observation') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Notas adicionales sobre la zona...">{{ old('observation', $customerZone->observation) }}</textarea>
                                    @error('observation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Información adicional -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i>
                                            <strong>Creado:</strong> {{ $customerZone->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="alert alert-info">
                                            <i class="bi bi-clock"></i>
                                            <strong>Última actualización:</strong> {{ $customerZone->updated_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('comercial-zones.index') }}" class="btn btn-danger" onclick="return confirm('¿Está seguro que desea cancelar? Los cambios no guardados se perderán.')">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Actualizar Zona
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
.form-label {
    font-weight: 600;
    color: #495057;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.alert {
    border: none;
    border-radius: 0.5rem;
}
</style>

@endsection