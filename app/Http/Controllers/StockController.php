<?php

namespace App\Http\Controllers;

use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
        return view('stocks.index', [
            'stocks' => Stock::with([
                'product',
                'pembelian.supplier',
                'ownerStock.owner',
            ])
                ->orderBy('product_id')
                ->orderBy('expired_at')
                ->get()
        ]);
    }

    public function show(Stock $stock)
    {
        $stock->delete();

        $total = $stock->pembelian->stocks->sum('subtotal');
        $stock->pembelian->update(['total' => $total]);

        return redirect()->back()->with('toast_success', 'Berhasil Menghapus Data!');
    }

    public function destroy(Stock $stock)
    {
        dd(
            'destory Stock',
            $stock->toArray(),
            $stock->pembelian->toArray()
        );
        // $stock->delete();

        return redirect()->back()->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
