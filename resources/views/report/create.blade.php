@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <style>
        .report-certificate-ui {
            --cert-primary: #02265Aff;
            --cert-secondary: #b0bec5;
            --cert-ink: #0f2233;
            --cert-muted: #5d6c79;
            --cert-border: #d5dee4;
            --cert-surface: #f7fafc;
            font-family: "Helvetica", Arial, sans-serif;
            color: var(--cert-ink);
            background-color: #f4f6f9;
            min-height: 100vh;
        }

        .report-certificate-ui .report-topbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--cert-border);
        }

        .report-certificate-ui .card {
            border: 1px solid var(--cert-border);
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(15, 34, 51, 0.08) !important;
            overflow: hidden;
        }

        .report-certificate-ui .card-header {
            background-color: var(--cert-primary);
            color: #ffffff;
            font-weight: 700;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            letter-spacing: 0.2px;
        }

        .report-certificate-ui .card-body {
            background: #fff;
        }

        .report-certificate-ui .smnote {
            background: #fff;
        }

        .report-certificate-ui .form-control,
        .report-certificate-ui .form-select {
            border-color: var(--cert-border);
        }

        .report-certificate-ui .form-control:focus,
        .report-certificate-ui .form-select:focus {
            border-color: #0a2986;
            box-shadow: 0 0 0 0.2rem rgba(10, 41, 134, 0.2);
        }

        .report-certificate-ui .table {
            border-color: var(--cert-border);
            font-size: 0.88rem;
        }

        .report-certificate-ui .table thead th {
            background-color: var(--cert-secondary) !important;
            color: #12283a;
            border-color: var(--cert-border);
            font-weight: 700;
        }

        .report-certificate-ui .table-striped>tbody>tr:nth-of-type(odd)>* {
            background-color: var(--cert-surface);
        }

        .report-certificate-ui .border,
        .report-certificate-ui .border-top,
        .report-certificate-ui .border-bottom,
        .report-certificate-ui .border-start,
        .report-certificate-ui .border-end {
            border-color: var(--cert-border) !important;
        }

        .report-certificate-ui .bg-secondary-subtle {
            background-color: #e8edf1 !important;
            color: var(--cert-ink);
        }

        .report-certificate-ui .text-muted,
        .report-certificate-ui small {
            color: var(--cert-muted) !important;
        }

        .report-certificate-ui .section-action-bar {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
            margin-top: 0.75rem;
            padding-top: 0.65rem;
            flex-wrap: wrap;
            border-top: 1px dashed var(--cert-border);
        }

        .report-certificate-ui .section-action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: flex-start;
            order: 1;
        }

        .report-certificate-ui .autosave-status {
            font-size: 0.78rem;
            color: var(--cert-muted);
            background: #f2f6fa;
            border: 1px solid var(--cert-border);
            border-radius: 999px;
            padding: 0.15rem 0.55rem;
            white-space: nowrap;
            order: 2;
        }

        .report-certificate-ui .autosave-status.is-saving {
            color: #925f00;
            background: #fff8e1;
            border-color: #ffd970;
        }

        .report-certificate-ui .autosave-status.is-saved {
            color: #176538;
            background: #eafaf1;
            border-color: #7cd3a6;
        }

        .report-certificate-ui .autosave-status.is-error {
            color: #8b1a1a;
            background: #ffefef;
            border-color: #f3a6a6;
        }

        .report-certificate-ui .report-save-btn {
            font-weight: 600;
        }

        .report-certificate-ui .report-save-btn i {
            margin-right: 0.2rem;
        }

        .report-certificate-ui .report-generate-bar {
            position: sticky;
            bottom: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.95);
            border-top: 1px solid var(--cert-border);
            margin-top: 1rem;
            padding: 0.85rem 0;
            display: flex;
            justify-content: flex-start;
            backdrop-filter: blur(4px);
        }

        .report-certificate-ui #generate-report-btn {
            min-width: 220px;
            font-size: 0.95rem;
            padding: 0.55rem 1rem;
        }
    </style>

    <div class="container-fluid p-0 report-certificate-ui">
        <div class="d-flex align-items-center ps-4 p-2 report-topbar">
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
