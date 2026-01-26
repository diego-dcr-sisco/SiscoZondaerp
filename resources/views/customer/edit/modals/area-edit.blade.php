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
    </style>

    <div class="row m-0 w-100 h-100">
            @include('customer.navigation')

        <div class="col-11">
            <div class="row p-3 border-bottom">
                <a href="javascript:history.back()" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 m-0">Editar zona <span class="fw-bold">{{$zone->name}}</span></h1>
            </div>
            
            <div class="container" >
            @include('messages.alert')
                <form class="modal-content" id="area-form" action="{{ route('quality.zone.update', ['id' => $zone->id]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')
                    <div class="modal-header">
                        <h1 class="modal-title fs-5 fw-bold" id="areaModalEditLabel">Editar zona</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="{{ $zone->id }}">
                        <div class="mb-3">
                            <label for="exampleFormControlInput1" class="form-label is-required">Nombre de zona</label>
                            <input type="text" class="form-control" id="area-name" name="name"
                                placeholder="Escribe el nombre de la zona" value="{{ $zone->name }}">
                        </div>
                        <div class="mb-3">
                            <label for="exampleFormControlInput2" class="form-label">Tipo de zona</label>
                            <select class="form-select " id="area-zone-type" name="zone_type_id">
                                <option value="">No Aplica (N/A)</option>
                                @foreach($zone_types as $item)
                                    <option value="{{$item->id}}" {{ $zone->zone_type_id == $item->id ? 'selected' : '' }}>{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="m2" class="form-label is-required">Metros cuadrados (m²)</label>
                            <input type="number" class="form-control" id="area-m2" name="m2" min="0" max="10000" value="{{ $zone->m2 }}" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary"> {{ __('buttons.update') }} </button>
                        <button type="button" class="btn btn-danger" onclick="history.back()">{{ __('buttons.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('js/user/validations.min.js') }}"></script>
@endsection

