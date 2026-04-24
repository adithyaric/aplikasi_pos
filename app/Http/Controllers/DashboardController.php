<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
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

        $activeAdjustments = \App\Models\ProductMinimumAdjustment::query()
            ->activeOn()
            ->orderByDesc('active_from')
            ->orderByDesc('id')
            ->get()
            ->groupBy('product_id');

        $adjustedProductIds = $activeAdjustments->keys();

        $lowVelocityProducts = Product::select('id', 'code', 'name', 'min_stock')
            ->withSum('stocks', 'qty_available')
            ->whereIn('id', $adjustedProductIds)
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($activeAdjustments) {
                $adj          = $activeAdjustments->get($product->id)?->first();
                $effectiveMin = $adj
                    ? (int) ceil($product->min_stock * (1 + $adj->adjustment_percentage / 100))
                    : (int) $product->min_stock;
                $currentStock = (int) ($product->stocks_sum_qty_available ?? 0);

                $product->effective_min         = $effectiveMin;
                $product->current_stock         = $currentStock;
                $product->adjustment_percentage = $adj?->adjustment_percentage ?? 0;
                $product->deficit               = max(0, $effectiveMin - $currentStock);

                return $product;
            })
            ->filter(fn ($p) => $p->current_stock < $p->effective_min)
            ->sortByDesc('deficit')
            ->values();

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

        // $activeAdjustments is defined above and reused here for the adjustment modal
        $adjustmentProducts = Product::select('id', 'code', 'name', 'min_stock')
            ->withSum('stocks', 'qty_available')
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($activeAdjustments) {
                $adj = $activeAdjustments->get($p->id)?->first();
                $p->active_from  = $adj?->active_from;
                $p->active_until = $adj?->active_until;
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
            'lowVelocityProducts' => $lowVelocityProducts,
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
