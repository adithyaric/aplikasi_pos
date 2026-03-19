<?php

namespace App\Http\Controllers;

use App\Exports\DeliveryOrderSingleExport;
use App\Exports\KartuStokExport;
use App\Exports\LabaRugiExport;
use App\Exports\PembelianExport;
use App\Exports\PembelianSingleExport;
use App\Exports\PembelianSupplierExport;
use App\Exports\PenerimaanExport;
use App\Exports\PengeluaranExport;
use App\Exports\PenjualanExport;
use App\Exports\PenjualanKasirExport;
use App\Exports\PenjualanSupplierExport;
use App\Exports\PickingListSingleExport;
use App\Exports\RequestOrderSingleExport;
use App\Exports\StockExport;
use App\Exports\StockOpnameExport;
use App\Models\DeliveryOrder;
use App\Models\Outlet;
use App\Models\Pembelian;
use App\Models\PickingList;
use App\Models\RequestOrder;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function exportPembelian(Request $request, $id = null)
    {
        $settings = json_decode(Storage::disk('public')->get('settings.json'), true) ?? [];

        if ($id) {
            $pembelian = Pembelian::with(['supplier', 'pembelianProducts.product'])->findOrFail($id);

            return Excel::download(new PembelianSingleExport($pembelian, $settings), 'Dokumen_PO-'.$pembelian->code.'.xlsx');
        }

        return Excel::download(new PembelianExport($request, $settings), 'laporan-pembelian.xlsx');
    }

    public function exportPickingList(Request $request, $id = null)
    {
        $settings = json_decode(Storage::disk('public')->get('settings.json'), true) ?? [];

        if ($id) {
            $pickinglist = PickingList::with(['requestOrder', 'items.product'])->findOrFail($id);

            return Excel::download(new PickingListSingleExport($pickinglist, $settings), 'Dokumen_Picking_list-'.$pickinglist->code.'.xlsx');
        }

        return abort(404);
    }

    public function exportRequestOrder(Request $request, $id = null)
    {
        $settings = json_decode(Storage::disk('public')->get('settings.json'), true) ?? [];

        if ($id) {
            $requestOrder = RequestOrder::with(['owner', 'items.product'])->findOrFail($id);

            return Excel::download(new RequestOrderSingleExport($requestOrder, $settings), 'Dokumen_Surat_Permintaan_Barang_(SPB)-'.$requestOrder->code.'.xlsx');
        }

        return abort(404);
    }

    public function exportDeliveryOrder(Request $request, $id = null)
    {
        $settings = json_decode(Storage::disk('public')->get('settings.json'), true) ?? [];

        if ($id) {
            $deliveryOrder = DeliveryOrder::with(['owner', 'requestOrder', 'items.product'])->findOrFail($id);

            return Excel::download(new DeliveryOrderSingleExport($deliveryOrder, $settings), 'Dokumen_Surat_Jalan-'.$deliveryOrder->code.'.xlsx');
        }

        return abort(404);
    }

    public function exportKartuStok(Request $request, $id = null)
    {
        $settings = json_decode(Storage::disk('public')->get('settings.json'), true) ?? [];

        if (! $id) {
            return abort(404);
        }

        $stock = Stock::with(['product', 'pembelian.supplier'])->findOrFail($id);
        $movements = StockMovement::where('product_id', $stock->product_id)
            ->where(function ($q) use ($stock) {
                $q->where('notes', 'like', "%SKU: {$stock->sku}%")
                    ->orWhere(function ($q2) use ($stock) {
                        $q2->where('reference_type', 'App\Models\Pembelian')
                            ->where('reference_id', $stock->pembelian_id);
                    });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return Excel::download(new KartuStokExport($stock, $movements, $settings), 'Kartu_Stok-'.$stock->sku.'.xlsx');
    }

    public function exportStockOpname(Request $request)
    {
        $settings = json_decode(Storage::disk('public')->get('settings.json'), true) ?? [];

        $date = $request->input('tanggal', date('Y-m-d'));
        $adjustments = StockAdjustment::with(['product', 'stock'])
            ->whereDate('adjustment_date', $date)
            ->get();

        return Excel::download(new StockOpnameExport($adjustments, $date, $settings), 'Stock_Opname-'.$date.'.xlsx');
    }

    public function exportPenerimaan(Request $request, Pembelian $pembelian, $type = 'po')
    {
        $settings = json_decode(Storage::disk('public')->get('settings.json'), true) ?? [];

        $pembelian->load(['supplier', 'pembelianProducts.product', 'stocks.product']);

        return Excel::download(
            new PenerimaanExport($pembelian, $type, $settings),
            'Penerimaan-'.$type.' '.$pembelian->code.'.xlsx'
        );
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
