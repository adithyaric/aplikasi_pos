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
use PDF;

class PembelianController extends Controller
{
    public function getPembelian($outlet_id)
    {
        $pembelians = Pembelian::where('outlet_id', $outlet_id)->get();

        return response()->json($pembelians);
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

    public function store(PembelianRequest $request)
    {
        $data = $request->validated();
        $pembelian = Pembelian::create($data);
        $this->updateStock($request, $pembelian);
        $kas = Kas::find($request->kas_id);
        $kas->nominal -= $request->total;
        $kas->save();

        // $pdf = PDF::loadView('pembelians.pembelian_pdf', ['pembelian' => $pembelian]);

        // return $pdf->download('pembelian_' . $pembelian->id . '.pdf')->with('toast_success', 'Berhasil Menyimpan Data!');

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function show(Pembelian $pembelian)
    {
        // dd($pembelian->load(['stocks', 'stocks.product'])->toArray());
        // Generate and download the PDF file
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

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Memperbarui Data!');
    }

    private function updateStock($request, $pembelian)
    {
        if ($pembelian->is_published) {
            foreach ($request as $productData) {
                Stock::updateOrCreate(
                    ['pembelian_id' => $pembelian->id, 'product_id' => $productData->product_id],
                    [
                        'harga_beli' => (int) str_replace(',', '', $productData->harga_beli),
                        'qty' => (int) $productData->qty,
                        'subtotal' => (int) $productData->subtotal,
                        'expired_at' => $productData->expired_at,
                    ]
                );
                $product = Product::find($productData->product_id);
                $product->update(['harga_beli' => (int) str_replace(',', '', $productData->harga_beli)]);
            }
        } else {
            if (isset($request->product)) {
                foreach ($request->product as $productData) {
                    PembelianProduct::updateOrCreate(
                        ['pembelian_id' => $pembelian->id, 'product_id' => $productData['product_id']],
                        [
                            'harga_beli' => (int) str_replace(',', '', $productData['harga_beli']),
                            'qty' => (int) $productData['qty'],
                            'subtotal' => (int) $productData['subtotal'],
                            'expired_at' => $productData['expired'],
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
}
