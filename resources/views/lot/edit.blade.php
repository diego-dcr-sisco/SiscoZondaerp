@extends('layouts.app')
@section('content')

@if (!auth()->check())
    <?php
    header('Location: /login');
    exit();
    ?>
@endif

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

@include('components.page-header', [
    'title' => 'EDITAR LOTE ' . $lot->registration_number,
    'icon' => 'bi-box-seam',
])

<div class="container-fluid p-0">
    @include('lot.edit.form')
</div>

@endsection
