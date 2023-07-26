<?php

namespace App\Http\Controllers;

use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
        return view('stocks.index', [
            'stocks' => Stock::get()->sortBy('created_at'),
        ]);
    }
}
