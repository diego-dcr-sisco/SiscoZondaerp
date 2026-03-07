@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                REVISION DEL REPORTE <span class="ms-2 fs-4"> {{ $order->folio }} [{{ $order->id }}]</span>
            </span>
            <span id="approved-badge" class="badge bg-success ms-3 fs-6" style="{{ $order->status_id == 5 ? '' : 'display: none;' }}">
                <i class="bi bi-check-circle-fill me-1"></i>
                Aprobado
            </span>
        </div>

        @include('report.create.form')
    </div>

@endsection
