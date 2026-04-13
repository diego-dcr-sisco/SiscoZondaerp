 <div class="modal fade" id="directoryModal" tabindex="-1" aria-labelledby="directoryModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <form class="modal-content" method="POST" id="directoryForm" action="{{ route('client.directory.store') }}"
             enctype="multipart/form-data">
             @csrf
             <div class="modal-header">
                 <h1 class="modal-title fs-5" id="directoryModalLabel">Crear carpeta</h1>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <div class="alert alert-light border py-2 px-3 mb-3 small">
                     <span class="fw-semibold">Ruta actual:</span>
                     <span id="parentPathPreview" class="text-break"></span>
                 </div>
                 <label for="name" class="form-label is-required">Nombre: </label>
                 <input type="text" class="form-control" id="name" name="folder_name" maxlength="1024"
                     autocomplete="off" required>
                 <input type="hidden" name="parent_path" value="{{ $data['root_path'] }}" />
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-danger"
                     data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                 <button type="submit" class="btn btn-primary" id="btnCreateDir">{{ __('buttons.store') }}</button>
             </div>
         </form>
     </div>
 </div>

 <script>
     $(document).ready(function() {
         let isSubmitting = false;
         const initialParentPath = $('#directoryForm input[name="parent_path"]').val();

         function refreshParentPathPreview() {
             const parentPath = ($('#directoryForm input[name="parent_path"]').val() || '').trim();
             $('#parentPathPreview').text(parentPath || '(sin ruta)');
         }

         function getParentPathFromCurrentRoute() {
             const pathname = window.location.pathname || '';
             const routeMatch = pathname.match(/\/clients\/(system|mip)\/(.+)$/);

             if (!routeMatch || !routeMatch[2]) {
                 return null;
             }

             const currentPath = decodeURIComponent(routeMatch[2]).replace(/\/+$/, '');
             return currentPath || null;
         }

         // Restaurar el parent_path original entregado por backend al abrir el modal.
         $('#directoryModal').on('show.bs.modal', function() {
             $('#directoryForm input[name="parent_path"]').val(initialParentPath);
             refreshParentPathPreview();
         });

         $('#directoryForm').on('submit', function(e) {
             if (isSubmitting) {
                 e.preventDefault();
                 return false;
             }

             const parentPathInput = $('#directoryForm input[name="parent_path"]');
             const parentPathValue = (parentPathInput.val() || '').trim();

             // Solo usar la URL como respaldo cuando el backend no envía parent_path.
             if (!parentPathValue) {
                 const fallbackPath = getParentPathFromCurrentRoute();
                 if (fallbackPath) {
                     parentPathInput.val(fallbackPath);
                 }
             }

             refreshParentPathPreview();

             const folderName = $('#name').val().trim();
             if (!folderName) {
                 e.preventDefault();
                 alert('El nombre de la carpeta no puede estar vacío');
                 return false;
             }

             const finalParentPath = (parentPathInput.val() || '').trim().replace(/\/+$/, '');
             const targetPath = finalParentPath ? `${finalParentPath}/${folderName}` : folderName;
             alert('Se va a crear la carpeta en:\n' + targetPath);

             isSubmitting = true;
             $('#btnCreateDir').prop('disabled', true).html(
                 '<span class="spinner-border spinner-border-sm me-2"></span>Creando...');
         });

         // Resetear al cerrar el modal
         $('#directoryModal').on('hidden.bs.modal', function() {
             isSubmitting = false;
             $('#btnCreateDir').prop('disabled', false).html('{{ __('buttons.store') }}');
             $('#name').val('');
         });

         refreshParentPathPreview();
     });
 </script>
