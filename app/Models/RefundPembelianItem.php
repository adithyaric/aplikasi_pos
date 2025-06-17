<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefundPembelianItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'refund_pembelian_id',
        'product_id',
        'qty',
        'alasan',
    ];

    public function refundPembelian()
    {
        return $this->belongsTo(RefundPembelian::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
