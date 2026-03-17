<?php

namespace App\Exports;

use App\Models\Pembelian;
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

class PembelianSingleExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings, WithCustomStartCell, WithProperties
{
    use Exportable;

    protected $pembelian;

    public function __construct(Pembelian $pembelian)
    {
        $this->pembelian = $pembelian;
    }

    public function collection()
    {
        return $this->pembelian->pembelianProducts;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Qty',
            'Satuan',
            'Harga',
            'Total',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $item->product->code ?? '',
            $item->product->name ?? '',
            $item->qty,
            $item->product->unit ?? 'PCS',
            'Rp '.number_format($item->harga_beli, 0, ',', '.'),
            'Rp '.number_format($item->subtotal, 0, ',', '.'),
        ];
    }

    public function startCell(): string
    {
        return 'A14';
    }

    public function styles(Worksheet $sheet)
    {
        // Logo spacing
        $sheet->getRowDimension(1)->setRowHeight(50);

        // HEADER TEXT
        $sheet->setCellValue('C2', 'NAMA PERUSAHAAN');
        $sheet->mergeCells('C2:G2');
        $sheet->getStyle('C2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('C3', 'ALAMAT');
        $sheet->mergeCells('C3:G3');

        $sheet->setCellValue('C4', 'NO TELP | EMAIL | WEBSITE');
        $sheet->mergeCells('C4:G4');

        $sheet->getStyle('C2:C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ADD EMPTY ROW (spacing before line)
        $sheet->getRowDimension(5)->setRowHeight(20);

        // LINE (moved down)
        $sheet->mergeCells('A6:G6');
        $sheet->getStyle('A6:G6')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

        // TITLE
        $sheet->setCellValue('A8', 'PURCHASE ORDER (PO)');
        $sheet->mergeCells('A8:G8');
        $sheet->getStyle('A8')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // INFO
        $sheet->setCellValue('B10', 'Kode PO :');
        $sheet->setCellValue('C10', $this->pembelian->code);

        $sheet->setCellValue('B11', 'Tanggal PO :');
        $sheet->setCellValue('C11', Carbon::parse($this->pembelian->created_at)->isoFormat('DD MMMM YYYY'));

        $sheet->setCellValue('B12', 'Nama Supplier :');
        $sheet->setCellValue('C12', $this->pembelian->supplier->name ?? '');

        // TABLE HEADER STYLE
        $sheet->getStyle('A14:G14')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8EAADB'],
            ],
        ]);

        $highestRow = $sheet->getHighestRow();

        if ($highestRow > 14) {
            $sheet->getStyle('A15:G'.$highestRow)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // WIDTH
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(8);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(18);

        // ALIGNMENT
        $sheet->getStyle('D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // SIGNATURE
        $row = $highestRow + 3;

        $sheet->mergeCells('A'.$row.':C'.$row);
        $sheet->mergeCells('E'.$row.':G'.$row);
        $sheet->setCellValue('A'.$row, 'Dibuat Oleh');
        $sheet->setCellValue('E'.$row, 'Disetujui Oleh');

        $sheet->getStyle('A'.$row.':G'.$row)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $sheet->mergeCells('A'.$row.':C'.$row);
        $sheet->mergeCells('E'.$row.':G'.$row);
        $sheet->setCellValue('A'.$row, 'Staff Gudang');
        $sheet->setCellValue('E'.$row, 'Manager');

        $sheet->getStyle('A'.$row.':C'.$row)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('E'.$row.':G'.$row)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 5;
        $sheet->mergeCells('A'.$row.':C'.$row);
        $sheet->mergeCells('E'.$row.':G'.$row);
        $sheet->setCellValue('A'.$row, 'Nama');
        $sheet->setCellValue('E'.$row, 'Nama');

        $sheet->getStyle('A'.($row - 1).':G'.($row - 1))
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('img/logo.jpeg'));
        $drawing->setHeight(80);
        $drawing->setCoordinates('B2');

        return [$drawing];
    }

    public function properties(): array
    {
        return [
            'creator' => config('app.name'),
            'title' => 'Purchase Order',
            'description' => 'PO '.$this->pembelian->code,
        ];
    }
}