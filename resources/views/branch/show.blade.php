@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'VER SUCURSAL',
        'icon' => 'bi-diagram-3',
        'backRoute' => url()->previous(),
    ])
@if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    @php
        function isPDF($filePath)
        {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            return $extension === 'pdf' || $extension == 'PDF';
        }
    @endphp

    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }
    </style>

    <div class="row w-100 justify-content-between m-0 h-100">
        <div class="col-1 m-0" style="background-color: #343a40;">
            <div class="row">
                <a href="{{ Route('branch.show', ['id' => $branch->id, 'section' => 1]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Datos generales
                </a>
                <a href="{{ Route('branch.show', ['id' => $branch->id, 'section' => 2]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Datos del contracto
                </a>
            </div>
        </div>

        <div class="col-11">
<div class="row p-5 pt-3">
                @if ($section == 1)
                    <div class="row">
                        <span class="col fw-bold">{{ __('branch.data.name') }}:</span>
                        <span class="col fw-normal">{{ $branch->name }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">Status:</span>
                        <span class="col fw-normal">{{ $branch->status->name }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">Código: </span>
                        <span class="col fw-normal">{{ $branch->code }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('branch.data.address') }}:</span>
                        <span class="col fw-normal">{{ $branch->address }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('branch.data.colony') }}:</span>
                        <span class="col fw-normal">{{ $branch->colony }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('branch.data.zip_code') }}:</span>
                        <span class="col fw-normal">{{ $branch->zip_code }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('branch.data.city') }}:</span>
                        <span class="col fw-normal">{{ $branch->city }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('branch.data.state') }}:</span>
                        <span class="col fw-normal">{{ $branch->state }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('branch.data.country') }}:</span>
                        <span class="col fw-normal">{{ $branch->country }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('branch.data.license_number') }}:</span>
                        <span class="col fw-normal">{{ $branch->license_number }}</span>
                    </div>
                @endif

                @if ($section == 2)
                <div class="row">
                    <span class="col fw-bold">{{ __('branch.data.email') }}:</span>
                    <span class="col fw-normal">{{ $branch->email }}</span>
                </div>
                <div class="row">
                    <span class="col fw-bold">Correo alternativo:</span>
                    <span class="col fw-normal">{{ $branch->alt_email }}</span>
                </div>
                <div class="row">
                    <span class="col fw-bold">{{ __('branch.data.phone') }}:</span>
                    <span class="col fw-normal">{{ $branch->phone }}</span>
                </div>
                <div class="row">
                    <span class="col fw-bold">{{ __('branch.data.alt_phone') }}:</span>
                    <span class="col fw-normal">{{ $branch->alt_phone }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
