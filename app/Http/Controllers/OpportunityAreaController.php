<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OpportunityArea;

use App\PDF\OpportunityAreaPDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use Carbon\Carbon;
class OpportunityAreaController extends Controller
{
    private $path = 'opportunity_areas/';
    private $tracing_options = ['Pendiente', 'En proceso', 'Concluido'];
    private $status_options = ['Abierto', 'Cerrado'];

    private $size = 50;

    public function getImage($id, $type)
    {
        $opportunityArea = OpportunityArea::findOrFail($id);
        $img = $type == 1 ? $opportunityArea->img_incidence : $opportunityArea->img_conclusion;
        
        if (!$opportunityArea->img_incidence) {
            abort(404);
        }
        
        $img_base64 = preg_replace('/^data:image\/(png|jpg|jpeg|gif);base64,/', '', $img);
        $img = base64_decode($img_base64);

        return response($img)
            ->header('Content-Type', 'image/jpeg'); // Asegúrate de cambiarlo según el tipo de imagen
    }


    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $customerId)
    {
        $customer = Customer::find($customerId);
        $tracing_options = $this->tracing_options;
        $status_options = $this->status_options;
        return view('opportunity-area.create', compact('customer', 'tracing_options', 'status_options'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $disk = Storage::disk('public');
        $customer = Customer::find($request->input('customer_id'));

        $opportunity_area = new OpportunityArea();
        $opportunity_area->fill($request->all());

        if ($request->hasFile('img_incidence')) {
            $file = $request->file('img_incidence');
            $opportunity_area->img_incidence = file_get_contents($file); // Convierte a binario
        }
        
        if ($request->hasFile('img_conclusion')) {
            $file = $request->file('img_conclusion');
            $opportunity_area->img_conclusion = file_get_contents($file); // Convierte a binario
        }

        $opportunity_area->save();

        return redirect()->route('quality.opportunity-area', ['id' => $customer->id]);
    }

    public function uploadFile(Request $request, string $customerId, string $type)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $opportunity_area = OpportunityArea::find($id);
        $tracing_options = $this->tracing_options;
        $status_options = $this->status_options;
        return view('opportunity-area.edit', compact('opportunity_area', 'tracing_options', 'status_options'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $opportunity_area = OpportunityArea::find($id);
        $opportunity_area->fill($request->all());

        if ($request->hasFile('img_incidence')) {
            $file = $request->file('img_incidence');
            $img_content = file_get_contents($file); // Convierte a binario
            $img_base64 = base64_encode($img_content);
            $img = 'data:image/png;base64,' . $img_base64;
            $opportunity_area->img_incidence = $img;
        }
        
        if ($request->hasFile('img_conclusion')) {
            $file = $request->file('img_conclusion');
            $img_content = file_get_contents($file); // Convierte a binario
            $img_base64 = base64_encode($img_content);
            $img = 'data:image/png;base64,' . $img_base64;
            $opportunity_area->img_conclusion = $img; 
        }

        $opportunity_area->save();

        return back();
    }

    public function search(Request $request, string $customerId)
    {
        $date = $request->input('date');
        $customer = Customer::find($customerId);
        $opportunity_areas = OpportunityArea::where('customer_id', $customer->id);

        if ($date) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $date));
            $startDate = $startDate->format('Y-m-d');
            $endDate = $endDate->format('Y-m-d');
            $opportunity_areas = $opportunity_areas->whereBetween('date', [$startDate, $endDate]);
        }

        $opportunity_areas = $opportunity_areas->paginate($this->size)->appends($request->all());

        return view('dashboard.quality.show.opportunity-area', compact('customer', 'opportunity_areas'));
    }

    public function destroy(string $id)
    {
        OpportunityArea::find($id)->delete();
        return back();
    }

    public function print(Request $request, string $customerId)
    {
        $op_area_ids = json_decode($request->op_area_boxes);

        $customer = Customer::find($customerId);
        if ($customer) {
            $pdf_name = 'Reporte_AreasOportunidad_' . $customer->name . '.pdf';
            $pdf = new OpportunityAreaPDF($customerId);
            $pdf->AddPage();
            $pdf->Customer();
            $pdf->Objective($request->objective);
            $pdf->Incidents($op_area_ids);
            $pdf->Output($pdf_name, 'I');
        }
    }
}
