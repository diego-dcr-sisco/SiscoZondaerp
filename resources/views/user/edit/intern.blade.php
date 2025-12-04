@extends('layouts.app')
@section('content')
      

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
                EDITAR USUARIO INTERNO <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $user->name }}</span>
            </span>
        </div>
        <form class="form m-3" method="POST" action="{{ route('user.update', ['id' => $user->id]) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-6 col-12 mb-3">
                    <input type="hidden" name="url_customer" id="url-customer"
                        value="{{ route('order.search.customer') }}" />
                    <div class="border rounded shadow p-3">
                        <div class="row">
                            <div class="fw-bold mb-2 fs-5">Datos personales del usuario</div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="name" class="form-label is-required">{{ __('user.data.name') }}:
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ $user->name }}" autocomplete="off" maxlength="50" required>
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
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ $user->email }}" autocomplete="off" maxlength="50" required>
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
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="birthdate" class="form-label">{{ __('user.data.birthdate') }}:
                                </label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate"
                                    value="{{ !empty($user->roleData->birthdate) ? $user->roleData->birthdate : '' }}">
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="phone" class="form-label">{{ __('user.data.phone') }}: </label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ !empty($user->roleData->phone) ? $user->roleData->phone : '' }}"
                                    autocomplete="off" maxlength="10" placeholder="0000000000">
                            </div>

                            <div class="col-lg-4 col-12 mb-3">
                                <label for="company_phone" class="form-label">{{ __('user.data.company_phone') }}:
                                </label>
                                <input type="text" class="form-control" id="company_phone" name="company_phone"
                                    autocomplete="off" maxlength="10"
                                    value="{{ !empty($user->roleData->company_phone) ? $user->roleData->company_phone : '' }}"
                                    placeholder="0000000000">
                            </div>

                            <div class="col-lg-6 col-12 mb-3">
                                <label for="address" class="form-label">{{ __('user.data.address') }}:
                                </label>
                                <input type="text" class="form-control" id="address" name="address"
                                    placeholder="#00 Example"
                                    value="{{ !empty($user->roleData->address) ? $user->roleData->address : '' }}"
                                    autocomplete="off" maxlength="50">
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="colony" class="form-label">{{ __('user.data.colony') }}:
                                </label>
                                <input type="text" class="form-control" id="colony" name="colony"
                                    value="{{ !empty($user->roleData->colony) ? $user->roleData->colony : '' }}"
                                    placeholder="Example" maxlength="50">
                            </div>
                            <div class="col-lg-3 col-12 mb-3">
                                <label for="zip_code" class="form-label">{{ __('user.data.zip_code') }}:
                                </label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" min=10000
                                    placeholder="00000"
                                    value="{{ !empty($user->roleData->zip_code) ? $user->roleData->zip_code : '' }}"
                                    maxlength="5">
                            </div>
                            <div class="col-lg-3 col-12 mb-3">
                                <label for="country" class="form-label">{{ __('user.data.country') }}:
                                </label>
                                <input type="text" class="form-control " value="México" disabled>
                                <input type="hidden" id="country" name="country" value="México">
                            </div>
                            <div class="col-lg-3 col-12 mb-3">
                                <label for="state" class="form-label">{{ __('user.data.state') }}:
                                </label>
                                <select class="form-select " id="state" name="state" onchange="load_city()">
                                    <option value="" selected disabled hidden>{{ __('user.data.state') }}
                                    </option>
                                    @foreach ($states as $state)
                                        @if (!is_null($user->roleData) && isset($user->roleData->state) && $state['key'] == $user->roleData->state)
                                            <option value="{{ $state['key'] }}" selected>{{ $state['name'] }}
                                            </option>
                                        @else
                                            <option value="{{ $state['key'] }}">{{ $state['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-12 mb-3">
                                <label for="city" class="form-label">{{ __('user.data.city') }}: </label>
                                <select type="text" class="form-select " id="city" name="city">
                                    <option value="" selected disabled hidden>{{ __('user.data.city') }}
                                    </option>
                                    @foreach ($states as $state)
                                        @if (!is_null($user->roleData) && isset($user->roleData->state) && $state['key'] == $user->roleData->state)
                                            @foreach ($cities[$state['key']] as $city)
                                                @if (!is_null($user->roleData->city) && $city == $user->roleData->city)
                                                    <option value="{{ $city }}" selected>{{ $city }}
                                                    </option>
                                                @else
                                                    <option value="{{ $city }}">{{ $city }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-4 col-12 mb-3">
                                <label for="curp" class="form-label">{{ __('user.data.curp') }}: </label>
                                <input type="text" class="form-control" id="curp" name="curp"
                                    value="{{ !empty($user->roleData->curp) ? $user->roleData->curp : '' }}"
                                    autocomplete="off" minlength="18" maxlength="18" placeholder="ABCD010203HDFGHI01"
                                    oninput="this.value = this.value.toUpperCase()" />
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="nss" class="form-label">{{ __('user.data.nss') }}: </label>
                                <input type="text" class="form-control" id="nss" name="nss"
                                    value="{{ !empty($user->roleData->nss) ? $user->roleData->nss : '' }}"
                                    autocomplete="off" placeholder="12345678900" minlength="11" maxlength="11"
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="rfc" class="form-label">{{ __('user.data.rfc') }}: </label>
                                <input type="text" class="form-control" id="rfc" name="rfc"
                                    value="{{ !empty($user->roleData->rfc) ? $user->roleData->rfc : '' }}"
                                    autocomplete="off" minlength="12" maxlength="13" placeholder="ABCD010203XYZ"
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12 mb-3">
                    <div class="border rounded shadow p-3">
                        <div class="row">
                            <div class="fw-bold mb-2 fs-5">Datos empresariales del usuario</div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="company" class="form-label is-required">{{ __('user.data.company') }}:
                                </label>
                                <select class="form-select " id="company" name="company_id" required>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ $company->id == $user->company_id ? 'selected' : '' }}>
                                            {{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="role" class="form-label is-required">{{ __('user.data.role') }}:
                                </label>
                                <select class="form-select " id="role" name="role_id"
                                    onchange="set_role_restiction()" {{ $user->role_id == 3 ? 'disabled' : '' }} required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $role->id == $user->role_id ? 'selected' : '' }}>
                                            {{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="department" class="form-label is-required">{{ __('user.data.department') }}:
                                </label>
                                <select class="form-select " id="wk-department"
                                    onchange="$('#work-department').val(this.value)"
                                    {{ $user->role_id == 3 ? 'disabled' : '' }}>
                                    @foreach ($work_departments as $department)
                                        <option class="option-department" value="{{ $department->id }}"
                                            {{ $department->id == $user->work_department_id ? 'selected' : '' }}>
                                            {{ $department->name }} </option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="work-department" name="work_department_id"
                                    value="{{ $user->work_department_id }}">
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="status" class="form-label is-required">{{ __('user.data.status') }}:
                                </label>
                                <select class="form-select " id="status" name="status_id" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}"
                                            {{ $status->id == $user->status_id ? 'selected' : '' }}>
                                            {{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="branch"
                                    class="form-label is-required">{{ __('user.data.assigned_branch') }}: </label>
                                <select class="form-select " id="branch" name="branch_id" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ $branch->id == $user->branch_id ? 'selected' : '' }}>
                                            {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="hiredate" class="form-label">{{ __('user.data.hiredate') }}:
                                </label>
                                <input type="date" class="form-control" id="hiredate" name="hiredate"
                                    value="{{ !empty($user->roleData->hiredate) ? $user->roleData->hiredate : '' }}">
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="salary" class="form-label">{{ __('user.data.salary') }}:
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="salary" name="salary"
                                        min="0" placeholder="00" value="{{ $user->roleData->salary ?? '' }}" />
                                </div>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="clabe" class="form-label">{{ __('user.data.clabe') }}: </label>
                                <input type="TEXT" class="form-control" id="clabe" name="clabe" minlength="16"
                                    maxlength="16"
                                    value="{{ !empty($user->roleData->clabe) ? $user->roleData->clabe : '' }}" min=0
                                    placeholder="012345678901234567" oninput="this.value = this.value.toUpperCase()">
                            </div>

                            <div class="col-lg-4 col-12 mb-3">
                                <label for="contract" class="form-label">{{ __('user.data.contract_type') }}:
                                </label>
                                <select class="form-select " id="contract" name="contract">
                                    @foreach ($contracts as $contract)
                                        <option value="{{ $contract->id }}"
                                            {{ $user->contracts->last()->contract_type_id == $contract->id ? 'selected' : '' }}>
                                            {{ $contract->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="contract_startdate" class="form-label">{{ __('user.data.startdate') }}:
                                </label>
                                <input type="date" class="form-control" id="contract_startdate"
                                    name="contract_startdate" value="{{ $dates['startdate'] }}">
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="contract_enddate" class="form-label">{{ __('user.data.enddate') }}:
                                </label>
                                <input type="date" class="form-control" id="contract_enddate" name="contract_enddate"
                                    value="{{ $dates['enddate'] }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="fw-bold mb-2 fs-5">Archivos del usuario</div>
                        <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal"
                            data-bs-target="#filesModal" data-bs-config='{"backdrop":"static"}'>Agregar archivo</button>
                        <button type="button" class="btn btn-warning btn-sm mb-3" data-bs-toggle="modal"
                            data-bs-target="#filesModalAdd" data-bs-config='{"backdrop":true}'>Crear archivo</button>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Archivo</th>
                                        <th scope="col">Fecha de expiración</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!$user->files->isEmpty())
                                        @foreach ($user->files as $file)
                                            <tr>
                                                <td>{{ $file->filename->name ??  $file->file_name}}</td>
                                                <td>
                                                    <a href="{{ route('user.file.download', ['id' => $file->id]) }}"
                                                        class="btn btn-link{{ $file->verifyPath() ? '' : ' disabled' }}">
                                                        {{ $file->verifyPath() ? basename($file->path) : '-' }}
                                                    </a>
                                                </td>
                                                <td>{{ $file->expirated_at ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('user.file.destroy', ['fileId' => $file->id]) }}" class="btn btn-danger btn-sm" onclick="return confirm('Estas seguro de eliminar el archivo?')">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <a class="text-link" href="https://onlinesignature.com/draw-a-signature-online" target="_blank">
                            Para dibujar la
                            firma y descargar la imagen, haz click aqui </a>
                    </div>

                </div>
            </div>
            <button class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
        </form>
    </div>

    @include('user.modals.files.create')
    @include('user.modals.files.add')

    <input type="hidden" id="url-directories" value="{{ route('user.directories') }}" />

    <script src="{{ asset('js/user/actions.min.js') }}"></script>
    <script src="{{ asset('js/customer.min.js') }}"></script>

    <script type="text/javascript">
        function load_city() {
            var select_state = $("#state");
            var select_city = $("#city");
            var state = select_state.val();

            select_city.html('<option value="" selected disabled hidden>Selecciona un municipio</option>');

            if (state !== "") {
                var cities = @json($cities);
                var cityOptions = cities[state].map(city => `<option value="${city}">${city}</option>`);

                select_city.append(cityOptions.join(''));
            }
        }
    </script>
@endsection
