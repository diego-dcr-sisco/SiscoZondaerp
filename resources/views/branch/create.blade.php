@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CREAR SUCURSAL',
        'icon' => 'bi-diagram-3',
        'backRoute' => url()->previous(),
    ])
@if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    <div class="container-fluid p-0">
<div class="px-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-lg-4">
                    @include('branch.create.form')
                </div>
            </div>
        </div>
    </div>
@endsection

