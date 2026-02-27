<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'kode_supplier',
        'pic_supplier',
        'alamat',
        'no_telp',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_supplier');
    }
}
