<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Slider;
use App\Models\Voucher;
use Darryldecode\Cart\CartCondition;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index()
    {
        return view('market.index', [
            'products' => Product::get(),
            'sliders' => Slider::where('status', 'active')->get(),
        ]);
    }

    public function show($id)
    {
        $product = Product::find($id);
        $fiveStarReviews = $product->reviews->where('rating', 5)->count();
        $fourStarReviews = $product->reviews->where('rating', 4)->count();
        $threeStarReviews = $product->reviews->where('rating', 3)->count();
        $twoStarReviews = $product->reviews->where('rating', 2)->count();
        $oneStarReviews = $product->reviews->where('rating', 1)->count();

        return view('market.show', [
            'product' => $product,
            'fiveStarReviews' => $fiveStarReviews,
            'fourStarReviews' => $fourStarReviews,
            'threeStarReviews' => $threeStarReviews,
            'twoStarReviews' => $twoStarReviews,
            'oneStarReviews' => $oneStarReviews,
        ]);
    }

    public function checkout()
    {
        return view('market.checkout', [
            'cartItems' => Cart::session(auth()->id())->getContent(),
        ]);
    }

    public function store(Request $request)
    {
        dd($request->all());
    }

    public function coupon(Request $request)
    {
        // Get the voucher from the database
        $voucher = Voucher::where('code', $request->code)->first();

        // Check if the voucher is valid
        if ($voucher) {
            // Create a new CartCondition instance
            $condition = new CartCondition([
                'name' => $voucher->name,
                'type' => $voucher->type,
                'target' => $voucher->jenis == 'keseluruhan' ? 'total' : 'subtotal',
                'value' => $voucher->type == 'percentage' ? -$voucher->value.'%' : -$voucher->value,
            ]);

            // Check if the voucher should be applied to specific items
            if ($voucher->jenis == 'satuan') {
                // Get the product ID from the voucher
                $productId = $voucher->product_id;

                // Apply the condition to specific items in the cart
                Cart::session(auth()->id())->addItemCondition($productId, $condition);
            } else {
                // Apply the condition to the cart
                Cart::session(auth()->id())->condition($condition);
            }

            // dd("success', 'Voucher applied successfully!", $condition);

            return redirect()->back()->with('success', 'Voucher applied successfully!');
        } else {
            // dd("error', 'Invalid voucher code");

            return redirect()->back()->with('error', 'Invalid voucher code');
        }
    }
}
