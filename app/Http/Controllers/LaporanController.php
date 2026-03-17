<?php

namespace App\Http\Controllers;

use App\Exports\DeliveryOrderSingleExport;
use App\Exports\KartuStokExport;
use App\Exports\LabaRugiExport;
use App\Exports\PembelianExport;
use App\Exports\PembelianSingleExport;
use App\Exports\PembelianSupplierExport;
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
        if ($id) {
            $pembelian = Pembelian::with(['supplier', 'pembelianProducts.product'])->findOrFail($id);

            return Excel::download(new PembelianSingleExport($pembelian), 'Dokumen_PO-'.$pembelian->code.'.xlsx');
        }

        // Existing logic for date‑filtered exports...
        return Excel::download(new PembelianExport($request), 'laporan-pembelian.xlsx');
    }

    public function exportPickingList(Request $request, $id = null)
    {
        if ($id) {
            $pickinglist = PickingList::with(['requestOrder', 'items.product'])->findOrFail($id);

            return Excel::download(new PickingListSingleExport($pickinglist), 'Dokumen_Picking_list-'.$pickinglist->code.'.xlsx');
        }

        return abort(404);
        // return Excel::download(new PickingListExport($request), 'laporan-pickinglist.xlsx');
    }

    public function exportRequestOrder(Request $request, $id = null)
    {
        if ($id) {
            $requestOrder = RequestOrder::with(['owner', 'items.product'])->findOrFail($id);

            return Excel::download(new RequestOrderSingleExport($requestOrder), 'Dokumen_Surat_Permintaan_Barang_(SPB)-'.$requestOrder->code.'.xlsx');
        }

        return abort(404);
        // return Excel::download(new RequestOrderExport($request), 'laporan-requestOrder.xlsx');
    }

    public function exportDeliveryOrder(Request $request, $id = null)
    {
        if ($id) {
            $deliveryOrder = DeliveryOrder::with(['owner', 'requestOrder', 'items.product'])->findOrFail($id);

            return Excel::download(new DeliveryOrderSingleExport($deliveryOrder), 'Dokumen_Surat_Jalan-'.$deliveryOrder->code.'.xlsx');
        }

        return abort(404);
        // return Excel::download(new RequestOrderExport($request), 'laporan-requestOrder.xlsx');
    }

    public function exportKartuStok(Request $request, $id = null)
    {
        if (! $id) { return abort(404); }

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

        return Excel::download(new KartuStokExport($stock, $movements), 'Kartu_Stok-'.$stock->sku.'.xlsx');
    }

    public function exportStockOpname(Request $request)
    {
        $date = $request->input('tanggal', date('Y-m-d'));
        $adjustments = StockAdjustment::with(['product', 'stock'])
            ->whereDate('adjustment_date', $date)
            ->get();

        return Excel::download(new StockOpnameExport($adjustments, $date), 'Stock_Opname-'.$date.'.xlsx');
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
