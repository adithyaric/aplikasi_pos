<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuppliersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private bool $templateOnly = false) {}

    public function collection()
    {
        return $this->templateOnly ? collect([]) : Supplier::all();
    }

    public function headings(): array
    {
        return ['kode_supplier', 'nama', 'pic', 'alamat', 'no_telp'];
    }

    public function map($row): array
    {
        return [
            $row->kode_supplier,
            $row->name,
            $row->pic_supplier,
            $row->alamat,
            $row->no_telp,
        ];
    }
}
