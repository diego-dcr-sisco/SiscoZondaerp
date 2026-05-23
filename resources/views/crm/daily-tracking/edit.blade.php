@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0">

        {{-- Header --}}
        <div class="d-flex align-items-center border-bottom ps-4 p-2 mb-3">
            <a href="{{ route('crm.daily-tracking.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-5">EDITAR REGISTRO DE ACTIVIDAD DIARIA</span>
            <span class="ms-3 text-muted">#{{ $dailyTracking->id }}</span>
        </div>

        <div class="px-4 pb-4">
            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <strong>Corrige los siguientes errores:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('crm.daily-tracking.update', $dailyTracking) }}" method="POST">
                @csrf
                @method('PUT')
                @include('crm.daily-tracking._form', ['dailyTracking' => $dailyTracking])

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Actualizar registro
                    </button>
                    <a href="{{ route('crm.daily-tracking.show', $dailyTracking) }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
