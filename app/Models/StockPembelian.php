<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockPembelian extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pembelian_id',
        'product_id',
        'subtotal',
        'harga_beli',
        'qty',
        'expired_at',
        'serial_number', // For individual items like laptops
        'imei', // For phones
        'condition', // new, used, refurbished
        'status', // available, sent_to_outlet, reserved
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('qty', '>', 0)->where('status', 'available');
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
