@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row border-bottom p-3 mb-4">
        <a href="{{ route('supplier.index') }}" class="col-auto btn-primary p-0 fs-3">
            <i class="bi bi-arrow-left fs-4"></i>
        </a>        
        <h1 class="col-auto fs-2 fw-bold m-0">Detalles del proveedor</h1>      
    </div>
    <div class="row mx-3">
        <form action="{{ route('supplier.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="name" class="form-label is-required">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $supplier->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="rfc" class="form-label is-required">RFC</label>
                        <input type="text" class="form-control" id="rfc" name="rfc" value="{{ $supplier->rfc }}" minlength="12" maxlength="13" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $supplier->email }}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label is-required">Teléfono</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ $supplier->phone }}" minlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ $supplier->address }}">
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label is-required">Categoría</label>
                        <select class="form-control" id="category" name="category_id" required>
                            <option value="">Seleccione una Categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $supplier->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-floppy-fill"></i> Guardar
            </button>
        
        </form>
    </div>
</div>
@endsection