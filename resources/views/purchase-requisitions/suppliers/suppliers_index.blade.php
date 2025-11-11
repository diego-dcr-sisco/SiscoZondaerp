@extends('layouts.app')

@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .flat-btn {
            background-color: #55ff00;
        }
    </style>

    <div class="row w-100 h-100 m-0">
        @include('purchase-requisitions.navigation')
        <div class="col-11 p-3 m-0">
            <div class="container-fluid">
                <div class="row justify-content-between">
                    <div class="col">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                            <i class="bi bi-plus-lg fw-bold"></i> Crear proveedor
                        </button>
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
                    @include('purchase-requisitions.suppliers.tables.index')
                </div>
                {{ $suppliers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    @include('purchase-requisitions.modals.add_supplier_modal')
@endsection
