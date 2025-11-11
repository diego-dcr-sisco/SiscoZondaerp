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

     <div class="container-fluid font-small p-3">
         <ul class="nav nav-tabs mb-3">
             <li class="nav-item">
                 <a class="nav-link {{ $nav == 'c' ? 'active' : '' }}" aria-current="page"
                     href="{{ route('crm.agenda') }}">Calendario</a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ $nav == 't' ? 'active' : '' }}" href="{{ route('crm.tracking') }}">Seguimientos</a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ $nav == 'q' ? 'active' : '' }}" href="{{ route('crm.quotation') }}">Cotizaciones</a>
             </li>
         </ul>
         <div class="card">
             <div class="card-header fw-bold">
                 Seguimientos
             </div>
             <div class="card-body">
                 <div class="table-responsive">
                     <table class="table table-bordered table-sm caption-top">
                         <caption class="border p-2 text-dark rounded-top">
                             <form action="{{ route('crm.tracking') }}" method="GET">
                                 @csrf
                                 <div class="row align-items-end g-2">
                                     <!-- Cliente/Lead -->
                                     <div class="col-lg-4 col-sm-6">
                                         <label class="form-label" for="trackable-id">Nombre del cliente/lead</label>
                                         <input type="text" class="form-control form-control-sm" id="trackable"
                                             name="trackable" value="{{ request('trackable') }}" />
                                     </div>

                                     <!-- Rango de fechas -->
                                     <div class="col-lg-4 col-sm-6">
                                         <label class="form-label" for="date-range">Rango de fechas</label>
                                         <input type="text" class="form-control form-control-sm" id="date-range"
                                             name="date-range" value="{{ request('date-range') }}" />
                                     </div>

                                     <!-- Servicio -->
                                     <div class="col-lg-4 col-sm-6">
                                         <label class="form-label" for="service">Servicio</label>
                                         <input type="text" class="form-control form-control-sm" id="service"
                                             name="service" value="{{ request('service') }}" />
                                     </div>

                                     <div class="col-auto">
                                         <label for="signature_status" class="form-label">Dirección</label>
                                         <select class="form-select form-select-sm" id="direction" name="direction">
                                             <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                                 DESC
                                             </option>
                                             <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>
                                                 ASC
                                             </option>
                                         </select>
                                     </div>

                                     <div class="col-auto">
                                         <label for="order_type" class="form-label">Total</label>
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

                                     <!-- Botón Buscar -->
                                     <div class="col-lg-12 d-flex justify-content-end">
                                         <button type="submit" class="btn btn-primary btn-sm" id="search"
                                             name="search">
                                             Buscar
                                         </button>
                                     </div>
                                 </div>

                                 <input type="hidden" name="view" value="agenda" />
                             </form>
                         </caption>
                         <thead>
                             <tr>
                                 <th>Cliente/Cliente potencial</th>
                                 <th>Orden</th>
                                 <th>Servicio</th>
                                 <th>Próxima Fecha</th>
                                 <th>Titulo</th>
                                 <th>Descripción</th>
                                 <th>Rango</th>
                                 <th>Estado</th>
                                 <th></th>
                             </tr>
                         </thead>
                         <tbody>
                             @foreach ($trackings as $tracking)
                                 @php
                                     $range = json_decode($tracking->range);
                                 @endphp
                                 <tr>
                                     <td>{{ $tracking->trackable->name ?? '-' }}</td>
                                     <td>{{ $tracking->order->folio ?? '-' }}</td>
                                     <td>{{ $tracking->service->name ?? '-' }}</td>
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
                                     <td class="text-center">
                                         <a href="{{ route('crm.tracking.edit', ['id' => $tracking->id]) }}"
                                             class="btn btn-sm btn-secondary"
                                             onclick="return confirm('📅 EDITAR Seguimiento\n\n¿Deseas reprogramar esta actividad?')"
                                             data-bs-toggle="tooltip" data-bs-placement="top"
                                             data-bs-custom-class="custom-tooltip" data-bs-title="Editar seguimiento">
                                             <i class="bi bi-pencil-square"></i>
                                         </a>
                                         @if ($tracking->status != 'canceled')
                                             <a href="{{ route('crm.tracking.complete', ['id' => $tracking->id]) }}"
                                                 class="btn btn-sm btn-success"
                                                 onclick="return confirm('✅ COMPLETAR Seguimiento\n\n¿Deseas completar esta actividad?')"
                                                 data-bs-toggle="tooltip" data-bs-placement="top"
                                                 data-bs-custom-class="custom-tooltip"
                                                 data-bs-title="Completar seguimiento">
                                                 <i class="bi bi-check-lg"></i>
                                             </a>
                                             <a href="{{ route('crm.tracking.cancel', ['id' => $tracking->id]) }}" class="btn btn-sm btn-danger"
                                                 onclick="return confirm('❌ CANCELAR de Seguimiento\n\n¿Deseas cancelar esta actividad?')"
                                                 data-bs-toggle="tooltip" data-bs-placement="top"
                                                 data-bs-custom-class="custom-tooltip" data-bs-title="Cancelar seguimiento">
                                                 <i class="bi bi-x-lg"></i>
                                            </a>
                                         @endif
                                         <a href="{{ route('crm.tracking.destroy', ['id' => $tracking->id]) }}"
                                             class="btn btn-outline-danger btn-sm"
                                             onclick="return confirm('ELIMINAR Seguimiento\n\n¿Deseas eliminar esta actividad?')"><i
                                                 class="bi bi-trash-fill" data-bs-toggle="tooltip"
                                                 data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                                 data-bs-title="Eliminar seguimiento"></i></a>
                                     </td>
                                 </tr>
                             @endforeach
                         </tbody>
                     </table>
                 </div>
             </div>
             <div class="card-footer">
                 {{ $trackings->links('pagination::bootstrap-5') }}
             </div>
         </div>
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
         });
     </script>
 @endsection
