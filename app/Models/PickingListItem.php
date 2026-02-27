<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickingListItem extends Model
{
    protected $fillable = [
        'picking_list_id',
        'product_id',
        'stock_id',
        'qty_to_pick',
        'qty_picked',
        'location',
        'sku',
        'is_picked',
    ];

    protected $casts = [
        'is_picked' => 'boolean',
    ];

    public function pickingList()
    {
        return $this->belongsTo(PickingList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
