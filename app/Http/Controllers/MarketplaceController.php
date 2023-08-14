<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Slider;
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
        return view('market.show', [
            'product' => Product::find($id),
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
}
