<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PembelianRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required',
            'outlet_id' => 'required',
            'supplier_id' => 'required',
            // 'product_id' => 'required',
            // 'qty' => 'required',
            // 'expired' => 'required',
            // 'harga_beli' => 'required',
            'subtotal' => 'nullable',
            'total' => 'nullable',
        ];
    }
}
