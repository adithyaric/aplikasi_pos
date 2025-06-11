<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

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
        return $query->where('qty', '>', 0);
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expired_at' => 'datetime',
    ];
}
