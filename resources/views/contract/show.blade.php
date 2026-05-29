@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'VER CONTRATO',
        'icon' => 'bi-file-earmark-text',
        'backRoute' => url()->previous(),
    ])
@php
        if (!function_exists('isPDF')) { 
            function isPDF($filePath)
            {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $extension = strtolower($extension);
                return $extension === 'pdf' || $extension == 'PDF';
            }
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

        .flat-btn {
            background-color: #FF6B35;
        }
    </style>

    <div class="container-fluid">
<div class="m-3">
            @include('messages.alert')
            <div class="overflow-auto w-100">
                @include('contract.tables.orders')
            </div>
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
