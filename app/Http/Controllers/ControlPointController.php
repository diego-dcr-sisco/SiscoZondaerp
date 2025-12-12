<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use App\Models\ApplicationMethod;
use App\Models\ControlPoint;
use App\Models\ControlPointAppMethods;
use App\Models\ControlPointProduct;
use App\Models\Purpose;
use App\Models\LineBusiness;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ControlPointQuestion;
use App\Models\ProductCatalog;
use App\Models\FloorPlans;
use Illuminate\Support\Str;

class ControlPointController extends Controller
{
    private $file_answers_path = 'datas/json/answers.json';

    private $size = 50;

    private function getOptions($id, $answers)
    {
        foreach ($answers as $answer) {
            if ($answer['id'] == $id) {
                return $answer['options'];
            }
        }
        return [];
    }

    public function index()
    {
        $points = ControlPoint::orderBy('id', 'desc')->paginate($this->size);
        return view('control_point.index', compact('points'));
    }

    public function create()
    {
        $questions_data = [];
        //$products = ProductCatalog::where('presentation_id', '!=', 1)->get();
        $products = ProductCatalog::orderBy('name')->get();
        $options = QuestionOption::all();
        $questions = Question::all();
        $devices = ProductCatalog::where('presentation_id', 1)->orderBy('name')->get();
        $answers = json_decode(file_get_contents(public_path($this->file_answers_path)), true);

        foreach ($questions as $index => $q) {
            $questions_data[] = [
                'key' => 'qex_' . $index,
                'id' => $q->id,
                'text' => $q->question,
                'option' => $q->question_option_id,
                'options' => $this->getOptions($q->question_option_id, $answers),
                'answer_default' => $q->answer_default,
                'has_question' => false,
            ];
        }

        $questions = $questions_data;

        return view('control_point.create', compact('products', 'devices', 'options', 'answers', 'questions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $point = new ControlPoint();
        $point->fill($request->all());
        $point->save();

        $questions = json_decode($request->input('questions'), true);
        $updated_cpqs = [];

        if (!empty($questions)) {
            foreach ($questions as $question) {
                if ($question['id'] == null) {
                    $new_question = Question::create([
                        'question' => $question['text'],
                        'question_option_id' => $question['option'],
                        'answer_default' => $question['answer_default'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $new_question = Question::find($question['id']);
                }

                $cpq = ControlPointQuestion::create([
                    'question_id' => $new_question->id,
                    'control_point_id' => $point->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $updated_cpqs[] = $cpq->id;
            }

            ControlPointQuestion::whereNotIn('id', $updated_cpqs)->where('control_point_id', $point->id)->delete();
        }

        return redirect()->route('point.index');
    }

    public function edit(string $id)
    {
        $questions_data = [];
        $assign_questions = [];
        $keys = [];

        $point = ControlPoint::find($id);

        $products = ProductCatalog::all();
        $options = QuestionOption::all();
        $questions = Question::all();
        $devices = ProductCatalog::where('presentation_id', 1)->get();
        $answers = json_decode(file_get_contents(public_path($this->file_answers_path)), true);

        foreach ($questions as $index => $q) {
            $questions_data[] = [
                'key' => 'qex_' . $index,
                'id' => $q->id,
                'text' => $q->question,
                'option' => $q->question_option_id,
                'options' => $this->getOptions($q->question_option_id, $answers),
                'answer_default' => $q->answer_default,
                'has_question' => $point->hasQuestion($q->id) ?? false,
            ];

            $keys[$q->id] = 'qex_' . $index;
        }

        foreach ($point->questions as $index => $q) {
            $assign_questions[] = [
                'key' => $keys[$q->id] ?? ('qex_' . $index),
                'id' => $q->id,
                'text' => $q->question,
                'option' => $q->question_option_id,
                'options' => $this->getOptions($q->question_option_id, $answers),
                'answer_default' => $q->answer_default,
                'has_question' => true,
            ];
        }

        $questions = $questions_data;

        return view('control_point.edit', compact('products', 'devices', 'options', 'answers', 'questions', 'point', 'assign_questions'));
    }

    public function show(string $id): View
    {
        $point = ControlPoint::find($id);
        $products = ProductCatalog::where('presentation_id', 1)->get();
        $products_included = ProductCatalog::where('presentation_id', '!=', 1)->get();
        $lineBs = LineBusiness::all();
        $porps = Purpose::all();
        $controlPoint_questions = ControlPointQuestion::where('control_point_id', $id)->get();
        $question_options = QuestionOption::all();
        $quests = Question::all();
        return view('control_point.show', compact('quests', 'question_options', 'controlPoint_questions', 'porps', 'lineBs', 'point', 'products', 'products_included'));
    }

    public function update(Request $request, string $id)
    {
        $point = ControlPoint::findOrFail($id);
        $point->fill($request->all());
        $point->save();

        $questions = json_decode($request->input('questions'), true);
        $updated_cpqs = [];

        if (!empty($questions)) {
            foreach ($questions as $question) {
                if ($question['id'] == null) {
                    $new_question = Question::create([
                        'question' => $question['text'],
                        'question_option_id' => $question['option'],
                        'answer_default' => $question['answer_default'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $new_question = Question::find($question['id']);
                }

                $cpq = ControlPointQuestion::create([
                    'question_id' => $new_question->id,
                    'control_point_id' => $point->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $updated_cpqs[] = $cpq->id;
            }
 
            ControlPointQuestion::whereNotIn('id', $updated_cpqs)->where('control_point_id', $point->id)->delete();
        }

        return back();
    }

    public function destroy(string $id)
    {
        $point = ControlPoint::find($id);
        if ($point) {
            $point->delete();
        }
        return back();
    }

    public function search(Request $request)
    {
        $size = $request->input('size');
        $direction = $request->input('direction', 'DESC');
        $query_point = ControlPoint::query();

        if ($request->name) {
            $query_point = $query_point->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->code) {
            $query_point = $query_point->where('code', 'LIKE', '%' . $request->code . '%');
        }

        $points = $query_point->orderBy('name', $direction ?? 'DESC')->paginate($size ?? $this->size)->appends($request->all());

        return view(
            'control_point.index',
            compact(
                'points',

            )
        );
    }

    public function geolocateDevices($floorplan_id)
    {
        $floorplan = FloorPlans::find($floorplan_id);
        $devices = $floorplan->devices($floorplan->lastVersion() ?? 1)->get();

        return view('floorplan.geolocation.index', compact('floorplan', 'devices'));
    }

}