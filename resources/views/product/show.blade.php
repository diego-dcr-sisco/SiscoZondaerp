@extends('layouts.app')
@section('content')
    <!-- Formulario para ver un pesticida-->
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    @php
        function isPDF($filePath)
        {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            return $extension === 'pdf' || $extension == 'PDF';
        }
    @endphp

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
                <a href="{{ Route('product.show', ['id' => $product->id, 'section' => 1]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Datos generales
                </a>
                <a href="{{ Route('product.show', ['id' => $product->id, 'section' => 2]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Datos económicos
                </a>
                <a href="{{ Route('product.show', ['id' => $product->id, 'section' => 3]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Plagas
                </a>
                <a href="{{ Route('product.show', ['id' => $product->id, 'section' => 4]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Archivos
                </a>
            </div>
        </div>

        <div class="col-11">
            <div class="row p-3 border-bottom">
                <a href="{{ route('product.index') }}" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 m-0">{{ __('product.title.show') }} <span
                        class="fw-bold">[{{ $product->name }}]</span></h1>
            </div>
            @if ($section == 1)
                <div class="row p-3">
                    <div class="col-3 p-3">
                        @if ($product->image_path)
                            <img class="border border-1 rounded p-3"
                                src="{{ route('image.show', ['filename' => $product->image_path]) }}" style="width: 15em;">
                        @else
                            <p class="text-danger fw-bold"> Sin imagen selecionada</p>
                        @endif
                    </div>
                    <div class="col-9 p-3">
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.name') }} :</span>
                            <span class="col fw-normal">{{ $product->name }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.business_name') }} :</span>
                            <span class="col fw-normal">{{ $product->business_name ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.bar_c') }} :</span>
                            <span class="col fw-normal">{{ $product->bar_code ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.obsolete') }} :</span>
                            <span
                                class="col fw-normal {{ $product->obsolete ? 'text-danger' : 'text-success' }}">{{ $product->obsolete ? 'No' : 'Si' }}</span>
                        </div>

                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.pres') }} :</span>
                            <span class="col fw-normal">{{ $product->presentation->name ?? 'S/A' }}</span>
                        </div>

                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.line_b') }}</span>
                            <span class="col fw-normal">{{ $product->lineBusiness->name ?? 'S/A' }}</span>
                        </div>


                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.porp') }} :</span>
                            <span class="col fw-normal">{{ $product->purpose->name ?? 'S/A' }}</span>
                        </div>

                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.manufacturer') }} :</span>
                            <span class="col fw-normal">{{ $product->manufacturer ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">Descripción:</span>
                            <span class="col fw-normal">{{ $product->description ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.indications_execution') }} :</span>
                            <span class="col fw-normal">{{ $product->indications_execution ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.register_number') }} :</span>
                            <span class="col fw-normal">{{ $product->register_number ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.valid_date') }} :</span>
                            <span class="col fw-normal">{{ $product->validity_date ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.active_ingredient') }} :</span>
                            <span class="col fw-normal">{{ $product->active_ingredient ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.peractive_ingredient') }} :</span>
                            <span class="col fw-normal">{{ $product->per_active_ingredient ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.dos') }} :</span>
                            <span class="col fw-normal">{{ $product->dosage ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.safety_period') }} :</span>
                            <span class="col fw-normal">{{ $product->safety_period ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.res_ef') }} :</span>
                            <span class="col fw-normal">{{ $product->residual_effect ?? 'S/A' }}</span>
                        </div>
                        <div class="row">
                            <span class="col fw-bold">{{ __('product.product-data.tox') }} :</span>
                            <span
                                class="col fw-normal {{ $product->toxicity ? 'text-success' : 'text-danger' }}">{{ $product->toxicity ? '(Si) ' . $product->toxicityCategory->name : 'No' }}</span>
                        </div>
                        @if ($product->toxicity == 1)
                            <div class="row">
                                <span class="col fw-bold">{{ __('product.product-data.tox_c') }} :</span>
                                <span class="col fw-normal">{{ $product->toxicityCategory->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($section == 2)
                <div class="container-fluid pt-3">
                    <div class="row">
                        <span class="col fw-bold">{{ __('product.econom-data-product.purchase_price') }} :</span>
                        <span class="col fw-normal">{{ $product->purchase_price ?? 'S/A' }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('product.econom-data-product.min_purchase_unit') }} :</span>
                        <span class="col fw-normal">{{ $product->min_purchase_unit ?? 'S/A' }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('product.econom-data-product.mult_purchase') }} :</span>
                        <span class="col fw-normal">{{ $product->mult_purchase ?? 'S/A' }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('product.econom-data-product.supplier') }} :</span>
                        <span class="col fw-normal">{{ $product->supplier->name ?? 'S/A' }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">Es vendible? </span>
                        @if ($product->selling == 1)
                            <span class="col fw-normal">Si</span>
                        @else
                            <span class="col fw-normal">No</span>
                        @endif
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('product.econom-data-product.selling_price') }} :</span>
                        <span class="col fw-normal">{{ $product->selling_price }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('product.econom-data-product.subaccount_purchases') }} :</span>
                        <span class="col fw-normal">{{ $product->subaccount_purchases }}</span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('product.econom-data-product.subaccount_sales') }} :</span>
                        <span class="col fw-normal">{{ $product->subaccount_sales }}</span>
                    </div>
                </div>
            @endif

            @if ($section == 3)
                <div class="container-fluid pt-3">
                    @if (!$product->pests->isEmpty())
                        @foreach ($product->pests as $i => $pest)
                            <div class="row">
                                <span class="col fw-bold">Plaga {{ $i + 1 }} :</span>
                                <span class="col fw-normal">{{ $pest->name }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-danger"> Sin plagas asociadas </p>
                    @endif
                </div>
            @endif

            @if ($section == 4)
                <div class="container-fluid pt-3">
                    <table class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nombre del archivo</th>
                                <th scope="col">Archivo</th>
                                <th scope="col">Fecha de vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filenames as $index => $filename)
                                <tr>
                                    <th class="align-middle" scope="row">{{ $index + 1 }}</th>
                                    <td class="align-middle">{{ $filename->name }}</td>
                                    <td class="align-middle">
                                        @if ($product->file($filename->id))
                                            <a
                                                href="{{ route('product.file.download', ['id' => $product->id, 'file' => $filename->id]) }}">
                                                {{ $filename->name }} </a>
                                        @else
                                            <p class="text-danger fw-bold">Sin archivo</p>
                                        @endif
                                    </td>
                                    <td class="align-middle">{{ $product->file($filename->id)->expirated_at ?? 'S/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
