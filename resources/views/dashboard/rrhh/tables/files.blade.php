<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0 rrhh-table">
        <thead class="table-light">
            <tr>
                <th scope="col">Prioridad</th>
                <th scope="col">Documento</th>
                <th scope="col">Colaborador</th>
                <th scope="col">Rol / Departamento</th>
                <th scope="col">Vencimiento</th>
                <th scope="col" class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($files as $file)
                @php
                    $expiration = $file->expirated_at ? \Carbon\Carbon::parse($file->expirated_at) : null;
                    $isMissing = empty($file->path);
                    $isExpired = !$isMissing && $expiration && $expiration->isPast();
                    $isExpiring = !$isMissing && !$isExpired && $expiration && $expiration->lte($expiresUntil);
                    $documentName = $file->filename->name ?? $file->file_name ?? 'Documento sin nombre';
                    $user = $file->user;
                    $priorityClass = 'text-bg-success';
                    $priorityLabel = 'Vigente';

                    if ($isMissing) {
                        $priorityClass = 'text-bg-danger';
                        $priorityLabel = 'Faltante';
                    } elseif ($isExpired) {
                        $priorityClass = 'text-bg-dark';
                        $priorityLabel = 'Vencido';
                    } elseif ($isExpiring) {
                        $priorityClass = 'text-bg-warning';
                        $priorityLabel = 'Por vencer';
                    }
                @endphp
                <tr>
                    <td>
                        <span class="badge {{ $priorityClass }}">{{ $priorityLabel }}</span>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $documentName }}</div>
                        <div class="small text-muted">{{ $file->path ? basename($file->path) : 'Sin archivo adjunto' }}</div>
                    </td>
                    <td>
                        <div>{{ $user->name ?? 'Usuario no disponible' }}</div>
                        <div class="small text-muted">{{ $user->email ?? '-' }}</div>
                    </td>
                    <td>
                        <div>{{ $user->simpleRole->name ?? '-' }}</div>
                        <div class="small text-muted">{{ $user->workDepartment->name ?? 'Sin departamento' }}</div>
                    </td>
                    <td>
                        @if ($expiration)
                            <span class="{{ $isExpired ? 'text-danger fw-bold' : ($isExpiring ? 'text-warning fw-bold' : '') }}">
                                {{ $expiration->format('d/m/Y') }}
                            </span>
                            <div class="small text-muted">
                                @if ($isExpired)
                                    Vencio hace {{ $expiration->diffInDays(now()) }} dias
                                @elseif ($isExpiring)
                                    Faltan {{ now()->diffInDays($expiration) }} dias
                                @else
                                    Vigente
                                @endif
                            </div>
                        @else
                            <span class="text-muted">Sin fecha</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            @if (!$isMissing)
                                <a href="{{ route('user.file.download', ['id' => $file->id]) }}" class="btn btn-outline-primary"
                                    title="Descargar">
                                    <i class="bi bi-download"></i>
                                </a>
                            @endif
                            @if ($user)
                                <a href="{{ route('user.edit', ['id' => $file->user_id]) }}" class="btn btn-outline-secondary"
                                    title="Editar expediente">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No hay documentos para mostrar con los filtros actuales.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="p-3 border-top">
    {{ $files->links('pagination::bootstrap-5') }}
</div>
