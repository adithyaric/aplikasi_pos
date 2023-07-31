<?php

namespace App\Exports;

use App\Models\Pembelian;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PembelianExport implements FromView
{
    public function view(): View
    {
        return view('exports.laporan-pembelian', [
            'pembelians' => Pembelian::all(),
        ]);
    }
}
