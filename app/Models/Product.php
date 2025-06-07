<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'pic',
        'code',
        'name',
        'category_id',
        'desc',
        'warna',
        'ukuran',
        'outlet_id', //unsued, currently just for experiment
        'supplier_id', //unsued, currently just for experiment
        'harga_beli',
        'harga_jual',
        'diskon',
        'berat',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // public function outlet()
    // {
    //     return $this->belongsTo(Outlet::class);
    // }

    // public function supplier()
    // {
    //     return $this->belongsTo(Supplier::class);
    // }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlist()
    {
        return $this->belongsToMany(User::class, 'user_wishlist')->withPivot('qty', 'name', 'customer_id');
    }

    public function penjualanItems()
    {
        return $this->hasMany(PenjualanItem::class);
    }
}
