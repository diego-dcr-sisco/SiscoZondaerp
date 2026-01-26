@extends('layouts.app')
@section('content')
    @php
        if (!function_exists('extractFileName')) {
            function extractFileName($filePath)
            {
                $fileNameWithExtension = basename($filePath);
                $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

                return $fileName;
            }

        }
        
        
    @endphp
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                ARCHIVOS DE LA SEDE </span> <span class="ms-2 fs-4"> {{ $customer->name }}</span>
            </span>
        </div>

        <div class="p-3">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filesModal">
                    Agregar archivo </button>
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
                        @if (!$customer->files->isEmpty())
                            @foreach ($customer->files as $file)
                                <tr>
                                    <td class="">{{ $file->filename->name }}</td>
                                    <td class="">
                                        <a href="{{ route('customer.file.download', ['id' => $file->id]) }}"
                                            class="{{ $file->path ? '' : 'text-decoration-none' }}">
                                            {{ $file->path ? extractFileName($file->path) : '-' }}
                                        </a>
                                    </td>
                                    <td>{{ $file->expirated_at ? Carbon\Carbon::parse($file->expirated_at)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ $file->updated_at ? Carbon\Carbon::parse($file->updated_at)->format('d-m-Y H:i:s') : '-' }}
                                    </td>
                                    <td>
                                        <div class="text-center" role="group" aria-label="Basic example">
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Editar archivo">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#filesModal" onclick="setFileId({{ $file->id }})" id="edit-btn-{{ $file->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            </span>
                                            <a href="{{ route('customer.destroy.file', ['id' => $file->id]) }}"
                                                class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-title="Eliminar archivo"
                                                onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
        }));
    </script>

    @include('customer.modals.files')
@endsection
