<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenjualanRequest;
use App\Models\Penjualan;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index', [
            'penjualan' => Penjualan::get(),
        ]);
    }

    public function create()
    {
        return view('penjualan.create', []);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'total' => 'required',
        ]);

        DB::beginTransaction();

        try {
            $lastOrder = Penjualan::orderBy('created_at', 'desc')->first();
            $nextInvoiceNumber = $lastOrder ? ((int) substr($lastOrder->code, 3) + 1) : 1;
            $nextInvoiceNumber = str_pad($nextInvoiceNumber, 3, '0', STR_PAD_LEFT);
            $nextInvoiceCode = 'INV'.$nextInvoiceNumber;

            $order = Penjualan::create([
                'code' => $nextInvoiceCode,
                'customer_id' => $request->customer_id,
                'kasir_id' => auth()->id(),
                'discount' => $request->discount,
                'total' => $request->total,
            ]);

            $cart = $request->user()->cart()->get();
            foreach ($cart as $item) {
                $order->items()->create([
                    'subtotal' => $item->harga_jual * $item->pivot->qty,
                    'price' => $item->harga_jual,
                    'qty' => $item->pivot->qty,
                    'product_id' => $item->id,
                ]);

                $now = Carbon::now();
                $stock = Stock::where('product_id', $item->id)
                    ->where('created_at', '<=', $now)
                    ->where('expired_at', '>=', $now)
                    ->first();

                if ($stock) {
                    if ($stock->qty >= $item->pivot->qty) {
                        $stock->qty -= $item->pivot->qty;
                        $stock->save();
                    } else {
                        throw new Exception('Insufficient stock quantity');
                    }
                } else {
                    throw new Exception('Stock not found or expired');
                }
            }

            $request->user()->cart()->detach();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }
    }

    public function show(Penjualan $penjualan)
    {
        dd($penjualan->load(['kasir', 'customer', 'items.product'])->toArray());
    }

    // public function edit(Penjualan $penjualan)
    // {
    //     return view('penjualan.edit', [
    //         'penjualan' => $penjualan,
    //     ]);
    // }

    // public function update(PenjualanRequest $request, Penjualan $penjualan)
    // {
    //     $data = $request->validated();

    //     $penjualan->update($data);

    //     return redirect(route('penjualan.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    // }

    public function destroy(Penjualan $penjualan)
    {
        $penjualan->delete();

        return redirect(route('penjualan.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
