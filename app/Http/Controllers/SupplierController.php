<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\PurchaseRequisition;
use App\Models\SupplierCategory;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();
        $categories = SupplierCategory::all();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $suppliers = $query->paginate(50);

        return view('purchase-requisitions.suppliers.suppliers_index', compact('suppliers', 'categories'));
    }

    public function create(Request $request)
    {
        $categories = SupplierCategory::all();
        $supplier = new Supplier();
        $supplier->category = $request->category;
        $supplier->fill($request->all());
        $supplier->save();

        return redirect()->route('supplier.index')->with('success', 'Proveedor creado exitosamente.');
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        $categories = SupplierCategory::all();

        return view('purchase-requisitions.suppliers.show', compact('supplier', 'categories'));
    }

    public function store(Request $request)
    {
        $supplier = new Supplier();

        $supplier->fill($request->all());
        $supplier->save();

        return redirect()->route('supplier.index')->with('success', 'Proveedor creado exitosamente.');
    }

    public function edit(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $category = $supplier->category;
        $categories = SupplierCategory::all();
        
        return view('purchase-requisitions.suppliers.edit', compact('supplier', 'categories', 'category'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->fill($request->all());
        $supplier->save();

        return redirect()->route('supplier.index')->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', 'Proveedor eliminado exitosamente.');
    }

}
