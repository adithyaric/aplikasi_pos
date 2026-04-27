<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockOpnameExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings, WithCustomStartCell, WithProperties
{
    use Exportable;

    protected $adjustments;
    protected $date;
    protected $settings;

    public function __construct($adjustments, $date, array $settings = [])
    {
        $this->adjustments = $adjustments;
        $this->date        = $date;
        $this->settings    = $settings;
    }

    public function collection()
    {
        return $this->adjustments;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Batch',
            'Expired',
            'Satuan',
            'Konversi',
            'Stok Sistem',
            'Stok Fisik',
            'Selisih',
            'QTY Adjust',
            'Alasan',
            'Status',
            'Keterangan',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        $systemQty   = $item->system_qty ?? 0;
        $physicalQty = $item->physical_qty ?? 0;
        $selisih     = $physicalQty - $systemQty;
        $konvQty     = $item->product->konversi_qty;
        $satuanBesar = $item->product->satuan_besar;
        $konvDisplay = ($konvQty > 0 && $satuanBesar) ? round($systemQty / $konvQty).' '.$satuanBesar : '-';

        return [
            $no,
            $item->product->code ?? '-',
            $item->product->name ?? '-',
            $item->sku ?? '-',
            $item->stock?->expired_at
                ? Carbon::parse($item->stock->expired_at)->format('d/m/Y')
                : '-',
            $item->product->satuan ?? 'PCS',
            $konvDisplay,
            $systemQty,
            $physicalQty,
            ($selisih >= 0 ? '+' : '').$selisih,
            ($item->quantity >= 0 ? '+' : '').$item->quantity,
            $item->reason ?? '-',
            $item->status ?? 'Selesai',
            $item->keterangan ?? '-',
        ];
    }

    public function startCell(): string
    {
        return 'B16';
    }

    public function styles(Worksheet $sheet)
    {
        $companyName = $this->settings['name'] ?? 'NAMA PERUSAHAAN';
        $address     = $this->settings['address'] ?? 'ALAMAT';
        $phone       = $this->settings['telp'] ?? '';
        $email       = $this->settings['email'] ?? '';
        $website     = $this->settings['website'] ?? '';
        $contactInfo = trim("$phone | $email | $website", ' |');

        $sheet->getRowDimension(1)->setRowHeight(50);

        $sheet->setCellValue('D2', $companyName);
        $sheet->mergeCells('D2:P2');
        $sheet->getStyle('D2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->setCellValue('D3', $address);
        $sheet->mergeCells('D3:P3');
        $sheet->setCellValue('D4', $contactInfo);
        $sheet->mergeCells('D4:P4');
        $sheet->getStyle('D3:D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(5)->setRowHeight(20);

        $sheet->mergeCells('B6:P6');
        $sheet->getStyle('B6:P6')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

        $sheet->setCellValue('B8', 'LAPORAN STOK OPNAME & ADJUSTMENT');
        $sheet->mergeCells('B8:P8');
        $sheet->getStyle('B8')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $docNo = 'SO/'.Carbon::parse($this->date)->format('Y/m').'/'.str_pad($this->adjustments->count(), 5, '0', STR_PAD_LEFT);
        $sheet->setCellValue('B10', 'No Dokumen :');
        $sheet->setCellValue('D10', $docNo);
        $sheet->getStyle('B10')->getFont()->setBold(true);

        $sheet->setCellValue('B11', 'Tanggal :');
        $sheet->setCellValue('D11', Carbon::parse($this->date)->isoFormat('DD MMMM YYYY'));
        $sheet->getStyle('B11')->getFont()->setBold(true);

        $sheet->setCellValue('B12', 'Nama :');
        $sheet->setCellValue('D12', ' ');
        $sheet->getStyle('B12')->getFont()->setBold(true);

        $sheet->setCellValue('B13', 'Referensi :');
        $sheet->setCellValue('D13', 'Stok Opname / Koreksi Stok');
        $sheet->getStyle('B13')->getFont()->setBold(true);

        $sheet->setCellValue('B15', 'Detail Penyesuaian Stok');
        $sheet->getStyle('B15')->getFont()->setBold(true);

        // TABLE HEADER — cols B:O = 14 columns
        $sheet->getStyle('B16:O16')->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8EAADB']],
        ]);
        $sheet->getRowDimension(16)->setRowHeight(28);

        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 16) {
            $sheet->getStyle('B17:O'.$highestRow)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(7);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(9);
        $sheet->getColumnDimension('L')->setWidth(9);
        $sheet->getColumnDimension('M')->setWidth(16);
        $sheet->getColumnDimension('N')->setWidth(10);
        $sheet->getColumnDimension('O')->setWidth(16);

        foreach (['G', 'I', 'J', 'K', 'L', 'N'] as $col) {
            $sheet->getStyle($col)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // SUMMARY
        $totalMasuk  = $this->adjustments->where('quantity', '>', 0)->sum('quantity');
        $totalKeluar = $this->adjustments->where('quantity', '<', 0)->sum(fn ($a) => abs($a->quantity));

        $summaryRow = $highestRow + 2;
        $sheet->setCellValue('B'.$summaryRow, 'Total Penyesuaian Masuk :');
        $sheet->mergeCells('B'.$summaryRow.':E'.$summaryRow);
        $sheet->setCellValue('F'.$summaryRow, $totalMasuk);
        $sheet->getStyle('B'.$summaryRow)->getFont()->setBold(true);

        $sheet->setCellValue('B'.($summaryRow + 1), 'Total Penyesuaian Keluar :');
        $sheet->mergeCells('B'.($summaryRow + 1).':E'.($summaryRow + 1));
        $sheet->setCellValue('F'.($summaryRow + 1), $totalKeluar);
        $sheet->getStyle('B'.($summaryRow + 1))->getFont()->setBold(true);

        $sheet->getStyle('B'.$summaryRow.':O'.($summaryRow + 1))
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        // SIGNATURE
        $row = $summaryRow + 4;
        $sheet->mergeCells('B'.$row.':D'.$row);
        $sheet->mergeCells('F'.$row.':J'.$row);
        $sheet->mergeCells('L'.$row.':O'.$row);
        $sheet->setCellValue('B'.$row, 'Dibuat Oleh');
        $sheet->setCellValue('F'.$row, 'Diperiksa');
        $sheet->setCellValue('L'.$row, 'Disetujui');
        $sheet->getStyle('B'.$row.':O'.$row)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row++;
        $sheet->mergeCells('B'.$row.':D'.$row);
        $sheet->mergeCells('F'.$row.':J'.$row);
        $sheet->mergeCells('L'.$row.':O'.$row);
        $sheet->setCellValue('B'.$row, 'Staff Gudang');
        $sheet->setCellValue('F'.$row, 'Supervisor Gudang');
        $sheet->setCellValue('L'.$row, 'Manager');
        $sheet->getStyle('B'.$row.':O'.$row)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row += 5;
        $sheet->mergeCells('B'.$row.':D'.$row);
        $sheet->mergeCells('F'.$row.':J'.$row);
        $sheet->mergeCells('L'.$row.':O'.$row);
        $sheet->setCellValue('B'.$row, 'Nama');
        $sheet->setCellValue('F'.$row, 'Nama');
        $sheet->setCellValue('L'.$row, 'Nama');
        $sheet->getStyle('B'.$row.':O'.$row)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('B'.($row - 1).':O'.($row - 1))
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');

        $logoPath = $this->settings['logo'] ?? null;
        if ($logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)) {
            $drawing->setPath(\Illuminate\Support\Facades\Storage::disk('public')->path($logoPath));
        } else {
            $drawing->setPath(public_path('img/logo.jpeg'));
        }

        $drawing->setHeight(80);
        $drawing->setCoordinates('B2');

        return [$drawing];
    }

    public function properties(): array
    {
        return [
            'creator'     => config('app.name'),
            'title'       => 'Laporan Stok Opname & Adjustment',
            'description' => 'Stock Opname '.$this->date,
        ];
    }
}
