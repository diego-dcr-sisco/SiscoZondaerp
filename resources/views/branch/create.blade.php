@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom px-4 py-3 mb-3">
            <a href="{{ route('branch.index') }}" class="text-decoration-none pe-3" aria-label="Volver a sucursales">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">{{ __('branch.title.create') }}</span>
        </div>

        <div class="px-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-lg-4">
                    @include('branch.create.form')
                </div>
            </div>
        </div>
    </div>
@endsection

