@extends('layouts.app')
@section('content')
    <div class="row w-100 h-100 m-0">
        @include('purchase-requisitions.navigation')
        <div class="col-11 p-3 m-0">
            <div class="container-fluid">
                
                <div class="row justify-content-between">
                    
                    <!-- <div class="w-25">
                        <div type="browser" class="row mb-3">
                            <form action="{{ route('supplier.index') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" class="form-control d-flex" name="search"
                                        placeholder="Buscar por nombre" aria-label="Recipient's username"
                                        aria-describedby="button-addon2">
                                    <button class="btn btn-success" type="submit" id="button-addon2">Buscar</button>
                                </div>
                            </form>
                        </div>
                    </div> -->
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <h2 class="text-center">Consumo de Materiales</h2>
                    </div>
                    <div class="col-lg-6 text-end">
                        <a href="{{ route('consumption.create') }}" class="btn btn-primary">Registrar Consumo</a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <form action="{{ route('consumption.index') }}" method="GET" class="d-flex">
                            <input type="text" class="form-control me-2" name="search"
                                placeholder="Buscar por nombre o código" aria-label="Search">
                            <button class="btn btn-success" type="submit">Buscar</button>
                        </form>
                    </div>
                </div>

                {{-- <div class="table-responsive">
                    @include('purchase-requisitions.consumption.tables.index')
                </div>
                {{ $consumptions->links('pagination::bootstrap-5') }}
                 --}}
            </div>
        </div>
    </div>

    
@endsection
