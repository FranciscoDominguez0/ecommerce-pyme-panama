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

        return view('admin.perfil.index', compact('usuario', 'actividadReciente'));
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
