@extends('layouts.app')
@section('content')
    @php
        function formatPath($path)
        {
            return str_replace(['/', ' '], ['-', ''], $path);
        }
    @endphp

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('user.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR USUARIO INTERNO
            </span>
        </div>


        <form class="m-3" method="POST" action="{{ route('user.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3" style="background-color: #ffffff">
                <div class="row">
                    <div class="fw-bold mb-2 fs-5">Datos del empleado</div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('user.data.name') }}
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Example"
                            autocomplete="off" maxlength="50" required />
                    </div>

                    <div class="col-lg-6 col-12 mb-3">
                        <label for="username" class="form-label is-required">{{ __('user.data.username') }}
                        </label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Example"
                            autocomplete="off" maxlength="20" required />
                    </div>

                    <div class="col-lg-6 col-12 mb-3">
                        <label for="email" class="form-label is-required">
                            {{ __('user.data.email') }}
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="email-text" name="email-text"
                                placeholder="example" onblur="$('#email').val(this.value + '@siscoplagas');" required />
                            <span class="input-group-text" id="email-type">@siscoplagas</span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-12 mb-3">
                        <label for="password" class="form-label is-required">{{ __('user.data.password') }} </label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="password" name="password" placeholder="********"
                                autocomplete="off" maxlength="20" required>
                            <button class="btn btn-warning" type="button" onclick="generatePassword()"><i
                                    class="bi bi-arrow-clockwise"></i></button>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="branch" class="form-label">{{ __('user.data.branch') }}
                        </label>
                        <div class="input-group flex-nowrap">
                            <select class="form-select" id="branch" name="branch_id" autocomplete="off">
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4 col-12 mb-3">
                        <label for="role" class="form-label">{{ __('user.data.role') }}
                        </label>
                        <select class="form-select" id="role" name="role_id" onchange="restiction(this.value)">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-12 mb-3">
                        <label for="work-department" class="form-label">{{ __('user.data.department') }}
                        </label>
                        <select class="form-select" id="wk-department" onchange="$('#work-department').val(this.value)">
                            @foreach ($work_departments as $department)
                                <option class="option-department" value="{{ $department->id }}">
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <input type="hidden" name="type" value="1" />
                <input type="hidden" id="email" name="email" />
                <input type="hidden" id="work-department" name="work_department_id"
                    value="{{ $work_departments[0]['id'] }}" />


            </div>

            <button type="submit" class="btn btn-primary my-3">
                {{ __('buttons.store') }}
            </button>
        </form>

    </div>
    <script src="{{ asset('js/user/actions.min.js') }}"></script>
@endsection
