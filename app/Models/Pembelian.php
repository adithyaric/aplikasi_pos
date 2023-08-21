<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'outlet_id',
        'supplier_id',
        'kas_id',
        'total',
        'is_published',
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

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function pembelianProducts()
    {
        return $this->hasMany(PembelianProduct::class);
    }
}
