@extends('layouts.app')

@section('content')
@php
    $productid = session()->get('product_id');
@endphp

<div class="data-container border rounded shadow m-5 p-3 bg-white">
    <div class="edit-title d-flex justify-content-start align-items-center gap-2 text-center">
        <a href="{{ route('point.edit',$productid) }}" class="btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
        <h1 class="fs-2 fw-bold m-0">Edita pregunta</h1>
    </div>
    <div>
    <form method="POST" class="card card-body" action="{{ route('question.update', ['question' => $question->id]) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <label for="ask" class="form-label">Texto de la pregunta</label>
        <input type="text" class="form-control" id="ask" name="ask" value="{{$question->question}}">
        <br>
        <label for="ask" class="form-label">Tipo de respuesta admitida</label>
        <table id="asks">
            <thead>
                <tr>
                <th></th>
                <th>Valor</th>
                <th>Descripcion</th>
                </tr>
            </thead>
            <tbody>
                @foreach($question_options as $item)
                <tr>
                    <td>
                    @if($question->question_option_id == $item->id)
                        <input type="radio" name="selected_option" value="{{ $item->id }}"checked>
                    @else
                        <input type="radio" name="selected_option" value="{{ $item->id }}">
                    @endif
                    </td>
                    <td>{{ $item->value }}</td>
                    
                    <td>
                    {{ $item->description }} {{ $item->value }}
                  
                    @if($item->id == 12)
                        <select class="form-select border-secondary" name="category_select" id="category_select">
                            <option value="0">Elegir Categoria</option>
                            @foreach($pest_category as $pesc)
                                @if($pesc->id == $question->pest_category_id)
                                    <option value="{{$pesc->id}}" selected>{{$pesc->category}}</option>
                                @else
                                    <option value="{{$pesc->id}}">{{$pesc->category}}</option>
                                @endif
                            @endforeach
                        </select>
                    @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <button type="submit"  class="btn btn-success">Guardar</button>
    </form>
    </div>
</div>
@endsection
