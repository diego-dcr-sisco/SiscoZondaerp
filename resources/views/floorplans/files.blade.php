<div class="row">
    <div class="col-4 p-5 pt-3">
        <label for="plans" class="form-label">Planos agregados</label>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">An item</li>
            <li class="list-group-item">A second item</li>
            <li class="list-group-item">A third item</li>
            <li class="list-group-item">A fourth item</li>
            <li class="list-group-item">And a fifth one</li>
        </ul>
    </div>
    <div class="col-8 p-5 pt-3">
        <form class="row border shadow rounded p-3" action="{{ route('floorplan.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            <div class="col-12 mb-3">
                <div class="row mb-2">
                    <div class="col-auto d-flex flex-row align-items-center">
                        <label for="filename" class="form-label is-required">Nombre: </label>
                    </div>
                    <div class="col-sm-10">
                        <input class="form-control" type="text" id="filename" name="filename"
                            required>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <div class="row border  rounded shadow-sm p-3 m-0 mb-2">
                    <h5 class="fw-bold">Arrastra y suelta o elige tus archivos haciendo clic aquí:</h5>
                    <p class="text-danger mb-0">
                        Solo se permiten archivos con formato .PDF .JPG .JPEG .PNG
                    </p>
                    <p class="text-danger">
                        Los archivos deben ser menores a 3MB.
                    </p>
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <input accept=".png, .jpg, .jpeg" type="file" name="file" required>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Crear plano</button>
            </div>
        </form>
    </div>
</div>

<div id="edit_files" class="row p-3 pt-1">
    <ul class="list-group col-4 mb-5">
        @if (!$customer->floorplans->isEmpty())
            @foreach ($customer->floorplans as $item)
                <li class="list-group-item ">
                    <div class="row">
                        <strong>{{ $item->filename }}</strong>
                    </div>
                    <div class="row">
                        <p class="text-center ">
                            {{ $item->path }}
                            <a onclick="return confirm('Estas seguro de eliminar el archivo {{ $item->filename }}?')"
                                href="{{ route('floorplan.delete', ['id' => $customer->id, 'fileid' => $item->id]) }}"
                                class=" btn btn-danger btn-sm float-end">Eliminar
                            </a>
                        </p>
                    </div>
                </li>
            @endforeach
        @endif
    </ul>
  
</div>
