@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row border-bottom p-3 mb-4">
        <a href="{{ route('supplier.index') }}" class="col-auto btn-primary p-0 fs-3">
            <i class="bi bi-arrow-left fs-4"></i>
        </a>        
        <h1 class="col-auto fs-2 fw-bold m-0">Detalles del proveedor</h1>      
    </div>
    <div class="row px-3">
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $supplier->name }}" readonly>
            </div>
            <div class="mb-3">
                <label for="rfc" class="form-label">RFC</label>
                <input type="text" class="form-control" id="rfc" name="rfc" value="{{ $supplier->rfc }}" readonly>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ $supplier->email }}" readonly>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="phone" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="phone" name="phone" value="{{ $supplier->phone }}" readonly>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ $supplier->address }}" readonly>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Categoría</label>
                <input type="text" class="form-control" id="category" name="category" value="{{ $supplier->category->name }}" readonly>
            </div>
        </div>
        <div class="justify-content-start ">
            <a href="{{ route('supplier.edit', $supplier->id) }}" class="btn btn-secondary flat-btn m-2">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
        </div>
    </div>
    
</div>
@endsection