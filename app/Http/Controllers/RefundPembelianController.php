<?php

namespace App\Http\Controllers;

use App\Http\Requests\RefundPembelianRequest;
use App\Models\Outlet;
use App\Models\Pembelian;
use App\Models\Product;
use App\Models\RefundPembelian;
use App\Models\RefundPembelianItem;
use App\Models\Supplier;
use App\Models\User;

class RefundPembelianController extends Controller
{
    public function index()
    {
        return view('refundPembelians.index', [
            'refundPembelians' => RefundPembelian::get(),
        ]);
    }

    public function create()
    {
        return view('refundPembelians.create', [
            'outlets' => Outlet::get(),
            'customers' => User::where('role', 'customer')->get(),
            'pembelians' => Pembelian::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
        ]);
    }

    public function edit(RefundPembelian $refundPembelian)
    {
        return view('refundPembelians.edit', [
            'refundPembelian' => $refundPembelian,
            'outlets' => Outlet::get(),
            'customers' => User::where('role', 'customer')->get(),
            'pembelians' => Pembelian::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
        ]);
    }

    public function show(RefundPembelian $refundPembelian)
    {
        // dd($refundPembelian->load(['customer', 'outlet', 'pembelian', 'refundPembelianItems', 'refundPembelianItems.product'])->toArray());
        return view('refundPembelians.show', [
            'refundPembelian' => $refundPembelian,
        ]);
    }

    public function store(RefundPembelianRequest $request)
    {
        $data = $request->validated();

        $data['total'] = (int) str_replace(',', '', $data['total']);
        $data['user_id'] = auth()->user()->id;
        $refundPembelian = RefundPembelian::create($data);

        foreach ($request->product as $product) {
            RefundPembelianItem::create([
                'refund_pembelian_id' => $refundPembelian->id,
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'alasan' => $product['alasan'],
            ]);
        }

        return redirect(route('refundPembelian.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function update(RefundPembelianRequest $request, RefundPembelian $refundPembelian)
    {
        $data = $request->validated();
        $data['total'] = (int) str_replace(',', '', $data['total']);
        $data['user_id'] = auth()->user()->id;
        $refundPembelian->update($data);
        RefundPembelianItem::where('refund_pembelian_id', $refundPembelian->id)->delete();
        foreach ($request->product as $product) {
            RefundPembelianItem::create([
                'refund_pembelian_id' => $refundPembelian->id,
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'alasan' => $product['alasan'],
            ]);
        }

        return redirect(route('refundPembelian.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function destroy(RefundPembelian $refundPembelian)
    {
        $refundPembelian->delete();

        return redirect(route('refundPembelian.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
