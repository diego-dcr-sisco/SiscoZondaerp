@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        {{-- Encabezado de Navegación --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 fw-bold text-dark mb-0">Gestión de Tratamientos</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mt-1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('product.index') }}"
                                class="text-decoration-none text-primary">Catálogo</a></li>
                        <li class="breadcrumb-item active text-muted">{{ $product->name }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('product.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Volver al Catálogo
            </a>
        </div>

        <div class="row g-4">
            {{-- Formulario de Registro --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white py-3 rounded-top-3">
                        <h6 class="m-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Tratamiento</h6>
                    </div>
                    <div class="card-body p-4">
                        {{-- Ruta ajustada a tu ProductController --}}
                        <form action="{{ route('product.treatments.store', $product->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-secondary">Nombre del Tratamiento</label>
                                <input type="text" name="name" class="form-control shadow-none"
                                    placeholder="Ej: Fumigación Nocturna" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-secondary">Descripción</label>
                                <textarea name="description" class="form-control shadow-none" rows="3"
                                    placeholder="Detalles del procedimiento..."></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-secondary">Precio del Servicio</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" step="0.01" name="price" class="form-control shadow-none"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                                <i class="fas fa-save me-2"></i> Registrar Tratamiento
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Listado de Tratamientos --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="m-0 fw-bold text-primary text-uppercase small">Tratamientos Registrados</h6>
                    </div>
                    <div class="card-body p-0">
                        @forelse($product->treatments as $treatment)
                            <div class="p-4 border-bottom d-flex justify-content-between align-items-center hover-bg">
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark">{{ $treatment->name }}</h5>
                                    <p class="text-muted small mb-2">{{ $treatment->description ?: 'Sin descripción' }}</p>
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success fw-bold border border-success border-opacity-25">
                                        <i class="fas fa-tag me-1"></i> ${{ number_format($treatment->price, 2) }}
                                    </span>
                                </div>
                                {{-- Botón de eliminar con la ruta de tu controller --}}
                                <form
                                    action="{{ route('product.treatments.destroy', ['id' => $product->id, 'treatmentId' => $treatment->id]) }}"
                                    method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0"
                                        onclick="return confirm('¿Eliminar este tratamiento?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="mb-3"><i class="fas fa-clipboard-list fa-3x text-muted opacity-25"></i></div>
                                <p class="text-muted">No hay tratamientos registrados para este producto.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-bg:hover {
            background-color: #f8f9fa !important;
            transition: background 0.2s;
        }

        .card {
            border-radius: 0.75rem;
        }
    </style>
@endsection