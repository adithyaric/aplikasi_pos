<?php

namespace App\Http\Controllers;

use App\Http\Requests\RefundPembelianRequest;
use App\Models\Kas;
use App\Models\Outlet;
use App\Models\Pembelian;
use App\Models\Product;
use App\Models\RefundPembelian;
use App\Models\RefundPembelianItem;
use App\Models\Supplier;
use App\Models\User;

class RefundPembelianController extends Controller
{
    public function index()
    {
        return view('refundPembelians.index', [
            'refundPembelians' => RefundPembelian::get(),
        ]);
    }

    public function create()
    {
        return view('refundPembelians.create', [
            'outlets' => Outlet::whereHas('pembelian')->get(),
            'customers' => User::where('role', 'customer')->get(),
            'pembelians' => Pembelian::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
            'kas' => Kas::get(),
        ]);
    }

    public function edit(RefundPembelian $refundPembelian)
    {
        return view('refundPembelians.edit', [
            'refundPembelian' => $refundPembelian,
            'outlets' => Outlet::get(),
            'customers' => User::where('role', 'customer')->get(),
            'pembelians' => Pembelian::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
            'kas' => Kas::get(),
        ]);
    }

    public function show(RefundPembelian $refundPembelian)
    {
        // dd($refundPembelian->load(['customer', 'outlet', 'pembelian', 'refundPembelianItems', 'refundPembelianItems.product'])->toArray());
        return view('refundPembelians.show', [
            'refundPembelian' => $refundPembelian,
        ]);
    }

    //Menambah pembelian Stock & Mengurangi market Stocks
    public function store(RefundPembelianRequest $request)
    {
        $data = $request->validated();

        $data['total'] = (int) str_replace(',', '', $data['total']);
        $data['user_id'] = auth()->user()->id;
        $refundPembelian = RefundPembelian::create($data);

        foreach ($request->product as $product) {
            RefundPembelianItem::create([
                'refund_pembelian_id' => $refundPembelian->id,
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'alasan' => $product['alasan'],
            ]);

            $productModel = Product::find($product['product_id']);

            if ($productModel->is_serialized) {
                StockPembelian::create([
                    'product_id' => $product['product_id'],
                    'serial_number' => $product['alasan'],
                    'pembelian_id' => $refundPembelian->pembelian_id,
                    'qty' => 1,
                    'harga_beli' => $productModel->harga_beli,
                    'status' => 'available',
                ]);

                // For serialized market stock
                $marketStock = Stock::where('product_id', $product['product_id'])
                    ->where('serial_number', $product['alasan'])
                    ->first();

                if ($marketStock) {
                    $marketStock->qty -= 1;
                    $marketStock->save();
                }
            } else {
                $stockPembelian = StockPembelian::where('product_id', $product['product_id'])->first();
                if ($stockPembelian) {
                    $stockPembelian->qty += $product['qty'];
                    $stockPembelian->save();
                } else {
                    StockPembelian::create([
                        'product_id' => $product['product_id'],
                        'pembelian_id' => $refundPembelian->pembelian_id,
                        'qty' => $product['qty'],
                        'harga_beli' => $productModel->harga_beli,
                        'status' => 'available',
                    ]);
                }

                // For non-serialized market stock
                $marketStock = Stock::where('product_id', $product['product_id'])->first();
                if ($marketStock) {
                    $marketStock->qty -= $product['qty'];
                    $marketStock->save();
                }
            }
        }

        $kas = Kas::find($request->kas_id);
        $kas->nominal += $data['total'];
        $kas->save();

        return redirect(route('refundPembelian.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }
    public function update(RefundPembelianRequest $request, RefundPembelian $refundPembelian)
    {
        $oldTotal = $refundPembelian->total;
        $data = $request->validated();
        $data['total'] = (int) str_replace(',', '', $data['total']);
        $data['user_id'] = auth()->user()->id;
        $refundPembelian->update($data);
        RefundPembelianItem::where('refund_pembelian_id', $refundPembelian->id)->delete();
        foreach ($request->product as $product) {
            RefundPembelianItem::create([
                'refund_pembelian_id' => $refundPembelian->id,
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'alasan' => $product['alasan'],
            ]);
        }

        $kas = Kas::find($request->kas_id);
        $kas->nominal += $oldTotal != $data['total'] ? $data['total'] : 0;
        $kas->save();

        return redirect(route('refundPembelian.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function destroy(RefundPembelian $refundPembelian)
    {
        $refundPembelian->delete();

        return redirect(route('refundPembelian.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
