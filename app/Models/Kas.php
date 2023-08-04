<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'outlet_id',
        'nominal',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }
}
