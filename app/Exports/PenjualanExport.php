<?php

namespace App\Exports;

use App\Models\Penjualan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PenjualanExport implements FromView
{
    public function view(): View
    {
        return view('exports.laporan-penjualan', [
            'penjualans' => Penjualan::all(),
        ]);
    }
}
