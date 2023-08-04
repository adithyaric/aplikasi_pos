<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'code' => 'required',
            'type' => 'required',
            'jenis' => 'required',
            'limit' => 'required',
            'value' => 'required',
            'min_purchase' => 'required',
            'start_at' => 'nullable',
            'end_at' => 'nullable',
            'desc' => 'required',
        ];
    }
}
