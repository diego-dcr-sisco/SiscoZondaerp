<div class="modal fade" id="questionModal" tabindex="-1" aria-labelledby="questionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionModalLabel">Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="question" class="form-label is-required">Texto de la pregunta: </label>
                    <input type="text" class="form-control" id="question-text" name="question_text" placeholder="Escribe aqui la pregunta..." required>
                </div>

                <div class="fw-bold mb-2">Opciones de respuesta</div>
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th scope="col">Opciones</th>
                            <th scope="col">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($options as $option)
                            <tr>
                                <td><input type="radio" name="option_id" value="{{ $option->id }}"
                                        onchange="handleOption()">
                                    {{ $option->value }} </td>
                                <td class="text-center">{{ $option->description }} </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mb-3">
                    <label for="question" class="form-label is-required">Opción por default: </label>
                    <input type="text" class="form-control" id="option-default-input" name="option_default">
                    <select class="form-select" id="option-default-select" name="option_default"></select>
                    <input type="hidden" id="default-answer" name="default_answer" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addQuestion()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        const $select = $('#option-default-select');
        const $input = $('#option-default-input');

        $input.show();
        $select.hide();

        $input.attr('placeholder', 'Escribe tu respuesta por default');
        $input.prop('disabled', true)
    });


    function getOptions(id, answers) {
        for (const answer of answers) {
            if (answer.id == id) {
                return answer.options;
            }
        }
        return [];
    }

    function handleOption() {
        const option_value = $('input[name="option_id"]:checked').val();

        if (!option_value) {
            alert('Debes seleccionar una opción de respuesta')
            return;
        }

        const $select = $('#option-default-select');
        const $input = $('#option-default-input');
        var found_answers = getOptions(option_value, answers);

        $input.prop('disabled', false)

        if (found_answers.length > 0) {
            $input.val(null);
            $input.hide();
            $select.show();
            $select.empty();
            found_answers.forEach(function(found_answer, index) {
                $select.append('<option value="' + found_answer + '">' + found_answer + '</option>');
            });
        } else {
            $select.hide();
            $select.val(null);
            $input.show();
            $input.val('');
        }
    }

    function addQuestion() {
        const $select = $('#option-default-select');
        const $input = $('#option-default-input');
        const $option = $('input[name="option_id"]:checked');
        const $text = $('#question-text');

        var value = null;

        if (!$text.val() && $text.val() == '') {
            alert('Debes ingresar una pregunta');
        }

        if ($input.is(':visible')) {
            value = $input.val();
        } else if ($select.is(':visible')) {
            value = $select.val();
        }

        if (!value) {
            alert('Debes ingresar una respuesta obligatoria');
            return;
        }

        var question = {
            key: `qnw_${count_newqs}`,
            id: null,
            text: $text.val(),
            option: $option.val(),
            options: getOptions($option.val(), answers),
            answer_default: value,
            has_question: true,
        }

        count_newqs++;

        questions.push(question);
        paintQuestions();

        $('#questions').val(JSON.stringify(questions));
        $('#default-answer').val(value);
        $('#questionModal').modal('hide');
    }
    
</script>
