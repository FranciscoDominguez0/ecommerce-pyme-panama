<?php

namespace App\Exports;

use App\Models\Producto;
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

class InventarioExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $productos;
    protected $totalProductos;
    protected $totalStock;
    protected $valorizacionTotal;
    
    public function __construct()
    {
        $this->productos = Producto::with('categoria')->sinEliminar()->orderBy('nombre', 'asc')->get();
        $this->totalProductos = $this->productos->count();
        $this->totalStock = $this->productos->sum('stock');
        
        $this->valorizacionTotal = 0;
        foreach ($this->productos as $producto) {
            $this->valorizacionTotal += ($producto->stock * $producto->precio);
        }
    }

    public function collection()
    {
        return $this->productos;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            'SKU',
            'PRODUCTO',
            'CATEGORÍA',
            'ESTADO',
            'STOCK',
            'PRECIO UNIT. (USD)',
            'VALOR TOTAL (USD)'
        ];
    }

    public function map($producto): array
    {
        $estado = 'Agotado';
        if ($producto->stock > ($producto->stock_minimo ?? 5)) {
            $estado = 'En Stock';
        } elseif ($producto->stock > 0) {
            $estado = 'Bajo Stock';
        }

        return [
            $producto->sku ?? 'N/A',
            $producto->nombre,
            $producto->categoria->nombre ?? 'N/A',
            $estado,
            $producto->stock,
            $producto->precio,
            ($producto->stock * $producto->precio)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para los encabezados de la tabla
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF059669'] // Color Esmeralda
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
                $sheet->setCellValue('A2', 'Reporte de Valorización de Inventario');
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Fecha
                $sheet->setCellValue('A3', 'Fecha de Generación: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A3:G3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A3')->getFont()->getColor()->setARGB('FF64748B');

                // KPIs (Resumen Ejecutivo)
                $sheet->setCellValue('A4', 'Total SKUs: ' . $this->totalProductos);
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

                // Formato de moneda para columnas de precio y valor total (F y G)
                $sheet->getStyle('F7:G' . $highestRow)->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
                // Alinear al centro el estado y stock (D y E)
                $sheet->getStyle('D7:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
