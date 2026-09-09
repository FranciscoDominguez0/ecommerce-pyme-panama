<?php

namespace App\Exports;

use Illuminate\Support\Collection;
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
    private const FILA_INICIO = 'A6';
    private const COLOR_ESMERALDA = 'FF059669'; // Verde esmeralda corporativo
    private const COLOR_BORDE = 'FFCBD5E1';     // Borde suave de tabla (slate-300)
    private const COLOR_SUBTITULO = 'FF64748B'; // Gris para fecha y notas (slate-500)
    private const STOCK_MINIMO_DEFECTO = 5;
    private const FORMATO_MONEDA = '"$"#,##0.00_-';

    protected Collection $items;
    protected int $totalItems;
    protected int $totalStock;
    protected float $valorizacionTotal;
    
    public function __construct(Collection $items)
    {
        $this->items = $items;
        $this->totalItems = $items->count();
        $this->totalStock = (int) $items->sum('stock');
        $this->valorizacionTotal = (float) $items->sum(fn ($item) => $item->stock * $item->precio);
    }

    public function collection(): Collection
    {
        return $this->items;
    }

    public function startCell(): string
    {
        return self::FILA_INICIO;
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

    /**
     * Mapea cada producto o variante a una fila de la exportación.
     */
    public function map($item): array
    {
        return [
            $item->sku ?? 'N/A',
            $item->nombre_completo,
            $item->categoria->nombre ?? 'N/A',
            $this->determinarEstadoStock($item),
            $item->stock,
            $item->precio,
            ($item->stock * $item->precio)
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => self::COLOR_ESMERALDA]
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
                
                // Encabezado institucional de la empresa
                $sheet->setCellValue('A1', 'PAYME PANAMÁ, S.A.');
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB(self::COLOR_ESMERALDA);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A2', 'Reporte de Stock Actual (Filtrado)');
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A3', 'Fecha de Generación: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A3:G3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A3')->getFont()->getColor()->setARGB(self::COLOR_SUBTITULO);

                // Métricas clave del reporte filtrado
                $sheet->setCellValue('A4', 'Total Items: ' . $this->totalItems);
                $sheet->setCellValue('C4', 'Unidades en Stock: ' . $this->totalStock);
                $sheet->setCellValue('F4', 'Valorización Total: $' . number_format($this->valorizacionTotal, 2));
                
                $sheet->getStyle('A4:G4')->getFont()->setBold(true);
                $sheet->getStyle('F4:G4')->getFont()->getColor()->setARGB(self::COLOR_ESMERALDA);

                // Cuadrícula de la tabla
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A6:G' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => self::COLOR_BORDE],
                        ],
                    ],
                ]);

                // Formato de moneda para precio y total
                $sheet->getStyle('F7:G' . $highestRow)->getNumberFormat()->setFormatCode(self::FORMATO_MONEDA);
                $sheet->getStyle('D7:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    /**
     * Determina el estado del inventario comparando el stock contra el umbral mínimo.
     */
    private function determinarEstadoStock($item): string
    {
        $minimo = $item->stock_minimo ?? self::STOCK_MINIMO_DEFECTO;

        if ($item->stock > $minimo) {
            return 'En Stock';
        }

        if ($item->stock > 0) {
            return 'Bajo Stock';
        }

        return 'Agotado';
    }
}
