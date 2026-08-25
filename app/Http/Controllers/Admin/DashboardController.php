<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\LogAuditoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra el panel principal administrativo con métricas reales de la base de datos.
     */
    public function index(Request $request): View
    {
        // 1. Totales Principales y Métricas Financieras (Históricas)
        $ventasTotales = (float) (Factura::where('estado', 'emitida')->sum('total') ?: 0);
        $totalPedidos = Pedido::count();
        $ticketPromedio = $totalPedidos > 0 ? ($ventasTotales / $totalPedidos) : 0;

        // Pedidos que requieren atención (no completados ni cancelados)
        $pedidosPendientes = Pedido::whereDoesntHave('estados', function ($query) {
            $query->whereIn('estado', ['completado', 'entregado', 'cancelado']);
        })->count();

        // Total de Clientes registrados
        $totalClientes = Usuario::role('cliente')->count();
        $clientesNuevosMes = Usuario::role('cliente')
            ->where('creado_en', '>=', Carbon::now()->startOfMonth())
            ->count();

        // 2. Datos para Sparklines (Últimos 7 días)
        $ventas7Dias = [];
        $pedidos7Dias = [];
        $clientes7Dias = [];
        $aov7Dias = [];
        $diasLabels = [];

        // Porcentajes de crecimiento vs 7 días anteriores
        $ventas7DiasAnteriores = 0;
        $pedidos7DiasAnteriores = 0;
        $clientes7DiasAnteriores = 0;

        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::now()->subDays($i);
            $diasLabels[] = $dia->locale('es')->isoFormat('ddd');
            
            $ventasDia = (float) Factura::where('estado', 'emitida')->whereDate('emitida_en', $dia)->sum('total');
            $ventas7Dias[] = $ventasDia;
            
            $pedidosDia = Pedido::whereDate('creado_en', $dia)->count();
            $pedidos7Dias[] = $pedidosDia;
            
            $clientesDia = Usuario::role('cliente')->whereDate('creado_en', $dia)->count();
            $clientes7Dias[] = $clientesDia;
            
            $aov7Dias[] = $pedidosDia > 0 ? round($ventasDia / $pedidosDia, 2) : 0;

            // Días anteriores (para % de crecimiento)
            $diaAnterior = Carbon::now()->subDays($i + 7);
            $ventas7DiasAnteriores += (float) Factura::where('estado', 'emitida')->whereDate('emitida_en', $diaAnterior)->sum('total');
            $pedidos7DiasAnteriores += Pedido::whereDate('creado_en', $diaAnterior)->count();
            $clientes7DiasAnteriores += Usuario::role('cliente')->whereDate('creado_en', $diaAnterior)->count();
        }

        $ventasTotal7Dias = array_sum($ventas7Dias);
        $pedidosTotal7Dias = array_sum($pedidos7Dias);
        $clientesTotal7Dias = array_sum($clientes7Dias);

        $crecimientoVentas = $ventas7DiasAnteriores > 0 ? round((($ventasTotal7Dias - $ventas7DiasAnteriores) / $ventas7DiasAnteriores) * 100, 1) : 100;
        $crecimientoPedidos = $pedidos7DiasAnteriores > 0 ? round((($pedidosTotal7Dias - $pedidos7DiasAnteriores) / $pedidos7DiasAnteriores) * 100, 1) : 100;
        $crecimientoClientes = $clientes7DiasAnteriores > 0 ? round((($clientesTotal7Dias - $clientes7DiasAnteriores) / $clientes7DiasAnteriores) * 100, 1) : 100;

        // 3. Datos para Gráfico Principal (Últimos 6 meses: Ingresos y Órdenes)
        $ventasMeses = [];
        $ordenesMeses = [];
        $mesesLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $mesesLabels[] = $mes->locale('es')->isoFormat('MMM');
            
            $ventasMes = Factura::where('estado', 'emitida')->whereBetween('emitida_en', [
                $mes->copy()->startOfMonth(),
                $mes->copy()->endOfMonth(),
            ])->sum('total') ?: 0;
            $ventasMeses[] = (float) $ventasMes;

            $ordenesMes = Pedido::whereBetween('creado_en', [
                $mes->copy()->startOfMonth(),
                $mes->copy()->endOfMonth(),
            ])->count();
            $ordenesMeses[] = $ordenesMes;
        }

        // 4. Transacciones / Pedidos Recientes (Reales de la base de datos)
        $transaccionesRecientes = Pedido::with(['usuario', 'ultimoEstado'])
            ->latest('creado_en')
            ->take(5)
            ->get();

        // 5. Actividad Reciente de Auditoría
        $actividadesRecientes = LogAuditoria::with('usuario')
            ->latest('creado_en')
            ->take(5)
            ->get();

        // 6. Top Productos (Basado en ItemPedido)
        $topProductos = Producto::withSum(['itemsPedido as ventas_totales' => function ($query) {
            $query->whereHas('pedido', function ($q) {
                $q->whereDoesntHave('estados', function ($sq) {
                    $sq->where('estado', 'cancelado');
                });
            });
        }], 'cantidad')
        ->with('categoria')
        ->orderByDesc('ventas_totales')
        ->take(4)
        ->get();

        return view('admin.dashboard', compact(
            'ventasTotales',
            'totalPedidos',
            'ticketPromedio',
            'pedidosPendientes',
            'totalClientes',
            'clientesNuevosMes',
            
            'diasLabels',
            'ventas7Dias',
            'pedidos7Dias',
            'clientes7Dias',
            'aov7Dias',
            'crecimientoVentas',
            'crecimientoPedidos',
            'crecimientoClientes',

            'transaccionesRecientes',
            'actividadesRecientes',
            'topProductos',
            
            'ventasMeses',
            'ordenesMeses',
            'mesesLabels'
        ));
    }
}

