<div class="modal fade" id="technicianModal{{ $contract->id }}" tabindex="-1" aria-labelledby="technicianModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <form class="modal-content" method="POST" action="{{ Route('contract.update.technicians', ['id' => $contract->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="technicianModalLabel">Técnicos [Contrato {{ $contract->id }}]</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <li class="list-group-item ">
                        <div class="form-check">
                            <input class="form-check-input me-1 technician{{ $contract->id }}" type="checkbox" value="0"
                                id="technician-0" onchange="setModalTechnician({{$contract->id}})">
                            <label class="form-check-label fw-bold" for="firstCheckbox">
                                Todos los técnicos
                            </label>
                        </div>
                    </li>
                    @foreach ($technicians as $technician)
                        <li class="list-group-item">
                            <input class="form-check-input me-1 technician{{ $contract->id }}" type="checkbox"
                                value="{{ $technician->id }}" id="technician{{ $technician->id }}"
                                {{ $contract->hasTechnician($technician->id) ? 'checked' : '' }}
                                onchange="setModalTechnician({{ $contract->id }})">
                            <label class="form-check-label" for="firstCheckbox">{{ $technician->user->name }}</label>
                        </li>
                    @endforeach
                </ul>
                <input type="hidden" name="technicians" id="technicians{{ $contract->id }}" value="">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"
                    onclick="console.log($('#technicians').val())">{{ __('buttons.update') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        setModalTechnician({{ $contract->id }})
    })
</script>
