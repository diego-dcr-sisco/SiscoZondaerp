@extends('layouts.app')
@section('content')
    @php
        $offset = ($opportunity_areas->currentPage() - 1) * $opportunity_areas->perPage();
    @endphp

    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-3">
            <a href="javascript:history.back()" class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0 fw-bold">{{ $customer->name }}</h1>
        </div>

        <div class="row mb-3">
            <div class="col-auto">
                @can('write_order')
                    <a class="btn btn-primary" href="{{ route('opportunity-area.create', ['customerId' => $customer->id]) }}">
                        <i class="bi bi-plus-lg fw-bold"></i> Crear area de oportunidad
                    </a>
                @endcan
            </div>
            <div class="col-auto">
                @can('write_order')
                    <a class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#printOpAreaModal">
                        <i class="bi bi-file-pdf-fill"></i> {{ __('buttons.report') }}
                    </a>
                @endcan
            </div>
        </div>

        @include('messages.alert')
        <div class="form-check">
            <input class="form-check-input border border-secondary" type="checkbox" id="op-checkbox"
                onchange="selectAllBoxes(this)">
            <label class="form-check-label" for="op-checkbox">
                Todos las areas de oportunidad
            </label>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <form method="GET"
                        action="{{ route('opportunity-area.search', ['customerId' => $customer->id]) }}">
                        @csrf
                        <tr>
                            <th class="text-center" scope="col"></th>
                            <th class="text-center" scope="col">#</th>
                            <th class="text-center" scope="col">Fecha
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="date-range" name="date"
                                        value="" />
                                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                                </div>
                            </th>
                            <th class="text-center" scope="col">Área</th>
                            <!--th scope="col">Cliente</th-->
                            <!--th scope="col">Fecha estimada</th-->
                            <th class="text-center" scope="col">Área de oportunidad</th>
                            <th class="text-center" scope="col">Recomendación</th>
                            <th class="text-center" scope="col">Sgto</th>
                            <th class="text-center" scope="col">Estado
                            </th>
                            <th class="text-center" scope="col">{{ __('buttons.actions') }}
                            </th>
                        </tr>
                    </form>
                </thead>
                <tbody>
                    @foreach ($opportunity_areas as $index => $opportunity_area)
                        <tr>
                            <th scope="row">
                                <div class="d-flex justify-content-center">
                                    <input class="form-check-input border border-secondary op-checkbox" type="checkbox"
                                        value="{{ $opportunity_area->id }}">
                                </div>
                            </th>
                            <td class="text-center">{{ $offset + $index + 1 }}</th>
                            <td class="text-center">{{ $opportunity_area->date }}</td>
                            <td class="text-center">{{ $opportunity_area->applicationArea->name }}</td>
                            <!--td>{{ $opportunity_area->estimated_date }}</td-->
                            <td>{{ $opportunity_area->opportunity }}</td>
                            <td>{{ $opportunity_area->recommendation }}</td>
                            <td class="text-center fw-bold {{ $opportunity_area->tracing == 0 ? 'text-warning' : ($opportunity_area->tracing == 1 ? 'text-primary' : 'text-success')  }}">{{ $opportunity_area->getTracing() }}</td>
                            <td class="text-center fw-bold {{ $opportunity_area->status == 0 ? 'text-success' : 'text-danger'  }}"">{{ $opportunity_area->getStatus() }}</td>
                            <td class="text-center">
                                <a class="btn btn-info btn-sm" href="">
                                    <i class="bi bi-eye-fill"></i> {{ __('buttons.show') }}
                                </a>
                                @can('write_order')
                                    <a class="btn btn-secondary btn-sm"
                                        href="{{ route('opportunity-area.edit', ['id' => $opportunity_area->id]) }}">
                                        <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                                    </a>
                                    <a class="btn btn-danger btn-sm"
                                        href="{{ route('opportunity-area.destroy', ['id' => $opportunity_area->id]) }}"
                                        onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"><i
                                            class="bi bi-trash-fill"></i>
                                        {{ __('buttons.delete') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="printOpAreaModal" tabindex="-1" aria-labelledby="printOpAreaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            {{-- href="" --}}
            <form class="modal-content" method="POST"
                action="{{ route('opportunity-area.print', ['customerId' => $customer->id]) }}" target="_blank">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="printOpAreaModalLabel">Area de oportunidad: Objetivo</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label is-required">Objetivo</label>
                        <textarea class="form-control" id="objective" name="objective" rows="5">Se realiza recorrido en planta, para detectar áreas de mejora, se describen a continuación, riesgo y recomendación por parte de Siscoplagas, y disminuir la posibilidad de ingreso de organismos a planta. </textarea>
                    </div>

                    <input type="hidden" id="op-area-boxes" name="op_area_boxes" />
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"
                        onclick="selectBoxes()">{{ __('buttons.print') }}</button>
                    <button type="button" class="btn btn-danger"
                        data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var boxes = [];

        function selectAllBoxes(element) {
            var value = element.value;
            var is_checked = element.checked;

            $(".op-checkbox").each(function() {
                $(this).prop("checked", is_checked);
            });
        }

        function selectBoxes() {
            $('#op-area-boxes').val(JSON.stringify($(".op-checkbox:checked").map(function() {
                return parseInt($(this).val());
            }).get())); // Guarda como JSON
        }
        
        $(function() {
            $('#date-range').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'DD/MM/YYYY' // Cambiar el formato aquí
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este año': [moment().startOf('year'), moment().endOf('year')],
                },
                alwaysShowCalendars: true,
                autoUpdateInput: false,
            });
        });

        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        });
    </script>
@endsection
