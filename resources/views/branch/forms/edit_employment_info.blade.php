<form class="border shadow rounded m-3 p-4" method="POST" action="/users/update/{{ $user->id }}" enctype="multipart/form-data">
    @csrf    
    <input type="hidden" name="type_data" value="2">
    <h2 class="text-start fs-5 fw-bold mb-2 pb-2">{{ __('modals.title.employment_information') }}</h2>
    <div class="row mb-3">
        <div class="col">
            <label for="role" class="form-label">{{ __('modals.data.role') }}: </label>
            <select class="form-select" id="role" name="role">
                @foreach ($roles as $role)
                    @if ($role->id == $user->role_id)
                        <option value="{{ $role->id }}" selected>{{ $role->name }}</option>
                    @else
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col">
            <label for="department" class="form-label">{{ __('modals.data.department') }}: </label>
            <select class="form-select" id="work_department" name="work_department">
                @foreach ($work_departments as $department)
                    @if ($department->id == $user->work_department_id)
                        <option value="{{ $department->id }}" selected>{{ $department->name }}</option>
                    @else
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col">
            <label for="status" class="form-label">{{ __('modals.data.status') }}: </label>
            <select class="form-select" id="status" name="status">
                @foreach ($statuses as $status)
                    @if ($status->id == $user->status_id)
                        <option value="{{ $status->id }}" selected>{{ $status->name }}</option>
                    @else
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="branch" class="form-label">{{ __('modals.data.assigned_branch') }}: </label>
            @foreach ($branches as $branch)
                @if ($branch->id == $user_adit_data->branch_id)
                    <input type="text" class="form-control" id="branch" name="branch" value="{{ $branch->name }}"
                        >
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                @endif
            @endforeach
        </div>
        <div class="col-3">
            <div class="col-3"></div>
            <label for="hiredate" class="form-label">{{ __('modals.data.hiredate') }}: </label>
            <input type="date" class="form-control" id="hiredate" name="hiredate" value="{{ $user_adit_data->hiredate }}" >
        </div>
        <div class="col-3">
            <label for="salary" class="form-label">{{ __('modals.data.salary') }}: </label>
            <input type="text" class="form-control" id="salary" name="salary" value="{{ $user_adit_data->salary }}" >
        </div>
    </div>
    <div class="row">
        <div class="mb-0">
            <button type="submit" class="btn btn-primary">{{ __('modals.button.save') }}</button>
        </div>
    </div>
</form>