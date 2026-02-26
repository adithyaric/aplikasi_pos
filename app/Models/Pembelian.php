<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembelian extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'outlet_id',
        'supplier_id',
        'kas_id',
        'total',
        'is_published',
        'receipt_date',
        'receipt_pic',
        'receipt_status',
        'receipt_photo',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function kas()
    {
        return $this->belongsTo(Kas::class);
    }

    // Market stocks (after published)
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    // Warehouse stocks (before published)
    public function stockPembelians()
    {
        return $this->hasMany(StockPembelian::class);
    }

    public function pembelianProducts()
    {
        return $this->hasMany(PembelianProduct::class);
    }

    public function pembelianTransaction()
    {
        return $this->hasOne(PembelianTransaction::class);
    }
}
