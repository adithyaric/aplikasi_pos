<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'username' => 'required',
            'outlet_id' => 'required_if:role,kasir|required_if:role,admin',
            'role' => 'required',
            'status' => 'required',
            'email' => 'required|email',
            'password' => 'required|same:confirm-password',
        ];
    }
}
