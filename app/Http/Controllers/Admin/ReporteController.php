<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use App\Services\ReporteService;
use App\Exports\ReporteGeneralExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    protected ReporteService $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    /**
     * Muestra el panel principal de reportes y estadísticas.
     */
    public function index(Request $request)
    {
        $datos = $this->reporteService->obtenerEstadisticas($request);
        return view('admin.reportes.index', $datos);
    }

    public function exportarExcel(Request $request, AuditoriaService $auditoria)
    {
        $datos = $this->reporteService->obtenerEstadisticas($request);
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
        $datos = $this->reporteService->obtenerEstadisticas($request);
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
