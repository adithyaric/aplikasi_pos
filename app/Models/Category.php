<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'outlet_id', //unsued, currently just for experiment
    ];

    // public function outlet()
    // {
    //     return $this->belongsTo(Outlet::class);
    // }
}
