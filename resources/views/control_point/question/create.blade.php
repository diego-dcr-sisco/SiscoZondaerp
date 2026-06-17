@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CREAR PUNTO DE CONTROL',
        'icon' => 'bi-geo-alt',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid">
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
