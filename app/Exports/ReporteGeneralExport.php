<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReporteGeneralExport implements WithMultipleSheets
{
    use Exportable;

    protected $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function sheets(): array
    {
        $sheets = [];

        if (in_array($this->datos['tipoReporte'], ['ventas', 'completo'])) {
            $sheets[] = new ReporteSheetVentas($this->datos);
        }
        if (in_array($this->datos['tipoReporte'], ['productos', 'completo'])) {
            $sheets[] = new ReporteSheetProductos($this->datos);
        }
        if (in_array($this->datos['tipoReporte'], ['clientes', 'completo'])) {
            $sheets[] = new ReporteSheetClientes($this->datos);
        }
        if (in_array($this->datos['tipoReporte'], ['stock', 'completo'])) {
            $sheets[] = new ReporteSheetStock($this->datos);
        }

        return $sheets;
    }
}

// =========================================================================
// HOJAS INDIVIDUALES
// =========================================================================

abstract class BaseReporteSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected $datos;
    protected $tituloSheet;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function title(): string
    {
        return $this->tituloSheet;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF059669'] // Color Esmeralda
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCBD5E1'],
                        ],
                    ],
                ]);
            },
        ];
    }
}

class ReporteSheetVentas extends BaseReporteSheet
{
    protected $tituloSheet = 'Resumen de Ingresos';

    public function headings(): array { return ['Fecha / Periodo', 'Total Descuentos (USD)', 'Total Ventas (USD)']; }

    public function array(): array
    {
        $filas = [];
        foreach ($this->datos['ventasPorPeriodo'] as $item) {
            $filas[] = [
                $item['etiqueta'],
                $item['descuentos'],
                $item['total']
            ];
        }
        return $filas;
    }

    public function registerEvents(): array
    {
        $events = parent::registerEvents();
        $events[AfterSheet::class] = function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $highestRow = $sheet->getHighestRow();
            $sheet->getStyle('A1:C' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
            $sheet->getStyle('B2:C' . $highestRow)->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
        };
        return $events;
    }
}

class ReporteSheetProductos extends BaseReporteSheet
{
    protected $tituloSheet = 'Top Productos';

    public function headings(): array { return ['SKU', 'Producto', 'Unidades Vendidas', 'Ingresos Generados (USD)']; }

    public function array(): array
    {
        $filas = [];
        foreach ($this->datos['productosMasVendidos'] as $prod) {
            $filas[] = [
                $prod->sku,
                $prod->nombre,
                $prod->total_vendido,
                $prod->ingresos_generados
            ];
        }
        return $filas;
    }

    public function registerEvents(): array
    {
        $events = parent::registerEvents();
        $events[AfterSheet::class] = function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $highestRow = $sheet->getHighestRow();
            $sheet->getStyle('A1:D' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
            $sheet->getStyle('D2:D' . $highestRow)->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
            $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        };
        return $events;
    }
}

class ReporteSheetClientes extends BaseReporteSheet
{
    protected $tituloSheet = 'Mejores Clientes';

    public function headings(): array { return ['Cliente', 'Email', 'Total Pedidos', 'Total Gastado (USD)', 'Último Pedido']; }

    public function array(): array
    {
        $filas = [];
        foreach ($this->datos['clientesFrecuentes'] as $cli) {
            $filas[] = [
                $cli->nombre . ' ' . $cli->apellido,
                $cli->email,
                $cli->total_pedidos,
                $cli->total_gastado,
                \Carbon\Carbon::parse($cli->ultimo_pedido_en)->format('d/m/Y')
            ];
        }
        return $filas;
    }

    public function registerEvents(): array
    {
        $events = parent::registerEvents();
        $events[AfterSheet::class] = function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $highestRow = $sheet->getHighestRow();
            $sheet->getStyle('A1:E' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
            $sheet->getStyle('D2:D' . $highestRow)->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
            $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        };
        return $events;
    }
}

class ReporteSheetStock extends BaseReporteSheet
{
    protected $tituloSheet = 'Stock Crítico';

    public function headings(): array { return ['SKU', 'Producto', 'Stock Actual', 'Mínimo Requerido', 'Estado']; }

    public function array(): array
    {
        $filas = [];
        foreach ($this->datos['stockCritico'] as $prod) {
            $esCritico = $prod->stock == 0 || $prod->stock < ($prod->stock_minimo / 2);
            $filas[] = [
                $prod->sku,
                $prod->nombre,
                $prod->stock,
                $prod->stock_minimo,
                $esCritico ? 'Crítico' : 'Bajo'
            ];
        }
        return $filas;
    }

    public function registerEvents(): array
    {
        $events = parent::registerEvents();
        $events[AfterSheet::class] = function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $highestRow = $sheet->getHighestRow();
            $sheet->getStyle('A1:E' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
            $sheet->getStyle('C2:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        };
        return $events;
    }
}
