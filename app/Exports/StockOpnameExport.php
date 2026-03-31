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
        $this->date = $date;
        $this->settings = $settings;
    }

    public function collection()
    {
        return $this->adjustments;
    }

    public function headings(): array
    {
        return ['No', 'Kode Barang', 'Nama Barang', 'Satuan', 'Qty PO', 'Qty Diterima', 'Harga', 'Total'];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;
        $qty = abs($item->quantity);
        $harga = $item->stock->harga_beli ?? 0;

        return [
            $no,
            $item->product->code ?? '-',
            $item->product->name ?? '-',
            $item->product->satuan ?? 'PCS',
            $qty,
            $qty,
            'Rp '.number_format($harga, 0, ',', '.'),
            'Rp '.number_format($qty * $harga, 0, ',', '.'),
        ];
    }

    public function startCell(): string
    {
        return 'B14';
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
        $sheet->mergeCells('D2:J2');
        $sheet->getStyle('D2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->setCellValue('D3', $address);
        $sheet->mergeCells('D3:J3');
        $sheet->setCellValue('D4', $contactInfo);
        $sheet->mergeCells('D4:J4');
        $sheet->getStyle('D3:D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(5)->setRowHeight(20);

        $sheet->mergeCells('B6:J6');
        $sheet->getStyle('B6:J6')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

        $sheet->setCellValue('B8', 'DOKUMEN PENYESUAIAN STOK');
        $sheet->mergeCells('B8:J8');
        $sheet->getStyle('B8')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $docNo = 'PS/'.Carbon::parse($this->date)->format('Y/m').'/'.str_pad($this->adjustments->count(), 5, '0', STR_PAD_LEFT);

        $sheet->setCellValue('B10', 'No Dokumen :');
        $sheet->setCellValue('D10', $docNo);
        $sheet->getStyle('B10')->getFont()->setBold(true);

        $sheet->setCellValue('B11', 'Tanggal :');
        $sheet->setCellValue('D11', Carbon::parse($this->date)->isoFormat('DD MMMM YYYY'));
        $sheet->getStyle('B11')->getFont()->setBold(true);

        $sheet->setCellValue('B12', 'Dibuat Oleh :');
        $sheet->setCellValue('D12', auth()->user()->name ?? '-');
        $sheet->getStyle('B12')->getFont()->setBold(true);

        $sheet->setCellValue('B13', 'Referensi :');
        $sheet->setCellValue('D13', 'Stok Opname / Koreksi Stok');
        $sheet->getStyle('B13')->getFont()->setBold(true);

        $sheet->setCellValue('B14', 'Detail Barang Diterima');
        $sheet->getStyle('B14')->getFont()->setBold(true);

        // TABLE HEADER at row 14 via startCell B14 → header is row 14
        $sheet->getStyle('B14:I14')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8EAADB']],
        ]);

        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 14) {
            $sheet->getStyle('B15:I'.$highestRow)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(16);

        $sheet->getStyle('F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // SUMMARY
        $totalMasuk  = $this->adjustments->where('quantity', '>', 0)->sum('quantity');
        $totalKeluar = $this->adjustments->where('quantity', '<', 0)->sum(fn ($a) => abs($a->quantity));

        $summaryRow = $highestRow + 2;
        $sheet->setCellValue('B'.$summaryRow, 'Total Penyesuaian Masuk :');
        $sheet->mergeCells('B'.$summaryRow.':D'.$summaryRow);
        $sheet->setCellValue('E'.$summaryRow, $totalMasuk);

        $sheet->setCellValue('B'.($summaryRow + 1), 'Total Penyesuaian Keluar :');
        $sheet->mergeCells('B'.($summaryRow + 1).':D'.($summaryRow + 1));
        $sheet->setCellValue('E'.($summaryRow + 1), $totalKeluar);

        $sheet->setCellValue('B'.($summaryRow + 2), 'Keterangan');
        $sheet->setCellValue('D'.($summaryRow + 2), ':');

        // SIGNATURE
        $row = $summaryRow + 5;
        $sheet->mergeCells('B'.$row.':C'.$row);
        $sheet->mergeCells('E'.$row.':G'.$row);
        $sheet->mergeCells('H'.$row.':I'.$row);
        $sheet->setCellValue('B'.$row, 'Diterima Oleh');
        $sheet->setCellValue('E'.$row, 'Diperiksa');
        $sheet->setCellValue('H'.$row, 'Disetujui');
        $sheet->getStyle('B'.$row.':I'.$row)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row++;
        $sheet->mergeCells('B'.$row.':C'.$row);
        $sheet->mergeCells('E'.$row.':G'.$row);
        $sheet->mergeCells('H'.$row.':I'.$row);
        $sheet->setCellValue('B'.$row, 'Staff Gudang');
        $sheet->setCellValue('E'.$row, 'Supervisor Gudang');
        $sheet->setCellValue('H'.$row, 'Manager');
        $sheet->getStyle('B'.$row.':I'.$row)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row += 5;
        $sheet->mergeCells('B'.$row.':C'.$row);
        $sheet->mergeCells('E'.$row.':G'.$row);
        $sheet->mergeCells('H'.$row.':I'.$row);
        $sheet->setCellValue('B'.$row, 'Nama');
        $sheet->setCellValue('E'.$row, 'Nama');
        $sheet->setCellValue('H'.$row, 'Nama');
        $sheet->getStyle('B'.$row.':I'.$row)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('B'.($row - 1).':I'.($row - 1))
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');

        // Use stored logo path or fallback to default
        $logoPath = $this->settings['logo'] ?? null;
        if ($logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)) {
            $drawing->setPath(\Illuminate\Support\Facades\Storage::disk('public')->path($logoPath));
        } else {
            $drawing->setPath(public_path('img/logo.jpeg')); // fallback
        }

        $drawing->setHeight(80);
        $drawing->setCoordinates('B2');

        return [$drawing];
    }

    public function properties(): array
    {
        return [
            'creator' => config('app.name'),
            'title' => 'Dokumen Penyesuaian Stok',
            'description' => 'Stock Opname '.$this->date,
        ];
    }
}
