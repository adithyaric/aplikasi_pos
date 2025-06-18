<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'pic' => 'nullable',
            'code' => 'required',
            'name' => 'required',
            'category_id' => 'required',
            // 'expired' => 'nullable',
            'desc' => 'required',
            'warna' => 'nullable',
            'ukuran' => 'nullable',
            'brand' => 'nullable',
            'model' => 'nullable',
            'is_serialized' => 'nullable',
            'outlet_id' => 'required',
            // 'supplier_id' => 'required',
            'harga_beli' => 'required',
            'harga_jual' => 'required',
            'diskon' => 'nullable',
            'berat' => 'nullable',
        ];
    }
}
