<!-- Modal -->
<div class="modal fade" id="performanceModal" tabindex="-1" aria-labelledby="performanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="performanceModalLabel">Rendimiento</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Encargado (Calidad)</label>
                    <input class="form-control" id="perf-user" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cliente (Matriz)</label>
                    <input class="form-control" id="perf-customer" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sedes</label>
                    <div class="accordion" id="accordionSedes"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('buttons.close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    function setPerformance(element) {
    const data = JSON.parse(element.getAttribute("data-performance"));
    let html = '';
    
    console.log(data.user);
    $('#perf-user').val(data.user);
    $('#perf-customer').val(data.customer_matrix);

    data.sedes.forEach((sede, index) => {
        html += `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse${index}" aria-expanded="true" aria-controls="collapse${index}">
                        ${sede.name}
                    </button>
                </h2>
                <div id="collapse${index}" class="accordion-collapse collapse"
                    data-bs-parent="#accordionSedes">
                    <div class="accordion-body p-1">
                        <ul class="list-group list-group-flush">
                            ${
                                sede.orders.map(order => `
                                    <li class="list-group-item">
                                        ${order.status_name}: ${order.count}    
                                    </li>
                                `).join('')
                            }
                        </ul>
                    </div>
                </div>
            </div>
        `;
    });

    $('#accordionSedes').html(html);
}
</script>
