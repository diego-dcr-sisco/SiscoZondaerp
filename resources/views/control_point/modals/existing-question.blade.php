<div class="modal fade" id="exQuestionModal" tabindex="-1" aria-labelledby="exQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exQuestionModalLabel">Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <input class="form-check-input" type="checkbox"
                                            onchange="setAllQuestions(this.checked)">
                                    </th>
                                    <th scope="col">#</th>
                                    <th scope="col">Pregunta</th>
                                    <th scope="col">Opciones</th>
                                    <th scope="col">Respuesta default</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($questions as $index => $quest)
                                    <tr>
                                        <th scope="col">
                                            <input class="form-check-input question-checkbox" type="checkbox"
                                                value="{{ $quest['id'] }}"
                                                onchange="setQuestionId({{ $quest['id'] }})"
                                                {{ $quest['has_question'] ? 'checked' : '' }}>
                                        </th>
                                        <th scope="col">{{ $index + 1 }}</th>
                                        <th scope="col">{{ $quest['text'] }}</th>
                                        <th scope="col">{{ implode(', ', $quest['options']) }}</th>
                                        <th scope="col">{{ $quest['answer_default'] }}</th>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="setExQuestions()">Agregar</button>
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


    function setAllQuestions(checked) {
        $('.question-checkbox').each(function() {
            $(this).prop('checked', checked);
            const id = parseInt($(this).val());

            if (checked) {
                if (!select_questions.includes(id)) {
                    select_questions.push(id);
                }
            } else {
                select_questions = [];
            }
        });
    }

    function setQuestionId(q_id) {
        if (select_questions.includes(q_id)) {
            select_questions = select_questions.filter(q => q != q_id);
        } else {
            select_questions.push(q_id);
        }
    }

    function setExQuestions() {
        const found_questions = existing_questions.filter(ex_q => select_questions.includes(ex_q.id));
        found_questions.forEach(question => {
            const alreadyExists = questions.some(q => q.key == question.key);

            if (!alreadyExists) {
                questions.push({
                    key: question.key,
                    id: question.id,
                    text: question.text,
                    option: question.option,
                    options: question.options,
                    answer_default: question.answer_default,
                    has_question: true,
                });
            }
        });

        
        paintQuestions();
        $('#questions').val(JSON.stringify(questions));
        $('#exQuestionModal').modal('hide');
    }
</script>
