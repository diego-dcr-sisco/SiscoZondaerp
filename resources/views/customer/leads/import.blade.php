@extends('layouts.app')

@section('content')
    <div class="row w-100 h-100 m-0">
        @include('crm.navigation')
        <div class="col-11 p-3 m-0">
            <div class="row border-bottom py-3 mb-3">
                <a href="{{ route('leads.index') }}" class="col-auto btn-primary p-0"><i
                        class="bi bi-arrow-left m-3 fs-4"></i></a>
                <h1 class="col-auto fs-2 fw-bold m-0"> Importar tabla de clientes potenciales </h1>
            </div>
            {{-- Card para importacion --}}
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm mt-4">
                        <div class="card-header">Importar Leads</div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('leads.import') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label for="file" class="form-label">Archivo</label>
                                    <input class="form-control" type="file" id="file" name="file" required
                                        accept=".csv, .xlsx, .xls, .tsv, .ods, text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                    <div class="form-text">
                                        Formatos aceptados: CSV, TSV, XLSX, XLS, ODS<br>
                                        Columnas requeridas: Nombre, Teléfono<br>
                                        Columnas opcionales: Correo, Estado, Servicio, Estado del cliente, Fecha
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-lg-flex justify-content-lg-end">
                                    <a href="{{ route('leads.index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Importar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ejemplo de plantilla tabla para importar --}}
            
        </div>
    </div>
@endsection
