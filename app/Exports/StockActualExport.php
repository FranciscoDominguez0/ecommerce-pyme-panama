<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StockActualExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $items;
    protected $totalItems;
    protected $totalStock;
    protected $valorizacionTotal;
    
    public function __construct($items)
    {
        $this->items = $items;
        $this->totalItems = $items->count();
        $this->totalStock = $items->sum('stock');
        
        $this->valorizacionTotal = $items->sum(function ($item) {
            return $item->stock * $item->precio;
        });
    }

    public function collection()
    {
        return $this->items;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            'SKU',
            'PRODUCTO Y VARIANTE',
            'CATEGORÍA',
            'ESTADO',
            'STOCK',
            'PRECIO UNIT. (USD)',
            'VALOR TOTAL (USD)'
        ];
    }

    public function map($item): array
    {
        $estado = 'Agotado';
        $minimo = $item->stock_minimo ?? 5;
        
        if ($item->stock > $minimo) {
            $estado = 'En Stock';
        } elseif ($item->stock > 0) {
            $estado = 'Bajo Stock';
        }

        return [
            $item->sku ?? 'N/A',
            $item->nombre_completo,
            $item->categoria->nombre ?? 'N/A',
            $estado,
            $item->stock,
            $item->precio,
            ($item->stock * $item->precio)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF059669'] // Color Esmeralda corporativo
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Título Principal
                $sheet->setCellValue('A1', 'PAYME PANAMÁ, S.A.');
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FF059669');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Subtítulo
                $sheet->setCellValue('A2', 'Reporte de Stock Actual (Filtrado)');
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Fecha
                $sheet->setCellValue('A3', 'Fecha de Generación: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A3:G3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A3')->getFont()->getColor()->setARGB('FF64748B');

                // KPIs (Resumen Ejecutivo)
                $sheet->setCellValue('A4', 'Total Items: ' . $this->totalItems);
                $sheet->setCellValue('C4', 'Unidades en Stock: ' . $this->totalStock);
                $sheet->setCellValue('F4', 'Valorización Total: $' . number_format($this->valorizacionTotal, 2));
                
                // Estilo a los KPIs
                $sheet->getStyle('A4:G4')->getFont()->setBold(true);
                $sheet->getStyle('F4:G4')->getFont()->getColor()->setARGB('FF059669');

                // Aplicar bordes y alineación a la tabla
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A6:G' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCBD5E1'],
                        ],
                    ],
                ]);

                // Formato de moneda para columnas de precio y valor total
                $sheet->getStyle('F7:G' . $highestRow)->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
                // Alinear al centro el estado y stock
                $sheet->getStyle('D7:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
