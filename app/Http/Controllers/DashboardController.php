<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\PenjualanItem;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $bestBuyProducts = PenjualanItem::select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->take(10)
            ->get();

        $bestBuySuppliers = PenjualanItem::select('supplier_id', DB::raw('SUM(qty) as total_qty'), 'suppliers.name as supplier_name')
            ->join('products', 'penjualan_items.product_id', '=', 'products.id')
            ->join('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->groupBy('supplier_id')
            ->orderBy('total_qty', 'desc')
            ->take(10)
            ->get();

        $salesGraph = Penjualan::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_sales'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date = Carbon::parse($item->date)->format('d-M-Y');

                return $item;
            });

        $productGraph = PenjualanItem::select(DB::raw('DATE(penjualan_items.created_at) as date'), 'products.name as product_name', DB::raw('SUM(qty) as total_qty'))
            ->join('penjualans', 'penjualan_items.penjualan_id', '=', 'penjualans.id')
            ->join('products', 'penjualan_items.product_id', '=', 'products.id')
            ->groupBy('date', 'product_name')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date = Carbon::parse($item->date)->format('d-M-Y');

                return $item;
            });

        $monthlyRevenue = Penjualan::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw("DATE_FORMAT(created_at, '%M') as month"),
            DB::raw('SUM(total) as total')
        )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")
            ->get();

        if ($request->wantsJson()) {
            // Return the data as a JSON response
            return response()->json([
                'bestBuyProducts' => $bestBuyProducts,
                'bestBuySuppliers' => $bestBuySuppliers,
                'salesGraph' => $salesGraph,
                'productGraph' => $productGraph,
                'monthlyRevenue' => $monthlyRevenue,
            ]);
        }

        return view('dashboard.index', [
            // 'users' => User::count(),
            'products' => Product::count(),
            'stocks' => Stock::sum('qty'),
            'penjualans' => Penjualan::count(),
            'pembelianTerkirim' => Pembelian::where('is_published', true)->count(),
            'totalRevenue' => $monthlyRevenue->sum('total'),
            // 'sliders' => Slider::where('status', 'active')->get(),
        ]);
    }
}
