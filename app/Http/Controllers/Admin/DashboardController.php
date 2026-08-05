<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        // 1. Totales Principales y Métricas Financieras
        $ventasTotales = (float) (Pedido::sum('total') ?: 0);
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

        // Inventario y Productos
        $totalProductos = Producto::where('activo', true)->count();
        $stockTotal = (int) (Producto::sum('stock') ?: 0);
        $productosBajoStock = Producto::whereColumn('stock', '<=', 'stock_minimo')->count();

        // 2. Transacciones / Pedidos Recientes (Reales de la base de datos)
        $transaccionesRecientes = Pedido::with(['usuario', 'ultimoEstado'])
            ->latest('creado_en')
            ->take(6)
            ->get();

        // 3. Actividad Reciente de Auditoría
        $actividadesRecientes = LogAuditoria::with('usuario')
            ->latest('creado_en')
            ->take(6)
            ->get();

        // 4. Datos para gráfico de rendimiento (Ventas últimos 6 meses)
        $ventasMeses = [];
        $mesesLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $mesesLabels[] = $mes->locale('es')->isoFormat('MMM');
            $ventasMes = Pedido::whereBetween('creado_en', [
                $mes->copy()->startOfMonth(),
                $mes->copy()->endOfMonth(),
            ])->sum('total') ?: 0;
            $ventasMeses[] = (float) $ventasMes;
        }

        // 5. Datos para gráfico de Crecimiento de Clientes
        $clientesMeses = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $clientesMes = Usuario::role('cliente')
                ->where('creado_en', '<=', $mes->copy()->endOfMonth())
                ->count();
            $clientesMeses[] = $clientesMes;
        }

        return view('admin.dashboard', compact(
            'ventasTotales',
            'totalPedidos',
            'ticketPromedio',
            'pedidosPendientes',
            'totalClientes',
            'clientesNuevosMes',
            'totalProductos',
            'stockTotal',
            'productosBajoStock',
            'transaccionesRecientes',
            'actividadesRecientes',
            'ventasMeses',
            'clientesMeses',
            'mesesLabels'
        ));
    }
}
