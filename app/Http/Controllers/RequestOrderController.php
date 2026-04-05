<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\RequestOrder;
use App\Models\RequestOrderItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestOrderController extends Controller
{
    public function show($id)
    {
        $ro = RequestOrder::with(['items.product.stocks', 'pickingList', 'deliveryOrder'])->findOrFail($id);
        dd($ro?->toArray());
    }

    public function index()
    {
        $requests = RequestOrder::with(['owner', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('request-orders.index', compact('requests'));
    }

    public function create()
    {
        return view('request-orders.create', [
            'outlets' => Outlet::get(),
            'products' => Product::with(['stocks' => function ($q) {
                $q->where('qty_available', '>', 0)
                    ->where('status', 'available');
            }])->whereHas('stocks', function ($q) {
                $q->where('qty_available', '>', 0)
                    ->where('status', 'available');
            })
                // ->where('is_serialized', false)
                ->get()
                ->map(function ($product) {
                    $product->total_available = (int) $product->stocks->sum('qty_available');

                    return $product;
                }),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:outlets,id',
            'request_date' => 'required|date',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty_requested' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $lastRequest = RequestOrder::withTrashed()->latest('id')->first();
            $nextNumber = $lastRequest ? ((int) substr($lastRequest->code, 3) + 1) : 1;
            $code = 'REQ'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            $requestOrder = RequestOrder::create([
                'code' => $code,
                'owner_id' => $request->owner_id,
                'requested_by' => auth()->id(),
                'request_date' => $request->request_date,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                RequestOrderItem::create([
                    'request_order_id' => $requestOrder->id,
                    'product_id' => $item['product_id'],
                    'stock_id' => null, // No stock assigned yet
                    'qty_requested' => $item['qty_requested'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('request-orders.verify', $requestOrder)
                ->with('toast_success', 'Request created successfully. Please assign stocks.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function verify(RequestOrder $requestOrder)
    {
        $requestOrder->load(['items.product.stocks']);

        // dd($requestOrder?->toArray());

        return view('request-orders.verify', compact('requestOrder'));
    }

    public function processVerification(Request $request, RequestOrder $requestOrder)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:request_order_items,id',
            'items.*.qty_approved' => 'required|integer|min:0',
            'items.*.item_status' => 'required|in:approved,partial,rejected',
        ]);

        // Validate qty_approved against specific SKU stock
        foreach ($request->items as $itemData) {
            $item = RequestOrderItem::find($itemData['id']);
            $stock = $item->stock;

            if (! $stock) {
                return back()->withErrors([
                    'items.'.array_search($itemData, $request->items).'.qty_approved' => "Stock not found for product {$item->product->name}"
                ])->withInput();
            }

            // Validate against requested qty
            if ($itemData['qty_approved'] > $item->qty_requested) {
                return back()->withErrors([
                    'items.'.array_search($itemData, $request->items).'.qty_approved' => "Product {$item->product->name}: Approved qty cannot exceed requested qty ({$item->qty_requested})"
                ])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // FIRST: Unreserve all previous reservations
            foreach ($request->items as $itemData) {
                $item = RequestOrderItem::find($itemData['id']);
                $stock = $item->stock;

                if ($item->qty_approved > 0 && $stock) {
                    $stock->unreserve($item->qty_approved);
                }
            }

            // SECOND: Refresh stocks and validate new quantities
            foreach ($request->items as $itemData) {
                $item = RequestOrderItem::find($itemData['id']);
                $stock = $item->stock->fresh(); // Refresh from DB after unreserve

                // Skip validation if rejected
                if ($itemData['item_status'] === 'rejected') {
                    continue;
                }

                // Validate available stock after unreserving
                if ($itemData['qty_approved'] > 0) {
                    if ($stock->qty_available < $itemData['qty_approved']) {
                        // Rollback and show error with current available
                        DB::rollBack();

                        return back()->withErrors([
                            'items.'.array_search($itemData, $request->items).'.qty_approved' => "Product {$item->product->name} (SKU: {$stock->sku}): Only {$stock->qty_available} available after releasing previous reservation. Cannot approve {$itemData['qty_approved']}."
                        ])->withInput();
                    }
                }
            }

            // THIRD: Update items and reserve new quantities
            $hasApproved = false;
            $hasPartial = false;
            $allRejected = true;

            foreach ($request->items as $itemData) {
                $item = RequestOrderItem::find($itemData['id']);
                $stock = $item->stock->fresh();

                // Handle rejected status
                if ($itemData['item_status'] === 'rejected') {
                    $item->update([
                        'qty_approved' => 0,
                        'item_status' => 'rejected',
                        'notes' => $itemData['notes'] ?? null,
                    ]);

                    continue;
                }

                $item->update([
                    'qty_approved' => $itemData['qty_approved'],
                    'item_status' => $itemData['item_status'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Reserve new quantity
                if ($itemData['qty_approved'] > 0) {
                    $stock->reserve($itemData['qty_approved']);
                }

                // Determine overall status
                if ($itemData['item_status'] === 'approved') {
                    $hasApproved = true;
                }
                if ($itemData['item_status'] === 'partial') {
                    $hasPartial = true;
                    $hasApproved = true;
                }
                if ($itemData['item_status'] !== 'rejected') {
                    $allRejected = false;
                }
            }

            // Update request order status
            if ($allRejected) {
                $status = 'rejected';
            } elseif ($hasPartial) {
                $status = 'partial';
            } else {
                $status = 'approved';
            }

            $requestOrder->update([
                'status' => $status,
                'verified_by' => auth()->id(),
                'verified_date' => now(),
                'verification_notes' => $request->verification_notes,
            ]);

            DB::commit();

            $message = $requestOrder->wasChanged('status')
                ? 'Request verified successfully'
                : 'Request verification updated successfully';

            return redirect()->route('request-orders.verify', $requestOrder)
                ->with('toast_success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function updateStocks(Request $request, RequestOrder $requestOrder)
    {
        $request->validate([
            'stock_assignments' => 'required|array',
            'stock_assignments.*.item_id' => 'required|exists:request_order_items,id',
            'stock_assignments.*.stock_id' => 'required|exists:stocks,id|distinct',
            'stock_assignments.*.qty' => 'required|integer|min:1',
        ], [
            'stock_assignments.*.stock_id.distinct' => 'Terdapat stok yang sama (ID :input) dimasukkan lebih dari satu kali.',
        ]);

        DB::beginTransaction();
        try {
            // Group by item_id
            $grouped = collect($request->stock_assignments)->groupBy('item_id');

            foreach ($grouped as $itemId => $assignments) {
                $originalItem = RequestOrderItem::find($itemId);
                $totalQty = $assignments->sum('qty');

                if ($totalQty != $originalItem->qty_requested) {
                    throw new \Exception("Product {$originalItem->product->name}: Total assigned qty ({$totalQty}) must equal requested qty ({$originalItem->qty_requested})");
                }

                // Delete original item (will be replaced by split items)
                $originalItem->delete();

                // Create new items for each stock assignment
                foreach ($assignments as $assignment) {
                    $stock = Stock::find($assignment['stock_id']);

                    if ($stock->qty_available < $assignment['qty']) {
                        throw new \Exception("Stock {$stock->sku}: Only {$stock->qty_available} available, cannot assign {$assignment['qty']}");
                    }

                    RequestOrderItem::create([
                        'request_order_id' => $requestOrder->id,
                        'product_id' => $originalItem->product_id,
                        'stock_id' => $assignment['stock_id'],
                        'qty_requested' => $assignment['qty'],
                        'notes' => $originalItem->notes,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('request-orders.verify', $requestOrder)
                ->with('toast_success', 'Stocks assigned successfully. Now you can approve.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage())->withInput();
        }
    }
}
