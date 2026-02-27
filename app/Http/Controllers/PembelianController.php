<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembelianRequest;
use App\Models\Kas;
use App\Models\Outlet;
use App\Models\Pembelian;
use App\Models\PembelianProduct;
use App\Models\PembelianTransaction;
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

    public function getProductsBySupplier(Supplier $supplier)
    {
        $products = $supplier->products()->select('id', 'name', 'is_serialized', 'harga_beli')->get();

        return response()->json($products);
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

        $supplier = Supplier::find($request->supplier_id);
        PembelianTransaction::create([
            'pembelian_id' => $pembelian->id,
            'payment_date' => null,
            'payment_method' => 'bank_transfer',
            'payment_reference' => $supplier->bank_no_rek.'-'.$supplier->bank_nama ?? 'TRX-'.now(),
            // 'amount' => $grandTotal,
            'amount' => 0,
            'status' => 'unpaid',
            'notes' => null,
        ]);

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
        $pembelian->load(['pembelianProducts.product', 'stocks.product', 'supplier']);

        return view('pembelians.penerimaan', compact('pembelian'));
    }

    public function storePenerimaan(Request $request, Pembelian $pembelian)
    {
        $request->validate([
            'receipt_date' => 'required|date',
            'receipt_pic' => 'required|string',
            'receipt_status' => 'required|in:draft,validated,completed',
            'receipt_photo' => 'nullable|image|max:2048',
            'items' => 'required|array',
            'items.*.stock_id' => 'nullable|exists:stocks,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.sku' => 'required|string',
            'items.*.qty_diterima' => 'required|integer|min:1',
            'items.*.expired_at' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Handle photo upload
            $photoPath = $pembelian->receipt_photo;
            if ($request->hasFile('receipt_photo')) {
                $photoPath = $request->file('receipt_photo')->store('receipt-photos', 'public');
            }

            // Update pembelian receipt info
            $pembelian->update([
                'code_gr' => $request->code_gr,
                'receipt_date' => $request->receipt_date,
                'receipt_pic' => $request->receipt_pic,
                'receipt_status' => $request->receipt_status,
                'receipt_photo' => $photoPath,
            ]);

            foreach ($request->items as $itemData) {
                $qtyDiterima = (int) $itemData['qty_diterima'];
                $sku = trim($itemData['sku']);
                $expiredAt = ! empty($itemData['expired_at']) ? $itemData['expired_at'] : null;

                $product = Product::find($itemData['product_id']);
                $pembelianProduct = $pembelian->pembelianProducts()
                    ->where('product_id', $itemData['product_id'])
                    ->first();

                if (! $pembelianProduct) { continue; }

                // Check if updating existing stock or creating new
                if (! empty($itemData['stock_id'])) {
                    // UPDATE existing stock
                    $stock = Stock::find($itemData['stock_id']);

                    if ($stock && $stock->pembelian_id == $pembelian->id) {
                        $oldQty = $stock->qty;

                        $stock->update([
                            'sku' => $sku,
                            'qty' => $qtyDiterima,
                            'harga_beli' => $pembelianProduct->harga_beli,
                            'subtotal' => $qtyDiterima * $pembelianProduct->harga_beli,
                            'expired_at' => $expiredAt,
                        ]);

                        // Log movement if qty changed
                        if ($oldQty != $qtyDiterima) {
                            $diff = $qtyDiterima - $oldQty;
                            StockMovement::create([
                                'product_id' => $itemData['product_id'],
                                'user_id' => auth()->id(),
                                'type' => $diff > 0 ? 'in' : 'adjustment',
                                'reference_type' => Pembelian::class,
                                'reference_id' => $pembelian->id,
                                'qty_in' => $diff > 0 ? $diff : 0,
                                'qty_out' => $diff < 0 ? abs($diff) : 0,
                                'balance' => $product->stocks()->sum('qty'),
                                'notes' => "Stock update - SKU: {$sku}, Old Qty: {$oldQty}, New Qty: {$qtyDiterima}",
                            ]);
                        }
                    }
                } else {
                    // CREATE new stock
                    Stock::create([
                        'pembelian_id' => $pembelian->id,
                        'product_id' => $itemData['product_id'],
                        'sku' => $sku,
                        'harga_beli' => $pembelianProduct->harga_beli,
                        'qty' => $qtyDiterima,
                        'subtotal' => $qtyDiterima * $pembelianProduct->harga_beli,
                        'expired_at' => $expiredAt,
                        'condition' => 'new',
                        'status' => 'available',
                    ]);

                    // Log movement
                    StockMovement::create([
                        'product_id' => $itemData['product_id'],
                        'user_id' => auth()->id(),
                        'type' => 'in',
                        'reference_type' => Pembelian::class,
                        'reference_id' => $pembelian->id,
                        'qty_in' => $qtyDiterima,
                        'balance' => $product->stocks()->sum('qty'),
                        'notes' => "Goods receipt from {$pembelian->supplier->name} - SKU: {$sku}",
                    ]);
                }

                // Update product HPP
                $newHPP = $product->calculateHPP($qtyDiterima, $pembelianProduct->harga_beli);
                $product->update([
                    'hpp' => $newHPP,
                ]);
                $product->updateStockValue();
            }

            // Mark as published if status is completed
            if ($request->receipt_status == 'completed' && ! $pembelian->is_published) {
                $pembelian->update(['is_published' => true]);
            }

            DB::commit();

            return redirect()->route('pembelian.penerimaan', $pembelian)
                ->with('toast_success', 'Penerimaan updated successfully');
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

    public function editPembayaran(Pembelian $pembelian)
    {
        $pembelian->load(['supplier', 'pembelianProducts.product', 'pembelianTransaction']);
        $title = 'Edit Pembayaran Pembelian';
        $paymentHistory = $pembelian->pembelianTransaction?->payment_history ?? [];

        return view('pembelians.pembayaran-edit', compact('pembelian', 'title', 'paymentHistory'));
    }

    public function updatePembayaran(Request $request, Pembelian $pembelian)
    {
        $currentAmount = $pembelian->pembelianTransaction?->amount ?? 0;
        $maxAmount = $pembelian->total - $currentAmount;

        $request->validate([
            'payment_date'      => 'required|date',
            'payment_method'    => 'required|in:cash,bank_transfer,giro_cek,lainnya',
            'payment_reference' => 'nullable|string',
            'amount'            => 'required|numeric|min:0|max:'.$maxAmount,
            'notes'             => 'nullable|string',
            'status'            => 'required|in:unpaid,paid,partial',
            'bukti_transfer'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $previousAmount = $pembelian->pembelianTransaction?->amount ?? 0;
            $newTotalAmount = $previousAmount + $request->amount;

            // Handle file upload
            $buktiPath = null;
            if ($request->hasFile('bukti_transfer')) {
                $file = $request->file('bukti_transfer');
                $filename = 'bukti_'.time().'_'.$pembelian->id.'.'.$file->getClientOriginalExtension();
                $buktiPath = $file->storeAs('bukti_transfer', $filename, 'public');
            }

            if ($pembelian->pembelianTransaction) {
                // Update existing transaction
                $paymentHistory = $pembelian->pembelianTransaction->payment_history ?? [];

                if ($request->amount > 0) {
                    $paymentHistory[] = [
                        'payment_date'      => $request->payment_date,
                        'amount'            => $request->amount,
                        'payment_method'    => $request->payment_method,
                        'payment_reference' => $request->payment_reference,
                        'bukti_transfer'    => $buktiPath ?? null,
                        'notes'             => $request->notes,
                        'created_at'        => now()->toDateTimeString(),
                    ];
                }

                $transactionData = [
                    'payment_date'      => $request->payment_date,
                    'payment_method'    => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'amount'            => $newTotalAmount,
                    'payment_history'   => $paymentHistory,
                    'notes'             => $request->notes,
                    'status'            => $request->status,
                ];

                if ($buktiPath) {
                    $transactionData['bukti_transfer'] = $buktiPath;
                }

                $pembelian->pembelianTransaction->update($transactionData);
            } else {
                // Create new transaction
                $paymentHistory = [];
                if ($request->amount > 0) {
                    $paymentHistory[] = [
                        'payment_date'      => $request->payment_date,
                        'amount'            => $request->amount,
                        'payment_method'    => $request->payment_method,
                        'payment_reference' => $request->payment_reference,
                        'bukti_transfer'    => $buktiPath,
                        'notes'             => $request->notes,
                        'created_at'        => now()->toDateTimeString(),
                    ];
                }

                $transactionData = [
                    'payment_date'      => $request->payment_date,
                    'payment_method'    => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'amount'            => $request->amount,
                    'payment_history'   => $paymentHistory,
                    'notes'             => $request->notes,
                    'status'            => $request->status,
                    'bukti_transfer'    => $buktiPath,
                ];

                $pembelian->pembelianTransaction()->create($transactionData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage()
            ], 500);
        }
    }
}
