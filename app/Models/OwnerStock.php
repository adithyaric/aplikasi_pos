<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OwnerStock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'product_id',
        'qty',
        'batch_number',
        'expired_at',
        'hpp',
    ];

    protected $casts = [
        'expired_at' => 'date',
    ];

    public function owner()
    {
        return $this->belongsTo(Outlet::class, 'owner_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
