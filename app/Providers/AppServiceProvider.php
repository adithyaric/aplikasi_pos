<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Blade::directive('currency', function ($expression) {
            return "Rp. <?php echo number_format($expression,0,',','.'); ?>";
        });

        View::composer('layouts.market.header', function ($view) {
            $view->with('categories', \App\Models\Category::where('type', 'product')->get());
        });

        View::composer('layouts.master', function ($view) {
            if (Auth::check()) {
                $lowStockProducts = Product::with('stocks')
                    ->get()
                    ->filter(function ($product) {
                        return $product->stocks()->sum('qty_available') < $product->min_stock;
                    });
                $view->with('lowStockProducts', $lowStockProducts);
            }
        });
    }
}
