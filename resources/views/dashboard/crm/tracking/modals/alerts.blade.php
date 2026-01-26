<div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Seguimientos pendientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loadingIndicator" class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    <p class="mt-2">Buscando trackings pendientes...</p>
                </div>
                <div id="pendientesContent" style="display: none;">
                    <p>Total de pendientes: <span id="pendientesCount" class="text-danger">0</span></p>
                    <div class="table-responsive">
                        
                    </div>
                </div>
                <div id="pendientesError" class="alert alert-danger" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

 <script>
        const user_wkdm_id = {{ auth()->user()->work_department_id }};
        const statusMap = {
            'active': 'Activo',
            'completed': 'Completado',
            'canceled': 'Cancelado'
        };


        function loadTrackings() {
            // Mostrar loading, ocultar contenido y error
            $('#loadingIndicator').show();
            $('#pendientesContent').hide();
            $('#pendientesError').hide();

            $.ajax({
                url: "{{ route('crm.tracking.pending') }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Actualizar el contador
                        $('#pendientesCount').text(response.count);

                        // Limpiar tabla
                        $('#pendientesTableBody').empty();

                        // Llenar la tabla con los datos
                        if (response.trackings.length > 0) {
                            $.each(response.trackings, function(index, tracking) {
                                let fechaFormateada = tracking.next_date ? new Date(tracking
                                    .next_date).toLocaleDateString() : 'Sin fecha';

                                $('#pendientesTableBody').append(`
                                <tr>
                                    <td>${tracking.customer ?? 'N/A'}</td>
                                    <td>
                                      ${tracking.order ? `<a href="${tracking.order.url}" class="text-primary">${tracking.order.folio}</a>` : '-'}
                                    </td>
                                    <td>${tracking.next_date ?? ''}</td>
                                    <td>${tracking.title ?? '-'}</td>
                                    <td class="text-${tracking.status == 'active' ? 'success' : (tracking.status == 'completed' ? 'primary' : 'danger')}">
                                     ${statusMap[tracking.status] || tracking.status}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                        ${tracking.status != 'canceled' ? 
                                        `
                                            <a href="${tracking.edit_url}" class="btn btn-secondary">
                                                Editar
                                            </a>
                                            <a href="${tracking.auto_url}" class="btn btn-warning" onclick="return confirm('La reprogramación se realiza entorno a la frecuencia configurada, ¿Estas seguro de continuar?')">
                                                Reprogramar
                                            </a>
                                            <button class="btn btn-primary" onclick="completedModal(${tracking.id})">
                                                Completar
                                            </button>
                                            <button class="btn btn-danger" onclick="cancelModal(${tracking.id})">
                                                Cancelar
                                            </button>
                                            <a href="${tracking.destroy_url}" class="btn btn-outline-danger" onclick="return confirm('Estas seguro de eliminar el seguimiento?')"><i class="bi bi-trash-fill"></i></a>
                                        ` : `<a href="${tracking.edit_url}" class="btn btn-secondary">
                                                Editar
                                            </a>
                                            <a href="${tracking.destroy_url}" class="btn btn-outline-danger" onclick="return confirm('Estas seguro de eliminar el seguimiento?')"><i class="bi bi-trash-fill"></i></a>
                                            `}
                                        </div>
                                    </td>
                                </tr>
                            `);
                            });
                        } else {
                            $('#pendientesTableBody').append(`
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay seguimientos activos para esta semana</td>
                            </tr>
                        `);
                        }

                        // Mostrar contenido
                        $('#loadingIndicator').hide();
                        $('#pendientesContent').fadeIn();
                    }
                },
                error: function(xhr) {
                    $('#loadingIndicator').hide();
                    $('#pendientesError').text('Error al cargar los trackings: ' + xhr.statusText)
                        .fadeIn();
                }
            });
        }
    </script>