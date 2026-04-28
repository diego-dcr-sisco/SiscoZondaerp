 @extends('layouts.app')
 @section('content')
     @php
         $spanish_status = [
             'active' => 'Activo',
             'completed' => 'Completado',
             'canceled' => 'Cancelado',
         ];

         $spanish_timetypes = [
             'days' => 'Dias',
             'weeks' => 'Semanas',
             'months' => 'Meses',
         ];
     @endphp

     <style>
         .font-small {
             font-size: 14px;
         }

         /* Estilos mejorados para nav-tabs CRM */
         .nav-tabs {
             border: none !important;
             background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
             border-radius: 12px;
             padding: 4px;
             gap: 6px;
             box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
             margin-bottom: 1.5rem !important;
             border: 1px solid rgba(0, 0, 0, 0.1) !important;
         }

         .nav-tabs .nav-link {
             border: none !important;
             color: #495057 !important;
             font-weight: 500;
             padding: 0.5rem 1rem;
             border-radius: 8px;
             transition: all 0.3s ease;
             display: flex;
             align-items: center;
             gap: 0.5rem;
             position: relative;
             background: transparent;
         }

         .nav-tabs .nav-link:hover {
             background-color: rgba(0, 123, 255, 0.1);
             color: #0056b3 !important;
             transform: translateY(-2px);
         }

         .nav-tabs .nav-link.active {
             background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
             color: white !important;
             border-radius: 8px;
             box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
         }

         .nav-tabs .nav-link i {
             font-size: 1.1em;
         }
     </style>

     @include('components.page-header', [
         'title' => 'SEGUIMIENTOS',
         'icon' => 'bi-arrow-repeat',
     ])
     <div class="container-fluid font-small p-3">
         <ul class="nav nav-tabs mb-3">
             <li class="nav-item">
                 <a class="nav-link {{ $nav == 'c' ? 'active' : '' }}" aria-current="page" href="{{ route('crm.agenda') }}">
                     <i class="bi bi-calendar-week"></i>
                     <span>Calendario</span>
                 </a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ $nav == 't' ? 'active' : '' }}" href="{{ route('crm.tracking') }}">
                     <i class="bi bi-arrow-repeat"></i>
                     <span>Seguimientos</span>
                 </a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ $nav == 'q' ? 'active' : '' }}" href="{{ route('crm.quotation') }}">
                     <i class="bi bi-receipt"></i>
                     <span>Cotizaciones</span>
                 </a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ $nav == 'd' ? 'active' : '' }}" href="{{ route('crm.daily-tracking.index') }}">
                     <i class="bi bi-clock-history"></i>
                     <span>Actividades diarias</span>
                 </a>
             </li>
         </ul>

         <div class="border p-2 text-dark rounded mb-3 bg-light">
             <form action="{{ route('crm.tracking') }}" method="GET">
                 @csrf
                 <div class="row g-2 mb-0">
                     <!-- Cliente/Lead -->
                     <div class="col-lg-3">
                         <label class="form-label" for="trackable-id">Nombre del cliente/lead</label>
                         <div class="input-group input-group-sm mb-3">
                             <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i></span>
                             <input type="text" class="form-control form-control-sm" id="trackable" name="trackable"
                                 value="{{ request('trackable') }}" placeholder="Buscar por nombre del cliente..." />
                         </div>
                     </div>

                     <!-- Rango de fechas -->
                     <div class="col-lg-3">
                         <label class="form-label" for="date-range">Rango de fechas</label>
                         <div class="input-group input-group-sm mb-3">
                             <span class="input-group-text" id="basic-addon1"><i
                                     class="bi bi-calendar-week-fill"></i></span>
                             <input type="text" class="form-control form-control-sm" id="date-range" name="date-range"
                                 value="{{ request('date-range') }}" placeholder="Rango de fechas" />
                         </div>
                     </div>

                     <!-- Servicio -->
                     <div class="col-lg-3">
                         <label class="form-label" for="service">Servicio</label>
                         <div class="input-group input-group-sm mb-3">
                             <span class="input-group-text" id="basic-addon1"><i class="bi bi-gear-fill"></i></span>
                             <input type="text" class="form-control form-control-sm" id="service" name="service"
                                 value="{{ request('service') }}" placeholder="Tipo de servicio..." />
                         </div>
                     </div>

                     <div class="col-lg-2">
                         <label for="signature_status" class="form-label">Dirección</label>
                         <div class="input-group input-group-sm mb-3">
                             <span class="input-group-text" id="basic-addon1"><i class="bi bi-arrow-down-up"></i></span>
                             <select class="form-select form-select-sm" id="direction" name="direction">
                                 <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                     DESC
                                 </option>
                                 <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>
                                     ASC
                                 </option>
                             </select>
                         </div>
                     </div>

                     <div class="col-lg-1">
                         <label for="order_type" class="form-label">Total</label>
                         <div class="input-group input-group-sm mb-3">
                             <span class="input-group-text" id="basic-addon1"><i class="bi bi-list-ol"></i></span>
                             <select class="form-select form-select-sm" id="size" name="size">
                                 <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25
                                 </option>
                                 <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50
                                 </option>
                                 <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100
                                 </option>
                                 <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200
                                 </option>
                                 <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500
                                 </option>
                             </select>
                         </div>
                     </div>

                     <!-- Botón Buscar -->
                     <div class="col-lg-12 d-flex justify-content-end px-3 gap-2">
                         <a href="{{ route('crm.tracking.export', request()->query()) }}"
                             class="btn btn-success btn-sm export-btn" data-export-type="Excel">
                             <span class="btn-content">
                                 <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                             </span>
                             <span class="btn-loading d-none">
                                 <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                 Exportando...
                             </span>
                         </a>
                         <a href="{{ route('crm.tracking.export.pdf', request()->query()) }}"
                             class="btn btn-danger btn-sm export-btn" data-export-type="PDF">
                             <span class="btn-content">
                                 <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                             </span>
                             <span class="btn-loading d-none">
                                 <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                 Exportando...
                             </span>
                         </a>
                         <button type="submit" class="btn btn-primary btn-sm" id="search" name="search">
                             <i class="bi bi-funnel-fill"></i> Buscar
                         </button>
                     </div>
                 </div>

                 <input type="hidden" name="view" value="agenda" />
             </form>
         </div>

         <div style="overflow-x: auto; width: 100%;">
             <table class="table table-bordered table-sm">
                 <thead>
                     <tr>
                         <th>Cliente/Cliente potencial</th>
                         <th>Orden</th>
                         <th>Servicio</th>
                         <th>Costo</th>
                         <th>Próxima Fecha</th>
                         <th>Titulo</th>
                         <th>Descripción</th>
                         <th>Rango</th>
                         <th>Estado</th>
                         <th>Creado por</th>
                         <th class="col-2"></th>
                     </tr>
                 </thead>
                 <tbody>
                     @foreach ($trackings as $tracking)
                         @php
                             $range = json_decode($tracking->range);
                         @endphp
                         <tr>
                             <td>{{ $tracking->trackable->name ?? '-' }}</td>
                             <td>
                                 @if ($tracking->order_id && $tracking->order)
                                     <a href="{{ route('order.edit', ['id' => $tracking->order_id]) }}" target="_blank">
                                         {{ $tracking->order->folio ?? $tracking->order_id }}
                                     </a>
                                 @else
                                     -
                                 @endif
                             </td>
                             <td>{{ $tracking->service->name ?? '-' }}</td>
                             <td>{{ $tracking->cost ?? '-' }}</td>
                             <td>{{ \Carbon\Carbon::parse($tracking->next_date)->format('d/m/Y') }}</td>
                             <td>{{ $tracking->title ?? '-' }}</td>
                             <td>{{ $tracking->description ?? '-' }}</td>

                             <td> {{ $range && $range->frequency_type ? 'Cada ' . $range->frequency . ' ' . $range->frequency_type : '-' }}
                             </td>
                             <td
                                 class="fw-bold
                                                    {{ $tracking->status == 'active'
                                                        ? 'text-success'
                                                        : ($tracking->status == 'completed'
                                                            ? 'text-primary'
                                                            : ($tracking->status == 'canceled'
                                                                ? 'text-danger'
                                                                : 'text-secondary')) }}">
                                 {{ $spanish_status[$tracking->status] }}
                             </td>
                             <td>{{ $tracking->user->name ?? '-' }}</td>
                             <td class="py-3 px-2">
                                 <div class="d-flex gap-2 align-items-center justify-content-center flex-wrap">
                                     <a href="{{ route('crm.tracking.edit', ['id' => $tracking->id]) }}"
                                         class="btn btn-secondary btn-sm"
                                         onclick="return confirm('📅 EDITAR Seguimiento\n\n¿Deseas reprogramar esta actividad?')"
                                         data-bs-toggle="tooltip" data-bs-placement="top"
                                         data-bs-custom-class="custom-tooltip" data-bs-title="Editar seguimiento">
                                         <i class="bi bi-pencil-square"></i>
                                     </a>
                                     @if ($tracking->status != 'canceled')
                                         <a href="{{ route('crm.tracking.complete', ['id' => $tracking->id]) }}"
                                             class="btn btn-success btn-sm"
                                             onclick="return confirm('✅ COMPLETAR Seguimiento\n\n¿Deseas completar esta actividad?')"
                                             data-bs-toggle="tooltip" data-bs-placement="top"
                                             data-bs-custom-class="custom-tooltip" data-bs-title="Completar seguimiento">
                                             <i class="bi bi-check-lg"></i>
                                         </a>

                                         <a href="{{ route('crm.tracking.cancel', ['id' => $tracking->id]) }}"
                                             class="btn btn-danger btn-sm"
                                             onclick="return confirm('❌ CANCELAR de Seguimiento\n\n¿Deseas cancelar esta actividad?')"
                                             data-bs-toggle="tooltip" data-bs-placement="top"
                                             data-bs-custom-class="custom-tooltip" data-bs-title="Cancelar seguimiento">
                                             <i class="bi bi-x-lg"></i>
                                         </a>
                                     @endif
                                     <a href="{{ route('crm.tracking.destroy', ['id' => $tracking->id]) }}"
                                         class="btn btn-outline-danger btn-sm"
                                         onclick="return confirm('ELIMINAR Seguimiento\n\n¿Deseas eliminar esta actividad?')"
                                         data-bs-toggle="tooltip" data-bs-placement="top"
                                         data-bs-custom-class="custom-tooltip" data-bs-title="Eliminar seguimiento">
                                         <i class="bi bi-trash-fill"></i>
                                     </a>
                                 </div>
                             </td>
                         </tr>
                     @endforeach
                 </tbody>
             </table>
         </div>

         {{ $trackings->links('pagination::bootstrap-5') }}
     </div>

     <script>
         const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
         const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

         $(function() {
             // Configuración común para ambos datepickers
             const commonOptions = {
                 opens: 'left',
                 locale: {
                     format: 'DD/MM/YYYY'
                 },
                 ranges: {
                     'Hoy': [moment(), moment()],
                     'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                     'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                     'Este mes': [moment().startOf('month'), moment().endOf('month')],
                     'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                     'Este año': [moment().startOf('year'), moment().endOf('year')],
                 },
                 showDropdowns: true,
                 alwaysShowCalendars: true,
                 autoUpdateInput: false
             };

             $('#date-range').daterangepicker(commonOptions);

             $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                 $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                     'DD/MM/YYYY'));
             });

             $('.export-btn').on('click', function(e) {
                 e.preventDefault();

                 const button = $(this);
                 const exportUrl = button.attr('href');
                 const exportType = button.data('export-type');

                 const accepted = confirm(
                     `Se exportara la informacion de la tabla en formato ${exportType}. Se recomienda aplicar los filtros antes de continuar.\n\n¿Deseas continuar?`
                 );

                 if (!accepted) {
                     return;
                 }

                 button.addClass('disabled');
                 button.css('pointer-events', 'none');
                 button.find('.btn-content').addClass('d-none');
                 button.find('.btn-loading').removeClass('d-none');

                 setTimeout(function() {
                     window.location.href = exportUrl;
                 }, 150);

                 setTimeout(function() {
                     button.removeClass('disabled');
                     button.css('pointer-events', '');
                     button.find('.btn-loading').addClass('d-none');
                     button.find('.btn-content').removeClass('d-none');
                 }, 6000);
             });
         });
     </script>
 @endsection
