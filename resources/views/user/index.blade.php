    @extends('layouts.app')
    @section('content')
        @include('components.page-header', [
            'title' => 'USUARIOS',
            'icon' => 'bi-people',
            'actionRoute' => route('user.create'),
            'actionText' => __('user.title.create'),
        ])
        <div class="container-fluid">
            <div class="overflow-auto w-100">
                <table class="table table-sm table-bordered table-striped caption-top">
                    <caption class="border rounded-top p-2 text-dark bg-white">
                        <form action="{{ route('user.search') }}" method="GET">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-lg-2 col-12">
                                    <label for="name" class="form-label">Nombre</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="{{ request('name') }}" placeholder="Buscar nombre">
                                    </div>
                                </div>

                                {{-- Usuario --}}
                                <div class="col-lg-2 col-12">
                                    <label for="username" class="form-label">Usuario</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-at"></i></span>
                                        <input type="text" class="form-control" id="username" name="username"
                                            value="{{ request('username') }}" placeholder="Buscar usuario">
                                    </div>
                                </div>

                                {{-- Correo --}}
                                <div class="col-lg-2 col-12">
                                    <label for="email" class="form-label">Correo</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                        <input type="text" class="form-control" id="email" name="email"
                                            value="{{ request('email') }}" placeholder="Buscar correo">
                                    </div>
                                </div>

                                {{-- Rol --}}
                                <div class="col-lg-2">
                                    <label for="role" class="form-label">Rol</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                        <select class="form-select" id="role" name="role">
                                            <option value="">Todos</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ request('role') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Departamento --}}
                                <div class="col-lg-2">
                                    <label for="wk_dept" class="form-label">Departamento</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <select class="form-select" id="wk_dept" name="wk_dept">
                                            <option value="">Todos</option>
                                            @foreach ($wk_depts as $wk)
                                                <option value="{{ $wk->id }}"
                                                    {{ request('wk_dept') == $wk->id ? 'selected' : '' }}>
                                                    {{ $wk->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <label for="signature_status" class="form-label">Ordenar / Mostrar</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" id="basic-addon1"><i
                                                class="bi bi-arrow-down-up"></i></span>
                                        <select class="form-select form-select-sm" id="direction" name="direction">
                                            <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                                DESC
                                            </option>
                                            <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>
                                                ASC
                                            </option>
                                        </select>
                                        <span class="input-group-text" id="basic-addon1"><i
                                                class="bi bi-list-ol"></i></span>
                                        <select class="form-select form-select-sm" id="size" name="size">
                                            <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25
                                            </option>
                                            <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50
                                            </option>
                                            <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100
                                            </option>
                                            <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200
                                            </option>
                                            <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-end g-3 mb-0">
                                <div class="col-lg-1 col-6">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>
                                </div>
                                <div class="col-lg-1 col-6">
                                    <a href="{{ route('user.search') }}" class="btn btn-secondary btn-sm w-100">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </caption>
                    <thead>
                        <tr>
                            <th class="fw-bold" scope="col">#</th>
                            <th class="fw-bold" scope="col">{{ __('user.data.name') }}</th>
                            <th class="fw-bold" scope="col">{{ __('user.data.username') }}</th>
                            <th class="fw-bold" scope="col">{{ __('user.data.email') }}</th>
                            <th class="fw-bold" scope="col">{{ __('user.data.role') }}</th>
                            <th class="fw-bold" scope="col">{{ __('user.data.department') }}</th>
                            <th class="fw-bold" scope="col">{{ __('user.data.status') }}</th>
                            <th class="fw-bold" scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            <tr>
                                <th scope="row">{{ ++$index }}</th>
                                <td> {{ $user->name }} </td>
                                <td class="fw-bold"> {{ $user->username ?? '-' }} </td>
                                <td> {{ $user->email }} </td>
                                <td> {{ $user->simpleRole->name ?? '-' }} </td>
                                <td> {{ $user->workDepartment->name ?? '-' }} </td>
                                <td
                                    class="fw-bold {{ $user->status_id == 2 ? 'text-success' : ($user->status_id == 3 ? 'text-danger' : 'text-warning') }}">
                                    {{ $user->status->name ?? '-' }} </td>
                                <td>
                                    @can('write_user')
                                        <a href="{{ $user->role_id != 5 ? route('user.edit', ['id' => $user->id]) : route('user.edit.client', ['id' => $user->id]) }}"
                                            class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Editar usuario">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a href="{{ route('user.locations', ['id' => $user->id]) }}"
                                            class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Ubicaciones GPS">
                                            <i class="bi bi-geo-alt-fill"></i>
                                        </a>

                                        <a href="{{ route('user.destroy', ['id' => $user->id]) }}"
                                            onclick="return confirm('¿Estás seguro de eliminar este usuario?');"
                                            class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Eliminar usuario">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
            {{ $users->links('pagination::bootstrap-5') }}
        </div>

        <script>
            // data-bs-toggle="tooltip" data-bs-placement="top" title=""
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    @endsection
