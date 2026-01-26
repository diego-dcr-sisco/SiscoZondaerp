<table class="table-responsive table text-start">
    <tbody>
        @if($users[$i]->status_id == 3)
        <tr class="table-secondary">
        @else
        <tr>
        @endif
            <th scope="row">#</th>
            <td>{{ $users[$i]->id }}</td>
        </tr>
        @if($users[$i]->status_id == 3)
        <tr class="table-secondary">
        @else
        <tr>
        @endif
            <th scope="row">{{ __('user.data.name') }}:</th>
            <td>{{ $users[$i]->name }}</td>
        </tr>
        @if($users[$i]->status_id == 3)
        <tr class="table-secondary">
        @else
        <tr>
        @endif
            <th scope="row">{{ __('user.data.email') }}:</th>
            <td>{{ $users[$i]->email }}</td>
        </tr>
        @if($users[$i]->status_id == 3)
        <tr class="table-secondary">
        @else
        <tr>
        @endif
            <th scope="row">{{ __('user.data.role') }}:</th>
            <td>
                @foreach ($roles as $role)
                @if ($role->id == $users[$i]->role_id)
                    {{ $role->name }}
                @endif
                @endforeach
            </td>
        </tr>
        @if($users[$i]->status_id == 3)
        <tr class="table-secondary">
        @else
        <tr>
        @endif
            <th scope="row">{{ __('user.data.department') }}:</th>
            <td>
                @foreach ($work_departments as $work_department)
                @if ($work_department->id == $users[$i]->work_department_id)
                    {{ $work_department->name }}
                @endif
                @endforeach
            </td>
        </tr>
        @if($users[$i]->status_id == 3)
        <tr class="table-secondary">
        @else
        <tr>
        @endif
            <th scope="row">{{ __('user.data.status') }}:</th>
            <td>
                @foreach ($statuses as $status)
                @if ($status->id == $users[$i]->status_id)
                    {{ $status->name }}
                @endif
                @endforeach
            </td>
        </tr>
        @if($users[$i]->status_id == 3)
        <tr class="table-secondary">
        @else
        <tr>
        @endif
            <th scope="row">{{ __('user.data.functions') }}:</th>
            <td>
                <a href="{{ route('user.show', ['id' => $users[$i]->id])}}" class="btn btn-info">
                    <i class="bi bi-eye-fill"></i>
                </a>
                <a href="{{ route('user.edit', ['id' => $users[$i]->id])}}" class="btn btn-secondary"><i
                        class="bi bi-pencil-square"></i></a>
            </td>
        </tr>
    </tbody>
</table>