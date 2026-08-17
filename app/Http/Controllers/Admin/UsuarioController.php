<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Listado de usuarios filtrado por rol.
     */
    public function index(Role $rol)
    {
        $usuarios = Usuario::whereHas('roles', function($q) use ($rol) {
            $q->where('roles.id', $rol->id);
        })->orderBy('creado_en', 'desc')->paginate(10);
        
        return view('admin.usuarios.detalle-rol', compact('rol', 'usuarios'));
    }

    /**
     * Muestra formulario de creación.
     */
    public function create(Role $rol)
    {
        $roles = Role::all();
        return view('admin.usuarios.form', compact('rol', 'roles'));
    }

    /**
     * Guarda el nuevo usuario.
     */
    public function store(Request $request, Role $rol)
    {
        // Protección: Solo un Superadmin puede crear otro Superadmin
        if ($request->rol_id) {
            $nuevoRol = Role::find($request->rol_id);
            if ($nuevoRol && $nuevoRol->name === 'Superadmin' && !auth()->user()->hasRole('Superadmin')) {
                return back()->withInput()->with('toast_error', 'No tienes permiso para asignar el rol Superadmin.');
            }
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'rol_id' => 'required|exists:roles,id',
            'estado' => 'nullable|boolean'
        ]);

        $usuario = new Usuario();
        $usuario->nombre = $validated['nombre'];
        $usuario->apellido = $validated['apellido'] ?? null;
        $usuario->email = $validated['email'];
        $usuario->password_hash = Hash::make($validated['password']);
        $usuario->telefono = $validated['telefono'] ?? null;
        
        $usuario->activo = $request->has('estado');
        
        $usuario->save();

        $rolAsignar = Role::findOrFail($validated['rol_id']);
        $usuario->assignRole($rolAsignar->name);

        return redirect()->route('admin.usuarios.por-rol', $rolAsignar->id)
                         ->with('toast_success', 'Usuario creado correctamente.');
    }

    /**
     * Muestra formulario de edición.
     */
    public function edit(Usuario $usuario)
    {
        $roles = Role::all();
        $rol = $usuario->roles->first();
        return view('admin.usuarios.form', compact('usuario', 'rol', 'roles'));
    }

    /**
     * Actualiza un usuario existente.
     */
    public function update(Request $request, Usuario $usuario)
    {
        // Protección: No dejar modificar un Superadmin a menos que sea Superadmin
        if ($usuario->hasRole('Superadmin') && !auth()->user()->hasRole('Superadmin')) {
            return back()->with('toast_error', 'No tienes permiso para modificar un Superadmin.');
        }

        // Protección: Evitar asignar Superadmin
        if ($request->rol_id) {
            $nuevoRol = Role::find($request->rol_id);
            if ($nuevoRol && $nuevoRol->name === 'Superadmin' && !auth()->user()->hasRole('Superadmin')) {
                return back()->withInput()->with('toast_error', 'No tienes permiso para asignar el rol Superadmin.');
            }
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'password' => 'nullable|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'rol_id' => 'required|exists:roles,id',
            'estado' => 'nullable|boolean'
        ]);

        $usuario->nombre = $validated['nombre'];
        $usuario->apellido = $validated['apellido'] ?? null;
        $usuario->email = $validated['email'];
        if (!empty($validated['password'])) {
            $usuario->password_hash = Hash::make($validated['password']);
        }
        $usuario->telefono = $validated['telefono'] ?? null;
        
        $usuario->activo = $request->has('estado');
        
        $usuario->save();

        $rolAsignar = Role::findOrFail($validated['rol_id']);
        $usuario->syncRoles([$rolAsignar->name]);

        return redirect()->route('admin.usuarios.por-rol', $rolAsignar->id)
                         ->with('toast_success', 'Usuario actualizado correctamente.');
    }
}
