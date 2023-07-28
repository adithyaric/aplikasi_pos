<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'customer_id',
        'kasir_id',
        'discount',
        'total',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function items()
    {
        return $this->hasMany(PenjualanItem::class);
    }
}
