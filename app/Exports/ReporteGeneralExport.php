<?php

namespace App\Exports;

use Carbon\Carbon;
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

    protected array $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    /**
     * Construye las pestañas del libro de Excel según el tipo de reporte solicitado.
     */
    public function sheets(): array
    {
        $sheets = [];
        $tipo = $this->datos['tipoReporte'] ?? 'completo';

        if (in_array($tipo, ['ventas', 'completo'])) {
            $sheets[] = new ReporteSheetVentas($this->datos);
        }
        if (in_array($tipo, ['productos', 'completo'])) {
            $sheets[] = new ReporteSheetProductos($this->datos);
        }
        if (in_array($tipo, ['clientes', 'completo'])) {
            $sheets[] = new ReporteSheetClientes($this->datos);
        }
        if (in_array($tipo, ['stock', 'completo'])) {
            $sheets[] = new ReporteSheetStock($this->datos);
        }

        return $sheets;
    }
}

/**
 * Plantilla base para cada pestaña de reporte con estilos corporativos compartidos.
 */
abstract class BaseReporteSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected const COLOR_ESMERALDA = 'FF059669';
    protected const COLOR_BORDE = 'FFCBD5E1';
    protected const FORMATO_MONEDA = '"$"#,##0.00_-';

    protected array $datos;
    protected string $tituloSheet;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function title(): string
    {
        return $this->tituloSheet;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => self::COLOR_ESMERALDA],
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
                            'color' => ['argb' => self::COLOR_BORDE],
                        ],
                    ],
                ]);
            },
        ];
    }
}

class ReporteSheetVentas extends BaseReporteSheet
{
    protected string $tituloSheet = 'Resumen de Ingresos';

    public function headings(): array
    {
        return ['Fecha / Periodo', 'Total Descuentos (USD)', 'Total Ventas (USD)'];
    }

    public function array(): array
    {
        $filas = [];
        foreach ($this->datos['ventasPorPeriodo'] ?? [] as $item) {
            $filas[] = [
                $item['etiqueta'],
                $item['descuentos'],
                $item['total'],
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
            $sheet->getStyle('A1:C' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDE);
            $sheet->getStyle('B2:C' . $highestRow)->getNumberFormat()->setFormatCode(self::FORMATO_MONEDA);
        };
        return $events;
    }
}

class ReporteSheetProductos extends BaseReporteSheet
{
    protected string $tituloSheet = 'Top Productos';

    public function headings(): array
    {
        return ['SKU', 'Producto', 'Unidades Vendidas', 'Ingresos Generados (USD)'];
    }

    public function array(): array
    {
        $filas = [];
        foreach ($this->datos['productosMasVendidos'] ?? [] as $prod) {
            $filas[] = [
                $prod->sku,
                $prod->nombre,
                $prod->total_vendido,
                $prod->ingresos_generados,
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
            $sheet->getStyle('A1:D' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDE);
            $sheet->getStyle('D2:D' . $highestRow)->getNumberFormat()->setFormatCode(self::FORMATO_MONEDA);
            $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        };
        return $events;
    }
}

class ReporteSheetClientes extends BaseReporteSheet
{
    protected string $tituloSheet = 'Mejores Clientes';

    public function headings(): array
    {
        return ['Cliente', 'Email', 'Total Pedidos', 'Total Gastado (USD)', 'Último Pedido'];
    }

    public function array(): array
    {
        $filas = [];
        foreach ($this->datos['clientesFrecuentes'] ?? [] as $cli) {
            $fechaUltimoPedido = !empty($cli->ultimo_pedido_en)
                ? Carbon::parse($cli->ultimo_pedido_en)->format('d/m/Y')
                : 'N/A';

            $filas[] = [
                trim(($cli->nombre ?? '') . ' ' . ($cli->apellido ?? '')),
                $cli->email,
                $cli->total_pedidos,
                $cli->total_gastado,
                $fechaUltimoPedido,
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
            $sheet->getStyle('A1:E' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDE);
            $sheet->getStyle('D2:D' . $highestRow)->getNumberFormat()->setFormatCode(self::FORMATO_MONEDA);
            $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        };
        return $events;
    }
}

class ReporteSheetStock extends BaseReporteSheet
{
    protected string $tituloSheet = 'Stock Crítico';

    public function headings(): array
    {
        return ['SKU', 'Producto', 'Stock Actual', 'Mínimo Requerido', 'Estado'];
    }

    public function array(): array
    {
        $filas = [];
        foreach ($this->datos['stockCritico'] ?? [] as $prod) {
            $esCritico = $prod->stock == 0 || $prod->stock < ($prod->stock_minimo / 2);
            $filas[] = [
                $prod->sku,
                $prod->nombre,
                $prod->stock,
                $prod->stock_minimo,
                $esCritico ? 'Crítico' : 'Bajo',
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
            $sheet->getStyle('A1:E' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDE);
            $sheet->getStyle('C2:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        };
        return $events;
    }
}
