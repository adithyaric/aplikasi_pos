<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\ProductsMinStockExport;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Imports\ProductsImport;
use App\Imports\ProductsMinStockImport;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->input('status_produk', 'sudah');
        $products = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $products = $products->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('harga_jual', 'LIKE', "%{$search}%")
                    ->orWhere('brand', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%")
                    ->orWhereHas('stocks', function ($stockQuery) use ($search) {
                        $stockQuery->where('serial_number', 'LIKE', "%{$search}%")
                            ->orWhere('status', 'LIKE', "%{$search}%");
                    });
                });
        }

        if ($request->filled('outlet_id')) {
            $products = $products->where('outlet_id', $request->outlet_id);
        }

        if ($request->filled('category_id')) {
            $products = $products->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('lokasi')) {
            $products = $products->where('lokasi', $request->lokasi);
        }

        if ($statusFilter !== 'all') {
            $products = $products->where('status_produk', $statusFilter);
        }

        if (request()->wantsJson()) {
            $products = $products
                ->with(['category', 'stocks' => function ($query) {
                    $query->where('qty', '>', 0)
                        ->orderBy('status')
                        ->orderBy('serial_number');
                }])
                ->latest()
                ->paginate(10);

            return ProductResource::collection($products);
        }

        $products = $products
            ->with('category:id,name')
            ->withSum('ownerStocks as owner_stock_qty', 'qty')
            ->withSum('stocks as reserved_stock_qty', 'qty_reserved')
            ->withSum('stocks as available_stock_qty', 'qty_available')
            ->withSum([
                'stockPembelians as approved_stock_pembelians_qty' => function ($query) {
                    $query->whereHas('pembelian', fn ($pembelian) => $pembelian->where('owner_approval_status', 'approved'));
                },
            ], 'qty')
            ->orderBy('code')
            ->paginate(100)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'locations' => Product::query()
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->distinct()
                ->orderBy('lokasi')
                ->pluck('lokasi'),
            'search' => $request->search,
            'selectedCategoryId' => $request->input('category_id'),
            'selectedLokasi' => $request->input('lokasi'),
            'statusProdukOptions' => Product::STATUS_PRODUK,
            'selectedStatusProduk' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'categories' => Category::get(),
            'statusProdukOptions' => Product::STATUS_PRODUK,
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        if (($data['status_produk'] ?? 'sudah') !== 'tambahan_diskon') {
            $data['status_produk_note'] = null;
        }

        // Handle file upload
        if ($request->hasFile('pic')) {
            // Get the uploaded file
            $file = $request->file('pic');

            // Generate a unique file name
            $fileName = time().'.'.$file->getClientOriginalExtension();

            // Store the file
            $file->storeAs('public/pics', $fileName);

            // Add the file path to the data array
            $data['pic'] = 'storage/pics/'.$fileName;
        }

        $product = Product::create($data);

        // Sync suppliers (multiple select)
        if ($request->has('supplier_ids')) {
            $product->suppliers()->sync($request->supplier_ids);
        }

        return redirect(route('product.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function show(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'brand' => $product->brand,
            'model' => $product->model,
            'harga_beli' => $product->harga_beli,
            'harga_jual' => $product->harga_jual,
            'is_serialized' => $product->is_serialized,
            'total_stock' => $product->total_stock,
        ]);
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product->load('suppliers'),
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'categories' => Category::get(),
            'statusProdukOptions' => Product::STATUS_PRODUK,
            // optional: selected supplier IDs for form
            'selectedSuppliers' => $product->suppliers->pluck('id')->toArray(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        if (($data['status_produk'] ?? 'sudah') !== 'tambahan_diskon') {
            $data['status_produk_note'] = null;
        }
        if ($request->hasFile('pic')) {
            // Delete the old image file
            if ($product->pic) {
                Storage::delete(str_replace('storage', 'public', $product->pic));
            }
            // Store the new image file
            $file = $request->file('pic');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/pics', $fileName);
            $data['pic'] = 'storage/pics/'.$fileName;
        }
        $product->update($data);

        // Sync suppliers (multiple select)
        if ($request->has('supplier_ids')) {
            $product->suppliers()->sync($request->supplier_ids);
        } else {
            // If no supplier selected, detach all
            $product->suppliers()->detach();
        }

        return redirect(route('product.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function destroy(Product $product)
    {
        // Delete the image file
        if ($product->pic) {
            Storage::delete(str_replace('storage', 'public', $product->pic));
        }

        $product->delete();

        return redirect(route('product.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }

    public function priceHistory(Product $product)
    {
        $activities = Activity::forSubject($product)
            ->orderBy('created_at', 'asc')
            ->get()
            ->filter(function ($activity) {
                return isset($activity->properties['attributes']['harga_beli']);
            })
            ->map(function ($activity, $index) use (&$prev) {
                $new = $activity->properties['attributes']['harga_beli'];
                $old = $activity->event === 'created'
                    ? null
                    : ($activity->properties['old']['harga_beli'] ?? null);

                return [
                    'date'  => $activity->created_at->format('d M Y H:i'),
                    'user'  => $activity->causer?->name ?? 'System',
                    'old'   => (int) $old,
                    'new'   => (int) $new,
                    'event' => $activity->event,
                ];
            })->values();

        return response()->json(['success' => true, 'data' => $activities]);
    }

    ///-----------------------------------------------------------------------------------------------

    public function export()
    {
        return Excel::download(new ProductsExport(), 'products.xlsx');
    }

    public function exportTemplate()
    {
        return Excel::download(new ProductsExport(templateOnly: true), 'template_products.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new ProductsImport(), $request->file('file'));

        return redirect()->back()->with('toast_success', 'Berhasil Import Data!');
    }

    public function exportMinStock()
    {
        return Excel::download(new ProductsMinStockExport(), 'products_min_stock.xlsx');
    }

    public function exportMinStockTemplate()
    {
        return Excel::download(new ProductsMinStockExport(templateOnly: true), 'template_min_stock.xlsx');
    }

    public function importMinStock(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new ProductsMinStockImport(), $request->file('file'));

        return redirect()->back()->with('toast_success', 'Berhasil Import Min Stock!');
    }
}
