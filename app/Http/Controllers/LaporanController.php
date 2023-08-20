<?php

namespace App\Http\Controllers;

use App\Exports\LabaRugiExport;
use App\Exports\PembelianExport;
use App\Exports\PembelianSupplierExport;
use App\Exports\PengeluaranExport;
use App\Exports\PenjualanExport;
use App\Exports\PenjualanKasirExport;
use App\Exports\PenjualanSupplierExport;
use App\Exports\StockExport;
use App\Models\Outlet;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index', [
            'cashiers' => User::where('role', 'kasir')->get(),
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
        ]);
    }

    public function exportPembelian(Request $request)
    {
        return Excel::download(new PembelianExport($request), 'laporan-pembelian.xlsx');
    }

    public function exportPembelianSupplier(Request $request)
    {
        return Excel::download(new PembelianSupplierExport($request), 'laporan-pembelian-supplier-outlet.xlsx');
    }

    public function exportPenjualan(Request $request)
    {
        return Excel::download(new PenjualanExport($request), 'laporan-penjualan.xlsx');
    }

    public function exportPenjualanKasir(Request $request)
    {
        return Excel::download(new PenjualanKasirExport($request), 'laporan-penjualan-kasir.xlsx');
    }

    public function exportPenjualanSupplier(Request $request)
    {
        return Excel::download(new PenjualanSupplierExport($request), 'laporan-penjualan-supplier.xlsx');
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
