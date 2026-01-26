@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('point.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR PUNTO DE CONTROL
            </span>
        </div>
        <form method="POST" class="m-3" action="{{ route('point.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-4 col-12 mb-3">
                    <div class="border rounded shadow p-3">
                        <div class="row">
                            <div class="fw-bold mb-2 fs-5">Datos del punto de control</div>
                            <div class="col-12 mb-3">
                                <label for="name" class="form-label is-required">Nombre: </label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Equipo" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="device" class="form-label is-required"> Dispositivo asociado: </label>
                                <select class="form-select " name="device_id" id="associated-device-id" required>
                                    @foreach ($devices as $device)
                                        <option value="{{ $device->id }}">{{ $device->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="code" class="form-label is-required">Código: </label>
                                <input type="text" class="form-control" id="code" name="code" placeholder="EQ"
                                    required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="colorPicker" class="form-label is-required">Color: </label>
                                <input type="color" style="height: 40px;" class="form-control-file form-control"
                                    id="color" name="color" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-12 mb-3">
                    <div class="border rounded shadow p-3">
                        <div class="fw-bold mb-2 fs-5">Pregunta(s)</div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#questionModal"><i class="bi bi-plus-lg"></i> Nueva</button>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#exQuestionModal"><i class="bi bi-plus-lg"></i> Existente</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Pregunta</th>
                                        <th scope="col">Opciones</th>
                                        <th scope="col">Respuesta default</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody id="question-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" class="form-control" id="questions" name="questions" required>
            <button type="submit" class="btn btn-primary my-3">{{ __('buttons.store') }}</button>
        </form>
    </div>

    @include('control_point.modals.questions')
    @include('control_point.modals.existing-question')

    <script>
        const answers = @json($answers);
        const existing_questions = @json($questions);
        var select_questions = [];
        var questions = [];

        var count_newqs = 0;

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        function paintQuestions() {
            var html = ``;
            $('#question-table-body').html(html);
            questions.forEach((question, i) => {
                html += `
                <tr>
                    <th scope="row">${ i+1 }</th>
                    <td>${question.text}</td>
                    <td>${question.options}</td>
                    <td>${question.answer_default}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar pregunta"
                            onclick="deleteQuestion('${question.key}')">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            `;
            })
            $('#question-table-body').html(html);
        }

        function deleteQuestion(key) {
            questions = questions.filter(q => q.key != key);
            $('#questions').val(JSON.stringify(questions));
            paintQuestions();
        }
    </script>
@endsection
