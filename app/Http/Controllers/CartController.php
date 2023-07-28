<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            // Get the cart data
            $cart = $request->user()->cart()->get();

            // Add the available stock for each product
            foreach ($cart as $item) {
                $now = Carbon::now();
                $stockQty = $item->stocks()
                    ->where('created_at', '<=', $now)
                    ->where('expired_at', '>=', $now)
                    ->sum('qty');
                $item->availableStock = $stockQty;
            }

            // Return the cart data with the available stocks
            return response($cart);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate(['barcode' => 'required|exists:products,code']);
            $barcode = $request->barcode;
            $product = Product::where('code', $barcode)->first();
            $now = Carbon::now();
            $stockQty = $product->stocks()
                ->where('created_at', '<=', $now)
                ->where('expired_at', '>=', $now)
                ->sum('qty');
            $cart = $request->user()->cart()->where('code', $barcode)->first();
            if ($cart) {
                if ($stockQty <= $cart->pivot->qty) {
                    return response(['message' => 'Product available only: '.$stockQty], 400);
                }
                $cart->pivot->qty = $cart->pivot->qty + 1;
                $cart->pivot->save();
            } else {
                if ($stockQty < 1) {
                    return response(['message' => 'Product out of stock'], 400);
                }
                $request->user()->cart()->attach($product->id, ['qty' => 1]);
            }

            return response('success', 204);
        } catch (Exception $e) {
            error_log($e->getMessage());

            return response(['message' => 'An error occurred while processing your request.'], 500);
        }
    }

    public function changeQty(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'qty' => 'required|integer|min:1',
            ]);

            $product = Product::find($request->product_id);
            $cart = $request->user()->cart()->where('products.id', $request->product_id)->first();

            if ($cart) {
                $now = Carbon::now();
                $stockQty = $product->stocks()
                    ->where('created_at', '<=', $now)
                    ->where('expired_at', '>=', $now)
                    ->sum('qty');

                if ($stockQty < $request->qty) {
                    return response(['message' => 'Product available only: '.$stockQty], 400);
                } else {
                    $cart->pivot->qty = $request->qty;
                    $cart->pivot->save();
                }
            }

            return response(['success' => true]);
        } catch (Exception $e) {
            error_log($e->getMessage());

            return response(['message' => 'An error occurred while processing your request.'], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);
        $request->user()->cart()->detach($request->product_id);

        return response('success', 204);
    }

    public function empty(Request $request)
    {
        $request->user()->cart()->detach();

        return response('success', 204);
    }
}
