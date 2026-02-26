<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'pic', //picture
        'code',
        'name',
        'category_id',
        'desc',
        'warna',
        'ukuran',
        // 'outlet_id', //unsued, currently just for experiment
        // 'supplier_id', //unsued, currently just for experiment
        'brand', // Add for Lenovo, Samsung, etc.
        'satuan',
        'min_stock',
        'lokasi',
        'model', // Add for specific model info
        'is_serialized', // Boolean: true for unique items, false for bulk => now always false
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

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier');
    }

    // public function supplier()
    // {
    //     return $this->belongsTo(Supplier::class);
    // }

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

    public function calculateHPP($newQty, $newPrice)
    {
        if ($this->hpp_method === 'average') {
            $currentValue = $this->total_stock * $this->hpp;
            $newValue = $newQty * $newPrice;
            $totalQty = $this->total_stock + $newQty;

            return $totalQty > 0 ? ($currentValue + $newValue) / $totalQty : 0;
        }
        // FIFO handled differently in stock allocation
        return $this->hpp;
    }

    public function updateStockValue()
    {
        $this->stock_value = $this->total_stock * $this->hpp;
        $this->save();
    }

    public function getTotalAvailableStockAttribute()
    {
        return $this->stocks()->sum('qty_available');
    }

    public function getTotalReservedStockAttribute()
    {
        return $this->stocks()->sum('qty_reserved');
    }

    public function ownerStocks()
    {
        return $this->hasMany(OwnerStock::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock()
    {
        return $this->total_available_stock <= $this->min_stock;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept(['created_at', 'updated_at'])
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->setDescriptionForEvent(fn (string $eventName) => "Data Product has been {$eventName}")
            ->useLogName('Product');
    }
}
