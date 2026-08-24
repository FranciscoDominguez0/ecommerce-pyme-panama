<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActualizarPerfilRequest;
use App\Http\Requests\Admin\ActualizarPasswordRequest;
use App\Models\LogAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    /**
     * Muestra la vista del perfil con la actividad reciente.
     */
    public function index()
    {
        $usuario = auth()->user();
        
        // Obtener los últimos 5 logs del usuario
        $actividadReciente = LogAuditoria::where('usuario_id', $usuario->id)
            ->orderBy('creado_en', 'desc')
            ->take(5)
            ->get();

        $sesionesActivas = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $usuario->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'is_current_device' => $session->id === request()->session()->getId(),
                    'last_active' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->locale('es')->diffForHumans(),
                    'agent' => $this->crearAgente($session->user_agent)
                ];
            });

        return view('admin.perfil.index', compact('usuario', 'actividadReciente', 'sesionesActivas'));
    }

    /**
     * Actualiza los datos personales del usuario.
     */
    public function actualizarDatos(ActualizarPerfilRequest $request)
    {
        $usuario = auth()->user();
        $datosValidados = $request->validated();

        $valorAnterior = $usuario->only(['nombre', 'apellido', 'telefono', 'fecha_nacimiento']);
        
        $usuario->update($datosValidados);

        $this->registrarAuditoria(
            $usuario->id,
            'Perfil',
            'Datos actualizados',
            'Se actualizaron los datos personales del perfil',
            $valorAnterior,
            $datosValidados
        );

        return redirect()->route('admin.perfil')->with('toast_success', 'Datos personales actualizados correctamente.');
    }

    /**
     * Actualiza la foto de perfil.
     */
    public function actualizarFoto(Request $request)
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], [
            'foto.required' => 'Debe seleccionar una imagen.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        $usuario = auth()->user();
        
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($usuario->foto_perfil_ruta && Storage::disk('public')->exists(str_replace('storage/', '', $usuario->foto_perfil_ruta))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $usuario->foto_perfil_ruta));
            }

            $ruta = $request->file('foto')->store('perfiles', 'public');
            $rutaCompleta = 'storage/' . $ruta;

            $usuario->update(['foto_perfil_ruta' => $rutaCompleta]);

            $this->registrarAuditoria(
                $usuario->id,
                'Perfil',
                'Foto actualizada',
                'Se actualizó la foto de perfil',
                null,
                ['foto_perfil_ruta' => $rutaCompleta]
            );
        }

        return redirect()->route('admin.perfil')->with('toast_success', 'Foto de perfil actualizada correctamente.');
    }

    /**
     * Actualiza la contraseña del usuario.
     */
    public function actualizarPassword(ActualizarPasswordRequest $request)
    {
        $usuario = auth()->user();
        
        $usuario->update([
            'password_hash' => Hash::make($request->password)
        ]);

        $this->registrarAuditoria(
            $usuario->id,
            'Perfil',
            'Contraseña actualizada',
            'El usuario ha cambiado su contraseña de acceso',
            null,
            null
        );

        return redirect()->route('admin.perfil')->with('toast_success', 'Contraseña actualizada correctamente.');
    }

    /**
     * Habilita o deshabilita la Autenticación de Dos Factores (2FA).
     */
    public function actualizarDosFactores(Request $request)
    {
        $request->validate([
            'two_fa_habilitado' => 'boolean'
        ]);

        $usuario = auth()->user();
        $habilitar = $request->input('two_fa_habilitado', false);
        
        $usuario->update([
            'two_fa_habilitado' => $habilitar
        ]);

        $accion = $habilitar ? '2FA activado' : '2FA desactivado';
        
        $this->registrarAuditoria(
            $usuario->id,
            'Perfil',
            $accion,
            "El usuario ha $accion la autenticación de dos factores",
            ['two_fa_habilitado' => !$habilitar],
            ['two_fa_habilitado' => $habilitar]
        );

        return redirect()->route('admin.perfil')->with('toast_success', "Autenticación de Dos Factores " . ($habilitar ? 'activada' : 'desactivada') . ".");
    }

    /**
     * Cierra todas las sesiones en otros dispositivos.
     */
    public function cerrarSesiones(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        \Illuminate\Support\Facades\Auth::logoutOtherDevices($request->password);

        $usuario = auth()->user();
        $this->registrarAuditoria(
            $usuario->id,
            'Perfil',
            'Sesiones cerradas',
            'Se cerraron las sesiones activas en otros dispositivos',
            null,
            null
        );

        return back()->with('toast_success', 'Se han cerrado las sesiones en otros dispositivos.');
    }

    /**
     * Parsea de manera sencilla el User Agent.
     */
    private function crearAgente($userAgent)
    {
        $browser = 'Desconocido';
        $platform = 'Desconocido';
        $icon = 'devices';

        if (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
            $icon = 'desktop_windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
            $icon = 'desktop_mac';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
            $icon = 'computer';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
            $icon = 'phone_iphone';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
            $icon = 'phone_android';
        }

        if (preg_match('/edge/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        return (object) ['platform' => $platform, 'browser' => $browser, 'icon' => $icon];
    }

    /**
     * Método auxiliar para registrar en logs_auditoria
     */
    private function registrarAuditoria($usuarioId, $modulo, $accion, $descripcion, $valorAnterior = null, $valorNuevo = null)
    {
        LogAuditoria::create([
            'usuario_id' => $usuarioId,
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo,
            'ip' => request()->ip(),
            'agente_usuario' => request()->userAgent(),
        ]);
    }
}
