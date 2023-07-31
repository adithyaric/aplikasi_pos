<?php

namespace App\Exports;

use App\Models\Penjualan;
use App\Models\Refund;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LabaRugiExport implements FromView
{
    public function view(): View
    {
        $total_penjualan = Penjualan::sum('total');
        $total_refund = Refund::sum('total');
        $laba_rugi = $total_penjualan - $total_refund;

        return view('exports.laporan-laba-rugi', [
            'total_penjualan' => $total_penjualan,
            'total_refund' => $total_refund,
            'laba_rugi' => $laba_rugi,
        ]);
    }
}
