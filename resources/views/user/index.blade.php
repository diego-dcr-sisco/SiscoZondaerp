    @extends('layouts.app')
    @section('content')
        @include('components.page-header', [
            'title' => 'USUARIOS',
            'icon' => 'bi-people',
            'actionRoute' => route('user.create'),
            'actionText' => __('user.title.create'),
        ])
        <div class="container-fluid">
            <!-- Buscador -->
            <div class="mb-3">
                @include('user.search')
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped caption-top">
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
                                    <div class="d-flex gap-2 justify-content-center">
                                        @can('write_user')
                                            <a href="{{ $user->role_id != 5 ? route('user.edit', ['id' => $user->id]) : route('user.edit.client', ['id' => $user->id]) }}"
                                                class="btn btn-secondary btn-sm" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Editar usuario">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <a href="{{ route('user.locations', ['id' => $user->id]) }}"
                                                class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Ubicaciones GPS">
                                                <i class="bi bi-geo-alt-fill"></i>
                                            </a>

                                        @endcan
                                    </div>
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
