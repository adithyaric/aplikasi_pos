<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'penjualan_id',
        'product_id',
        'qty',
        'price',
        'subtotal',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
