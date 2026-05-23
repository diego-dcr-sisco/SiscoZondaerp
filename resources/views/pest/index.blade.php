@extends('layouts.app')
@section('content')
    @php
        $offset = ($pests->currentPage() - 1) * $pests->perPage();
    @endphp

    <div class="container-fluid">
        <div class="py-3">
            @can('write_product')
                <a class="btn btn-primary btn-sm" href="{{ route('pest.create') }}">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear plaga
                </a>
            @endcan
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">{{ __('pest.data.name') }} </th>
                        <th scope="col">{{ __('pest.data.code') }} </th>
                        <th scope="col">{{ __('pest.data.category') }} </th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pests as $index => $pest)
                        <tr>
                            <th scope="row">{{ $offset + $index + 1 }}</th>
                            @if ($pest->image)
                                <td><img src="{{ route('image.show', ['path' => $pest->image]) }}"
                                        style="width: 60px; height: 60px;" alt="min-img"></td>
                            @else
                                <td><i class="bi bi-image"></i></td>
                            @endif
                            <td>{{ $pest->name ?? '-' }}</td>
                            <td>{{ $pest->pest_code ?? '-' }}</td>
                            <td>{{ $pest->pestCategory->category ?? '-' }}</td>
                            <td>
                                <a href="{{ route('pest.edit', ['id' => $pest->id]) }}" class="btn btn-secondary btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Editar plaga"><i
                                        class="bi bi-pencil-square"></i></a>
                                <a href="{{ route('pest.destroy', ['id' => $pest->id]) }}" class="btn btn-danger btn-sm"
                                    onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar plaga">
                                    <i class="bi bi-trash-fill"></i> </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-danger fw-bold text-center" colspan="6">Sin plagas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pests->links('pagination::bootstrap-5') }}
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
