<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'penjualan_id',
        'payment_method',
        'tanggal',
        'status',
        'pic',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }
}
