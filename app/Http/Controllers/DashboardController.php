<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\PenjualanItem;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // $bestBuyProducts = PenjualanItem::select('product_id', DB::raw('SUM(qty) as total_qty'))
        //     ->with('product')
        //     ->groupBy('product_id')
        //     ->orderBy('total_qty', 'desc')
        //     ->take(10)
        //     ->get();

        // $bestBuySuppliers = PenjualanItem::select('supplier_id', DB::raw('SUM(qty) as total_qty'), 'suppliers.name as supplier_name')
        //     ->join('products', 'penjualan_items.product_id', '=', 'products.id')
        //     ->join('suppliers', 'products.supplier_id', '=', 'suppliers.id')
        //     ->groupBy('supplier_id')
        //     ->orderBy('total_qty', 'desc')
        //     ->take(10)
        //     ->get();

        // $salesGraph = Penjualan::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_sales'))
        //     ->groupBy('date')
        //     ->orderBy('date')
        //     ->get()
        //     ->map(function ($item) {
        //         $item->date = Carbon::parse($item->date)->format('d-M-Y');

        //         return $item;
        //     });

        // $productGraph = PenjualanItem::select(DB::raw('DATE(penjualan_items.created_at) as date'), 'products.name as product_name', DB::raw('SUM(qty) as total_qty'))
        //     ->join('penjualans', 'penjualan_items.penjualan_id', '=', 'penjualans.id')
        //     ->join('products', 'penjualan_items.product_id', '=', 'products.id')
        //     ->groupBy('date', 'product_name')
        //     ->orderBy('date')
        //     ->get()
        //     ->map(function ($item) {
        //         $item->date = Carbon::parse($item->date)->format('d-M-Y');

        //         return $item;
        //     });

        // $monthlyRevenue = Penjualan::select(
        //     DB::raw('YEAR(created_at) as year'),
        //     DB::raw("DATE_FORMAT(created_at, '%M') as month"),
        //     DB::raw('SUM(total) as total')
        // )
        //     ->groupBy('year', 'month')
        //     ->orderBy('year', 'asc')
        //     ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")
        //     ->get();

        $urgentSuppliers = Supplier::whereNotNull('deadline_days')
            ->whereNotNull('deadline_interval_weeks')
            ->get()
            ->filter(fn ($s) => $s->isDeadlineUrgent())
            ->map(function ($s) {
                $s->next_deadline = $s->nextDeadlineDate();
                return $s;
            })
            ->sortBy('next_deadline')
            ->values();

        $nearExpiryStocks = Stock::with('product:id,name,code')
            ->where('qty_available', '>', 0)
            ->whereNotNull('expired_at')
            ->whereDate('expired_at', '>=', now()->toDateString())
            ->whereDate('expired_at', '<=', now()->addDays(30)->toDateString())
            ->orderBy('expired_at')
            ->get(['id', 'product_id', 'qty_available', 'expired_at', 'batch_number', 'sku']);

        $bestBuyProducts = [];
        $bestBuySuppliers = [];
        $salesGraph = [];
        $productGraph = [];
        $monthlyRevenue = [];

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

        // Batch-load products for the min-stock adjustment modal (avoids N+1)
        $activeAdjustments = \App\Models\ProductMinimumAdjustment::query()
            ->activeOn()
            ->orderByDesc('active_from')
            ->get()
            ->groupBy('product_id');

        $adjustmentProducts = Product::select('id', 'code', 'name', 'min_stock')
            ->withSum('stocks', 'qty_available')
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($activeAdjustments) {
                $adj = $activeAdjustments->get($p->id)?->first();
                $p->current_stock = (int) ($p->stocks_sum_qty_available ?? 0);
                $p->effective_min = $adj
                    ? (int) ceil($p->min_stock * (1 + $adj->adjustment_percentage / 100))
                    : (int) $p->min_stock;
                return $p;
            });

        return view('dashboard.index', [
            // 'users' => User::count(),
            'products'           => Product::count(),
            'stocks'             => Stock::sum('qty'),
            'penjualans'         => Penjualan::count(),
            'pembelianTerkirim'  => Pembelian::where('is_published', true)->count(),
            'totalRevenue'       => 0,
            'urgentSuppliers'    => $urgentSuppliers,
            'nearExpiryStocks'    => $nearExpiryStocks,
            'lowVelocityProducts' => collect(), // placeholder — filled in Task 2
            'adjustmentProducts' => $adjustmentProducts,
            // 'sliders' => Slider::where('status', 'active')->get(),
        ]);
    }

    public function setting()
    {
        $settings = json_decode(Storage::disk('public')->get('settings.json'), true) ?? [];

        return view('dashboard.setting', [
            'name'    => $settings['name'] ?? '',
            'email'   => $settings['email'] ?? '',
            'telp'    => $settings['telp'] ?? '',
            'address' => $settings['address'] ?? '',
            'website' => $settings['website'] ?? '',
            'logo'    => $settings['logo'] ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'    => 'required',
            'email'   => 'required|email',
            'telp'    => 'required',
            'address' => 'required',
            'website' => 'nullable|url',
            'logo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'logo.image' => 'File yang diunggah harus berupa gambar.',
            'logo.mimes' => 'Logo harus bertipe: jpeg, png, jpg, atau gif.',
            'logo.max'   => 'Ukuran logo maksimal 2 MB.',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'telp'    => $request->telp,
            'address' => $request->address,
            'website' => $request->website,
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $path;
        }

        Storage::disk('public')->put('settings.json', json_encode($data));

        return redirect(route('setting'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }
}
