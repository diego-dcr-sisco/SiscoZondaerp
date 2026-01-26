@extends('layouts.app')
@section('content')
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
        <div class="row p-3 border-bottom">
            <a href="{{ Route('customer.edit', ['id' => $customer->id, 'type' => $type, 'section' => 8]) }}"
                class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0">{{ __('modals.title.edit_floorplan') }}</h1>
        </div>
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
