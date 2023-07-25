<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        return view('suppliers.index', [
            'suppliers' => Supplier::get(),
        ]);
    }

    public function create()
    {
        return view('suppliers.create', []);
    }

    public function store(SupplierRequest $request)
    {
        $data = $request->validated();

        Supplier::create($data);

        return redirect(route('supplier.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function show(Supplier $supplier)
    {
        dd($supplier);
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();

        $supplier->update($data);

        return redirect(route('supplier.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect(route('supplier.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
