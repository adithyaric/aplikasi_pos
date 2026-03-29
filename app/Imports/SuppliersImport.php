<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SuppliersImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        return Supplier::updateOrCreate(
            ['kode_supplier' => $row['kode_supplier']],
            [
                'name'         => $row['nama'],
                'pic_supplier' => $row['pic'] ?? null,
                'alamat'       => $row['alamat'] ?? null,
                'no_telp'      => $row['no_telp'] ?? null,
            ]
        );
    }
}
