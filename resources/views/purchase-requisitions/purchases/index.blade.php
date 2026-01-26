@extends('layouts.app')
@section('content')
    <div class="row w-100 h-100 m-0">
        @include('purchase-requisitions.navigation')
        <div class="col-11 p-3 m-0">
            <div class="container-fluid">
                <div class="row justify-content-between">
                    <div class="col">
                        <a class="btn btn-primary" href="{{ route('purchase-requisition.create') }}">
                            <i class="bi bi-plus-lg fw-bold"></i> Crear requisición
                        </a>
                    </div>
                    <div class="w-25">
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
                    </div>
                </div>

                
                <div class="table-responsive">
                    @include('purchase-requisitions.purchases.tables.index')
                </div>
                {{ $purchaseRequisitions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    
@endsection
