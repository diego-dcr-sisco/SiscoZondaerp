<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('supplier.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplierModalLabel">Crear proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="name" class="form-label is-required">Nombre</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Example"
                        required>
                </div>
                <div class="mb-3">
                    <label for="rfc" class="form-label is-required">RFC</label>
                    <input type="text" class="form-control" id="rfc" name="rfc" placeholder="XXXX000000XXX"
                        maxlength="13"
                        style="text-transform:uppercase;" oninput="this.value = this.value.toUpperCase()" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        placeholder="example@mail.com">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label is-required">Teléfono</label>
                    <input type="text" class="form-control" id="phone" name="phone" required minlength="10"
                        placeholder="0000000000 ext.4">
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="address" name="address"
                        placeholder="Example #100, Col. Colonia">
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label is-required">Categoría</label>
                    <select class="form-select" id="category" name="category_id" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ __('buttons.store') }}</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"> {{ __('buttons.cancel') }}
                    </button>
                </div>
        </form>
    </div>
</div>
</div>
</div>
