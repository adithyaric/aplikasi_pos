<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembelianRequest;
use App\Models\Outlet;
use App\Models\Pembelian;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;

class PembelianController extends Controller
{
    public function index()
    {
        return view('pembelians.index', [
            'pembelians' => Pembelian::get(),
        ]);
    }

    public function create()
    {
        return view('pembelians.create', [
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
        ]);
    }

    public function store(PembelianRequest $request)
    {
        $data = $request->validated();
        $pembelian = Pembelian::create($data);
        foreach ($request->product as $product) {
            $stock = new Stock();
            $stock->product_id = $product['product_id'];
            $stock->harga_beli = str_replace(',', '', $product['harga_beli']);
            $stock->qty = $product['qty'];
            $stock->subtotal = $product['subtotal'];
            $stock->created_at = now();
            $stock->expired_at = $product['expired'];
            $pembelian->stocks()->save($stock);
        }

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function show(Pembelian $pembelian)
    {
        dd(
            $pembelian->load(['stocks', 'stocks.product'])->toArray()
        );
    }

    public function edit(Pembelian $pembelian)
    {
        return view('pembelians.edit', [
            'pembelian' => $pembelian,
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
        ]);
    }

    public function update(PembelianRequest $request, Pembelian $pembelian)
    {
        $data = $request->validated();
        $pembelian->update($data);

        // Update related stocks
        $pembelian->stocks->each(function ($stock) use ($request) {
            // Find the matching product data in the request
            $productData = collect($request->product)->firstWhere('product_id', $stock->product_id);

            // Update the stock with the new data
            if ($productData) {
                $stock->update([
                    'harga_beli' => (int) str_replace(',', '', $productData['harga_beli']),
                    'qty' => (int) $productData['qty'],
                    'subtotal' => (int) $productData['subtotal'],
                    'expired_at' => $productData['expired'],
                ]);
            }
        });

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Memperbarui Data!');
    }

    public function destroy(Pembelian $pembelian)
    {
        $pembelian->stocks()->delete();
        $pembelian->delete();

        return redirect(route('pembelian.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
