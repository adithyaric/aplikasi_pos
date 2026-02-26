<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'kode_supplier' => 'nullable|string',
            'pic_supplier' => 'nullable|string',
            'name' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
        ];
    }
}
