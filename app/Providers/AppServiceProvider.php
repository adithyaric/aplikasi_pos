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
                $today = now()->toDateString();
                $activeAdjs = \App\Models\ProductMinimumAdjustment::activeOn($today)
                    ->orderByDesc('active_from')
                    ->orderByDesc('id')
                    ->get()
                    ->keyBy('product_id');

                $lowStockProducts = Product::withSum('stocks', 'qty_available')
                    ->get()
                    ->filter(function ($product) use ($activeAdjs) {
                        $current = (int) ($product->stocks_sum_qty_available ?? 0);
                        $adj = $activeAdjs->get($product->id);
                        $effectiveMin = $adj
                            ? (int) ceil($product->min_stock * (1 + $adj->adjustment_percentage / 100))
                            : (int) $product->min_stock;
                        return $current <= $effectiveMin;
                    });
                $view->with('lowStockProducts', $lowStockProducts);
            }
        });
    }
}
