<?php

namespace App\Http\Controllers;

use App\Exports\LabaRugiExport;
use App\Exports\PembelianExport;
use App\Exports\PengeluaranExport;
use App\Exports\PenjualanExport;
use App\Exports\PenjualanKasirExport;
use App\Exports\StockExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function exportPembelian()
    {
        return Excel::download(new PembelianExport, 'laporan-pembelian.xlsx');
    }

    public function exportPenjualan()
    {
        return Excel::download(new PenjualanExport, 'laporan-penjualan.xlsx');
    }

    public function exportPenjualanKasir()
    {
        return Excel::download(new PenjualanKasirExport, 'laporan-penjualan-kasir.xlsx');
    }

    public function exportStock()
    {
        return Excel::download(new StockExport, 'laporan-stock.xlsx');
    }

    public function exportPengeluaran()
    {
        return Excel::download(new PengeluaranExport, 'laporan-pengeluaran.xlsx');
    }

    public function exportLabaRugi()
    {
        return Excel::download(new LabaRugiExport, 'laporan-laba-rugi.xlsx');
    }
}
