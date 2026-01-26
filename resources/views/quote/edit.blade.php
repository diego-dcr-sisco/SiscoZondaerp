@extends('layouts.app')
@section('content')
    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .table-text {
            font-size: 0.9rem;
        }
    </style>

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.quote', ['id' => $quote->model_id, 'class' => $class]) }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR COTIZACIÓN <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $quote->model->name }}</span>
            </span>
        </div>
        <form class="form m-3" method="POST" action="{{ route('customer.quote.update', ['id' => $quote->id]) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-6 col-12 mb-3">
                    <div class="border rounded shadow p-3">
                        <div class="fw-bold mb-2 fs-5">Datos de la cotización</div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="service" class="form-label">Servicio</label>
                                <select class="form-select" id="service" name="service_id">
                                    <option value="">Sin servicio</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}"
                                            {{ $quote->service_id == $service->id ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4 mb-3">
                                <label for="start-date" class="form-label is-required">Fecha de inicio</label>
                                <input type="date" class="form-control" id="start-date" name="start_date"
                                    value="{{ $quote->start_date->format('Y-m-d') }}" required />
                            </div>

                            <div class="col-4 mb-3">
                                <label for="end-date" class="form-label is-required">Fecha estimada de fin</label>
                                <input type="date" class="form-control" name="end_date" value="{{ $quote->end_date->format('Y-m-d') }}"
                                    required />
                            </div>

                            <div class="col-4 mb-3">
                                <label for="valid-until" class="form-label is-required">Válido hasta</label>
                                <input type="date" class="form-control" name="valid_until"
                                    value="{{ $quote->valid_until->format('Y-m-d') }}" required />
                            </div>

                            <div class="col-4 mb-3">
                                <label for="value" class="form-label is-required">Valor de la cotización</label>
                                <input type="number" class="form-control" name="value" min="0" step="0.01"
                                    value="{{ $quote->value }}" placeholder="0.00" required />
                            </div>
                            <div class="col-4 mb-3">
                                <label for="priority" class="form-label is-required">Prioridad</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    @foreach ($quote_priority as $priority)
                                        <option value="{{ $priority->value }}"
                                            {{ $quote->priority->value == $priority->value ? 'selected' : '' }}>
                                            {{ $priority->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4 mb-3">
                                <label for="status" class="form-label is-required">Estado</label>
                                <select class="form-select" id="status" name="status" required>
                                    @foreach ($quote_status as $status)
                                        <option value="{{ $status->value }}"
                                            {{ $quote->status->value == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="comments" class="form-label">Comentarios</label>
                                <textarea class="form-control" id="comments" name="comments" rows="3">{{ $quote->comments }}</textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="file" class="form-label">Archivo PDF</label>
                                <input type="file" class="form-control" id="file" name="file">
                                @if ($quote->file)
                                    <small class="text-muted">Archivo actual: <a
                                            href="{{ route('customer.quote.download', ['id' => $quote->id]) }}">Descargar
                                            PDF</a></small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12 mb-3">
                    <div class="border rounded shadow p-3">
                        <div class="fw-bold mb-2 fs-5">Cambios y movimientos</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm caption-top">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Campo</th>
                                        <th>Antes</th>
                                        <th>Después</th>
                                        <th>Modificado por</th>
                                        <th>Cuando</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($histories as $cambio)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $historyColumns[ucfirst(str_replace('_', ' ', $cambio->changed_column))] ?? '-' }}</td>
                                            <td>{{ $cambio->old_value ?? '-' }}</td>
                                            <td>{{ $cambio->new_value }}</td>
                                            <td>{{ optional($cambio->user)->name ?? 'Sistema' }}</td>
                                            <td>{{ $cambio->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
        </form>
    </div>
@endsection
