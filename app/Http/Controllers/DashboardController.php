<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Stock;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'users' => User::count(),
            'products' => Product::count(),
            'stocks' => Stock::count(),
            'penjualans' => Penjualan::count(),
            // 'sliders' => Slider::where('status', 'active')->get(),
        ]);
    }
}
