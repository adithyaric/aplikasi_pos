<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembelianRequest;
use App\Models\Kas;
use App\Models\Outlet;
use App\Models\Pembelian;
use App\Models\PembelianProduct;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use PDF;

class PembelianController extends Controller
{
    public function getPembelian($outlet_id)
    {
        $pembelians = Pembelian::where('outlet_id', $outlet_id)->get();

        return response()->json($pembelians);
    }

    public function getItems($pembelian_id)
    {
        $pembelian = Pembelian::find($pembelian_id);
        if ($pembelian) {
            $items = $pembelian->stocks;

            return response()->json($items);
        } else {
            return response()->json([], 404);
        }
    }

    public function index()
    {
        return view('pembelians.index', [
            'pembelians' => Pembelian::get(),
        ]);
    }

    public function create()
    {
        return view('pembelians.create', [
            'kas' => Kas::get(),
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:pembelians,code',
            'outlet_id' => 'required|exists:outlets,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'kas_id' => 'required|exists:kas,id',
            'total' => 'required|numeric',
            'product' => 'required|array',
            'product.*.product_id' => 'required|exists:products,id',
            'product.*.qty' => 'required|numeric|min:1',
            'product.*.harga_beli' => 'required|min:0',
            'product.*.subtotal' => 'required|min:0',
            'product.*.serial_numbers' => 'nullable|string',
        ]);

        $pembelian = Pembelian::create([
            'code' => $request->code,
            'outlet_id' => $request->outlet_id,
            'supplier_id' => $request->supplier_id,
            'kas_id' => $request->kas_id,
            'total' => $request->total,
            'is_published' => false,
        ]);

        $this->updateStock($request, $pembelian);

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function show(Pembelian $pembelian)
    {
        // dd($pembelian->load(['stocks', 'stocks.product'])->toArray());
        return view('pembelian.show', [
            'pembelian' => $pembelian,
        ]);
    }

    public function print(Pembelian $pembelian)
    {
        $pdf = PDF::loadView('pembelians.pembelian_pdf', ['pembelian' => $pembelian]);

        return $pdf->download('pembelian_'.$pembelian->id.'.pdf');
    }

    public function edit(Pembelian $pembelian)
    {
        return view('pembelians.edit', [
            'pembelian' => $pembelian,
            'kas' => Kas::get(),
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
        ]);
    }

    public function update(PembelianRequest $request, Pembelian $pembelian)
    {
        $data = $request->validated();
        $pembelian->update($data);
        $this->updateStock($request, $pembelian);

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Memperbarui Data!');
    }

    public function publish(Pembelian $pembelian)
    {
        $pembelian->is_published = $pembelian->is_published ? false : true;
        $this->updateStock($pembelian->pembelianProducts, $pembelian);
        $pembelian->save();
        $kas = Kas::find($pembelian->kas_id);
        $kas->nominal -= $pembelian->total;
        $kas->save();

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Memperbarui Data!');
    }

    private function updateStock($request, $pembelian)
    {
        if ($pembelian->is_published) {
            foreach ($request as $productData) {
                $product = Product::find($productData->product_id);
                if ($product->is_serialized && ! empty($productData->serial_numbers)) {
                    // For serialized items (laptops), create individual stock entries
                    $serialNumbers = is_array($productData->serial_numbers)
                        ? $productData->serial_numbers
                        : explode("\n", trim($productData->serial_numbers));

                    foreach ($serialNumbers as $serial) {
                        $serial = trim($serial);
                        if (! empty($serial)) {
                            Stock::updateOrCreate(
                                [
                                    'pembelian_id' => $pembelian->id,
                                    'product_id' => $productData->product_id,
                                    'serial_number' => $serial
                                ],
                                [
                                    'harga_beli' => (int) str_replace(',', '', $productData->harga_beli),
                                    'qty' => 1, // Always 1 for serialized items
                                    'subtotal' => (int) str_replace(',', '', $productData->harga_beli),
                                    'expired_at' => $productData->expired_at ?? null,
                                    'condition' => 'new',
                                ]
                            );
                        }
                    }
                } else {
                    // For bulk items (cables, adapters)
                    Stock::updateOrCreate(
                        ['pembelian_id' => $pembelian->id, 'product_id' => $productData->product_id],
                        [
                            'harga_beli' => (int) str_replace(',', '', $productData->harga_beli),
                            'qty' => (int) $productData->qty,
                            'subtotal' => (int) $productData->subtotal,
                            'expired_at' => $productData->expired_at ?? null,
                            'condition' => 'new',
                        ]
                    );
                }

                $product->update(['harga_beli' => (int) str_replace(',', '', $productData->harga_beli)]);
            }
        } else {
            if (isset($request->product)) {
                foreach ($request->product as $productData) {
                    // Process serial numbers for PembelianProduct
                    $serialNumbers = null;
                    if (isset($productData['serial_numbers']) && ! empty($productData['serial_numbers'])) {
                        $serialNumbers = is_array($productData['serial_numbers'])
                            ? $productData['serial_numbers']
                            : array_filter(array_map('trim', explode("\n", $productData['serial_numbers'])));
                    }

                    PembelianProduct::updateOrCreate(
                        ['pembelian_id' => $pembelian->id, 'product_id' => $productData['product_id']],
                        [
                            'harga_beli' => (int) str_replace(',', '', $productData['harga_beli']),
                            'qty' => (int) $productData['qty'],
                            'subtotal' => (int) $productData['subtotal'],
                            'expired_at' => $productData['expired'] ?? null,
                            'serial_numbers' => $serialNumbers,
                        ]
                    );
                }
            }
        }
    }

    public function destroy(Pembelian $pembelian)
    {
        $pembelian->stocks()->delete();
        $pembelian->delete();

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }

    public function stockDestroy($id)
    {
        $pembelianProduct = PembelianProduct::find($id);
        $pembelian = $pembelianProduct->pembelian;
        $pembelianProduct->delete();

        $pembelian->update(
            ['total' => $pembelian->pembelianProducts->sum('subtotal')]
        );

        return redirect()->back()->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
