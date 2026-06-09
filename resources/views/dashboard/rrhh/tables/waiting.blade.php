<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0 rrhh-table">
        <thead class="table-light">
            <tr>
                <th scope="col">Colaborador</th>
                <th scope="col">Rol / Departamento</th>
                <th scope="col">Contacto</th>
                <th scope="col">Estado</th>
                <th scope="col" class="text-center">Expediente</th>
                <th scope="col" class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                @php
                    $missingCount = (int) ($user->missing_files_count ?? 0);
                    $expiredCount = (int) ($user->expired_files_count ?? 0);
                    $expiringCount = (int) ($user->expiring_files_count ?? 0);
                    $hasAlerts = $missingCount + $expiredCount + $expiringCount > 0;
                    $phone = $user->roleData->phone ?? '-';
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold">{{ $user->name }}</div>
                        <div class="small text-muted">{{ $user->email ?? $user->username ?? '-' }}</div>
                    </td>
                    <td>
                        <div>{{ $user->simpleRole->name ?? '-' }}</div>
                        <div class="small text-muted">{{ $user->workDepartment->name ?? 'Sin departamento' }}</div>
                    </td>
                    <td>{{ $phone }}</td>
                    <td>
                        <span class="badge {{ ($user->status->id ?? null) == 2 ? 'text-bg-success' : 'text-bg-warning' }}">
                            {{ $user->status->name ?? 'Sin estado' }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if ($hasAlerts)
                            <div class="d-flex justify-content-center flex-wrap gap-1">
                                @if ($missingCount > 0)
                                    <span class="badge text-bg-danger">Faltan {{ $missingCount }}</span>
                                @endif
                                @if ($expiredCount > 0)
                                    <span class="badge text-bg-dark">Vencidos {{ $expiredCount }}</span>
                                @endif
                                @if ($expiringCount > 0)
                                    <span class="badge text-bg-warning">Por vencer {{ $expiringCount }}</span>
                                @endif
                            </div>
                        @else
                            <span class="badge text-bg-success">Sin alertas</span>
                        @endif
                        <div class="small text-muted mt-1">{{ $user->total_files_count ?? 0 }} documentos registrados</div>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('user.show', ['id' => $user->id, 'section' => 1]) }}"
                                class="btn btn-outline-primary" title="Ver usuario">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            @can('write_user')
                                <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-outline-secondary"
                                    title="Editar expediente">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No se encontraron colaboradores con los filtros actuales.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="p-3 border-top">
    {{ $users->links('pagination::bootstrap-5') }}
</div>
