<?php

namespace App\Http\Controllers;

use App\Models\ApplicationArea;
use Illuminate\Http\Request;

class ApplicationAreasController extends Controller
{
    public function store(Request $request, string $customerId) {
        $app_area = new ApplicationArea();
        $app_area->fill($request->all());
        $app_area->customer_id = $customerId;
        $app_area->save();

        return back();
    }

    public function update (Request $request) {
        $app_area_id = $request->id;

        $app_area = ApplicationArea::find($app_area_id);
        $app_area->update($request->all());
        return back();
    }

    public function destroy(string $id) {
        ApplicationArea::find($id)->delete();
        return back();
    }
}
