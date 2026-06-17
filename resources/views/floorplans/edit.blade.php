@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR PLANO - PLANOS',
        'icon' => 'bi-map',
        'backRoute' => url()->previous(),
    ])
@if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    @php
        $pointNames = [];
        $areaNames = [];
        $productNames = [];
        $image = route('image.show', ['path' => $floorplan->path]);

        foreach ($products as $product) {
            $productNames[] = [
                'id' => $product->id,
                'name' => $product->name,
            ];
        }
    @endphp
    <div class="col-11">
<div class="row p-5 pt-3">
            @if ($section == 1)
                @include('floorplans.edit.form')
            @endif

            @if ($section == 2)
                @include('floorplans.edit.devices')
            @endif
        </div>
    </div>
@endsection
