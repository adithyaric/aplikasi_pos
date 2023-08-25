<?php

namespace App\Http\Controllers;

use App\Http\Requests\RefundRequest;
use App\Models\Kas;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\Product;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\User;

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
            'outlets' => Outlet::whereHas('penjualan')->get(),
            'customers' => User::where('role', 'customer')->get(),
            'penjualans' => Penjualan::get(),
            'products' => Product::get(),
            'kas' => Kas::get(),
        ]);
    }

    public function edit(Refund $refund)
    {
        return view('refunds.edit', [
            'refund' => $refund,
            'outlets' => Outlet::get(),
            'customers' => User::where('role', 'customer')->get(),
            'penjualans' => Penjualan::get(),
            'products' => Product::get(),
            'kas' => Kas::get(),
        ]);
    }

    public function show(Refund $refund)
    {
        // dd($refund->load(['customer', 'outlet', 'penjualan', 'refundItems', 'refundItems.product'])->toArray());
        return view('refunds.show', [
            'refund' => $refund,
        ]);
    }

    public function store(RefundRequest $request)
    {
        $data = $request->validated();

        $data['total'] = (int) str_replace(',', '', $data['total']);
        $data['user_id'] = auth()->user()->id;
        $refund = Refund::create($data);

        foreach ($request->product as $product) {
            RefundItem::create([
                'refund_id' => $refund->id,
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'alasan' => $product['alasan'],
            ]);
        }

        $kas = Kas::find($request->kas_id);
        $kas->nominal += $data['total'];
        $kas->save();

        return redirect(route('refund.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function update(RefundRequest $request, Refund $refund)
    {
        $oldTotal = $refund->total;
        $data = $request->validated();
        $data['total'] = (int) str_replace(',', '', $data['total']);
        $data['user_id'] = auth()->user()->id;
        $refund->update($data);
        RefundItem::where('refund_id', $refund->id)->delete();
        foreach ($request->product as $product) {
            RefundItem::create([
                'refund_id' => $refund->id,
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'alasan' => $product['alasan'],
            ]);
        }

        $kas = Kas::find($request->kas_id);
        $kas->nominal += $oldTotal != $data['total'] ? $data['total'] : 0;
        $kas->save();

        return redirect(route('refund.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function destroy(Refund $refund)
    {
        $refund->delete();

        return redirect(route('refund.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
