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
            // 'outlet_id' => 'required',
            'supplier_id' => 'required',
            // 'kas_id' => 'required',
            // 'qty' => 'required',
            // 'expired' => 'required',
            // 'harga_beli' => 'required',
            'subtotal' => 'nullable',
            'total' => 'nullable',
            'product' => 'required|array',
            'product.*.product_id' => 'required|exists:products,id',
            'product.*.qty' => 'required|numeric|min:1',
            'product.*.harga_beli' => 'required|min:0',
            'product.*.subtotal' => 'required|min:0',
            'product.*.serial_numbers' => 'nullable|string',
        ];
    }
}
