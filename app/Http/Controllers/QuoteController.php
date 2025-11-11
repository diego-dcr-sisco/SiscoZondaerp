<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\Quote;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Tracking;
use App\Enums\QuoteStatus;
use App\Enums\QuotePriority;

class QuoteController extends Controller
{
    private $files_path = 'quotes/files/';

    private $historyColumns = [
        'Priority' => 'Prioridad',
        'Status' => 'Estado',
        'Value' => 'Valor'
    ];

    public function index(string $id, string $class)
    {
        $quotes_data = [];
        $customer = null;

        if ($class == 'customer') {
            $customer = Customer::findOrFail($id);
        }

        if ($class == 'lead') {
            $customer = Lead::findOrFail($id);
        }

        if (!$customer) {
            return redirect()->back()->with('error', 'Cliente o cliente potencial no encontrado.');
        }

        $services = Service::select('id', 'name')->orderBy('name')->get();
        $quote_status = QuoteStatus::cases();
        $quote_priority = QuotePriority::cases();

        if ($customer::class == Lead::class) {
            $navigation = [
                'Cliente potencial' => route('customer.edit.lead', ['id' => $customer->id]),
                'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'lead']),
            ];
        } else {
            $navigation = [
                'Sede' => route('customer.edit.sede', ['id' => $customer->id]),
                'Archivos' => route('customer.show.sede.files', ['id' => $customer->id]),
                'Planos' => route('customer.show.sede.floorplans', ['id' => $customer->id]),
                'Portal' => route('customer.show.sede.portal', ['id' => $customer->id]),
                'Areas de aplicación' => route('customer.show.sede.areas', ['id' => $customer->id]),
                //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
                'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'customer']),
            ];
        }

        $quotes = Quote::where('model_id', $customer->id)
            ->where('model_type', Customer::class)
            ->orderBy('created_at', 'desc')
            ->get();

        $customer = [
            'id' => $customer->id,
            'name' => $customer->name,
            'type' => Customer::class,
        ];

        return view('quote.index', compact('customer', 'services', 'quotes', 'quote_status', 'quote_priority', 'navigation', 'quotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $quote = new Quote();
        $quote->fill($request->except(['file']));

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = 'quote_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = 'quotes/' . now()->format('Y/m');
            $filePath = $directory . '/' . $fileName;
            $fileContent = file_get_contents($file->getRealPath());

            Storage::disk('public')->put($filePath, $fileContent);
            $quote->file = $filePath;
        }

        $quote->save();
        return back();
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
        $quote = Quote::findOrFail($id);
        $customer_id = $quote->model_id;
        $class = $quote->model_type == Customer::class ? 'customer' : 'lead';

        if ($quote->model_type == Lead::class) {
            $navigation = [
                'Cliente potencial' => route('customer.edit.lead', ['id' => $customer_id]),
                'Cotizaciones' => route('customer.quote', ['id' => $customer_id, 'class' => 'lead']),
            ];
        } else {
            $navigation = [
                'Sede' => route('customer.edit.sede', ['id' => $customer_id]),
                'Archivos' => route('customer.show.sede.files', ['id' => $customer_id]),
                'Planos' => route('customer.show.sede.floorplans', ['id' => $customer_id]),
                'Portal' => route('customer.show.sede.portal', ['id' => $customer_id]),
                'Areas de aplicación' => route('customer.show.sede.areas', ['id' => $customer_id]),
                //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
                'Cotizaciones' => route('customer.quote', ['id' => $customer_id, 'class' => 'customer']),
            ];
        }

        return view('quote.edit', [
            'quote' => $quote,
            'services' => Service::select('id', 'name')->orderBy('name')->get(),
            'quote_status' => QuoteStatus::cases(),
            'quote_priority' => QuotePriority::cases(),
            'histories' => Quote::findOrFail($id)->histories()->orderBy('created_at', 'desc')->get(),
            'historyColumns' => $this->historyColumns,
            'navigation' => $navigation,
            'class' => $class
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $quote = Quote::findOrFail($id);
        $quote->update($request->except(['file']));

        if ($request->hasFile('file')) {
            // Eliminar el archivo anterior si existe
            if ($quote->file && Storage::disk('public')->exists($quote->file)) {
                Storage::disk('public')->delete($quote->file);
            }

            $file = $request->file('file');
            $fileName = 'quote_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = 'quotes/' . now()->format('Y/m');
            $filePath = $directory . '/' . $fileName;
            $fileContent = file_get_contents($file->getRealPath());

            Storage::disk('public')->put($filePath, $fileContent);
            $quote->file = $filePath;
        }

        $quote->save();
        return back()->with('success', 'Cotización actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $quote = Quote::findOrFail($id);
        if ($quote) {
            // Eliminar el archivo asociado si existe
            if ($quote->file && Storage::disk('public')->exists($quote->file)) {
                Storage::disk('public')->delete($quote->file);
            }
            $quote->delete();
            return back()->with('success', 'Cotización eliminada correctamente.');
        }
        return back()->with('error', 'Cotización no encontrada.');
    }

    public function download(string $id)
    {
        try {
            $quote = Quote::find($id);
            if (!Storage::disk('public')->exists($quote->file)) {
                return response()->json(['error' => 'El archivo no existe.'], 404);
            }
            return Storage::disk('public')->download($quote->file);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Registro de archivo no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al descargar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function search()
    {

    }

}
