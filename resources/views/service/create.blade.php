@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CREAR SERVICIO',
        'icon' => 'bi-tools',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid p-0">
@include('service.create.form')
    </div>
    <script src="{{ asset('js/service/functions.min.js') }}"></script>
@endsection
