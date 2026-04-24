<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class StockController extends Controller
{
    public function index()
    {
        return view('stocks.index', [
            'stocks' => Stock::with([
                'product',
                'pembelian.supplier',
                'ownerStock.owner',
            ])
                ->orderBy('product_id')
                ->orderBy('expired_at')
                ->get()
        ]);
    }

    public function show(Stock $stock)
    {
        $stock->delete();

        $total = $stock->pembelian->stocks->sum('subtotal');
        $stock->pembelian->update(['total' => $total]);

        return redirect()->back()->with('toast_success', 'Berhasil Menghapus Data!');
    }

    public function destroy(Stock $stock)
    {
        dd(
            'destory Stock',
            $stock->toArray(),
            $stock->pembelian->toArray()
        );
        // $stock->delete();

        return redirect()->back()->with('toast_success', 'Berhasil Menghapus Data!');
    }

    public function history(Stock $stock)
    {
        $activities = Activity::forSubject($stock)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($activity) {
                return [
                    'source'     => 'activity',
                    'date'       => $activity->created_at->format('d M Y H:i'),
                    'user'       => $activity->causer?->name ?? 'System',
                    'event'      => $activity->event,
                    'properties' => $activity->properties,
                ];
            });

        $movements = StockMovement::where('product_id', $stock->product_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($movement) {
                return [
                    'source'  => 'movement',
                    'date'    => $movement->created_at->format('d M Y H:i'),
                    'user'    => $movement->user?->name ?? 'System',
                    'type'    => $movement->type,
                    'qty_in'  => $movement->qty_in,
                    'qty_out' => $movement->qty_out,
                    'balance' => $movement->balance,
                    'notes'   => $movement->notes,
                ];
            });

        return response()->json(['success' => true, 'activities' => $activities, 'movements' => $movements]);
    }

    //kartu
    public function kartu(Request $request)
    {
        $stocks = Stock::with('product', 'pembelian.supplier')
            ->whereNotNull('sku')
            ->orderBy('product_id')
            ->orderBy('sku')
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'sku' => $stock->sku,
                    'product_name' => $stock->product->name,
                    'supplier' => $stock->pembelian->supplier->name ?? '-',
                    'harga_beli' => $stock->harga_beli,
                ];
            });

        return view('stocks.kartu', [
            'stocks' => $stocks,
        ]);
    }

    public function getKartuData(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id'
        ]);

        $stock = Stock::with('product', 'pembelian.supplier')->find($request->stock_id);

        if (! $stock) {
            return response()->json(['error' => 'Stock tidak ditemukan'], 404);
        }

        // Get all stock movements for this product with this SKU
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

        // Build transactions with running balance
        $result = [];
        $runningStock = 0;
        $currentPrice = $stock->harga_beli;

        foreach ($movements as $movement) {
            $date = $movement->created_at->format('Y-m-d');
            $stokAwal = $runningStock;
            $masuk = $movement->qty_in ?? 0;
            $keluar = $movement->qty_out ?? 0;
            $stokAkhir = $stokAwal + $masuk - $keluar;
            $nilai = $stokAkhir * $currentPrice;

            $keterangan = $movement->notes ?? '-';
            if ($movement->type === 'adjustment') {
                $adj = StockAdjustment::find($movement->reference_id);
                if ($adj && $adj->stock_id === $stock->id && !empty($adj->keterangan)) {
                    $keterangan = $adj->keterangan;
                }
            }

            $result[] = [
                'tanggal' => $date,
                'stok_awal' => $stokAwal,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'stok_akhir' => $stokAkhir,
                'harga' => $currentPrice,
                'nilai' => $nilai,
                'keterangan' => $keterangan,
            ];

            $runningStock = $stokAkhir;
        }

        return response()->json([
            'stock' => [
                'id' => $stock->id,
                'sku' => $stock->sku,
                'product_name' => $stock->product->name,
                'product_code' => $stock->product->code,
                'supplier' => $stock->pembelian->supplier->name ?? '-',
            ],
            'transactions' => $result
        ]);
    }

    //opname
    public function opname(Request $request)
    {
        $products = Product::with('stocks')->orderBy('name')->get();

        return view('stocks.opname', [
            'products' => $products,
        ]);
    }

    public function getOpnameData(Request $request)
    {
        $stocks = Stock::with('product')
            ->where('qty', '>', 0)
            ->whereNotNull('sku')
            ->orderBy('product_id')
            ->orderBy('sku')
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'product_id' => $stock->product_id,
                    'product_name' => $stock->product->name,
                    'product_code' => $stock->product->code,
                    'sku' => $stock->sku,
                    'satuan' => $stock->product->satuan ?? 'pcs',
                    'qty' => $stock->qty,
                    'qty_reserved' => $stock->qty_reserved,
                    'qty_available' => $stock->qty_available,
                    'keterangan' => $stock->adjustment?->keterangan ?? '',
                ];
            });

        return response()->json(['stocks' => $stocks->values()]);
    }

    public function saveOpname(Request $request)
    {
        $request->validate([
            'adjustment_date' => 'required|date',
            'items' => 'required|array',
            'items.*.stock_id' => 'required|exists:stocks,id',
            'items.*.selisih' => 'required|numeric',
            'items.*.keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                if ($item['selisih'] != 0) {
                    $stock = Stock::find($item['stock_id']);

                    // Create adjustment record
                    $savedAdj = StockAdjustment::create([
                        'adjustment_date' => $request->adjustment_date,
                        'product_id'      => $stock->product_id,
                        'stock_id'        => $stock->id,
                        'sku'             => $stock->sku,
                        'quantity'        => $item['selisih'],
                        'system_qty'      => $item['system_qty'] ?? $stock->qty,
                        'physical_qty'    => $item['physical_qty'] ?? ($stock->qty + $item['selisih']),
                        'reason'          => $item['keterangan'] ?? null,
                        'status'          => 'Selesai',
                        'keterangan'      => $item['keterangan'] ?? null,
                    ]);

                    $newQty = $stock->qty + $item['selisih'];
                    $stock->update(['qty' => $newQty]);

                    // Log movement
                    StockMovement::create([
                        'product_id'     => $stock->product_id,
                        'user_id'        => auth()->id(),
                        'type'           => 'adjustment',
                        'reference_type' => StockAdjustment::class,
                        'reference_id'   => $savedAdj->id,
                        'qty_in'         => $item['selisih'] > 0 ? $item['selisih'] : 0,
                        'qty_out'        => $item['selisih'] < 0 ? abs($item['selisih']) : 0,
                        'balance'        => $newQty,
                        'notes'          => "Stock opname adjustment - SKU: {$stock->sku} - ".($item['keterangan'] ?? 'Stock adjustment'),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Stok opname berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: '.$e->getMessage()], 500);
        }
    }
}
