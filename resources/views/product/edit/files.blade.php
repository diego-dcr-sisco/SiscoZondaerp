@extends('layouts.app')
@section('content')
    @php
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
                ARCHIVOS DEL PRODUCTO </span> <span class="ms-2 fs-4"> {{ $product->name }}</span>
            </span>
        </div>

        <div class="m-3">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filesModal">
                    <i class="bi bi-plus-lg"></i> Agregar archivo </button>
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
                        @foreach ($product->files as $file)
                            <tr>
                                <td>{{ $file->filename->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('product.download.file', ['id' => $file->id]) }}"
                                        class="{{ $file->path ? '' : 'text-decoration-none' }}">
                                        {{ $file->path ? extractFileName($file->path) : '-' }}
                                    </a>
                                </td>
                                <td>{{ $file->expirated_at ? \Carbon\Carbon::parse($file->expirated_at)->format('d-m-Y') : '-' }}
                                </td>
                                <td>{{ $file->updated_at ? Carbon\Carbon::parse($file->updated_at)->format('d-m-Y H:i:s') : '-' }}
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#filesModal" onclick="setFileId({{ $file->id }})">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <a href="{{ route('product.destroy.file', ['id' => $file->id]) }}" class="btn btn-danger btn-sm"
                                        onclick="return confirm('{{ '¿Estas seguro de eliminar el archivo?' }}')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>

    @include('product.modals.files')
@endsection
