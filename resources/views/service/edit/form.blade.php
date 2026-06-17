@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR SERVICIO',
        'icon' => 'bi-tools',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid p-0">
<form class="m-3" method="POST" action="{{ route('service.update', ['id' => $service->id]) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="fw-bold mb-2 fs-5">Datos del servicio</div>
                    <div class="col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('service.data.name') }}</label>
                        <div class="input-group">
                            <select class="input-group-text bg-warning" id="prefix" name="prefix">
                                @foreach ($prefixes as $prefix)
                                    <option value="{{ $prefix->id }}"
                                        {{ $prefix->id == $service->prefix ? 'selected' : '' }}>
                                        {{ $prefix->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control rounded-end" id="name" name="name"
                                placeholder="{{ __('service.data.input.name') }}" value="{{ $service->name }}"
                                autocomplete="off" required />
                        </div>
                    </div>

                    <div class="col-lg-3 col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('service.data.service_type') }} </label>
                        <select class="form-select" id="service_type" name="service_type_id" required>
                            @foreach ($service_types as $service_type)
                                <option value="{{ $service_type->id }}"
                                    {{ $service_type->id == $service->service_type_id ? 'selected' : '' }}>
                                    {{ $service_type->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-lg-3 col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('service.data.business_line') }}</label>
                        <select class="form-select " id="business_line" name="business_line_id" required>
                            <option value="" selected disabled>Selecciona una opción</option>
                            @foreach ($business_lines as $business_line)
                                <option value="{{ $business_line->id }}" @if ($business_line->id == $service->business_line_id) selected @endif>
                                    {{ $business_line->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="description" class="form-label is-required">Descripción del servicio</label>
                        <div id="summary-describe" class="smnote" style="height: 250px">
                            {!! $service->description !!}
                        </div>
                        <input type="hidden" id="description" name="description" value="{{ $service->description }}" />
                    </div>

                    <div class="col-lg-2 col-12 mb-3">
                        <label for="name" class="form-label">Costo del servicio</label>
                        <div class="input-group mb-0">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="cost" name="cost"
                                value="{{ $service->cost }}" min="0" placeholder="0" step="0.01" />
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-3">
                {{ __('buttons.update') }}
            </button>
        </form>
        @can('write_service')
            @include('components.danger-action', [
                'actionRoute' => route('service.destroy', ['id' => $service->id]),
                'title' => 'Zona de peligro',
                'description' => 'Elimina este servicio desde su pantalla de edición.',
                'buttonText' => 'Eliminar servicio',
            ])
        @endcan
    </div>

    @include('components.quill-service-editor-tools')

    <script>
        $(document).ready(function() {
            const serviceDescriptionEditor = new Quill('#summary-describe', {
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

            function syncServiceDescription() {
                const html = serviceDescriptionEditor.root.innerHTML;
                $('#description').val(html === '<p><br></p>' ? '' : html);
            }

            serviceDescriptionEditor.on('text-change', syncServiceDescription);
            addServiceEditorTableTools(serviceDescriptionEditor, 'summary-describe', syncServiceDescription);
            $('form').on('submit', syncServiceDescription);
        });
    </script>
@endsection
