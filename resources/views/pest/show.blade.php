@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'VER PLAGA',
        'icon' => 'bi-bug',
        'backRoute' => url()->previous(),
    ])
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
        <div class="col-1 m-0" style="background-color: #343a40;"> </div>
        <div class="col-11">
<div class="row justify-content-center p-5">
                <div class="col-6 text-center">
                    <img class="img-thumbnail shadow border-2" src="{{ asset($pest->image) }}" style="width: 25rem">
                </div>
                <div class="col-6 p-3">
                    <div class="row">
                        <span class="col fw-bold">{{ __('pagination.pest_catalog.nom') }}: <span
                                class="fw-normal">{{ $pest->name }}</span> </span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('pagination.pest_catalog.pcode') }}: <span
                                class="fw-normal">{{ $pest->pest_code }}</span> </span>

                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('pagination.pest_catalog.categ') }}: <span class="fw-normal">
                                @foreach ($categs as $categ)
                                    @if ($categ->id == $pest->pest_category_id)
                                        {{ $categ->category }}
                                    @endif
                                @endforeach
                            </span>
                        </span>
                    </div>
                    <div class="row">
                        <span class="col fw-bold">{{ __('pagination.pest_catalog.desc') }}: <span
                                class="fw-normal">{{ $pest->description }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
