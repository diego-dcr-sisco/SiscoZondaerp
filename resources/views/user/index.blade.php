    @extends('layouts.app')
    @section('content')
        <div class="container-fluid">
            <div class="py-3">
                @can('write_user')
                    <a class="btn btn-primary btn-sm" href="{{ route('user.create') }}">
                        <i class="bi bi-plus-lg fw-bold"></i> {{ __('user.title.create') }}
                    </a>
                @endcan
            </div>


            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped caption-top">
                    <caption class="border rounded-top p-2 text-dark bg-light">
                        <form action="{{ route('user.search') }}" method="GET">
                            @csrf
                            <div class="row g-3 mb-0">
                                <div class="col-lg-4 col-12">
                                    <label for="customer" class="form-label">Nombre</label>
                                    <input type="text" class="form-control form-control-sm" id="name" name="name"
                                        value="{{ request('name') }}" placeholder="Buscar nombre">
                                </div>

                                <div class="col-lg-4 col-12">
                                    <label for="date_range" class="form-label">Usuario</label>
                                    <input type="text" class="form-control form-control-sm" id="username"
                                        name="username" value="{{ request('username') }}" placeholder="Buscar usuario">
                                </div>

                                <div class="col-lg-4 col-12">
                                    <label for="time" class="form-label">Correo</label>
                                    <input type="text" class="form-control form-control-sm" id="email" name="email"
                                        value="{{ request('email') }}" placeholder="Buscar correo">
                                </div>

                                <div class="col-auto">
                                    <label for="service" class="form-label">Rol</label>
                                    <select class="form-select form-select-sm" id="role" name="role"
                                        value="{{ request('role') }}">
                                        <option value="">Todos</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ request('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label for="wk_dept" class="form-label">Departamento</label>
                                    <select class="form-select form-select-sm" id="wk_dept" name="wk_dept"
                                        value="{{ request('role') }}">
                                        <option value="">Todos</option>
                                        @foreach ($wk_depts as $wk)
                                            <option value="{{ $wk->id }}"
                                                {{ request('wk_dept') == $wk->id ? 'selected' : '' }}>{{ $wk->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label for="signature_status" class="form-label">Dirección</label>
                                    <select class="form-select form-select-sm" id="direction" name="direction">
                                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                        </option>
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                        </option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label for="order_type" class="form-label">Total</label>
                                    <select class="form-select form-select-sm" id="size" name="size">
                                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                </div>

                                <!-- Botones -->
                                <div class="col-lg-12 d-flex justify-content-end m-0">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>
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
