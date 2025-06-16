<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type', //nominal, percentage
        'jenis', //satuan, keseluruhan
        'limit', //usage limit
        'value',
        'min_purchase',
        'start_at',
        'end_at',
        'desc',
        'product_id',
        'kasir_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id'); //user role kasir
    }
}
