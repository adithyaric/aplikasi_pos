<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMinimumAdjustment;
use Illuminate\Http\Request;

class ProductMinimumAdjustmentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_ids'           => 'required|array|min:1',
            'product_ids.*'         => 'integer|exists:products,id',
            'adjustment_percentage' => 'required|integer|min:1|max:255',
            'active_from'           => 'required|date',
            'active_until'          => 'nullable|date|after_or_equal:active_from',
        ]);

        $saved = 0;
        foreach ($data['product_ids'] as $productId) {
            ProductMinimumAdjustment::create([
                'product_id'            => $productId,
                'adjustment_percentage' => $data['adjustment_percentage'],
                'active_from'           => $data['active_from'],
                'active_until'          => $data['active_until'] ?? null,
                'created_by'            => auth()->id(),
            ]);
            $saved++;
        }

        return response()->json([
            'success' => true,
            'message' => "Adjustment disimpan untuk {$saved} produk.",
        ]);
    }
}
