<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('/admin', AdminController::class);
    Route::resource('/customer', CustomerController::class);
    Route::resource('/bank', BankController::class);
    Route::resource('/outlet', OutletController::class);
    Route::resource('/supplier', SupplierController::class);
    Route::resource('/category', CategoryController::class);
    // Route::resource('/product', ProductController::class);
    Route::resource('/stock', StockController::class);
    Route::resource('/voucher', VoucherController::class);

    Route::resource('/pengeluaran', PengeluaranController::class);
    Route::resource('/pembelian', PembelianController::class);
    Route::resource('/penjualan', PenjualanController::class);
});

require __DIR__.'/auth.php';
Route::resource('/product', ProductController::class);
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
Route::delete('/cart/delete', [CartController::class, 'delete']);
Route::delete('/cart/empty', [CartController::class, 'empty']);

//* Artisan Commands
Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');

    return redirect('/login')->with(['success' => 'Optimization Berhasil']);
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');

    return redirect('/login')->with(['success' => 'Optimization Berhasil']);
});
