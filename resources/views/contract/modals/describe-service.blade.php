  <div class="modal fade" id="describeModal" tabindex="-2" aria-labelledby="describeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-xl">
          <div class="modal-content">
              <div class="modal-header">
                  <h1 class="modal-title fs-5" id="describeModalLabel">Describe el servicio</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div class="mb-3">
                      <div class="smnote" id="summary-describe" style="height: 250px"></div>
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
          const summaryDescribeQuill = new Quill('#summary-describe', {
              theme: 'snow',
              modules: {
                  toolbar: [
                      ['bold', 'italic', 'underline', 'strike'],
                      [{ list: 'ordered' }, { list: 'bullet' }],
                      ['link', 'image'],
                      ['clean']
                  ],
                  table: true
              }
          });

          window.getSummaryDescribeHtml = function() {
              const html = summaryDescribeQuill.root.innerHTML;
              return html === '<p><br></p>' ? '' : html;
          };

          window.setSummaryDescribeHtml = function(html) {
              summaryDescribeQuill.clipboard.dangerouslyPasteHTML(html || '');
          };
      });
  </script>
