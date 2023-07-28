<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'barcode' => $this->code,
            'desc' => $this->desc,
            'image' => $this->pic,
            'code' => $this->barcode,
            'harga_jual' => $this->harga_jual,
            'stocks' => $this->stocks->sum('qty'),
            'image_url' => asset($this->pic),
        ];
    }
}
