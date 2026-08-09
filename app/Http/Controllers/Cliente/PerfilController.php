<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function edit(): View
    {
        $usuario = Auth::user();

        return view('cliente.perfil.datos', compact('usuario'));
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:20',
        ]);

        $usuario->fill($validated);

        if ($usuario->isDirty('email')) {
            $usuario->email_verified_at = null;
        }

        $usuario->save();

        return redirect()->route('cliente.perfil.datos')->with('toast_success', 'Perfil actualizado correctamente.');
    }
}
