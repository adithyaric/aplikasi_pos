<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $category = Category::where('name', $row['kategori'])->first();

        $product = Product::updateOrCreate(
            ['code' => $row['kode']],
            [
                'name'        => $row['nama'],
                'category_id' => $category?->id,
                'brand'       => $row['brand'] ?? null,
                'model'       => $row['model'] ?? null,
                'warna'       => $row['warna'] ?? null,
                'ukuran'      => $row['ukuran'] ?? null,
                'satuan'      => $row['satuan'] ?? null,
                'min_stock'   => $row['min_stock'] ?? 0,
                'lokasi'      => $row['lokasi'] ?? null,
                'harga_beli'  => $row['harga_beli'] ?? 0,
                // 'harga_jual'  => $row['harga_jual'] ?? 0,
                // 'diskon'      => $row['diskon'] ?? 0,
                // 'berat'       => $row['berat'] ?? null,
                'desc'        => $row['deskripsi'] ?? null,
            ]
        );

        // Sync suppliers by name
        if (! empty($row['supplier'])) {
            $supplierNames = array_map('trim', explode(',', $row['supplier']));
            $supplierIds = Supplier::whereIn('name', $supplierNames)->pluck('id');
            $product->suppliers()->sync($supplierIds);
        }

        return $product;
    }
}
