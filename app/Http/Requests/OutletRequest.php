<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutletRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'logo' => 'nullable',
            'name' => 'required',
            'alamat' => 'required',
            'npwp' => 'required',
            'slogan' => 'required',
            'desc' => 'required',
            'footer' => 'required',
        ];
    }
}
