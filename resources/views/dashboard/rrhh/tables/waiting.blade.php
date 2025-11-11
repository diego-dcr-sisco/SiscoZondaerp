<table class="table table-responsive table-bordered table-striped text-center table-hover   ">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Ver</th>
            <th scope="col">Usuario</th>
            {{-- <th scope="col">{{ __('user.data.email') }}</th> --}}
            <th scope="col">{{ __('user.data.phone') }}</th>
            <th scope="col">{{ __('user.data.role') }}</th>
            <th scope="col">Archivos</th>
            {{-- <th scope="col">{{ __('user.data.department') }}</th> --}}
            <th scope="col">{{ __('user.data.status') }}</th>
            <th scope="col">Editar</th>
        </tr>
    </thead>
    <tbody class="align-middle">    
        @foreach ($users as $user)
            <tr>
                <th scope="row"> {{ $loop->iteration }} </th>
                <td>
                    <a href="{{ route('user.show', ['id' => $user->id, 'section' => 1]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye-fill"></i>
                    </a>
                </td>
                <td> 
                    {{ $user->name }}
                    <small class="text-muted d-block" title="{{ $user->email }}">
                        {{ Str::limit($user->email, 50) }}
                    </small> 
                </td>
                {{-- <td> {{ $user->email }}</td> --}}
                <td> {{ isset($user->roleData->phone) ? $user->roleData->phone : '-' }} </td>
                <td> 
                    {{ $user->simpleRole->name ?? '-' }}
                    <small class="text-muted d-block" title="{{ $user->workDepartment->name ?? '-' }}">
                        {{ Str::limit($user->workDepartment->name ?? '-', 50) }}
                    </small>
                </td>
                <td>
                    @if($user->simpleRole->name != 'Cliente')
                        @if (!empty($user->pendingFiles) && $user->pendingFiles->count() > 0 )
                            <span class="badge bg-danger">
                                Faltantes: {{ $user->pendingFiles->count() }}
                            </span>
                            <small class="text-muted">
                                @foreach($user->pendingFiles as $file)
                                    <span class="d-block">{{ $file->name }}</span>
                                @endforeach
                            </small>
                        @else
                            <span class="badge bg-success">Completo</span>
                        @endif
                    @else
                        <span class="badge bg-secondary">Cliente</span>
                    @endif
                </td>
                {{-- <td> {{ $user->workDepartment->name ?? '-' }} </td> --}}
                <td class="fw-bold {{ $user->status->id == 2 ? 'text-success' : 'text-danger' }}"> {{ $user->status->name }} </td>
                <td>
                    @can('write_user')
                        <a href="{{ route('user.edit', ['id' => $user->id, 'section' => 1]) }}"
                            class="btn btn-secondary btn-sm">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $users->links('pagination::bootstrap-5') }}
