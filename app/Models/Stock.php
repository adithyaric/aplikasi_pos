<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'subtotal',
        'harga_beli',
        'qty',
        'created_at',
        'expired_at',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected $casts = [
        'created_at' => 'datetime',
        'expired_at' => 'datetime',
    ];
}
