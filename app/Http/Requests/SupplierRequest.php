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
            'kode_supplier'            => 'required|string',
            'name'                     => 'required|string',
            'alamat'                   => 'required|string',
            'no_telp'                  => 'required|string',
            'deadline_days'            => 'nullable|array',
            'deadline_days.*'          => 'integer|between:1,7',
            'deadline_interval_weeks'  => 'nullable|integer|in:1,2,3',
            'deadline_reference_date'  => 'nullable|date',
        ];
    }
}
