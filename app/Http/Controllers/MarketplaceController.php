<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanItem;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Voucher;
use Darryldecode\Cart\CartCondition;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        try {
            // Get the data from the request
            $name = $request->name;
            $address = $request->address;
            $email = $request->email;
            $phone = $request->phone;
            $orderNotes = $request->order_notes;
            $paymentMethod = $request->payment_method;

            // Get the cart data
            $cartItems = Cart::session(auth()->id())->getContent();
            $cartSubtotal = Cart::session(auth()->id())->getSubTotal();
            $cartTotal = Cart::session(auth()->id())->getTotal();

            // Calculate the discount
            $discount = $cartSubtotal - $cartTotal;

            // Generate the next invoice code
            $lastOrder = Penjualan::orderBy('created_at', 'desc')->first();
            $nextInvoiceNumber = $lastOrder ? ((int) substr($lastOrder->code, 3) + 1) : 1;
            $nextInvoiceNumber = str_pad($nextInvoiceNumber, 3, '0', STR_PAD_LEFT);
            $nextInvoiceCode = 'INV'.$nextInvoiceNumber;

            // Create a new Penjualan instance
            $penjualan = new Penjualan([
                'code' => $nextInvoiceCode,
                'customer_id' => auth()->user()->id,
                'outlet_id' => null,
                'kasir_id' => null,
                'kas_id' => null,
                'discount' => $discount,
                'total' => $cartTotal,
            ]);

            // Save the Penjualan instance
            $penjualan->save();

            // Create a new PenjualanItem instance for each item in the cart
            foreach ($cartItems as $item) {
                $penjualanItem = new PenjualanItem([
                    'penjualan_id' => $penjualan->id,
                    'product_id' => $item->id,
                    'qty' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->getPriceSum(),
                ]);

                // Save the PenjualanItem instance
                $penjualanItem->save();
            }

            // Clear the cart
            Cart::session(auth()->id())->clear();
            Cart::session(auth()->id())->clearCartConditions();
            Cart::session(auth()->id())->removeConditionsByType('subtotal');
            // Cart::session(auth()->id())->getConditions()

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }
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
