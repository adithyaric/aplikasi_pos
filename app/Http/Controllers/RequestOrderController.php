<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\RequestOrder;
use App\Models\RequestOrderItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'products' => Product::whereHas('stocks', function ($q) {
                $q->where('qty_available', '>', 0)
                    ->where('status', 'available');
            })
                // ->where('is_serialized', false)
                ->get(),

        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:outlets,id',
            'request_date' => 'required|date',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.stock_id' => 'required|exists:stocks,id',
            'items.*.qty_requested' => 'required|integer|min:1',
        ]);

        // Validate stock availability per SKU
        foreach ($request->items as $index => $item) {
            $stock = Stock::find($item['stock_id']);

            if (! $stock || $stock->qty_available < $item['qty_requested']) {
                throw ValidationException::withMessages([
                    "items.{$index}.qty_requested" => 'Quantity requested exceeds available stock for selected SKU.'
                ]);
            }
        }

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
                    'stock_id' => $item['stock_id'],
                    'qty_requested' => $item['qty_requested'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('request-orders.verify', $requestOrder)
                ->with('toast_success', 'Request created successfully');
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

            $availableStock = $stock->qty_available;

            if ($itemData['qty_approved'] > $availableStock) {
                return back()->withErrors([
                    'items.'.array_search($itemData, $request->items).'.qty_approved' => "Product {$item->product->name} (SKU: {$stock->sku}): Approved qty ({$itemData['qty_approved']}) exceeds available stock ({$availableStock})"
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
            $hasApproved = false;
            $hasPartial = false;
            $allRejected = true;

            foreach ($request->items as $itemData) {
                $item = RequestOrderItem::find($itemData['id']);

                // Skip reservation if rejected
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

                // Reserve stock for approved/partial items from SPECIFIC SKU
                if ($itemData['qty_approved'] > 0) {
                    $stock = $item->stock;

                    if (! $stock) {
                        throw new \Exception("Stock not found for product: {$item->product->name}");
                    }

                    if ($stock->qty_available < $itemData['qty_approved']) {
                        throw new \Exception("Insufficient stock for product {$item->product->name} (SKU: {$stock->sku})");
                    }

                    // Reserve from specific stock
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

            return redirect()->route('request-orders.verify', $requestOrder)
                ->with('toast_success', 'Request verified successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage());
        }
    }
}
