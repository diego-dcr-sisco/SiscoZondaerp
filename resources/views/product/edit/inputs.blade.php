@extends('layouts.app')
@section('content')
    @php
        function formatPath($path)
        {
            return str_replace(['/', ' '], ['-', ''], $path);
        }

        function extractFileName($filePath)
        {
            $fileNameWithExtension = basename($filePath);
            $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

            return $fileName;
        }
    @endphp

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('product.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                INSUMOS DEL PRODUCTO <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $product->name }}</span>
            </span>
        </div>


        <div class="m-3">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inputModal">
                    <i class="bi bi-plus-lg"></i> Agregar insumo </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="fw-bold" scope="col">Nombre</th>
                            <th class="fw-bold" scope="col">Archivo</th>
                            <th class="fw-bold" scope="col">Fecha de vencimiento</th>
                            <th class="fw-bold" scope="col">Fecha de actualización</th>
                            <th class="fw-bold" scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @include('product.modals.input')
@endsection
