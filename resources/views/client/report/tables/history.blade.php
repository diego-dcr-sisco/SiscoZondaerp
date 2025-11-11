@if(isset($files) && empty($files))
<div class="alert alert-danger alert-dismissible" role="alert">
    No se encontraron coincidencias
    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert"
        aria-label="Close"
    ></button>
</div>
@endif

<table class="table table-bordered text-center caption-top">
    <caption class="border bg-secondary-subtle p-2 fw-bold text-dark">
        {{ __('order.navbar.history') }}
    </caption>
    <thead>
        <tr>
            <th class="col-2" scope="col">#</th>
            <th scope="col">Cliente</th>
            <th scope="col">Fecha</th>
            <th class="col-2" scope="col">{{ __('buttons.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($files)) @foreach($files as $file)
        @php 
            $filename = splitFileName($file['name']);
        @endphp
        <tr>
            <th scope="row">{{ str_replace(".pdf", "", $filename[2]) }}</th>
            <td>{{ $filename[1] }}</td>
            <td>{{ DateTime::createFromFormat('Ymd', $filename[0])->format('d-m-Y') }}</td>
            <td>
                <a
                    href="{{ Route('client.file.download', ['path' => $file['path']]) }}"
                    class="btn btn-success btn-sm"
                >
                    <i class="bi bi-download"></i> {{ __('buttons.download') }}
                </a>
            </td>
            @endforeach @endif
        </tr>
    </tbody>
</table>
