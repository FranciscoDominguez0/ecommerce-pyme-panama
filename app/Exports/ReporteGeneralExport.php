<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteGeneralExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $datos;
    protected $tipoReporte;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
        $this->tipoReporte = $datos['tipoReporte'];
    }

    public function collection()
    {
        switch ($this->tipoReporte) {
            case 'ventas':
                return collect($this->datos['ventasPorPeriodo']);
            case 'productos':
                return collect($this->datos['productosMasVendidos']);
            case 'clientes':
                return collect($this->datos['clientesFrecuentes']);
            case 'stock':
                return collect($this->datos['stockCritico']);
            default:
                return collect($this->datos['ventasPorPeriodo']);
        }
    }

    public function headings(): array
    {
        switch ($this->tipoReporte) {
            case 'ventas':
                return ['Fecha', 'Total de Ventas'];
            case 'productos':
                return ['ID', 'Producto', 'SKU', 'Total Vendido', 'Ingresos Generados'];
            case 'clientes':
                return ['ID', 'Nombre', 'Apellido', 'Email', 'Total Pedidos', 'Total Gastado', 'Último Pedido'];
            case 'stock':
                return ['ID', 'Producto', 'SKU', 'Stock Actual', 'Stock Mínimo', 'Estado'];
            default:
                return ['Fecha', 'Total de Ventas'];
        }
    }

    public function map($row): array
    {
        switch ($this->tipoReporte) {
            case 'ventas':
                // row is an array with 'etiqueta' and 'total'
                return [
                    $row['etiqueta'],
                    '$' . number_format($row['total'], 2)
                ];
            case 'productos':
                // row is an object
                return [
                    $row->id,
                    $row->nombre,
                    $row->sku,
                    $row->total_vendido,
                    '$' . number_format($row->ingresos_generados, 2)
                ];
            case 'clientes':
                return [
                    $row->id,
                    $row->nombre,
                    $row->apellido,
                    $row->email,
                    $row->total_pedidos,
                    '$' . number_format($row->total_gastado, 2),
                    $row->ultimo_pedido_en
                ];
            case 'stock':
                $criticidad = $row->stock == 0 || $row->stock < ($row->stock_minimo / 2) ? 'Crítico' : 'Bajo';
                return [
                    $row->id,
                    $row->nombre,
                    $row->sku,
                    $row->stock,
                    $row->stock_minimo,
                    $criticidad
                ];
            default:
                return [];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
