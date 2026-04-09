@extends('layouts.app')

@section('content')
    <style>
        .section-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
        }

        .hint {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .item-card {
            border: 1px dashed #ced4da;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background-color: #fcfdff;
        }
    </style>

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2 mb-3">
            <a href="{{ route('dashboard') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">CERTIFICADO MANUAL</span>
            <span class="ms-2 badge text-bg-warning">Sin guardado en BD</span>
        </div>

        <div class="px-5 py-3">
            @include('messages.alert')

            <div class="alert alert-info mb-3" role="alert">
                Completa los campos y genera el PDF. Puedes usar <strong>Autocompletar demo</strong> para una prueba rapida.
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error de validacion:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="manual-certificate-form" action="{{ route('report.manual-certificate.generate') }}" method="POST" target="_blank">
                @csrf

                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="section-title mb-0">Datos generales</div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="fill-demo">
                            <i class="bi bi-magic"></i> Autocompletar demo
                        </button>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label is-required">Titulo del certificado</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', 'Certificado de Servicio Manual') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre de archivo (opcional)</label>
                            <input type="text" name="filename" class="form-control" value="{{ old('filename', '') }}" placeholder="certificado_manual.pdf">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label is-required">Fecha de ejecucion</label>
                            <input type="text" name="programmed_date" class="form-control" value="{{ old('programmed_date', now()->format('d-m-Y')) }}" placeholder="dd-mm-aaaa" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha inicio</label>
                            <input type="text" name="start_date" class="form-control" value="{{ old('start_date', now()->format('d-m-Y')) }}" placeholder="dd-mm-aaaa">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hora inicio</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '09:00') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha fin</label>
                            <input type="text" name="end_date" class="form-control" value="{{ old('end_date', now()->format('d-m-Y')) }}" placeholder="dd-mm-aaaa">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hora fin</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '10:00') }}">
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title">Sucursal</div>
                    <div class="row g-3">
                        <div class="col-md-4"><input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', 'SISCOPLAGAS') }}" placeholder="Empresa"></div>
                        <div class="col-md-4"><input type="text" name="branch_sede" class="form-control" value="{{ old('branch_sede', '') }}" placeholder="Sede"></div>
                        <div class="col-md-4"><input type="text" name="branch_no_license" class="form-control" value="{{ old('branch_no_license', '') }}" placeholder="Licencia sanitaria"></div>
                        <div class="col-md-6"><input type="text" name="branch_address" class="form-control" value="{{ old('branch_address', '') }}" placeholder="Direccion"></div>
                        <div class="col-md-3"><input type="email" name="branch_email" class="form-control" value="{{ old('branch_email', '') }}" placeholder="Correo"></div>
                        <div class="col-md-3"><input type="text" name="branch_phone" class="form-control" value="{{ old('branch_phone', '') }}" placeholder="Telefono"></div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title">Cliente y tecnico</div>
                    <div class="row g-3">
                        <div class="col-12 fw-bold text-primary">Cliente</div>
                        <div class="col-md-6"><input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', '') }}" placeholder="Nombre de sede o cliente" required></div>
                        <div class="col-md-6"><input type="text" name="customer_social_reason" class="form-control" value="{{ old('customer_social_reason', '') }}" placeholder="Razon social"></div>
                        <div class="col-md-6"><input type="text" name="customer_address" class="form-control" value="{{ old('customer_address', '') }}" placeholder="Direccion"></div>
                        <div class="col-md-2"><input type="text" name="customer_city" class="form-control" value="{{ old('customer_city', '') }}" placeholder="Municipio"></div>
                        <div class="col-md-2"><input type="text" name="customer_state" class="form-control" value="{{ old('customer_state', '') }}" placeholder="Estado"></div>
                        <div class="col-md-2"><input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', '') }}" placeholder="Telefono"></div>
                        <div class="col-md-4"><input type="text" name="customer_rfc" class="form-control" value="{{ old('customer_rfc', '') }}" placeholder="RFC"></div>
                        <div class="col-md-4"><input type="text" name="customer_signed_by" class="form-control" value="{{ old('customer_signed_by', '') }}" placeholder="Nombre de quien firma"></div>
                        <div class="col-md-4">
                            <input type="text" name="customer_signature_base64" class="form-control" value="{{ old('customer_signature_base64', '') }}" placeholder="Firma cliente base64 (opcional)">
                            <div class="hint mt-1">Puedes dejarlo vacio si no hay firma.</div>
                        </div>

                        <div class="col-12 mt-3 fw-bold text-primary">Tecnico</div>
                        <div class="col-md-4"><input type="text" name="technician_name" class="form-control" value="{{ old('technician_name', '') }}" placeholder="Nombre del tecnico" required></div>
                        <div class="col-md-4"><input type="text" name="technician_rfc" class="form-control" value="{{ old('technician_rfc', '') }}" placeholder="RFC del tecnico"></div>
                        <div class="col-md-4"><input type="text" name="technician_signature_base64" class="form-control" value="{{ old('technician_signature_base64', '') }}" placeholder="Firma tecnico base64 (opcional)"></div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-title mb-0">Servicios <span class="badge text-bg-secondary" id="services-count">1</span></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="add-service">
                            <i class="bi bi-plus-circle"></i> Agregar servicio
                        </button>
                    </div>
                    <div id="services-container" class="mb-0"></div>
                </div>

                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-title mb-0">Productos <span class="badge text-bg-secondary" id="products-count">1</span></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="add-product">
                            <i class="bi bi-plus-circle"></i> Agregar producto
                        </button>
                    </div>
                    <div id="products-container" class="mb-0"></div>
                </div>

                <div class="section-card">
                    <div class="section-title">Notas y recomendaciones</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Notas del cliente">{{ old('notes', '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Recomendaciones</label>
                            <textarea name="recommendations" class="form-control" rows="4" placeholder="Recomendaciones">{{ old('recommendations', '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf"></i> Generar certificado
                    </button>
                    <button type="button" class="btn btn-outline-dark" id="clear-form">
                        Limpiar formulario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var samplePayload = @json($sampleJson);
            var form = document.getElementById('manual-certificate-form');
            var servicesContainer = document.getElementById('services-container');
            var addServiceBtn = document.getElementById('add-service');
            var productsContainer = document.getElementById('products-container');
            var addProductBtn = document.getElementById('add-product');
            var servicesCount = document.getElementById('services-count');
            var productsCount = document.getElementById('products-count');
            var fillDemoBtn = document.getElementById('fill-demo');
            var clearFormBtn = document.getElementById('clear-form');

            function updateCounters() {
                servicesCount.innerText = servicesContainer.querySelectorAll('.service-item').length;
                productsCount.innerText = productsContainer.querySelectorAll('.product-item').length;
            }

            function createServiceItem(name, text) {
                var wrapper = document.createElement('div');
                wrapper.className = 'service-item item-card';
                wrapper.innerHTML = '<div class="row g-2 align-items-start">'
                    + '<div class="col-md-4"><input type="text" name="services_name[]" class="form-control" placeholder="Nombre del servicio" value="' + (name || '') + '"></div>'
                    + '<div class="col-md-7"><textarea name="services_text[]" class="form-control" rows="2" placeholder="Descripcion del servicio">' + (text || '') + '</textarea></div>'
                    + '<div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-service" title="Eliminar"><i class="bi bi-trash"></i></button></div>'
                    + '</div>';
                servicesContainer.appendChild(wrapper);
                updateCounters();
            }

            function createProductItem(product) {
                product = product || {};
                var wrapper = document.createElement('div');
                wrapper.className = 'product-item item-card';
                wrapper.innerHTML = '<div class="row g-2 align-items-start">'
                    + '<div class="col-md-3"><input type="text" name="products_name[]" class="form-control" placeholder="Nombre comercial" value="' + (product.name || '') + '"></div>'
                    + '<div class="col-md-2"><input type="text" name="products_active_ingredient[]" class="form-control" placeholder="Materia activa" value="' + (product.active_ingredient || '') + '"></div>'
                    + '<div class="col-md-2"><input type="text" name="products_no_register[]" class="form-control" placeholder="No registro" value="' + (product.no_register || '') + '"></div>'
                    + '<div class="col-md-2"><input type="text" name="products_safety_period[]" class="form-control" placeholder="Plazo seguridad" value="' + (product.safety_period || '') + '"></div>'
                    + '<div class="col-md-2"><input type="text" name="products_application_method[]" class="form-control" placeholder="Metodo aplicacion" value="' + (product.application_method || '') + '"></div>'
                    + '<div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-product" title="Eliminar"><i class="bi bi-trash"></i></button></div>'
                    + '<div class="col-md-2"><input type="text" name="products_dosage[]" class="form-control" placeholder="Dosificacion" value="' + (product.dosage || '') + '"></div>'
                    + '<div class="col-md-2"><input type="text" name="products_amount[]" class="form-control" placeholder="Cantidad" value="' + (product.amount || '') + '"></div>'
                    + '<div class="col-md-2"><input type="text" name="products_metric[]" class="form-control" placeholder="Unidad" value="' + (product.metric || '') + '"></div>'
                    + '<div class="col-md-3"><input type="text" name="products_lot[]" class="form-control" placeholder="Lote" value="' + (product.lot || '') + '"></div>'
                    + '</div>';
                productsContainer.appendChild(wrapper);
                updateCounters();
            }

            createServiceItem('', '');
            createProductItem({});

            addServiceBtn.addEventListener('click', function() {
                createServiceItem('', '');
            });

            addProductBtn.addEventListener('click', function() {
                createProductItem({});
            });

            servicesContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-service')) {
                    if (servicesContainer.querySelectorAll('.service-item').length > 1) {
                        e.target.closest('.service-item').remove();
                        updateCounters();
                    }
                }
            });

            productsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-product')) {
                    if (productsContainer.querySelectorAll('.product-item').length > 1) {
                        e.target.closest('.product-item').remove();
                        updateCounters();
                    }
                }
            });

            fillDemoBtn.addEventListener('click', function() {
                var sample = JSON.parse(samplePayload || '{}');
                var sampleOrder = sample.order || {};
                var sampleBranch = sample.branch || {};
                var sampleCustomer = sample.customer || {};
                var sampleTechnician = sample.technician || {};
                var sampleProducts = sample.products || {};

                form.querySelector('[name="title"]').value = sample.title || '';
                form.querySelector('[name="filename"]').value = sample.filename || '';
                form.querySelector('[name="programmed_date"]').value = sampleOrder.programmed_date || '';
                form.querySelector('[name="start_date"]').value = (sampleOrder.start || '').split(' - ')[0] || '';
                form.querySelector('[name="start_time"]').value = (sampleOrder.start || '').split(' - ')[1] || '09:00';
                form.querySelector('[name="end_date"]').value = (sampleOrder.end || '').split(' - ')[0] || '';
                form.querySelector('[name="end_time"]').value = (sampleOrder.end || '').split(' - ')[1] || '10:00';

                form.querySelector('[name="branch_name"]').value = sampleBranch.name || '';
                form.querySelector('[name="branch_sede"]').value = sampleBranch.sede || '';
                form.querySelector('[name="branch_address"]').value = sampleBranch.address || '';
                form.querySelector('[name="branch_email"]').value = sampleBranch.email || '';
                form.querySelector('[name="branch_phone"]').value = sampleBranch.phone || '';
                form.querySelector('[name="branch_no_license"]').value = sampleBranch.no_license || '';

                form.querySelector('[name="customer_name"]').value = sampleCustomer.name || '';
                form.querySelector('[name="customer_social_reason"]').value = sampleCustomer.social_reason || '';
                form.querySelector('[name="customer_address"]').value = sampleCustomer.address || '';
                form.querySelector('[name="customer_city"]').value = sampleCustomer.city || '';
                form.querySelector('[name="customer_state"]').value = sampleCustomer.state || '';
                form.querySelector('[name="customer_phone"]').value = sampleCustomer.phone || '';
                form.querySelector('[name="customer_rfc"]').value = sampleCustomer.rfc || '';
                form.querySelector('[name="customer_signed_by"]').value = sampleCustomer.signed_by || '';
                form.querySelector('[name="customer_signature_base64"]').value = sampleCustomer.signature_base64 || '';

                form.querySelector('[name="technician_name"]').value = sampleTechnician.name || '';
                form.querySelector('[name="technician_rfc"]').value = sampleTechnician.rfc || '';
                form.querySelector('[name="technician_signature_base64"]').value = sampleTechnician.signature_base64 || '';

                form.querySelector('[name="notes"]').value = (sample.notes || '').replace(/<[^>]+>/g, '');
                form.querySelector('[name="recommendations"]').value = (sample.recommendations || '').replace(/<[^>]+>/g, '');

                servicesContainer.innerHTML = '';
                productsContainer.innerHTML = '';

                if (Array.isArray(sample.services) && sample.services.length > 0) {
                    sample.services.forEach(function(service) {
                        createServiceItem(service.name || '', (service.text || '').replace(/<[^>]+>/g, ''));
                    });
                } else {
                    createServiceItem('', '');
                }

                if (Array.isArray(sampleProducts.data) && sampleProducts.data.length > 0) {
                    sampleProducts.data.forEach(function(product) {
                        createProductItem(product);
                    });
                } else {
                    createProductItem({});
                }
            });

            clearFormBtn.addEventListener('click', function() {
                form.reset();
                servicesContainer.innerHTML = '';
                productsContainer.innerHTML = '';
                createServiceItem('', '');
                createProductItem({});
            });

            form.addEventListener('submit', function(e) {
                var customerName = form.querySelector('[name="customer_name"]').value.trim();
                var techName = form.querySelector('[name="technician_name"]').value.trim();

                if (customerName === '' || techName === '') {
                    e.preventDefault();
                    alert('Completa al menos nombre de cliente y tecnico para generar el certificado.');
                }
            });
        });
    </script>
@endsection
