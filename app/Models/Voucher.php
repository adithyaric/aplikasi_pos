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
        'type',
        'jenis',
        'limit',
        'value',
        'min_purchase',
        'start_at',
        'end_at',
        'desc',
        'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
