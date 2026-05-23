@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-2 border-danger">
                <div class="card-header bg-gradient-danger">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-ban fa-2x mr-3"></i>
                        <h3 class="mb-0">Acceso Denegado</h3>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-danger border-left-danger border-left-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-3x mr-4 text-danger"></i>
                            <div>
                                <h4 class="alert-heading font-weight-bold">¡Error 403 - Prohibido!</h4>
                                <p class="mb-2">{{ $message ?? 'No tienes los permisos necesarios para acceder a este recurso' }}</p>
                                <hr>
                                <div class="mt-3">
                                    <p class="mb-1"><strong>Nombre:</strong> {{ auth()->user()->name }}</p>
                                    <p class="mb-1"><strong>Usuario:</strong> {{ auth()->user()->username }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-primary btn">
                            <i class="fas fa-arrow-left mr-2"></i> Volver atrás
                        </a>
                        
                    </div>
                </div>
                <div class="card-footer bg-light text-right text-muted small">
                    <i class="fas fa-info-circle mr-1"></i> Si crees que esto es un error, contacta al administrador
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .bg-gradient-danger {
        background: linear-gradient(135deg, #ff4d4d 0%, #d92626 100%);
    }
    .border-left-3 {
        border-left-width: 3px !important;
    }
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .card-header {
        padding: 1.25rem 1.5rem;
    }
    .alert {
        border-radius: 0.35rem;
    }
</style>
@endsection

@section('scripts')
@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Acceso Denegado',
        text: '{{ session('error') }}',
        confirmButtonColor: '#d33',
        backdrop: true
    });
</script>
@endif
@endsection