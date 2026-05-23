<?php

namespace App\Http\Controllers;

use App\Models\PestCatalog;
use App\Models\PestCategory;
use App\Models\PestService;
use App\Models\ApplicationMethod;
use App\Models\ApplicationMethodService;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\LineBusiness;
use App\Models\ServicePrefix;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;


class ServiceController extends Controller
{

    private $size = 50;

    public function index(): View
    {
        $services = Service::orderBy('id', 'desc')->paginate($this->size);
        $types = ServiceType::all();
        $prefix = ServicePrefix::all();
        return view(
            'service.index',
            compact(
                'services',
                'types',
                'prefix'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $service_types = ServiceType::all();
        $pest_categories = PestCategory::orderBy('category', 'asc')->get();
        $application_methods = ApplicationMethod::orderBy('name', 'asc')->get();
        $business_lines = LineBusiness::all();
        $prefixes = ServicePrefix::all();

        return view(
            'service.create',
            compact(
                'pest_categories',
                'service_types',
                'application_methods',
                'business_lines',
                'prefixes',
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) //: RedirectResponse
    {
        $appMethods_selected = json_decode($request->input('appMethods_selected'), true);
        $pests_selected = json_decode($request->input('pests_selected'), true);

        $service = new Service();
        $service->fill($request->all());
        $service->status_id = 1;
        $service->has_pests = count($pests_selected) > 0;
        $service->has_application_methods = count($appMethods_selected) > 0;
        $service->save();


        if (!empty($pests_selected)) {
            foreach ($pests_selected as $pest_id) {
                PestService::insert([
                    'service_id' => $service->id,
                    'pest_id' => $pest_id,
                ]);
            }
        }

        if (!empty($appMethods_selected)) {
            foreach ($appMethods_selected as $methd_id) {
                ApplicationMethodService::insert([
                    'service_id' => $service->id,
                    'application_method_id' => $methd_id,
                ]);
            }
        }

        return redirect()->route('service.index', ['page' => 1]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, string $section)
    {
        $service = Service::find($id);

        return view(
            'service.show',
            compact(
                'service',
                'section',
            )
        );
    }

    public function search(Request $request)
    {
        $size = $request->input('size');
        $direction = $request->input('direction', 'DESC');
        $query_services = Service::query();

        if ($request->name) {
            $query_services = $query_services->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->prefix) {
            $query_services = $query_services->where('prefix', $request->prefix);
        }

        if ($request->type) {
            $query_services = $query_services->where('service_type_id', $request->type);
        }


        $services = $query_services->orderBy('name', $direction ?? 'DESC')->paginate($size ?? $this->size)->appends($request->all());
        $types = ServiceType::all();
        $prefix = ServicePrefix::all();

        return view(
            'service.index',
            compact(
                'services',
                'types',
                'prefix'
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $service = Service::find($id);
        $service_types = ServiceType::all();
        $business_lines = LineBusiness::all();
        $prefixes = ServicePrefix::all();

        $navigation = [
            'Servicio' => route('service.edit', ['id' => $service->id]),
            'Plagas' => route('service.edit.pests', ['id' => $service->id]),
            'Métodos de aplicación' => route('service.edit.appMethods', ['id' => $service->id]),
            'Productos' => route('service.edit.products', ['id' => $service->id]),
        ];

        return view(
            'service.edit.form',
            compact(
                'service',
                'service_types',
                'business_lines',
                'prefixes',
                'navigation'
            )
        );
    }

    public function editPests(string $id): View
    {
        $service = Service::find($id);
        $pest_categories = PestCategory::all();

        $navigation = [
            'Servicio' => route('service.edit', ['id' => $service->id]),
            'Plagas' => route('service.edit.pests', ['id' => $service->id]),
            'Métodos de aplicación' => route('service.edit.appMethods', ['id' => $service->id]),
            'Productos' => route('service.edit.products', ['id' => $service->id]),
        ];

        return view(
            'service.edit.pests',
            compact(
                'service',
                'pest_categories',
                'navigation'
            )
        );
    }

    public function editAppMethods(string $id): View
    {
        $service = Service::find($id);
        $application_methods = ApplicationMethod::orderBy('name', 'asc')->get();

        $navigation = [
            'Servicio' => route('service.edit', ['id' => $service->id]),
            'Plagas' => route('service.edit.pests', ['id' => $service->id]),
            'Métodos de aplicación' => route('service.edit.appMethods', ['id' => $service->id]),
            'Productos' => route('service.edit.products', ['id' => $service->id]),
        ];

        return view(
            'service.edit.app-methods',
            compact(
                'service',
                'application_methods',
                'navigation'
            )
        );
    }

    public function editProducts(string $id): View
    {
        $service = Service::find($id);
        $pest_categories = PestCategory::all();

        $navigation = [
            'Servicio' => route('service.edit', ['id' => $service->id]),
            'Plagas' => route('service.edit.pests', ['id' => $service->id]),
            'Métodos de aplicación' => route('service.edit.appMethods', ['id' => $service->id]),
            'Productos' => route('service.edit.products', ['id' => $service->id]),
        ];

        return view(
            'service.edit.products',
            compact(
                'service',
                'pest_categories',
                'navigation'
            )
        );
    }
    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id): RedirectResponse
    {
        $appMethods_selected = json_decode($request->input('appMethods_selected'), true);
        $pests_selected = json_decode($request->input('pests_selected'), true);

        $service = Service::find($id);
        $service->fill($request->all());
        $service->save();

        if (!empty($appMethods_selected)) {
            ApplicationMethodService::where('service_id', $service->id)->whereNotIn('application_method_id', $appMethods_selected)->delete();
            foreach ($appMethods_selected as $methd_id) {
                ApplicationMethodService::updateOrCreate([
                    'service_id' => $service->id,
                    'application_method_id' => $methd_id
                ], [
                    'updated_at' => now()
                ]);
            }
        }

        if ($pests_selected) {
            PestService::where('service_id', $service->id)->whereNotIn('pest_id', $pests_selected)->delete();
            foreach ($pests_selected as $pest_id) {
                PestService::updateOrCreate([
                    'service_id' => $service->id,
                    'pest_id' => $pest_id
                ], [
                    'updated_at' => now()
                ]);
            }
        }


        return back();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        ApplicationMethodService::where('service_id', $id)->delete();
        PestService::where('service_id', $id)->delete();
        Service::destroy($id);

        return back();
    }
}
