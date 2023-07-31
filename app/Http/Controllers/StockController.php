<?php

namespace App\Http\Controllers;

use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::get()->sortBy('product_id')->sortBy('expired_at');
        $grouped_stocks = $stocks->groupBy('product_id');
        $sorted_grouped_stocks = $grouped_stocks->sortBy(function ($stock_group, $product_id) {
            return $stock_group->first()->product->name;
        });

        return view('stocks.index', ['grouped_stocks' => $sorted_grouped_stocks]);
    }

    public function show(Stock $stock)
    {
        // Delete the stock record
        $stock->delete();

        // Recalculate the total for the associated pembelian
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
