<?php

namespace App\Exports;

use App\Models\Stock;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StockExport implements FromView
{
    public function view(): View
    {
        $stocks = Stock::with(['pembelian', 'product'])
            ->orderBy('pembelian_id')
            ->orderBy('product_id')
            ->get();

        return view('exports.laporan-stock', [
            'stocks' => $stocks,
        ]);
    }
}
