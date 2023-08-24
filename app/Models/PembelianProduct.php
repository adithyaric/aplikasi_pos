<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianProduct extends Model
{
    protected $fillable = ['pembelian_id', 'product_id', 'harga_beli', 'qty', 'subtotal', 'expired_at'];

    protected $casts = [
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
}
