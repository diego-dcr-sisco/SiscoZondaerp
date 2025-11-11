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

    <div class="row w-100 justify-content-between m-0 h-100">
        @include('client.navbar')
        <div class="col-11">
            <div class="row w-100 justify-content-between p-3 m-0 mb-3">
                <div class="col-12 mb-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @foreach ($links as $i => $link)
                                <li class="breadcrumb-item">
                                    @if ($i == 0)
                                        <a href="{{ route('client.mip.index', ['path' => $link]) }}">Inicio</a>
                                    @else
                                        @if (count($links) != $i + 1)
                                            <a
                                                href="{{ route('client.mip.index', ['path' => $link]) }}">{{ basename($link) }}</a>
                                        @else
                                            {{ basename($link) }}
                                        @endif
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>

                    @can('write_system_client')
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#directoryModal">
                            <i class="bi bi-folder-fill"></i> Crear carpeta
                        </button>

                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#fileModal">
                            <i class="bi bi-file-earmark-arrow-up-fill"></i> {{ __('client.title.create_file') }}
                        </button>
                    @endcan
                </div>
                <div class="col-12">
                    <div class="list-group">
                        @foreach ($data['directories'] as $dir)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-0">
                                <a href="{{ route('client.mip.index', ['path' => $dir['path']]) }}"
                                    class="icon-link d-block text-decoration-none inline p-2 w-75">
                                    <i class="bi bi-folder-fill text-warning"></i> {{ $dir['name'] }}
                                </a>
                                {{-- @can('write_system_client')
                                    <div class="d-flex gap-2 me-2">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editDirectoryModal"
                                            onclick=""><i
                                                class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}</button>
                                    </div>
                                @endcan --}}
                            </div>
                        @endforeach

                        @foreach ($data['files'] as $file)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-0">
                                <a href="{{ route('client.file.download', ['path' => $file['path']]) }}"
                                    class="icon-link d-block text-decoration-none inline p-2 w-75" target="_blank">
                                    <i class="bi bi-file-pdf-fill text-danger fs-5"></i> {{ $file['name'] }}
                                </a>

                                @can('write_system_client')
                                    <div class="btn-group me-2">
                                        <a href="{{ route('client.file.destroy', ['path' => $file['path']]) }}"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')" target="_blank">
                                            <i class="bi bi-trash-fill"></i> {{ __('buttons.delete') }}
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('client.modals.create.directory')
    @include('client.modals.edit.directory')
    @include('client.modals.create.file')
@endsection
