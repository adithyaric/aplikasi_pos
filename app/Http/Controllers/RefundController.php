<?php

namespace App\Http\Controllers;

use App\Http\Requests\RefundRequest;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\Product;
use App\Models\Refund;

class RefundController extends Controller
{
    public function index()
    {
        return view('refunds.index', [
            'refunds' => Refund::get(),
        ]);
    }

    public function create()
    {
        return view('refunds.create', [
            'outlets' => Outlet::get(),
            'penjualan' => Penjualan::get(),
            'products' => Product::get(),
        ]);
    }

    public function store(RefundRequest $request)
    {
        $data = $request->validated();
        $refund = Refund::create($data);
        $this->updateStock($request, $refund);

        return redirect(route('refund.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function edit(Refund $refund)
    {
        return view('refunds.edit', [
            'refund' => $refund,
            'outlets' => Outlet::get(),
            'penjualan' => Penjualan::get(),
            'products' => Product::get(),
        ]);
    }

    public function update(RefundRequest $request, Refund $refund)
    {
        $data = $request->validated();
        $refund->update($data);
        $this->updateStock($request, $refund);

        return redirect(route('refund.index'))->with('toast_success', 'Berhasil Memperbarui Data!');
    }

    private function updateStock($request, $refund)
    {
        dd($request->product, $refund);
    }

    public function destroy(Refund $refund)
    {
        $refund->stocks()->delete();
        $refund->delete();

        return redirect(route('refund.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
