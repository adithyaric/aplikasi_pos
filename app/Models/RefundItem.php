<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'penjualan_id',
        'product_id',
        'satuan',
        'qty',
        'price',
        'discount',
        'subtotal',
        'alasan',
    ];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
