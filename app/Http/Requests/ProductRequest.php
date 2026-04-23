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
            'desc' => 'nullable',
            'warna' => 'nullable',
            'ukuran' => 'nullable',
            // 'brand' => 'nullable',
            'model' => 'nullable',
            'is_serialized' => 'nullable',
            'harga_beli' => 'required',
            'harga_jual' => 'nullable',
            'diskon' => 'nullable',
            'berat' => 'nullable',
            'satuan' => 'nullable|string',
            'min_stock' => 'nullable|integer|min:0',
            'lokasi' => 'nullable|string',
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages()
    {
        return [
            'code.required' => 'Kode produk wajib diisi.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'satuan.string' => 'Satuan harus berupa teks.',
            'min_stock.integer' => 'Minimal stok harus berupa angka.',
            'min_stock.min' => 'Minimal stok tidak boleh kurang dari 0.',
            'lokasi.string' => 'Lokasi harus berupa teks.',
            'supplier_ids.array' => 'Supplier harus berupa array.',
            'supplier_ids.*.exists' => 'Supplier yang dipilih tidak valid.',
        ];
    }
}
