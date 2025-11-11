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

        .draggable {
            width: 150px;
            cursor: pointer;
        }

        .droppable {
            min-height: 50px;
        }

        .container {
            overflow: hidden;
        }

        .content {
            width: 1000px;
            white-space: wrap;
        }
    </style>

    <div class="row w-100 justify-content-between m-0 h-100">
        

        <div class="col-11 p-3 container">
             <div class="col-12">
                
                @if ($section == 1)
                    @include('dashboard.planning.tables.schedule')
                @endif

                @if ($section == 2)
                    @include('dashboard.planning.tables.planning')
                @endif
            </div>
        </div>
    </div>
@endsection
