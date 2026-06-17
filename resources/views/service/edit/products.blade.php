@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR SERVICIO',
        'icon' => 'bi-tools',
        'backRoute' => url()->previous(),
    ])
<form class="m-3">
        <div class="border rounded shadow p-3">
            <span class="">En desarrollo</span>
        </div>
    </form>
@endsection
