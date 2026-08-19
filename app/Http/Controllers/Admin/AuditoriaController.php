<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAuditoria;
use App\Models\Usuario;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = LogAuditoria::with(['usuario.roles']);

        // Aplicar filtros si existen en la query string
        if ($request->filled('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }
        if ($request->filled('modulo')) {
            $query->porModulo($request->modulo);
        }
        if ($request->filled('accion')) {
            $query->porAccion($request->accion);
        }
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $query->porRangoFechas($request->fecha_inicio, $request->fecha_fin);
        }

        // Paginación conservando parámetros GET
        $logs = $query->orderBy('creado_en', 'desc')->paginate(15)->withQueryString();

        // Obtener datos para poblar los selects del filtro
        $usuariosIds = LogAuditoria::select('usuario_id')->distinct()->whereNotNull('usuario_id')->pluck('usuario_id');
        $usuarios = Usuario::with('roles')->whereIn('id', $usuariosIds)->orderBy('nombre')->get();
        $modulos = LogAuditoria::select('modulo')->distinct()->orderBy('modulo')->pluck('modulo');
        $acciones = LogAuditoria::select('accion')->distinct()->orderBy('accion')->pluck('accion');

        return view('admin.auditoria.index', compact('logs', 'usuarios', 'modulos', 'acciones'));
    }

    public function show($id)
    {
        // Retorna JSON para ser consumido por el modal Alpine.js
        $log = LogAuditoria::with('usuario')->findOrFail($id);
        return response()->json($log);
    }
}
