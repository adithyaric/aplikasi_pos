<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Exception;
use Illuminate\Http\Request;

class CartUserController extends Controller
{
    public function index()
    {
        return view('market.cart.index', [
            'cartItems' => Cart::session(auth()->id())->getContent(),
        ]);
    }

    public function addToCart(Request $request)
    {
        try {
            $product = Product::find($request->id);
            Cart::session(auth()->id())->add([
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->harga_jual,
                'quantity' => $request->quantity,
                'attributes' => [],
                'associatedModel' => $product,
            ]);

            return redirect()->route('marketcart.index')->with('toast_success', 'Product is Added to Cart Successfully !');
        } catch (Exception $e) {
            return redirect()->back()->with('toast_error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function updateCart(Request $request)
    {
        Cart::session(auth()->id())->update(
            $request->id,
            [
                'quantity' => [
                    'relative' => false,
                    'value' => $request->quantity,
                ],
            ]
        );

        return redirect()->route('marketcart.index')->with('toast_success', 'Item Cart is Updated Successfully !');
    }

    public function removeCart(Request $request)
    {
        Cart::session(auth()->id())->remove($request->id);

        return redirect()->route('marketcart.index')->with('toast_success', 'Item Cart Remove Successfully !');
    }

    public function clearAllCart()
    {
        Cart::session(auth()->id())->clear();

        return redirect()->route('marketcart.index')->with('toast_success', 'All Item Cart Clear Successfully !');
    }
}
