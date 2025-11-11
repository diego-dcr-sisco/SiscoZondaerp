<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\PestCatalog;
use App\Models\PestCategory;
use Psy\CodeCleaner\IssetPass;
use Symfony\Contracts\Service\Attribute\Required;
use Illuminate\Support\Facades\Storage;

class PestController extends Controller
{
    private $images_path = 'pests/images/';

    private $size = 50;

    public function index(): View
    {
        $pests = PestCatalog::orderBy('id', 'desc')->paginate($this->size);
        $pest_categories = PestCategory::orderBy('category')->get();
        return view('pest.index', compact('pests', 'pest_categories'));
    }
    public function create()
    {
        $pest_categories = PestCategory::orderBy('category')->get();
        return view('pest.create', compact('pest_categories'));
    }

    public function store(Request $request): RedirectResponse
    {

        $pest = new PestCatalog();
        $pest->fill($request->all());

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:5000',
            ]);

            $file = $request->file('image');
            $filename = $pest->name . '.' . $file->getClientOriginalExtension();
            $url = $this->images_path . $filename;
            Storage::disk('public')->put($url, file_get_contents($file));
            $pest->image = $url;
        }
        $pest->save();
        return redirect()->route('pest.index');
    }

    public function show(string $id): View
    {
        $categs = PestCategory::all();
        $pest = PestCatalog::find($id);
        return view('pest.show', compact('pest', 'categs'));
    }

    public function edit(string $id)
    {
        $pest_categories = PestCategory::orderBy('category')->get();
        $pest = PestCatalog::find($id);
        return view('pest.edit', compact('pest', 'pest_categories'));
    }

    public function update(Request $request, string $id)
    {
        $pest = PestCatalog::find($id);
        $pest->fill($request->all());

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:5000',
            ]);

            if ($pest->image && Storage::disk('public')->exists($pest->image)) {
                Storage::disk('public')->delete($pest->image);
            }

            $file = $request->file('image');
            $filename = $pest->name . '.' . $file->getClientOriginalExtension();
            $url = $this->images_path . $filename;
            Storage::disk('public')->put($url, file_get_contents($file));
            $pest->image = $url;
        }

        $pest->save();
        return back();
    }

    public function search(Request $request)
    {
        $size = $request->input('size');
        $direction = $request->input('direction', 'DESC');
        $query_pests = PestCatalog::query();

        if ($request->name) {
            $query_pests = $query_pests->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->code) {
            $query_pests = $query_pests->where('pest_code', 'LIKE', '%' . $request->code . '%');
        }

        if ($request->category_id) {
            $query_pests = $query_pests->where('pest_category_id', $request->category_id);
        }

        $pests = $query_pests->orderBy('name', $direction ?? 'DESC')->paginate($size ?? $this->size)->appends($request->all());
        $pest_categories = PestCategory::orderBy('category')->get();

        return view(
            'pest.index',
            compact(
                'pests',
                'pest_categories'
            )
        );
    }

    public function destroy(string $id)
    {
        $pest = PestCatalog::find($id);
        if ($pest) {
            $pest->delete();
        }
        return redirect()->route('pest.index');
    }
}
