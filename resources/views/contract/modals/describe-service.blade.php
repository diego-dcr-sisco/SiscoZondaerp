  <div class="modal fade" id="describeModal" tabindex="-2" aria-labelledby="describeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-xl">
          <div class="modal-content">
              <div class="modal-header">
                  <h1 class="modal-title fs-5" id="describeModalLabel">Describe el servicio</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div class="mb-3">
                      <textarea class="summernote" id="summary-describe" style="font-size:12px;">
                    </textarea>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal"
                      data-bs-target="#editServiceModal">Cancelar</button>
                  <button type="button" class="btn btn-primary" onclick="setDescription()">Guardar</button>
              </div>
          </div>
      </div>
  </div>


  <script>
      $(document).ready(function() {
          $('.summernote').summernote({
              height: 250, // altura del editor
              toolbar: [
                  ['style', ['bold', 'italic', 'underline', 'clear']],
                  ['font', ['fontsize', 'fontname']],
                  ['para', ['ul', 'ol', 'paragraph']],
                  ['height', ['height']],
                  ['insert', ['table', 'link']],
              ],
              fontSize: ['8', '10', '12', '14', '16'],
              lineHeights: ['0.25', '0.5', '1', '1.5', '2'],
              callbacks: {
                  onPaste: function(e) {
                      var thisNote = $(this);
                      var updatePaste = function() {
                          var original = thisNote.summernote('code');
                          var cleaned = cleanPaste(original);
                          thisNote.summernote('code', cleaned);
                      };
                      setTimeout(updatePaste, 10);
                  }
              }
          });
      });
  </script>
