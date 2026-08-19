<?php

namespace App\Services;

use App\Models\LogAuditoria;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class AuditoriaService
{
    /**
     * Registra una acción en el log de auditoría automáticamente capturando request actual.
     */
    public static function registrar($modulo, $accion, $descripcion, $valorAnterior = null, $valorNuevo = null)
    {
        // Soporte para procesos automáticos o consola donde no hay usuario autenticado
        $usuarioId = Auth::id(); 
        $ip = Request::ip();
        $agenteUsuario = Request::userAgent();
        
        // Truncar el user_agent si excede el límite de la base de datos
        if ($agenteUsuario && strlen($agenteUsuario) > 500) {
            $agenteUsuario = substr($agenteUsuario, 0, 497) . '...';
        }

        LogAuditoria::create([
            'usuario_id' => $usuarioId,
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo,
            'ip' => $ip,
            'agente_usuario' => $agenteUsuario,
        ]);
    }

    /**
     * Limpia datos sensibles (contraseñas, tokens) antes de serializar en log.
     */
    public static function limpiarCamposSensibles(?array $atributos)
    {
        if (!$atributos) return null;

        $camposSensibles = ['password', 'password_hash', 'remember_token', 'two_fa_secreto'];
        
        foreach ($camposSensibles as $campo) {
            if (array_key_exists($campo, $atributos)) {
                $atributos[$campo] = '********';
            }
        }
        
        return $atributos;
    }
}
