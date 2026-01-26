<?php

namespace App\Http\Controllers;

use App\Models\ControlPoint;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ControlPointQuestion;
use App\Models\ProductCatalog;
use App\Models\PestCategory;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(string $pointId)
    {
        $question_options = QuestionOption::all();
        $quests = Question::all();

        return view('control_point.question.create', compact('question_options', 'quests', 'pointId'));
    }
    public function store(Request $request, string $pointId)
    {
        $question = new Question();
        $question->fill($request->all());
        $question->save();

        $point_question = ControlPointQuestion::where('control_point_id', $pointId)->where('question_id', $question->id)->first();
        if (!$point_question) {
            ControlPointQuestion::create(
                ['control_point_id' => $pointId, 'question_id' => $question->id, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        return redirect()->route('point.edit', ['id' => $pointId, 'section' => 2]);
    }

    public function set(Request $request, string $pointId)
    {
        $selected_questions = json_decode($request->input('selected_question'));
        if(!empty($selected_questions)) {
            $fetched_questions = ControlPointQuestion::where('control_point_id', $pointId)->pluck('question_id')->toArray();
            $delete_questions = array_diff($fetched_questions, $selected_questions);
            $insert_questions = array_diff($selected_questions, $fetched_questions);

            ControlPointQuestion::where('control_point_id', $pointId)->whereIn('question_id', $delete_questions)->delete();

            foreach ($insert_questions as $questionId) {
                ControlPointQuestion::insert([
                    'question_id' => $questionId,
                    'control_point_id' => $pointId,
                    'created_at' => now(),
                ]);
            }
        }
        return back();
    }


    public function edit($id)
    {
        $question = Question::find($id);
        $question_options = QuestionOption::all();
        $pest_category = PestCategory::all();

        return view('product.control_point.question.edit', compact('pest_category', 'question_options', 'question'));
    }
    public function update(Request $request, $id)
    {
        /*$question = Question::find($id);
        $question->question = $request['ask'];
        $question->question_option_id = $request['selected_option'];
        $question->pest_category_id = $request['category_select'];
        $question->save();

        $productid = session()->get('product_id');
        $control_point_questions = ControlPointQuestion::where('control_point_id',$productid)->get();
        $question_options = QuestionOption::all();
        $quests = Question::all();
        $point = ControlPoint::find($pointID);
        $products = ProductCatalog::where('presentation_id', 1)->get();
        $products_included = ProductCatalog::where('presentation_id','!=', 1)->get();
        return view('product.control_point.edit', compact('question_options','quests','productid','control_point_questions','point','products','products_included','error','success','warning'));
        */
    }

    public function destroy(string $pointId, string $questionId)
    {
        ControlPointQuestion::where('control_point_id', $pointId)->where('question_id', $questionId)->delete();
        return back();
    }
}
