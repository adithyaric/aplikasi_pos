<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $now = Carbon::now();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'barcode' => $this->code,
            'desc' => $this->desc,
            'image' => $this->pic,
            'code' => $this->barcode,
            'harga_jual' => $this->harga_jual,
            'stocks' => $this->stocks()
                // ->where('created_at', '<=', $now)
                // ->where('expired_at', '>=', $now)
                ->sum('qty'),
            'image_url' => asset($this->pic),
        ];
    }
}
