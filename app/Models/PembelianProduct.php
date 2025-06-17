<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembelianProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pembelian_id',
        'product_id',
        'harga_beli',
        'qty',
        'subtotal',
        'expired_at',
        'serial_numbers' // JSON array for serialized items
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'serial_numbers' => 'array',
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
