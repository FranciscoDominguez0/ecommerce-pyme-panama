<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use App\Exports\ReporteGeneralExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Muestra el panel principal de reportes y estadísticas.
     */
    public function index(Request $request)
    {
        $datos = $this->prepararDatosReporte($request);
        return view('admin.reportes.index', $datos);
    }

    /**
     * Prepara los datos del reporte según los filtros.
     */
    private function prepararDatosReporte(Request $request)
    {
        $tipoFiltro = $request->query('tipo', 'todos'); // mes, año, todos
        $fechaInicioStr = $request->query('fecha_inicio');
        $fechaFinStr = $request->query('fecha_fin');
        $tipoReporte = $request->query('reporte', 'ventas'); // ventas, productos, clientes, stock

        $queryFacturas = Factura::where('estado', 'emitida');
        
        $fechaInicio = null;
        $fechaFin = null;
        
        if ($fechaInicioStr && $fechaFinStr) {
            $fechaInicio = Carbon::parse($fechaInicioStr)->startOfDay();
            $fechaFin = Carbon::parse($fechaFinStr)->endOfDay();
        } else {
            if ($tipoFiltro === 'mes') {
                $fechaInicio = Carbon::now()->startOfMonth();
                $fechaFin = Carbon::now()->endOfMonth();
            } elseif ($tipoFiltro === 'año') {
                $fechaInicio = Carbon::now()->startOfYear();
                $fechaFin = Carbon::now()->endOfYear();
            } else {
                // Todos
                $fechaInicio = Carbon::create(2020, 1, 1);
                $fechaFin = Carbon::now()->endOfDay();
            }
        }

        $queryFacturas->whereBetween('emitida_en', [$fechaInicio, $fechaFin]);
        
        // --- 1. KPIs ---
        $totalVentas = (clone $queryFacturas)->sum('total');
        $numeroPedidos = (clone $queryFacturas)->count();
        $ticketPromedio = $numeroPedidos > 0 ? $totalVentas / $numeroPedidos : 0;
        
        // --- 2. Ventas por periodo (Gráfica) ---
        $diferenciaDias = $fechaInicio->diffInDays($fechaFin);
        $ventasPorPeriodo = [];
        
        if ($diferenciaDias <= 31) {
            $ventasDB = (clone $queryFacturas)
                ->select(
                    DB::raw('DATE(emitida_en) as fecha'),
                    DB::raw('SUM(total) as total_ventas'),
                    DB::raw('SUM(descuento) as total_descuentos')
                )
                ->groupBy(DB::raw('DATE(emitida_en)'))
                ->orderBy('fecha')
                ->get();
            
            $ventasPorPeriodo = $ventasDB->map(function ($item) {
                return [
                    'etiqueta' => Carbon::parse($item->fecha)->format('d M Y'),
                    'total' => (float) $item->total_ventas,
                    'descuentos' => (float) $item->total_descuentos
                ];
            })->toArray();
        } else {
            $ventasDB = (clone $queryFacturas)
                ->select(
                    DB::raw('TO_CHAR(emitida_en, \'YYYY-MM\') as mes'), 
                    DB::raw('SUM(total) as total_ventas'),
                    DB::raw('SUM(descuento) as total_descuentos')
                )
                ->groupBy(DB::raw('TO_CHAR(emitida_en, \'YYYY-MM\')'))
                ->orderBy('mes')
                ->get();
                
            $ventasPorPeriodo = $ventasDB->map(function ($item) {
                return [
                    'etiqueta' => Carbon::parse($item->mes . '-01')->format('M Y'),
                    'total' => (float) $item->total_ventas,
                    'descuentos' => (float) $item->total_descuentos
                ];
            })->toArray();
        }

        // --- 3. Productos más vendidos ---
        $productosMasVendidos = DB::table('items_pedido')
            ->join('pedidos', 'items_pedido.pedido_id', '=', 'pedidos.id')
            ->join('facturas', 'pedidos.id', '=', 'facturas.pedido_id')
            ->join('productos', 'items_pedido.producto_id', '=', 'productos.id')
            ->where('facturas.estado', 'emitida')
            ->whereBetween('facturas.emitida_en', [$fechaInicio, $fechaFin])
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.sku',
                DB::raw('SUM(items_pedido.cantidad) as total_vendido'),
                DB::raw('SUM(items_pedido.subtotal) as ingresos_generados')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.sku')
            ->orderBy('total_vendido', 'desc')
            ->take(10)
            ->get();

        // --- 4. Clientes Frecuentes ---
        $clientesFrecuentes = DB::table('facturas')
            ->join('usuarios', 'facturas.usuario_id', '=', 'usuarios.id')
            ->join('usuario_roles', 'usuarios.id', '=', 'usuario_roles.usuario_id')
            ->join('roles', 'usuario_roles.rol_id', '=', 'roles.id')
            ->where('facturas.estado', 'emitida')
            ->where('roles.name', 'cliente')
            ->whereBetween('facturas.emitida_en', [$fechaInicio, $fechaFin])
            ->select(
                'usuarios.id',
                'usuarios.nombre',
                'usuarios.apellido',
                'usuarios.email',
                DB::raw('COUNT(facturas.id) as total_pedidos'),
                DB::raw('SUM(facturas.total) as total_gastado'),
                DB::raw('MAX(facturas.emitida_en) as ultimo_pedido_en')
            )
            ->groupBy('usuarios.id', 'usuarios.nombre', 'usuarios.apellido', 'usuarios.email')
            ->orderBy('total_gastado', 'desc')
            ->take(10)
            ->get();

        // --- 5. Stock Crítico ---
        $stockCritico = Producto::activos()
            ->whereRaw('stock <= stock_minimo')
            ->orderByRaw('CASE WHEN stock_minimo > 0 THEN stock::float / stock_minimo ELSE 0 END ASC')
            ->take(20)
            ->get();

        // --- 6. (Removido: Ventas por Método de Pago) ---

        // --- 7. Ventas por Categoría (Gráfica de Barras / Donut) ---
        $ventasPorCategoria = DB::table('items_pedido')
            ->join('pedidos', 'items_pedido.pedido_id', '=', 'pedidos.id')
            ->join('facturas', 'pedidos.id', '=', 'facturas.pedido_id')
            ->join('productos', 'items_pedido.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('facturas.estado', 'emitida')
            ->whereBetween('facturas.emitida_en', [$fechaInicio, $fechaFin])
            ->select('categorias.nombre as categoria', DB::raw('SUM(items_pedido.subtotal) as total_ventas'))
            ->groupBy('categorias.nombre')
            ->orderBy('total_ventas', 'desc')
            ->take(8)
            ->get()
            ->map(function ($item) {
                return [
                    'categoria' => $item->categoria,
                    'total_ventas' => (float) $item->total_ventas
                ];
            })
            ->toArray();

        return compact(
            'tipoFiltro',
            'fechaInicioStr',
            'fechaFinStr',
            'fechaInicio',
            'fechaFin',
            'tipoReporte',
            'totalVentas',
            'numeroPedidos',
            'ticketPromedio',
            'ventasPorPeriodo',
            'productosMasVendidos',
            'clientesFrecuentes',
            'stockCritico',
            'ventasPorCategoria'
        );
    }

    public function exportarExcel(Request $request, AuditoriaService $auditoria)
    {
        $datos = $this->prepararDatosReporte($request);
        $tipoReporte = $datos['tipoReporte'];
        $fechaInicioStr = $datos['fechaInicio']->format('Y-m-d');
        $fechaFinStr = $datos['fechaFin']->format('Y-m-d');

        $auditoria->registrar(
            'Reportes',
            'Exportación Excel',
            "Exportación de reporte tipo '{$tipoReporte}' ({$fechaInicioStr} al {$fechaFinStr})"
        );

        return Excel::download(new ReporteGeneralExport($datos), "reporte_{$tipoReporte}_{$fechaInicioStr}.xlsx");
    }

    public function exportarPdf(Request $request, AuditoriaService $auditoria)
    {
        $datos = $this->prepararDatosReporte($request);
        $tipoReporte = $datos['tipoReporte'];
        $fechaInicioStr = $datos['fechaInicio']->format('Y-m-d');
        $fechaFinStr = $datos['fechaFin']->format('Y-m-d');

        $auditoria->registrar(
            'Reportes',
            'Exportación PDF',
            "Exportación de reporte tipo '{$tipoReporte}' ({$fechaInicioStr} al {$fechaFinStr})"
        );

        $pdf = Pdf::loadView('admin.reportes.pdf.reporte', $datos);
        return $pdf->download("reporte_{$tipoReporte}_{$fechaInicioStr}.pdf");
    }
}
