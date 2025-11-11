@extends('layouts.app')

@section('content')

@if (!auth()->check())
<script>
    window.location.href = "{{ route('login') }}";
</script>
@endif
<div>
    <div class="col-11">
        <div class="row p-3 border-bottom">
            <a href="{{ route('dashboard.warehouse', ['status' => 1, 'page' => 1]) }}" class="col-auto btn-primary p-0 fs-3">
                <i class="bi bi-arrow-left m-3"></i>
            </a>
            <h1 class="col-auto fs-2 fw-bold m-0">Búsqueda de Movimiento:</h1>
        </div>
    </div>

    <form  method="POST" class="form p-5 pt-3" action="{{ route('warehouse.search') }}" enctype="multipart/form-data">
    @csrf
        <div class="row">
            <div class="col-lg-4 mb-3">
                <label for="date_from">Fecha Desde</label>
                <input type="date" id="date_from" name="date_from" class="form-control" required>
            </div>
            <div class="col-lg-4 mb-3">
                <label for="date_to">Fecha Hasta</label>
                <input type="date" id="date_to" name="date_to" class="form-control" required>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 mb-3">
                <label for="user_id">Creado por</label>
                <select id="user_id" name="user_id" class="form-control">
                    <option value="0">Cualquier usuario</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 mb-3">
                <label for="warehouse_id">Almacén</label>
                <select id="warehouse_id" name="warehouse_id" class="form-control">
                    <option value="0">Cualquier almacen</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 mb-3">
                <label for="type">Tipo de Movimiento</label>
                <select id="type" name="type" class="form-control">
                    <option value="entry">Entrada</option>
                    <option value="exit">Salida</option>
                </select>
            </div>
            <div class="col-lg-4 mb-3">
                <select id="product_id" name="product_id" class="form-control">
                    <option value="0">Cualquier producto</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>
</div>
@endsection