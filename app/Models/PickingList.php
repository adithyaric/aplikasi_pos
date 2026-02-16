<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickingList extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'request_order_id',
        'picker_id',
        'status',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function requestOrder()
    {
        return $this->belongsTo(RequestOrder::class);
    }

    public function picker()
    {
        return $this->belongsTo(User::class, 'picker_id');
    }

    public function items()
    {
        return $this->hasMany(PickingListItem::class);
    }
}
