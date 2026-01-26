@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
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

        .directory:hover {
            text-decoration: underline !important;
            color: #0d6efd !important;
        }
    </style>


    <div class="container-fluid">
        <div class="m-3">
            <nav aria-label="breadcrumb mb-3">
                <ol class="breadcrumb">
                    @foreach ($links as $i => $link)
                        <li class="breadcrumb-item">
                            @if ($i == 0)
                                <a href="{{ route('client.system.index', ['path' => $link]) }}">Inicio</a>
                            @else
                                @if (count($links) != $i + 1)
                                    <a href="{{ route('client.system.index', ['path' => $link]) }}">{{ basename($link) }}</a>
                                @else
                                    {{ basename($link) }}
                                @endif
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>

            <div class="mb-3">
                @can('write_system_client')
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#directoryModal">
                        <i class="bi bi-folder-fill"></i> Crear carpeta
                    </button>

                    <a href="{{ route('client.directory.mip', ['path' => $data['root_path']]) }}" class="btn btn-dark btn-sm"
                        onclick="return confirm('{{ __('messages.do_you_want_create_mip') }}')">
                        <i class="bi bi-bar-chart-steps"></i> {{ __('buttons.mip_structure') }}
                    </a>

                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#fileModal">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i> {{ __('buttons.upload_files') }}
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="clipboardMode()">
                        <i class="bi bi-clipboard-fill"></i> {{ __('buttons.clipboard') }}
                    </button>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <tbody>
                        @foreach ($data['mip_directories'] as $dir)
                            @if ($user->hasDirectory($dir['path']) || in_array($user->work_department_id, [1, 7]))
                                <tr>
                                    <td class="w-75">
                                        <a href="{{ route('client.system.index', ['path' => $dir['path']]) }}"
                                            class="text-decoration-none d-flex align-items-center gap-2">
                                            <i class="bi bi-folder-fill text-warning"></i>
                                            <span>{{ $dir['name'] }}</span>
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <!-- Espacio para acciones si es necesario -->
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                        @foreach ($data['directories'] as $dir)
                            @if (in_array($user->work_department_id, [1, 7]))
                                <tr>
                                    <td class="w-100">
                                        <div class="d-flex align-items-center gap-2 w-100">
                                            @can('write_system_client')
                                                <div class="form-check">
                                                    <input class="form-check-input dir-checkbox" type="checkbox"
                                                        value="{{ $dir['path'] }}">
                                                </div>
                                            @endcan
                                            <a href="{{ route('client.system.index', ['path' => $dir['path']]) }}"
                                                class="text-decoration-none d-flex align-items-center gap-2 w-100">
                                                <i class="bi bi-folder-fill text-warning"></i>
                                                <span>{{ $dir['name'] }}</span>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        @can('write_system_client')
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="{{ route('client.directory.mgmt', ['userId' => auth()->user()->id, 'path' => $dir['path']]) }}"
                                                    class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    data-bs-title=" {{ $user->dirManagement($dir['path']) ? 'Ocultar carpeta' : 'Mostrar carpeta' }}"
                                                    onclick="return confirm('{{ __('messages.are_you_sure_visible') }}')">
                                                    <i
                                                        class="bi {{ $user->dirManagement($dir['path']) ? 'bi-eye-slash-fill' : 'bi-eye-fill' }}"></i>
                                                </a>
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editDirectoryModal"
                                                    onclick="setRoot('{{ $dir['name'] }}', '{{ $dir['path'] }}')"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Editar carpeta">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm delete-directory-btn" 
                                                    data-path="{{ $dir['path'] }}"
                                                    data-name="{{ $dir['name'] }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top" 
                                                    data-bs-title="Eliminar carpeta">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @else
                                @if (($user->hasDirectory($dir['path']) || $user->hasPathInside($dir['path'])) && $user->dirManagement($dir['path']))
                                    <tr>
                                        <td class="w-75">
                                            <a href="{{ route('client.system.index', ['path' => $dir['path']]) }}"
                                                class="text-decoration-none d-flex align-items-center gap-2">
                                                <i class="bi bi-folder-fill text-warning"></i>
                                                <span>{{ $dir['name'] }}</span>
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            @can('write_system_client')
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <a href="{{ route('client.directory.mgmt', ['userId' => auth()->user()->id, 'path' => $dir['path']]) }}"
                                                        class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        data-bs-title=" {{ $user->dirManagement($dir['path']) ? 'Ocultar carpeta' : 'Mostrar carpeta' }}"
                                                        onclick="return confirm('{{ __('messages.are_you_sure_visible') }}')">
                                                        <i
                                                            class="bi {{ $user->dirManagement($dir['path']) ? 'bi-eye-slash-fill' : 'bi-eye-fill' }}"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-secondary btn-sm"
                                                        data-bs-toggle="modal" data-bs-target="#editDirectoryModal"
                                                        onclick="setRoot('{{ $dir['name'] }}', '{{ $dir['path'] }}')"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Editar carpeta">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm delete-directory-btn" 
                                                        data-path="{{ $dir['path'] }}"
                                                        data-name="{{ $dir['name'] }}"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top" 
                                                        data-bs-title="Eliminar carpeta">
                                                        <i class="bi bi-trash-fill"></i> {{ __('buttons.delete') }}
                                                    </button>
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        @endforeach

                        @foreach ($data['mip_files'] as $file)
                            <tr>
                                <td class="w-75">
                                    <a href="{{ route('client.file.download', ['path' => $file['path']]) }}"
                                        class="text-decoration-none d-flex align-items-center gap-2" target="_blank">
                                        <i class="bi bi-file-pdf-fill text-danger"></i>
                                        <span>{{ $file['name'] }}</span>
                                    </a>
                                </td>
                                <td class="text-end">
                                    <!-- Espacio para acciones si es necesario -->
                                </td>
                            </tr>
                        @endforeach

                        @foreach ($data['files'] as $file)
                            <tr>
                                <td class="w-75">
                                    <a href="{{ route('client.file.download', ['path' => $file['path']]) }}"
                                        class="text-decoration-none d-flex align-items-center gap-2" target="_blank">
                                        <i class="bi bi-file-pdf-fill text-danger"></i>
                                        <span>{{ $file['name'] }}</span>
                                    </a>
                                </td>
                                <td class="text-end">
                                    @can('write_system_client')
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editFileModal"
                                            onclick="setFilename('{{ $file['name'] }}', '{{ $file['path'] }}')"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Editar archivo">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger btn-sm delete-file-btn" 
                                            data-path="{{ $file['path'] }}"
                                            data-name="{{ $file['name'] }}"
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top"
                                            data-bs-title="Eliminar archivo">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('client.modals.create.directory')
    @include('client.modals.create.file')
    @include('client.modals.edit.directory')
    @include('client.modals.edit.file')
    @include('client.modals.edit.clipboard')

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

        // Variables para controlar eliminaciones en proceso
        let deletingItems = new Set();

        // Manejar eliminación de carpetas
        $(document).on('click', '.delete-directory-btn', function(e) {
            e.preventDefault();
            
            const btn = $(this);
            const path = btn.data('path');
            const name = btn.data('name');

            // Verificar si ya se está eliminando
            if (deletingItems.has(path)) {
                return false;
            }

            if (!confirm('{{ __('messages.are_you_sure_delete') }} la carpeta "' + name + '"?')) {
                return false;
            }

            // Marcar como en proceso de eliminación
            deletingItems.add(path);

            // Cambiar apariencia del botón
            const originalHtml = btn.html();
            btn.prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm me-1"></span>Eliminando...');

            // Realizar la petición
            window.location.href = '{{ route('client.directory.destroy', ['path' => '__PATH__']) }}'.replace('__PATH__', encodeURIComponent(path));
        });

        // Manejar eliminación de archivos
        $(document).on('click', '.delete-file-btn', function(e) {
            e.preventDefault();
            
            const btn = $(this);
            const path = btn.data('path');
            const name = btn.data('name');

            // Verificar si ya se está eliminando
            if (deletingItems.has(path)) {
                return false;
            }

            if (!confirm('{{ __('messages.are_you_sure_delete') }} el archivo "' + name + '"?')) {
                return false;
            }

            // Marcar como en proceso de eliminación
            deletingItems.add(path);

            // Cambiar apariencia del botón
            const originalHtml = btn.html();
            btn.prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm"></span>');

            // Realizar la petición
            window.location.href = '{{ route('client.file.destroy', ['path' => '__PATH__']) }}'.replace('__PATH__', encodeURIComponent(path));
        });
    </script>
@endsection
