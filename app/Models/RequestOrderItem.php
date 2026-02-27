<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_order_id',
        'product_id',
        'stock_id',
        'qty_requested',
        'qty_approved',
        'item_status',
        'notes',
    ];

    public function requestOrder()
    {
        return $this->belongsTo(RequestOrder::class);
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
