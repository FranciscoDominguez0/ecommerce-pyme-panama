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

use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Muestra el panel principal administrativo con métricas reales de la base de datos.
     */
    public function index(Request $request): View
    {
        $metricas = $this->dashboardService->obtenerMetricas();

        return view('admin.dashboard', $metricas);
    }
}

