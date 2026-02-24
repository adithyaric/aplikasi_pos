<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembelianRequest;
use App\Models\Kas;
use App\Models\Outlet;
use App\Models\Pembelian;
use App\Models\PembelianProduct;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockPembelian;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'pembelians' => Pembelian::latest()->get(),
        ]);
    }

    public function create()
    {
        $lastPembelian = Pembelian::latest('id')->first();
        $nextNumber = $lastPembelian ? ((int) substr($lastPembelian->code, 4) + 1) : 1;
        $code = 'PO'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return view('pembelians.create', [
            // 'kas' => Kas::get(),
            // 'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'products' => Product::get(),
            'code' => $code
        ]);
    }

    public function store(PembelianRequest $request)
    {
        $request->validated();

        $pembelian = Pembelian::create([
            'code' => $request->code,
            // 'outlet_id' => $request->outlet_id,
            'supplier_id' => $request->supplier_id,
            // 'kas_id' => $request->kas_id,
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
        // Prevent double publishing
        if ($pembelian->is_published) {
            return redirect()->route('pembelian.index')
                ->with('toast_error', 'Pembelian already published');
        }

        return redirect()->route('pembelian.penerimaan', $pembelian);
    }

    public function penerimaan(Pembelian $pembelian)
    {
        // if ($pembelian->is_published) {
        //     return redirect()->route('pembelian.index')
        //         ->with('toast_error', 'Pembelian already published');
        // }

        $pembelian->load(['pembelianProducts.product', 'stockPembelians.product', 'supplier']);

        // dd($pembelian?->toArray());
        return view('pembelians.penerimaan', compact('pembelian'));
    }

    public function storePenerimaan(Request $request, Pembelian $pembelian)
    {
        if ($pembelian->is_published) {
            return redirect()->route('pembelian.index')
                ->with('toast_error', 'Pembelian already published');
        }

        $request->validate([
            'receipt_date' => 'required|date',
            'receipt_pic' => 'required|string',
            'receipt_status' => 'required|in:draft,validated,completed',
            'receipt_photo' => 'nullable|image|max:2048',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty_diterima' => 'required|integer|min:0',
            'items.*.expired_at' => 'nullable|date',
            'items.*.serial_numbers' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('receipt_photo')) {
                $photoPath = $request->file('receipt_photo')->store('receipt-photos', 'public');
            }

            // Update pembelian receipt info
            $pembelian->update([
                'receipt_date' => $request->receipt_date,
                'receipt_pic' => $request->receipt_pic,
                'receipt_status' => $request->receipt_status,
                'receipt_photo' => $photoPath,
            ]);

            $hasReceived = false;

            foreach ($request->items as $itemData) {
                $qtyDiterima = (int) $itemData['qty_diterima'];

                if ($qtyDiterima <= 0) { continue; }

                $hasReceived = true;
                $product = Product::find($itemData['product_id']);
                $pembelianProduct = $pembelian->pembelianProducts()
                    ->where('product_id', $itemData['product_id'])
                    ->first();

                if (! $pembelianProduct) { continue; }

                $expiredAt = ! empty($itemData['expired_at']) ? $itemData['expired_at'] : null;

                if ($product->is_serialized) {
                    // For serialized items - USE ACTUAL RECEIVED SERIAL NUMBERS
                    $receivedSerials = ! empty($itemData['serial_numbers'])
                        ? array_filter(array_map('trim', explode("\n", $itemData['serial_numbers'])))
                        : [];

                    if (count($receivedSerials) !== $qtyDiterima) {
                        throw new \Exception("Product {$product->name}: Serial numbers count (".count($receivedSerials).") must match qty received ({$qtyDiterima})");
                    }

                    // Check available qty in warehouse
                    $availableQty = StockPembelian::where([
                        'pembelian_id' => $pembelian->id,
                        'product_id' => $itemData['product_id']
                    ])->sum('qty');

                    if ($qtyDiterima > $availableQty) {
                        throw new \Exception("Product {$product->name}: Cannot receive {$qtyDiterima} items, only {$availableQty} available in warehouse");
                    }

                    $receivedCount = 0;
                    foreach ($receivedSerials as $serial) {
                        if (empty($serial)) { continue; }

                        // Check if this serial already exists in Stock (prevent duplicate)
                        $existingStock = Stock::where([
                            'pembelian_id' => $pembelian->id,
                            'product_id' => $itemData['product_id'],
                            'serial_number' => $serial
                        ])->exists();

                        if ($existingStock) {
                            throw new \Exception("Serial number '{$serial}' already received for product {$product->name}");
                        }

                        // Create market stock with RECEIVED serial number
                        Stock::create([
                            'pembelian_id' => $pembelian->id,
                            'product_id' => $itemData['product_id'],
                            'serial_number' => $serial,
                            'harga_beli' => $pembelianProduct->harga_beli,
                            'qty' => 1,
                            'subtotal' => $pembelianProduct->harga_beli,
                            'expired_at' => $expiredAt,
                            'condition' => 'new',
                            'status' => 'available',
                        ]);

                        $receivedCount++;
                    }

                    // Decrease StockPembelian (any records, FIFO)
                    $remainingToDeduct = $receivedCount;
                    $stockPembelians = StockPembelian::where([
                        'pembelian_id' => $pembelian->id,
                        'product_id' => $itemData['product_id']
                    ])->where('qty', '>', 0)
                        ->orderBy('id', 'asc')
                        ->get();

                    foreach ($stockPembelians as $stockPembelian) {
                        if ($remainingToDeduct <= 0) { break; }

                        $deductQty = min($remainingToDeduct, $stockPembelian->qty);
                        $stockPembelian->decrement('qty', $deductQty);

                        if ($stockPembelian->qty <= 0) {
                            $stockPembelian->delete();
                        }

                        $remainingToDeduct -= $deductQty;
                    }

                    // Log movement
                    if ($receivedCount > 0) {
                        StockMovement::create([
                            'product_id' => $itemData['product_id'],
                            'user_id' => auth()->id(),
                            'type' => 'in',
                            'reference_type' => Pembelian::class,
                            'reference_id' => $pembelian->id,
                            'qty_in' => $receivedCount,
                            'balance' => $product->stocks()->sum('qty'),
                            'notes' => "Goods receipt from {$pembelian->supplier->name} - Serials: ".implode(', ', $receivedSerials),
                        ]);
                    }
                } else {
                    // For bulk items
                    $hargaBeli = $pembelianProduct->harga_beli;

                    // Check available qty in warehouse
                    $availableQty = StockPembelian::where([
                        'pembelian_id' => $pembelian->id,
                        'product_id' => $itemData['product_id']
                    ])->whereNull('serial_number')->sum('qty');

                    if ($qtyDiterima > $availableQty) {
                        throw new \Exception("Product {$product->name}: Cannot receive {$qtyDiterima} items, only {$availableQty} available in warehouse");
                    }

                    // Check existing stock for this pembelian+product
                    $existingStock = Stock::where([
                        'pembelian_id' => $pembelian->id,
                        'product_id' => $itemData['product_id']
                    ])->whereNull('serial_number')->first();

                    if ($existingStock) {
                        // Update existing
                        $existingStock->increment('qty', $qtyDiterima);
                        $existingStock->update([
                            'subtotal' => DB::raw('qty * harga_beli'),
                            'expired_at' => $expiredAt ?? $existingStock->expired_at,
                        ]);
                    } else {
                        // Create new stock
                        Stock::create([
                            'pembelian_id' => $pembelian->id,
                            'product_id' => $itemData['product_id'],
                            'harga_beli' => $hargaBeli,
                            'qty' => $qtyDiterima,
                            'subtotal' => $qtyDiterima * $hargaBeli,
                            'expired_at' => $expiredAt,
                            'condition' => 'new',
                            'status' => 'available',
                        ]);
                    }

                    // Decrease StockPembelian
                    $stockPembelian = StockPembelian::where([
                        'pembelian_id' => $pembelian->id,
                        'product_id' => $itemData['product_id']
                    ])->whereNull('serial_number')->first();

                    if ($stockPembelian) {
                        $stockPembelian->decrement('qty', $qtyDiterima);
                        if ($stockPembelian->qty <= 0) {
                            $stockPembelian->delete();
                        }
                    }

                    // Log movement
                    StockMovement::create([
                        'product_id' => $itemData['product_id'],
                        'user_id' => auth()->id(),
                        'type' => 'in',
                        'reference_type' => Pembelian::class,
                        'reference_id' => $pembelian->id,
                        'qty_in' => $qtyDiterima,
                        'balance' => $product->stocks()->sum('qty'),
                        'notes' => "Goods receipt from {$pembelian->supplier->name}",
                    ]);
                }

                // Update product HPP
                $newHPP = $product->calculateHPP($qtyDiterima, $pembelianProduct->harga_beli);
                $product->update([
                    'harga_beli' => $pembelianProduct->harga_beli,
                    'hpp' => $newHPP,
                ]);
                $product->updateStockValue();
            }

            if (! $hasReceived) {
                throw new \Exception('No items received');
            }

            // Check if all items fully received
            $remainingStock = $pembelian->stockPembelians()->sum('qty');

            if ($remainingStock == 0) {
                // All received, mark as published
                $pembelian->update(['is_published' => true]);

                // Deduct from Kas
                $kas = Kas::find($pembelian->kas_id);
                if ($kas) {
                    $kas->nominal -= $pembelian->total;
                    $kas->save();
                }
            }

            DB::commit();

            $message = $remainingStock > 0
                ? 'Partial receipt recorded. Remaining stock in warehouse: '.$remainingStock.' items'
                : 'All items received and published successfully';

            return redirect()->route('pembelian.index')
                ->with('toast_success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage())->withInput();
        }
    }

    private function updateStock($request, $pembelian)
    {
        if ($pembelian->is_published) {
            foreach ($request as $productData) {
                $product = Product::find($productData->product_id);
                if ($product->is_serialized && ! empty($productData->serial_numbers)) {
                    $serialNumbers = is_array($productData->serial_numbers)
                        ? $productData->serial_numbers
                        : explode("\n", trim($productData->serial_numbers));

                    foreach ($serialNumbers as $serial) {
                        $serial = trim($serial);
                        if (! empty($serial)) {
                            // Create market stock
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

                            // Decrease StockPembelian quantity
                            StockPembelian::where([
                                'pembelian_id' => $pembelian->id,
                                'product_id' => $productData->product_id,
                                'serial_number' => $serial
                            ])->decrement('qty', 1);
                        }
                    }
                } else {
                    // For bulk items
                    $qty = (int) $productData->qty;

                    // Create market stock
                    Stock::updateOrCreate(
                        ['pembelian_id' => $pembelian->id, 'product_id' => $productData->product_id],
                        [
                            'harga_beli' => (int) str_replace(',', '', $productData->harga_beli),
                            'qty' => $qty,
                            'subtotal' => (int) $productData->subtotal,
                            'expired_at' => $productData->expired_at ?? null,
                            'condition' => 'new',
                        ]
                    );

                    // Decrease StockPembelian quantity
                    StockPembelian::where([
                        'pembelian_id' => $pembelian->id,
                        'product_id' => $productData->product_id
                    ])->decrement('qty', $qty);
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
                            // 'expired_at' => $productData['expired'] ?? null,
                            'serial_numbers' => $serialNumbers,
                        ]
                    );

                    // Add StockPembelian for non-published products
                    if (Product::find($productData['product_id'])->is_serialized && ! empty($serialNumbers)) {
                        foreach ($serialNumbers as $serial) {
                            StockPembelian::updateOrCreate(
                                [
                                    'pembelian_id' => $pembelian->id,
                                    'product_id' => $productData['product_id'],
                                    'serial_number' => $serial
                                ],
                                [
                                    'harga_beli' => (int) str_replace(',', '', $productData['harga_beli']),
                                    'qty' => 1,
                                    'subtotal' => (int) str_replace(',', '', $productData['harga_beli']),
                                    // 'expired_at' => $productData['expired'] ?? null,
                                    'condition' => 'new',
                                    'status' => 'available',
                                ]
                            );
                        }
                    } else {
                        StockPembelian::updateOrCreate(
                            ['pembelian_id' => $pembelian->id, 'product_id' => $productData['product_id']],
                            [
                                'harga_beli' => (int) str_replace(',', '', $productData['harga_beli']),
                                'qty' => (int) $productData['qty'],
                                'subtotal' => (int) $productData['subtotal'],
                                // 'expired_at' => $productData['expired'] ?? null,
                                'condition' => 'new',
                                'status' => 'available',
                            ]
                        );
                    }
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
