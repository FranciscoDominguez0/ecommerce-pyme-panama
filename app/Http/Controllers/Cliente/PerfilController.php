<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Direccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function edit(): View
    {
        $usuario = Auth::user();

        $direccionPredeterminada = Direccion::where('usuario_id', $usuario->id)
            ->where('es_predeterminada', true)
            ->whereNull('eliminado_en')
            ->first();

        return view('cliente.perfil.datos', compact('usuario', 'direccionPredeterminada'));
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'eliminar_foto' => 'boolean',
        ]);

        if ($request->boolean('eliminar_foto')) {
            if ($usuario->foto_perfil_ruta && file_exists(public_path($usuario->foto_perfil_ruta))) {
                unlink(public_path($usuario->foto_perfil_ruta));
            }
            $usuario->foto_perfil_ruta = null;
        } elseif ($request->hasFile('foto_perfil')) {
            if ($usuario->foto_perfil_ruta && file_exists(public_path($usuario->foto_perfil_ruta))) {
                unlink(public_path($usuario->foto_perfil_ruta));
            }
            $archivo = $request->file('foto_perfil');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $ruta = 'uploads/avatars/' . $usuario->id;
            $archivo->move(public_path($ruta), $nombreArchivo);
            $usuario->foto_perfil_ruta = $ruta . '/' . $nombreArchivo;
        }

        $usuario->fill($validated);

        if ($usuario->isDirty('email')) {
            $usuario->email_verified_at = null;
        }

        $usuario->save();

        return redirect()->route('cliente.perfil.datos')->with('toast_success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($usuario) {
                if (!Hash::check($value, $usuario->password_hash)) {
                    $fail('La contraseña actual no es correcta.');
                }
            }],
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $usuario->update([
            'password_hash' => Hash::make($request->password),
        ]);

        Auth::guard('web')->login($usuario);

        return redirect()->route('cliente.perfil.datos')->with('toast_success', 'Contraseña actualizada correctamente.');
    }
}
