@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                PLANO <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $floorplan->filename }}</span>
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('floorplan.update', ['id' => $floorplan->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="col-lg-4 col-12 mb-3">
                        <div class="d-flex justify-content-center border rounded p-3" style="height: 400px;">
                            <img src="{{ route('image.show', ['path' => $floorplan->path]) }}" class="img-fluid rounded-3"
                                alt="Vista previa del plano">
                        </div>
                    </div>
                    <div class="col-lg-8 col-12 mb-3">
                        <div class="mb-3">
                            <label for="file" accept=".png, .jpg, .jpeg" class="form-label">Subir nuevo plano:</label>
                            <input type="file" class="form-control" id="file" name="file"
                                 accept=".png, .jpg, .jpeg">
                            <div class="form-text">Formatos aceptados: .png, .jpg, .jpeg</div>
                        </div>
                        <div class="mb-3">
                            <label for="filename" class="form-label">Nombre: </label>
                            <input type="string" class="form-control" id="filename" name="filename"
                                value="{{ $floorplan->filename }}">
                        </div>
                        <div class="mb-3">
                            <label for="exampleFormControlInput1" class="form-label">Servicio: </label>
                            <select class="form-select" id="service_id" name="service_id">
                                <option value="0"> Sin servicio </option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}"
                                        {{ $floorplan->service_id == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
        </form>
    </div>
@endsection
