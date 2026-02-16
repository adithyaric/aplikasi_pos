<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\OwnerStock;
use App\Models\PickingList;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    public function index()
    {
        $deliveryOrders = DeliveryOrder::with(['requestOrder', 'owner'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('delivery-orders.index', compact('deliveryOrders'));
    }

    public function show(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load(['requestOrder', 'owner', 'preparedBy', 'receivedBy', 'items.product']);

        return view('delivery-orders.show', compact('deliveryOrder'));
    }

    public function generate(PickingList $pickingList)
    {
        if ($pickingList->status !== 'completed') {
            return back()->with('toast_error', 'Picking must be completed first');
        }

        DB::beginTransaction();
        try {
            $lastDO = DeliveryOrder::latest('id')->first();
            $nextNumber = $lastDO ? ((int) substr($lastDO->code, 2) + 1) : 1;
            $code = 'DO'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            $requestOrder = $pickingList->requestOrder;

            $deliveryOrder = DeliveryOrder::create([
                'code' => $code,
                'request_order_id' => $requestOrder->id,
                'picking_list_id' => $pickingList->id,
                'owner_id' => $requestOrder->owner_id,
                'prepared_by' => auth()->id(),
                'delivery_date' => now(),
                'status' => 'draft',
            ]);

            foreach ($pickingList->items as $pickItem) {
                if ($pickItem->qty_picked > 0) {
                    DeliveryOrderItem::create([
                        'delivery_order_id' => $deliveryOrder->id,
                        'product_id' => $pickItem->product_id,
                        'stock_id' => $pickItem->stock_id,
                        'qty' => $pickItem->qty_picked,
                        'batch_number' => $pickItem->batch_number,
                        'expired_at' => $pickItem->stock->expired_at,
                        'hpp' => $pickItem->stock->harga_beli,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('delivery-orders.show', $deliveryOrder)
                ->with('toast_success', 'Delivery order created');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function send(DeliveryOrder $deliveryOrder)
    {
        DB::beginTransaction();
        try {
            foreach ($deliveryOrder->items as $item) {
                $stock = $item->stock;

                // Allocate stock (reduces qty and qty_reserved)
                $stock->allocate($item->qty);

                // Create owner stock
                OwnerStock::updateOrCreate(
                    [
                        'owner_id' => $deliveryOrder->owner_id,
                        'product_id' => $item->product_id,
                        'batch_number' => $item->batch_number,
                    ],
                    [
                        'qty' => DB::raw('qty + '.$item->qty),
                        'expired_at' => $item->expired_at,
                        'hpp' => $item->hpp,
                    ]
                );

                // Log movement
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'reference_type' => DeliveryOrder::class,
                    'reference_id' => $deliveryOrder->id,
                    'qty_out' => $item->qty,
                    'balance' => $item->product->stocks()->sum('qty'),
                    'notes' => "Delivery to {$deliveryOrder->owner->name}",
                ]);
            }

            $deliveryOrder->update(['status' => 'sent']);

            DB::commit();

            return redirect()->route('delivery-orders.show', $deliveryOrder)
                ->with('toast_success', 'Delivery order sent');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function receive(Request $request, DeliveryOrder $deliveryOrder)
    {
        $request->validate([
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('delivery-proofs', 'public');
            $deliveryOrder->photo_path = $path;
        }

        $deliveryOrder->update([
            'status' => 'delivered',
            'received_by' => auth()->id(),
            'received_date' => now(),
        ]);

        return redirect()->route('delivery-orders.show', $deliveryOrder)
            ->with('toast_success', 'Delivery received');
    }
}
