@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-3">
            <a href="javascript:history.back()" class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0">{{ __('control_point.title.create') }}</h1>
        </div>
        <div class="row justify-content-center">
            <div class="col-11">
                
            </div>
        </div>
    </div>

    <script>
        function showSelect(selectedValue) {
            var select = document.getElementById("category_select");
            if (selectedValue == 12) {
                select.style.display = "block";
            } else {
                select.style.display = "none";
            }
        }
    </script>
@endsection
