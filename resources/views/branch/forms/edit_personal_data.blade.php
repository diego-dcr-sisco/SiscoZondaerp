<form class="border shadow rounded m-3 p-4" method="POST" action="/users/update/{{ $user->id }}" enctype="multipart/form-data">
    @csrf    
    <input type="hidden" name="type_data" value="1">
    <h2 class="text-start fs-5 fw-bold mb-2 pb-2">{{ __('modals.title.personal_data') }}</h2>
    <div class="row">
        <div class="col mb-2">
            <label for="name" class="form-label">{{ __('modals.data.name') }}: </label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" >
        </div>
        <div class="col mb-2">
            <label for="nickname" class="form-label">{{ __('modals.data.nickname') }}: </label>
            <input type="text" class="form-control" id="nickname" name="nickname" value="{{ $user->nickname }}" >
        </div>
    </div>
    <div class="row">
        <div class="col mb-2">
            <label for="email" class="form-label">{{ __('modals.data.email') }}: </label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" >
        </div>
        <div class="col mb-2">
            <label for="phone" class="form-label">{{ __('modals.data.phone') }}: </label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ $user_adit_data->phone }}" >
        </div>
        <div class="col mb-2">
            <label for="birthdate" class="form-label">{{ __('modals.data.birthdate') }}: </label>
            <input type="date" class="form-control" id="birthdate" name="birthdate" value="{{ $user_adit_data->birthdate }}" >
        </div>
    </div>
    <div class="row">
        <div class="col mb-2">
            <label for="address" class="form-label">{{ __('modals.data.address') }}: </label>
            <input type="text" class="form-control" id="address" name="address" value="{{ $user_adit_data->address }}" >
        </div>
    </div>
    <div class="row">
        <div class="col mb-2">
            <label for="curp" class="form-label">{{ __('modals.data.curp') }}: </label>
            <input type="text" class="form-control" id="curp" name="curp" value="{{ $user_adit_data->curp }}" >
        </div>
        <div class="col mb-3">
            <label for="nss" class="form-label">{{ __('modals.data.nss') }}: </label>
            <input type="text" class="form-control" id="nss" name="nss" value="{{ $user_adit_data->nss }}" >
        </div>
        <div class="col mb-3">
            <label for="rfc" class="form-label">{{ __('modals.data.rfc') }}: </label>
            <input type="text" class="form-control" id="rfc" name="rfc" value="{{ $user_adit_data->rfc }}" >
        </div>
    </div>
    <div class="row">
        <div class="mb-0">
            <button type="submit" class="btn btn-primary">{{ __('modals.button.save') }}</button>
        </div>
    </div>
</form>