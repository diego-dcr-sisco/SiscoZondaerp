@extends('layouts.app')
@section('content')

    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
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
    </style>

    <div class="row w-100 justify-content-between m-0 h-100">
        <div class="col-1 m-0" style="background-color: #343a40;">
            <div class="row">
                <a href="{{ Route('point.show', ['id' => $point->id, 'section' => 1]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Datos generales
                </a>
                <a href="{{ Route('point.show', ['id' => $point->id, 'section' => 2]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Preguntas
                </a>
            </div>
        </div>

        <div class="col-11">
            <div class="row p-3 border-bottom">
                <a href="{{ route('point.index') }}" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 fw-bold m-0">Ver punto de control [{{ $point->id }}] {{ $point->name }}
                </h1>
            </div>
            <div class="row p-5 pt-3">
                @switch($section)
                    @case(1)
                        <div class="col-12">
                            <div class="row">
                                <span class="col fw-bold">Nombre: </span>
                                <span class="col fw-normal">{{ $point->name }}</span>
                            </div>
                            <div class="row fw-bold">
                                <span class="col">Dispositivo asociado: </span>
                                <span class="col">
                                    {{ isset($point->product) ? $point->product->name : 'S/N' }}
                                </span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">Color: </span>
                                <span class="col fw-normal">
                                    <div class="w-25">
                                        <div class="rounded w-25" style="background-color: {{ $point->color }}; height: 30px;">
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">Línea de negocio: </span>
                                <span class="col fw-normal">{{ $point->product->lineBusiness->name ?? 'N/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">Finalidad: </span>
                                <span class="col fw-normal">{{ $point->product->purpose->type ?? 'N/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">Nombre: </span>
                                <div class="col fw-normal">
                                    @if ($point->products->isEmpty())
                                        <p class="text-danger">Sin productos asociados</p>
                                    @else
                                        <ul class="ps-3" style="list-style-type: square;">
                                            @foreach ($point->products as $product)
                                                <li>{{ $product->name }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @break

                    @case(2)
                        <div class="col-12">
                            @if ($point->questions)
                                <table class="table table-bordered table-striped text-center">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('product.point.question') }} </th>
                                            <th scope="col">{{ __('product.point.op_question') }} </th>
                                            <th scope="col">{{ __('product.point.description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($point->questions as $index => $question)
                                            <tr>
                                                <th scope="row">{{ $index + 1 }}</th>
                                                <td>{{ $question->question }}</td>
                                                <td>{{ $question->option->value }}</td>
                                                <td>{{ $question->option->description }} <span class="fw-bold">{{ $question->option->value }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <span class="col fw-bold">Sin preguntas asociadas</span>
                            @endif
                        </div>
                    @break
                @endswitch
            </div>
        </div>
    </div>
@endsection
