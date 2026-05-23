<!-- Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('quality.permission.store') }}">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="permissionModalLabel">Relación encargado-cliente</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label is-required">Responsable</label>
                    <select class="form-select" id="user" name="user_id" required>
                        @foreach ($quality_users as $quality_user)
                            <option value="{{ $quality_user->id }}">{{ $quality_user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Cliente Matriz</label>
                    <select class="form-select" id="customer" name="customer_id" required>
                        @foreach ($matrix as $m)
                            @if ($m->administrative_id == null)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('buttons.create') }}</button>
            </div>
        </form>
    </div>
</div>
