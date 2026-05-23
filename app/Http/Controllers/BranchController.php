<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Status;

class BranchController extends Controller {
    private $states_route = 'datas/json/Mexico_states.json';
    private $cities_route = 'datas/json/Mexico_cities.json';

    private $size = 25;
    public function index()
    {
        $branches = Branch::where('status_id', '!=', 3)->paginate($this->size);
        $states = json_decode(file_get_contents(public_path($this->states_route)), true); 
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);
        return view('branch.index', compact('branches', 'states', 'cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::where('status_id', '!=', 3);
        $message = null;
        $states = json_decode(file_get_contents(public_path($this->states_route)), true); 
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);
        return view('branch.create', compact('branches', 'message', 'states', 'cities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $branch = new Branch();
        $branch->fill($request->all());
        $branch->status_id = 2;
        $branch->save();
        return redirect()->route('branch.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, int $section)
    {
        $branch = Branch::find($id);

        return view('branch.show', compact('branch', 'section'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $branch = Branch::find($id);
        $status = Status::all();
        $states = json_decode(file_get_contents(public_path($this->states_route)), true); 
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);

        $navigation = [
            'Sucursal' => route('branch.edit', ['id' => $branch->id]),
            'Contacto' => route('branch.edit.contact', ['id' => $branch->id])
        ];
        return view('branch.edit.form', compact('branch', 'states', 'cities', 'status', 'navigation'));
    }

    public function editContact(string $id)
    {
        $branch = Branch::find($id);        

        $navigation = [
            'Sucursal' => route('branch.edit', ['id' => $branch->id]),
            'Contacto' => route('branch.edit.contact', ['id' => $branch->id])
        ];
        return view('branch.edit.contact', compact('branch', 'navigation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $branch = Branch::find($id);
        $branch->fill($request->all());
        $branch->save();
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->status_id = 3;
        $branch->save();
        return back();
    }
}
