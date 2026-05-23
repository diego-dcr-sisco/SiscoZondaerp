<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST"
            action="{{ route('client.directory.permissions') }}"enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="permissionsModalLabel">Permisos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    @foreach ($users as $user)
                        <li class="list-group-item text-break">
                            <input class="user form-check-input border-secondary me-1" type="checkbox"
                                value="{{ $user->id }}" id="firstCheckbox" onclick="setUsers()">
                            <label class="form-check-label" for="firstCheckbox">{{ $user->name }}: [
                                {{ $user->email }} ]</label>
                        </li>
                    @endforeach
                </ul>

                <input type="hidden" id="directory-id" name="directory_id" />
                <input type="hidden" id="selected-users" name="selected_users" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('buttons.update') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setChecked(rootId) {
        $('#directory-id').val(rootId)
        var users = permissions.find(permission => permission.dirId == rootId).users;
        $('.user').each(function() {
            var valor = parseInt($(this).val());
            if (users.includes(valor)) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }

    function setUsers() {
        var input = '#selected-users'
        var usersClass = '.user';
        var checkedUsers = []
        $(usersClass).each(function() {
            if ($(this).is(':checked')) {
                checkedUsers.push(parseInt($(this).val()));
            }
        });
        $(input).val(JSON.stringify(checkedUsers));
        console.log($(input).val())
    }
</script>
