<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('/admin', AdminController::class);
    Route::resource('/customer', CustomerController::class);
    Route::get('/get-customer/{penjualan_id}', [CustomerController::class, 'getCustomer']);
    Route::resource('/bank', BankController::class);
    Route::resource('/outlet', OutletController::class);
    Route::resource('/supplier', SupplierController::class);
    Route::resource('/category', CategoryController::class);
    Route::resource('/product', ProductController::class);
    Route::resource('/stock', StockController::class);
    Route::resource('/voucher', VoucherController::class);
    Route::resource('/slider', SliderController::class);

    Route::resource('/pengeluaran', PengeluaranController::class);
    Route::resource('/pembelian', PembelianController::class);
    Route::resource('/refund', RefundController::class);
    Route::resource('/penjualan', PenjualanController::class);

    Route::resource('/cart', CartController::class);
    Route::post('/cart-change-qty', [CartController::class, 'changeQty']);
    Route::delete('/cart-empty', [CartController::class, 'empty']);
    Route::get('/wishlist', [CartController::class, 'getWishlist']);
    Route::post('/wishlist', [CartController::class, 'addToWishlist']);
    Route::post('/wishlist/move-to-cart', [CartController::class, 'moveToCart']);

    Route::get('/laporan/pembelian', [LaporanController::class, 'exportPembelian'])->name('laporan.pembelian');
    Route::get('/laporan/penjualan', [LaporanController::class, 'exportPenjualan'])->name('laporan.penjualan');
    Route::get('/laporan/penjualan-kasir', [LaporanController::class, 'exportPenjualanKasir'])->name('laporan.penjualan-kasir');
    Route::get('/laporan/stock', [LaporanController::class, 'exportStock'])->name('laporan.stock');
    Route::get('/laporan/pengeluaran', [LaporanController::class, 'exportPengeluaran'])->name('laporan.pengeluaran');
    Route::get('/laporan/labarugi', [LaporanController::class, 'exportLabaRugi'])->name('laporan.labarugi');
});

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
