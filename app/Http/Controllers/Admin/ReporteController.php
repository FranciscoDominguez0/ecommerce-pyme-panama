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
        $tipoFiltro = $request->query('tipo', 'año'); // mes, año, todos
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
                ->get()
                ->keyBy('fecha');
            
            $ventasPorPeriodo = [];
            $currentDate = clone $fechaInicio;
            while ($currentDate <= $fechaFin) {
                $fechaStr = $currentDate->format('Y-m-d');
                $ventasPorPeriodo[] = [
                    'etiqueta' => $currentDate->format('d M Y'),
                    'total' => isset($ventasDB[$fechaStr]) ? (float) $ventasDB[$fechaStr]->total_ventas : 0,
                    'descuentos' => isset($ventasDB[$fechaStr]) ? (float) $ventasDB[$fechaStr]->total_descuentos : 0
                ];
                $currentDate->addDay();
            }
        } else {
            $ventasDB = (clone $queryFacturas)
                ->select(
                    DB::raw('TO_CHAR(emitida_en, \'YYYY-MM\') as mes'), 
                    DB::raw('SUM(total) as total_ventas'),
                    DB::raw('SUM(descuento) as total_descuentos')
                )
                ->groupBy(DB::raw('TO_CHAR(emitida_en, \'YYYY-MM\')'))
                ->orderBy('mes')
                ->get()
                ->keyBy('mes');
                
            $ventasPorPeriodo = [];
            $currentMonth = clone $fechaInicio;
            $currentMonth->startOfMonth();
            $endMonth = clone $fechaFin;
            $endMonth->startOfMonth();
            
            while ($currentMonth <= $endMonth) {
                $mesStr = $currentMonth->format('Y-m');
                $ventasPorPeriodo[] = [
                    'etiqueta' => $currentMonth->format('M Y'),
                    'total' => isset($ventasDB[$mesStr]) ? (float) $ventasDB[$mesStr]->total_ventas : 0,
                    'descuentos' => isset($ventasDB[$mesStr]) ? (float) $ventasDB[$mesStr]->total_descuentos : 0
                ];
                $currentMonth->addMonth();
            }
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
        $todasLasCategorias = DB::table('items_pedido')
            ->join('pedidos', 'items_pedido.pedido_id', '=', 'pedidos.id')
            ->join('facturas', 'pedidos.id', '=', 'facturas.pedido_id')
            ->join('productos', 'items_pedido.producto_id', '=', 'productos.id')
            ->leftJoin('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('facturas.estado', 'emitida')
            ->whereBetween('facturas.emitida_en', [$fechaInicio, $fechaFin])
            ->select(DB::raw('COALESCE(categorias.nombre, \'Sin Categoría\') as categoria'), DB::raw('SUM(items_pedido.subtotal) as total_ventas'))
            ->groupBy(DB::raw('COALESCE(categorias.nombre, \'Sin Categoría\')'))
            ->orderBy('total_ventas', 'desc')
            ->get();

        $ventasPorCategoria = $todasLasCategorias->take(5)->map(function ($item) {
            return [
                'categoria' => $item->categoria,
                'total_ventas' => (float) $item->total_ventas
            ];
        })->toArray();

        $restantes = $todasLasCategorias->skip(5)->sum('total_ventas');
        if ($restantes > 0) {
            $ventasPorCategoria[] = [
                'categoria' => 'Otras',
                'total_ventas' => (float) $restantes
            ];
        }

        // --- 8. Ventas por Método de Pago ---
        $ventasPorMetodoPago = DB::table('pedidos')
            ->join('facturas', 'pedidos.id', '=', 'facturas.pedido_id')
            ->where('facturas.estado', 'emitida')
            ->whereBetween('facturas.emitida_en', [$fechaInicio, $fechaFin])
            ->select('pedidos.metodo_pago', DB::raw('SUM(facturas.total) as total_ventas'))
            ->groupBy('pedidos.metodo_pago')
            ->orderBy('total_ventas', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'metodo' => ucfirst($item->metodo_pago),
                    'total_ventas' => (float) $item->total_ventas
                ];
            })->toArray();

        // --- 9. Estado de las Facturas (Aprobadas, Anuladas, Pendientes) ---
        $estadosFacturas = DB::table('facturas')
            ->whereBetween('emitida_en', [$fechaInicio, $fechaFin])
            ->select('estado', DB::raw('COUNT(id) as cantidad'))
            ->groupBy('estado')
            ->get()
            ->map(function ($item) {
                return [
                    'estado' => ucfirst($item->estado),
                    'cantidad' => (int) $item->cantidad
                ];
            })->toArray();

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
            'ventasPorCategoria',
            'ventasPorMetodoPago',
            'estadosFacturas'
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
