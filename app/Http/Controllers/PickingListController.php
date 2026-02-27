<?php

namespace App\Http\Controllers;

use App\Models\PickingList;
use App\Models\PickingListItem;
use App\Models\RequestOrder;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PickingListController extends Controller
{
    public function index()
    {
        $pickingLists = PickingList::with(['requestOrder.owner', 'picker'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('picking-lists.index', compact('pickingLists'));
    }

    public function show(PickingList $pickingList)
    {
        $pickingList->load(['requestOrder.owner', 'picker', 'items.product']);

        return view('picking-lists.show', compact('pickingList'));
    }

    public function pick(PickingList $pickingList)
    {
        $pickingList->load(['items.product', 'items.stock']);

        return view('picking-lists.pick', compact('pickingList'));
    }

    public function generate(RequestOrder $requestOrder)
    {
        if ($requestOrder->status !== 'approved' && $requestOrder->status !== 'partial') {
            return back()->with('toast_error', 'Can only generate picking list for approved requests');
        }

        DB::beginTransaction();
        try {
            $lastPicking = PickingList::latest('id')->first();
            $nextNumber = $lastPicking ? ((int) substr($lastPicking->code, 4) + 1) : 1;
            $code = 'PICK'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            $pickingList = PickingList::create([
                'code' => $code,
                'request_order_id' => $requestOrder->id,
                'status' => 'draft',
            ]);

            foreach ($requestOrder->items()->where('qty_approved', '>', 0)->get() as $item) {
                $remainingQty = $item->qty_approved;

                $stocks = Stock::where('product_id', $item->product_id)
                    ->where('qty_reserved', '>', 0)
                    ->orderBy('expired_at', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($stocks as $stock) {
                    if ($remainingQty <= 0) { break; }

                    $qtyToPick = min($remainingQty, $stock->qty_reserved);

                    PickingListItem::create([
                        'picking_list_id' => $pickingList->id,
                        'product_id' => $item->product_id,
                        'stock_id' => $stock->id,
                        'qty_to_pick' => $qtyToPick,
                        'location' => $stock->product->lokasi,
                        'sku' => $stock->sku,
                    ]);

                    $remainingQty -= $qtyToPick;
                }
            }

            DB::commit();

            return redirect()->route('picking-lists.show', $pickingList)
                ->with('toast_success', 'Picking list generated');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function startPicking(PickingList $pickingList)
    {
        $pickingList->update([
            'status' => 'in_progress',
            'picker_id' => auth()->id(),
            'started_at' => now(),
        ]);

        return redirect()->route('picking-lists.pick', $pickingList);
    }

    public function updateItem(Request $request, PickingListItem $item)
    {
        $request->validate([
            'qty_picked' => 'required|integer|min:0|max:'.$item->qty_to_pick,
        ]);

        $item->update([
            'qty_picked' => $request->qty_picked,
            'is_picked' => $request->qty_picked == $item->qty_to_pick,
        ]);

        // return response()->json(['success' => true]);
        return redirect()->back();
    }

    public function complete(PickingList $pickingList)
    {
        $allPicked = $pickingList->items()->where('is_picked', false)->count() === 0;

        if (! $allPicked) {
            return back()->with('toast_error', 'All items must be picked');
        }

        $pickingList->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('picking-lists.show', $pickingList)
            ->with('toast_success', 'Picking completed');
    }
}
