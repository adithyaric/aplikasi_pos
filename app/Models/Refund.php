<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'kas_id',
        'customer_id',
        'penjualan_id',
        'outlet_id',
        'user_id',
        'tanggal',
        'total',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function kas()
    {
        return $this->belongsTo(Kas::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function refundItems()
    {
        return $this->hasMany(RefundItem::class);
    }

    protected $casts = [
        'tanggal' => 'datetime',
    ];
}
