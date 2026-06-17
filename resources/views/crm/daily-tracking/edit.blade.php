@extends('layouts.app')

@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR CRM',
        'icon' => 'bi-graph-up',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid p-0">

        {{-- Header --}}
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
