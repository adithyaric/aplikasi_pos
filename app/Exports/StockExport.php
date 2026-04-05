<?php

namespace App\Exports;

use App\Models\Stock;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StockExport implements FromCollection, WithHeadings, WithTitle
{
    use Exportable;

    public function title(): string
    {
        return 'Laporan Stok Barang';
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Batch',
            'Expired Date',
            'Kategori',
            'Satuan',
            'Stok',
            'Min Stok',
            'Selisih',
            'Status Stok',
            'Status Expired',
            'Lokasi',
        ];
    }

    public function collection()
    {
        $stocks = Stock::with(['product.category', 'pembelian'])
            ->orderBy('product_id')
            ->get();

        $rows = collect();
        $no = 1;

        foreach ($stocks as $s) {
            $minStok = $s->product?->min_stock ?? 0;
            $selisih = ($s->qty ?? 0) - $minStok;
            $statusStok = ($s->qty ?? 0) > $minStok ? 'Aman' : (($s->qty ?? 0) > 0 ? 'Kritis' : 'Habis');
            $statusExp = $s->expired_at && Carbon::parse($s->expired_at)->isPast() ? 'Expired' : 'Belum Expired';

            $rows->push([
                $no++,
                $s->product?->code ?? '-',
                $s->product?->name ?? '-',
                $s->sku ?? '-',
                $s->expired_at ? Carbon::parse($s->expired_at)->format('d/m/Y') : '-',
                $s->product?->category?->name ?? '-',
                $s->product?->satuan ?? 'PCS',
                $s->qty ?? 0,
                $minStok,
                ($selisih >= 0 ? '+' : '').$selisih,
                $statusStok,
                $s->expired_at ? $statusExp : '-',
                $s->location ?? '-',
            ]);
        }

        return $rows;
    }
}
