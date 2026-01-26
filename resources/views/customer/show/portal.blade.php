@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.index.sedes') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                PORTAL DE LA SEDE </span> <span class="ms-2 fs-4"> {{ $customer->name }}</span>
            </span>
        </div>

        <form action="{{ route('customer.update', ['id' => $customer->id ]) }}" method="POST" class="m-3" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-6 col-12">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="fw-bold mb-2 fs-5">Datos para el portal del cliente</div>
                        <div class="row">
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="name" class="form-label is-required">Nombre usuario del portal</label>
                                <input type="text" class="form-control" id="portal-username" name="username"
                                    placeholder="Nombre del portal" value="{{ $customer->username }}" required />
                            </div>

                            <div class="col-lg-6 col-12 mb-3">
                                <label for="url" class="form-label is-required">URL del portal</label>
                                <input type="text" class="form-control" id="portal-url" name="url"
                                    placeholder="https://example.com/portal" value="{{ $customer->url }}" required />
                            </div>

                            <div class="col-lg-6 col-12 mb-3">
                                <label for="description" class="form-label">Email</label>
                                <input type="email" class="form-control" id="portal_email" name="portal_email"
                                    placeholder="Email del portal" value="{{ $customer->portal_email }}"
                                    autocomplete="off" />
                            </div>

                            <div class="col-lg-6 col-12 mb-3">
                                <label for="logo" class="form-label">Contraseña</label>
                                <input type="text" class="form-control" id="password" name="password"
                                    placeholder="Contraseña del portal" value="{{ $customer->password }}"
                                    autocomplete="off" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="fw-bold mb-2 fs-5">Usuarios ligados a la sede</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th class="fw-bold" scope="col">Usuario</th>
                                        <th class="fw-bold" scope="col">Email</th>
                                        <th class="fw-bold" scope="col">Contraseña</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($access as $a)
                                        <tr>
                                            <td class="">{{ $a->username }}</td>
                                            <td class="">{{ $a->email }}</td>
                                            @can('write_order')
                                                <td class="">{{ $a->nickname }}</td>
                                            @else
                                                <td class="">****</td>
                                            @endcan
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-danger">Sin usuarios ligados</td>
                                        </tr>
                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary my-3">{{ __('buttons.update') }}</button>

        </form>
    </div>
@endsection
