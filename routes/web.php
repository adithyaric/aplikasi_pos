<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\RefundPembelianController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['role:kasir|superadmin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('/customer', CustomerController::class);
    Route::get('/get-customer/{penjualan_id}', [CustomerController::class, 'getCustomer']);
    Route::resource('/kas', KasController::class);
    Route::resource('/outlet', OutletController::class);
    Route::resource('/supplier', SupplierController::class);
    Route::resource('/category', CategoryController::class);
    Route::get('/category-product', [CategoryController::class, 'indexProduct'])->name('category.product.index');
    Route::get('/category-product-create', [CategoryController::class, 'createProduct'])->name('category.product.create');
    Route::get('/category-product/{category}/edit', [CategoryController::class, 'editProduct'])->name('category.product.edit');

    Route::get('/category-pengeluaran', [CategoryController::class, 'indexPengeluaran'])->name('category.pengeluaran.index');
    Route::get('/category-pengeluaran-create', [CategoryController::class, 'createPengeluaran'])->name('category.pengeluaran.create');
    Route::get('/category-pengeluaran/{category}/edit', [CategoryController::class, 'editPengeluaran'])->name('category.pengeluaran.edit');
    Route::resource('/product', ProductController::class);
    Route::resource('/stock', StockController::class);
    Route::resource('/voucher', VoucherController::class);
    Route::resource('/slider', SliderController::class);

    Route::resource('/pengeluaran', PengeluaranController::class);
    Route::resource('/pembelian', PembelianController::class);
    Route::resource('/refund', RefundController::class);
    Route::resource('/refundPembelian', RefundPembelianController::class);
    Route::resource('/penjualan', PenjualanController::class);

    Route::resource('/cart', CartController::class);
    Route::post('/cart-change-qty', [CartController::class, 'changeQty']);
    Route::delete('/cart-empty', [CartController::class, 'empty']);
    Route::get('/wishlist', [CartController::class, 'getWishlist']);
    Route::post('/wishlist', [CartController::class, 'addToWishlist']);
    Route::post('/wishlist/move-to-cart', [CartController::class, 'moveToCart']);

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pembelian', [LaporanController::class, 'exportPembelian'])->name('laporan.pembelian');
    Route::get('/laporan/penjualan', [LaporanController::class, 'exportPenjualan'])->name('laporan.penjualan');
    Route::get('/laporan/penjualan-kasir', [LaporanController::class, 'exportPenjualanKasir'])->name('laporan.penjualan-kasir');
    Route::get('/laporan/stock', [LaporanController::class, 'exportStock'])->name('laporan.stock');
    Route::get('/laporan/pengeluaran', [LaporanController::class, 'exportPengeluaran'])->name('laporan.pengeluaran');
    Route::get('/laporan/labarugi', [LaporanController::class, 'exportLabaRugi'])->name('laporan.labarugi');
});

Route::middleware(['role:superadmin'])->group(function () {
    Route::resource('/admin', AdminController::class);
});

Route::middleware(['role:customer'])->group(function () {
    Route::resource('/market', MarketplaceController::class);
    Route::controller(CartUserController::class)->group(function () {
        Route::get('marketcart', 'index')->name('marketcart.index');
        Route::post('marketcart', 'addToCart')->name('marketcart.store');
        Route::post('market-update-cart', 'updateCart')->name('marketcart.update');
        Route::post('market-remove', 'removeCart')->name('marketcart.remove');
        Route::post('market-clear', 'clearAllCart')->name('marketcart.clear');
    });
}
);

require __DIR__.'/auth.php';

//* Artisan Commands
Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');

    return redirect('/login')->with(['success' => 'Optimization Berhasil']);
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');

    return redirect('/login')->with(['success' => 'Optimization Berhasil']);
});
