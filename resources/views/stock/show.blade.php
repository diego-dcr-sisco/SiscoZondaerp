@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif



    <div class="container-fluid py-4">

        <!-- Título principal -->
        <div class="row mb-4">
            <div class="col-12 bg-white">
                <div class="d-flex align-items-center justify-content-between border-bottom">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('stock.index') }}" 
                           class="col-auto btn-primary p-0 fs-2">
                            <i class="bi bi-arrow-left fs-2 m-3"></i>
                        </a>
                        <div>
                            <h1 class="h2 mb-1">
                                <i class="fas fa-warehouse me-2"></i>Almacén: {{ $warehouse->name }}
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Información Principal -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light    ">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información General
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- ID del Almacén -->
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-hashtag text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-bold text-muted">ID del Almacén</h6>
                                        <p class="mb-0 fs-5">{{ $warehouse->id }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-toggle-on text-success fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-bold text-muted">Estado</h6>
                                        <p class="mb-0 fs-5">
                                            @if($warehouse->is_active)
                                                <span class="text-success fw-bold">Activo</span>
                                            @else
                                                <span class="text-danger fw-bold">Inactivo</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Recepciones de Material -->
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-box-open text-info fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-bold text-muted">Recibos de Material</h6>
                                        <p class="mb-0 fs-5">
                                            @if($warehouse->allow_material_receipts)
                                                <span class="text-success fw-bold">Permitido</span>
                                            @else
                                                <span class="text-danger fw-bold">No Permitido</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tipo de Almacén -->
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-building text-warning fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-bold text-muted">Tipo</h6>
                                        <p class="mb-0 fs-5">
                                            @if($warehouse->is_matrix)
                                                <span class="text-warning fw-bold">Almacén Matriz</span>
                                            @else
                                                <span class="text-primary fw-bold">Almacén Regular</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de Creación -->
                            @if($warehouse->created_at)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-calendar-alt text-info fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-bold text-muted">Fecha de Creación</h6>
                                        <p class="mb-0 fs-5">
                                            {{ $warehouse->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Fecha de Actualización -->
                            @if($warehouse->updated_at)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-calendar-alt text-info fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-bold text-muted">Fecha de Actualización</h6>
                                        <p class="mb-0 fs-5">
                                            {{ $warehouse->updated_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Observaciones -->
                            <div class="col-md-12">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-comment-dots text-secondary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-bold text-muted">Observaciones</h6>
                                        @if($warehouse->observations)
                                            <p class="mb-0 fs-5">{{ $warehouse->observations }}</p>
                                        @else
                                            <p class="mb-0 fs-5">Sin observaciones</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards en columna: Información de Sucursal y Técnico Asignado -->
            <div class="col-lg-4 mb-4">
                <div class="d-flex flex-column gap-3">
                    <!-- Información de Sucursal -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-building me-2"></i>Sucursal
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-info fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-bold">{{ $warehouse->branch->name }}</h6>
                                    <p class="mb-0 text-muted">{{ $branch->address }}</p>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-phone text-muted me-2"></i>
                                        <span class="text-muted">{{ $branch->phone ?: 'Sin teléfono' }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-pin text-muted me-2"></i>
                                        <span class="text-muted">{{ $branch->city }}, {{ $branch->state }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-mail-bulk text-muted me-2"></i>
                                        <span class="text-muted">{{ $branch->zip_code ?: 'Sin código postal' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Técnico Asignado -->
                    @if($warehouse->technician)
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                            <i class="fas fa-user-tie"></i>Técnico Responsable
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-user-tie"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-bold">{{ $warehouse->technician->user->name }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-cogs me-2"></i>Acciones Disponibles
                        </h6>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            @include('stock.action-buttons')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }
        
        .breadcrumb a:hover {
            color: var(--bs-primary) !important;
        }
        
        .badge {
            font-size: 0.875rem !important;
        }
        
        .bg-light {
            background-color: #f8f9fa !important;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .btn {
            transition: all 0.2s ease-in-out;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .d-flex.align-items-center.justify-content-between {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start !important;
            }
            
            .d-flex.gap-2 {
                align-self: stretch;
                justify-content: center;
            }
        }
    </style>

@endsection