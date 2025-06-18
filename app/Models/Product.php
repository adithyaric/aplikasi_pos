<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

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
        'brand', // Add for Lenovo, Samsung, etc.
        'model', // Add for specific model info
        'is_serialized', // Boolean: true for unique items, false for bulk
        'harga_beli',
        'harga_jual',
        'diskon',
        'berat',
    ];

    protected $casts = [
        'is_serialized' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

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

    // Get total available stock
    public function getTotalStockAttribute()
    {
        return $this->stocks()->sum('qty');
    }
}
