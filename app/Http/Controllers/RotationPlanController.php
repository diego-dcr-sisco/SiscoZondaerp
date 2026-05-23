<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\ProductCatalog;
use App\Models\RotationPlan;
use App\Models\RotationPlanChanges;
use App\Models\RotationPlanPeriod;
use App\Models\RotationPlanProduct;

use App\PDF\RotationPlanPDF;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use View;


class RotationPlanController extends Controller
{
    private $size = 50;

    private function getMonthsBetweenDates($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $period = CarbonPeriod::create($start, '1 month', $end);
        $months = [];
        foreach ($period as $date) {
            $months[] = [
                'index' => $date->month,
                'name' => $date->translatedFormat('F')
            ];
        }

        /*usort($months, function ($a, $b) {
            return $a['index'] <=> $b['index'];
        });*/

        return $months;
    }

    public function index(string $contractId)
    {
        $contract = Contract::find($contractId);
        $rotation_plans = RotationPlan::where('contract_id', $contract->id)->paginate($this->size);
        return view('rotation-plan.index', compact('contract', 'rotation_plans'));
    }

    public function create(string $contractId)
    {
        $contract = Contract::find($contractId);
        //$contracts = Contract::orderBy('id', 'asc')->get();
        $customers = Customer::orderBy('name', 'asc')->get();
        $products = ProductCatalog::orderBy('name', 'asc')->get();
        $months = $this->getMonthsBetweenDates($contract->startdate, $contract->enddate);

        return view('rotation-plan.create', compact('contract', 'customers', 'products', 'months'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {     
        $products = json_decode($request->input('products'));
        //dd($request->all());

        if (empty($products)) {
            $message = 'Debes agregar al menos 1 período';
            session()->flash('success', $message);
            dd();
            //return back();
        }

        $contract = Contract::find($request->input('contractId'));

        if ($contract) {
            $rotation_plan = new RotationPlan();
            $rotation_plan->fill($request->all());
            $rotation_plan->contract_id = $contract->id;
            $rotation_plan->customer_id = $contract->customer_id;
            $rotation_plan->no_review = 1;
            $rotation_plan->save();

            foreach ($products as $product) {
                RotationPlanProduct::create([
                    'rotation_plan_id' => $rotation_plan->id,
                    'product_id' => $product->id,
                    'color' => $product->color,
                    'months' => json_encode($product->months)
                ]);
            }
        }

        //return redirect()->route('rotation.index', ['contractId' => $contract->id]);

        return redirect()->route('rotation.edit', ['id' => $rotation_plan->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $products = [];
        $rotation_plan = RotationPlan::find($id);
        $reviews = RotationPlan::where('contract_id', $rotation_plan->contract_id)->get()->pluck('no_review', 'created_at')->toArray();
        $months = $this->getMonthsBetweenDates($rotation_plan->contract->startdate, $rotation_plan->contract->enddate);
        $changes = RotationPlanChanges::where('rotation_plan_id', $rotation_plan->id)->get();

        foreach ($rotation_plan->products as $i => $rp_product) {
            $product = ProductCatalog::find($rp_product->product_id);
            $products[] = [
                'index' => $i,
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->biocide->type ?? 'S/A',
                'group' => $product->biocide->group ?? 'S/A',
                'active_ingredient' => $product->active_ingredient ?? 'S/A',
                'status' => true,
                'color' => $rp_product->color,
                'months' => json_decode($rp_product->months),
            ];
        }

        return view('rotation-plan.edit', compact('rotation_plan', 'products', 'reviews', 'months', 'changes'));
    }

    public function searchProduct(Request $request)
    {
        $data = [];
        $search = json_decode($request->input('search'));
        $productTerm = '%' . $search . '%';
        $products = ProductCatalog::where('name', 'LIKE', $productTerm)->orWhere('active_ingredient', 'LIKE', $productTerm)->get();
        foreach ($products as $product) {
            $data[] = [
                'id' => $product->id,
                'name' => $product->name,
                'biocide_type' => $product->biocide->type ?? null,
                'biocide_group' => $product->biocide->group ?? null,
                'active_ingredient' => $product->active_ingredient ?? null,
                'status' => false
            ];
        }
        return response()->json($data);
    }

    public function searchReview(Request $request) {
        try {
            $review = $request->input('review');
            $contract_id = $request->input('contractId');
    
            if (!$contract_id) {
                return response()->json(['message' => 'El contract_id es requerido'], 400);
            }
    
            $rotation_plan = RotationPlan::where('contract_id', $contract_id)
                ->where('no_review', $review)
                ->first();
    
            if (!$rotation_plan) {
                return response()->json(['message' => 'Plan de rotación no encontrado'], 404);
            }

            $url = route('rotation.edit', ['id' => $rotation_plan->id]);
    
            $data = [
                'rotation_plan_id' => $rotation_plan->id,
                'url' => $url,
            ];
    
            return response()->json($data, 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error en el servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function update(Request $request, string $id)
    {
        $products = json_decode($request->input('products'));
        $changes = json_decode($request->input('changes'));
        $has_new_rotation_plan = $request->input('create_review');

        //dd($products);

        $rotation_plan = RotationPlan::find($id);
        $contract = Contract::find($rotation_plan->contract_id);

        $no_review = $contract ? $contract->rotationPlans->count() : $request->input('no_review');

        if ($has_new_rotation_plan) {
            $rotation_plan = new RotationPlan();
            $rotation_plan->fill($request->all());
            $rotation_plan->no_review = ++$no_review;
            $rotation_plan->contract_id = $contract->id;
            $rotation_plan->customer_id = $contract->customer_id;
            $rotation_plan->created_at = now();
            $rotation_plan->save();

        } else {
            $rotation_plan->fill($request->all());
            $rotation_plan->updated_at = now();
            $rotation_plan->save();

            if ($changes) {
                RotationPlanChanges::where('rotation_plan_id', $rotation_plan->id)->delete();
                foreach ($changes as $change) {
                    RotationPlanChanges::create([
                        'rotation_plan_id' => $rotation_plan->id,
                        'no_review' => $no_review,
                        'description' => $change->description
                    ]);
                }
            }
        }


        if ($products) {
            RotationPlanProduct::where('rotation_plan_id', $rotation_plan->id)->delete();
            foreach ($products as $product) {
                RotationPlanProduct::create(
                    [
                        'rotation_plan_id' => $rotation_plan->id,
                        'product_id' => $product->id,
                        'color' => $product->color,
                        'months' => json_encode($product->months)
                    ]
                );
            }
        }

        return redirect()->route('rotation.edit', ['id' => $rotation_plan->id]);
    }

    public function destroy(string $id)
    {
        $rotation_plan = RotationPlan::find($id);
        if ($rotation_plan) {
            RotationPlanProduct::where('rotation_plan_id', $rotation_plan->id)->delete();
            $rotation_plan->delete();
        }
        return back();
    }

    public function print(string $id)
    {
        $rotation_plan = RotationPlan::find($id);
        if ($rotation_plan) {
            $pdf_name = 'Plan_Rotacion_' . $rotation_plan->name . '.pdf';
            $pdf = new RotationPlanPDF($id);
            $pdf->AddPage();
            $pdf->Customer();
            $pdf->Changes();
            $pdf->Products();
            $pdf->Adc();
            $pdf->Output($pdf_name, 'I');
        }
    }

    public function changes(Request $request, string $id)
    {
        $no_review = RotationPlanChanges::where('rotation_plan_id', $id)->count();
        RotationPlanChanges::updateOrCreate(
            [
                'rotation_plan_id' => $id,
                'no_review' => ++$no_review,
            ],
            [
                'description' => $request->description
            ]
        );

        return back();
    }

    public function destroyChanges(string $id)
    {
        $count = 1;
        $change = RotationPlanChanges::find($id);
        $change->delete();

        $changes = RotationPlanChanges::where('rotation_plan_id', $change->rotation_plan_id)->get();

        foreach ($changes as $change) {
            $change->no_review = $count;
            $change->save();
            $count++;
        }

        return back();
    }
}
