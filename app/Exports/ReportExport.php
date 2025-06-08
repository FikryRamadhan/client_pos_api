<?php

namespace App\Exports;

use App\Helpers\APIResponse;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function __construct(protected $tahun, protected $bulan, protected $startDate, protected $endDate) {}

    public function collection()
    {
        $query = Transaction::with(['cashier', 'customer', 'transactionDetails']);

        if ($this->tahun && $this->bulan) {
            $data = $query->whereYear('created_at', $this->tahun)
                ->whereMonth('created_at', $this->bulan)
                ->get();
        } else {
            $data = $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->get();
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // ID
            'B' => 20, // Cashier Name
            'C' => 20, // Customer Name
            'D' => 15, // Total Price
            'E' => 15, // Payment Method
            'F' => 15, // Product
            'G' => 50, // Date
            'H' => 20, // Outlet Name
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cashier Name',
            'Outlet Name',
            'Customer Name',
            'Total Price',
            'Payment Method',
            'Product',
            'Date',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '4CAF50', // warna latar hijau
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }


    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->cashier->name ?? 'N/A',
            $transaction->cashier->outlet->name ?? 'N/A',
            $transaction->customer->name ?? 'N/A',
            $transaction->total_price,
            $transaction->payment_method,
            implode(", \n", $transaction->transactionDetails->map(function ($detail) {
                return $detail->product->name . ' (x' . $detail->quantity . ' - Rp.' . number_format($detail->subtotal) . ')\n';
            })->toArray()),
            $transaction->created_at->format('Y-m-d')
        ];
    }
}
