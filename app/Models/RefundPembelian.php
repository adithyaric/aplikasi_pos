<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundPembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'customer_id',
        'pembelian_id',
        'outlet_id',
        'user_id',
        'tanggal',
        'total',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function refundPembelianItems()
    {
        return $this->hasMany(RefundPembelianItem::class);
    }

    protected $casts = [
        'tanggal' => 'datetime',
    ];
}
