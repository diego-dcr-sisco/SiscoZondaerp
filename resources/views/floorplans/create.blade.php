<div class="modal fade" id="createFloorplanModal" tabindex="-1" aria-labelledby="createFloorplanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST"
            action="{{ route('floorplan.store', ['customerId' => $customer->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="createFloorplanModalLabel">
                    Agregar plano de planta
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="filename" class="form-label is-required">Nombre:
                    </label>
                    <input type="text" class="form-control" id="filename" name="filename" placeholder="Example"
                        required />
                </div>
                <div class="mb-3">
                    <label for="filename" class="form-label">Servicio:
                    </label>
                    <select class="form-select" name="service_id">
                        <option value="">Sin servicio</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="formFile" class="form-label is-required">Layout (Imagen)</label>
                    <input class="form-control" accept=".png, .jpg, .jpeg" type="file" name="file" required>
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}" />
                    <div class="form-text">Solo se permiten archivos con formato .JPG
                        .JPEG .PNG</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    {{ __('buttons.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ __('buttons.store') }}
                </button>
            </div>
        </form>
    </div>
</div>
