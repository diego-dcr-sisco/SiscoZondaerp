@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('product.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                PLAGAS DEL PRODUCTO </span> <span class="ms-2 fs-4"> {{ $product->name }}</span>
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('product.update', ['id' => $product->id]) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="accordion accordion-flush" id="accordionPest">
                        <div class="row">
                            @foreach ($pest_categories as $i => $pest_category)
                                <div class="col-lg-4 col-12">
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed border-bottom" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $i }}"
                                                aria-expanded="true" aria-controls="collapse{{ $i }}">
                                                {{ $pest_category->category }}
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $i }}" class="accordion-collapse collapse"
                                            data-bs-parent="#accordionPest">
                                            <div class="accordion-body">
                                                @if (!$pest_category->pests->isEmpty())
                                                    @foreach ($pest_category->pests as $pest)
                                                        <div class="form-check">
                                                            <input class="pest form-check-input " type="checkbox"
                                                                value="{{ $pest->id }}" onchange="setPests()"
                                                                {{ $product->hasPest($pest->id) ? 'checked' : '' }} />
                                                            <label class="form-check-label" for="pest-{{ $pest->id }}">
                                                                {{ $pest->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-danger fw-bold"> No hay plagas asociadas </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="pests-selected" name="pests_selected" value="" />
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
        </form>
    </div>
    <script src="{{ asset('js/handleSelect.js') }}"></script>
@endsection
