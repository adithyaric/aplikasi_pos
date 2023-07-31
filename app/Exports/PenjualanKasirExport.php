<?php

namespace App\Exports;

use App\Models\Penjualan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PenjualanKasirExport implements FromView
{
    public function view(): View
    {
        return view('exports.laporan-penjualan-kasir', [
            'penjualans' => Penjualan::whereDate('created_at', today())->get(),
        ]);
    }
}
