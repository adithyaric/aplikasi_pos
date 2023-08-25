<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo',
        'name',
        'alamat',
        'npwp',
        'slogan',
        'desc',
        'footer',
    ];

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class);
    }
}
