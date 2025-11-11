 <div class="modal fade" id="directoryModal" tabindex="-1" aria-labelledby="directoryModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <form class="modal-content" method="POST"
             action="{{ route('client.directory.store') }}"enctype="multipart/form-data">
             @csrf
             <div class="modal-header">
                 <h1 class="modal-title fs-5" id="directoryModalLabel">Crear carpeta</h1>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <label for="name" class="form-label is-required">Nombre: </label>
                 <input type="text" class="form-control" id="name"
                     name="folder_name" maxlength="50" autocomplete="off" required>
                 <input type="hidden" name="parent_path" value="{{ $data['root_path'] }}" />
             </div>
             <div class="modal-footer">
                 <button type="submit" class="btn btn-primary">{{ __('buttons.store') }}</button>
                 <button type="button" class="btn btn-danger"
                     data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
             </div>
         </form>
     </div>
 </div>
