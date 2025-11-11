@extends('layouts.app')
@section('content')
    @php
        function formatPath($path)
        {
            return str_replace(['/', ' '], ['-', ''], $path);
        }
    @endphp

    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .table-text {
            font-size: 0.9rem;
        }
    </style>

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('user.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR USUARIO TIPO CLIENTE <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $user->name }}</span>
            </span>
        </div>

        <form class="form m-3" method="POST" action="{{ route('user.update', ['id' => $user->id]) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3 mb-3">
                <div class="row">
                    <div class="fw-bold mb-2 fs-5">Datos personales del usuario</div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('user.data.name') }}:
                        </label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}"
                            autocomplete="off" maxlength="50" required>
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="username" class="form-label is-required">{{ __('user.data.username') }}:
                        </label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="{{ $user->username }}" autocomplete="off" maxlength="50" required>
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="email" class="form-label is-required">{{ __('user.data.email') }}:
                        </label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}"
                            autocomplete="off" maxlength="50" required>
                    </div>
                    @hasanyrole(['AdministradorDireccion', 'SupervisorRecursos Humanos', 'SupervisorCalidad'])
                        <div class="col-lg-6 col-12 mb-3">
                            <label for="password" class="form-label is-required">{{ __('user.data.password') }}:
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="password" name="password"
                                    value="{{ $user->nickname }}" autocomplete="off" maxlength="20" required>

                                <button class="btn btn-success" type="button" onclick="generatePassword()"><i
                                        class="bi bi-arrow-clockwise"></i></button>
                            </div>
                        </div>
                    @endhasanyrole
                </div>
            </div>

            <div class="border rounded shadow p-3 mb-3">
                <div class="fw-bold mb-2 fs-5">Cliente/sedes asociados a la cuenta</div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="accordionDirectory" class="form-label is-required">Buscar cliente
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Busca por el nombre del cliente"
                                id="search-sedes">
                            <button class="btn btn-outline-primary" type="button" id="button-addon2"
                                onclick="searchSedes()">Buscar</button>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <table class="table table-striped" id="sedesTable">
                            <thead>
                                <tr>
                                    <th scope="col">Cliente/sedes con permisos</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2" class="text-muted">No hay clientes asociados</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-label fw-bold">{{ __('user.title.permissions') }}
                        </div>
                        <ul class="list-group" id="tree-dir">
                            @foreach ($local_dirs as $path)
                                <li class="list-group-item" id="dir-{{ formatPath($path) }}">
                                    <input class="form-check-input me-1" type="checkbox" value="{{ $path }}/"
                                        id="input-{{ formatPath($path) }}" onchange="setDirectory(this)">
                                    <button type="button" class="form-check-label btn btn-link p-0"
                                        for="input-{{ formatPath($path) }}" data-path="{{ $path }}"
                                        onclick="getSubdirectories(this)">{{ basename($path) }}</button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <input type="hidden" id="directories" name="directories" value="" />
            <input type="hidden" id="sedes" name="sedes" value="" />
            <button class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
        </form>
    </div>

    @include('user.modals.customer.select')

    <script src="{{ asset('js/user/actions.min.js') }}"></script>
    <script src="{{ asset('js/customer.min.js') }}"></script>
    <script src="{{ asset('js/directory.min.js') }}"></script>

    <script>
        const directories = @json($local_dirs);
        const paths = @json($user->directories->pluck('path')->toArray());
        var clients = @json($clients ?? []);
    </script>
@endsection
