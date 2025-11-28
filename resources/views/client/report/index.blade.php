 @extends('layouts.app') @section('content')
     @php
         function splitFileName($filename)
         {
             $fileParts = explode('_', $filename);
             return $fileParts;
         }
     @endphp

     <div class="container-fluid">
         <div class="m-3">
             @php
                 function getFolioNumber($folio)
                 {
                     $partes = explode('-', $folio);
                     if (count($partes) > 1) {
                         return (int) $partes[1]; // Retorna el número como entero
                     }
                     return null; // Si no hay guion, retorna null
                 }

                 $offset = ($orders->currentPage() - 1) * $orders->perPage();

             @endphp

             <table class="table table-bordered table-sm table-striped caption-top">
                 <caption class="border rounded-top p-2 text-dark bg-light caption-top">
                     <form action="{{ route('client.reports') }}" method="GET">
                         @csrf
                         <div class="row g-3 mb-0">
                             <div class="col-lg-4 col-12">
                                 <label for="sede" class="form-label">Sede</label>
                                 <select class="form-select form-select-sm" id="sede" name="sede" required>
                                     @forelse ($sedes as $sede)
                                         <option value="{{ $sede->id }}"
                                             {{ request('sede') == $sede->id ? 'selected' : '' }}> {{ $sede->name }}
                                         </option>
                                     @empty
                                         <option value="">Sin sede(s) disponibles</option>
                                     @endforelse
                                 </select>
                             </div>
                             <div class="col-lg-2 col-12">
                                 <label for="no-report" class="form-label">No reporte</label>
                                 <input type="number" class="form-control form-control-sm" name="no_report"
                                     value="{{ request('no_report') }}" placeholder="1,2,3...4" min="0" />
                             </div>

                             <div class="col-lg-2 col-12">
                                 <label for="date-range" class="form-label">Fecha</label>
                                 <input type="text" class="form-control form-control-sm" id="date-range"
                                     name="date_range" value="{{ request('date_range') }}"
                                     placeholder="01/01/0000 - 31/12/0000" autocomplete="off" />
                             </div>

                             <div class="col-lg-4 col-12">
                                 <label for="service" class="form-label">Servicio</label>
                                 <input type="text" class="form-control form-control-sm" name="service"
                                     value="{{ request('service') }}" placeholder="Nombre del servicio" />
                             </div>

                             <div class="col-auto">
                                 <label for="has-signature" class="form-label">Firmado</label>
                                 <select class="form-select form-select-sm" name="has_signature">
                                     <option value="" {{ request('has_signature') == null ? 'selected' : '' }}>Todos
                                     </option>
                                     <option value="yes" {{ request('has_signature') == 'yes' ? 'selected' : '' }}>Sí
                                     </option>
                                     <option value="no" {{ request('has_signature') == 'no' ? 'selected' : '' }}>No
                                     </option>
                                 </select>
                             </div>

                             <div class="col-auto">
                                 <label for="signature_status" class="form-label">Dirección</label>
                                 <select class="form-select form-select-sm" id="direction" name="direction">
                                     <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                     </option>
                                     <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                     </option>
                                 </select>
                             </div>

                             <div class="col-auto">
                                 <label for="order_type" class="form-label">Total</label>
                                 <select class="form-select form-select-sm" id="size" name="size">
                                     <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                     <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                     <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                     <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                     <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                 </select>
                             </div>
                             <div class="col-lg-12 d-flex justify-content-end m-0">
                                 <button type="submit" class="btn btn-primary btn-sm me-2">
                                     <i class="bi bi-funnel-fill"></i> Filtrar
                                 </button>
                             </div>
                         </div>
                     </form>
                 </caption>
                 <thead>
                     <tr class="table-primary">
                         <th scope="col">No Reporte</th>
                         <th scope="col">Sede</th>
                         <th scope="col">Fecha y hora</th>
                         <th scope="col">Línea de negocio</th>
                         <th scope="col">Técnico</th>
                         <th scope="col">Servicio(s)</th>
                         <th scope="col">Firmado por</th>
                         <th scope="col">Firma</th>
                         <th scope="col"></th>
                     </tr>
                 </thead>
                 <tbody>
                     @if ($has_orders)
                         @forelse ($orders as $index => $order)
                             @php
                                 // Asegurarte que la cadena tiene el prefijo correcto
                                 $signature =
                                     strpos($order->customer_signature, 'data:image') === 0
                                         ? $order->customer_signature
                                         : 'data:image/png;base64,' . $order->customer_signature;
                             @endphp
                             <tr>
                                 <th scope="row">{{ getFolioNumber($order->folio) }}</th>
                                 <td>
                                     {{ $order->customer->name }}
                                 </td>
                                 <td>
                                     {{ Carbon\Carbon::parse($order->programmed_date)->format('d/m/Y') }} -
                                     {{ Carbon\Carbon::parse($order->start_time)->format('h:i') }}
                                 </td>
                                 <td>
                                     @foreach ($order->services as $service)
                                         {{ $service->businessLine->name }}
                                     @endforeach
                                 </td>
                                 <td>
                                     {{ $technician->user->name ?? '-' }}
                                 </td>
                                 <td>
                                     {{ implode(', ', $order->services->pluck('name')->toArray()) }}
                                 </td>
                                 <td class="fw-bold {{ $order->signature_name ? 'text-primary' : 'text-danger' }}"
                                     id="order{{ $order->id }}-signature-name">
                                     {{ $order->signature_name ?? 'Sin firma' }}</td>
                                 <td> <img class="border" style="width: 75px;" src="{{ $signature }}" alt="img_firma">
                                 </td>
                                 <td>
                                     <button type="button" class="btn btn-warning btn-sm mb-1"
                                         onclick="openModal({{ $order->id }})">
                                         <i class="bi bi-pencil-fill"></i>
                                     </button>
                                     <a href="{{ route('report.print', ['orderId' => $order->id]) }}"
                                         class="btn btn-dark btn-sm mb-1">
                                         <i class="bi bi-file-pdf-fill"></i>
                                     </a>
                                 </td>
                             </tr>
                         @empty
                             <tr class="text-center">
                                 <td class="fw-bold text-danger text-decoration-underline" colspan="9">No se han encontrado reportes</td>
                             </tr>
                         @endforelse
                     @else
                         <tr class="text-center">
                             <td class="fw-bold text-decoration-underline" colspan="9">Genera una busqueda utilizando
                                 los
                                 filtros de la parte superior </td>
                         </tr>
                     @endif
                 </tbody>
             </table>
             @if ($has_orders)
                 {{ $orders->links('pagination::bootstrap-5') }}
             @endif
         </div>
     </div>
     @include('client.report.modals.signature')

     <script>
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
