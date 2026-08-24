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

    /**
     * Elimina un usuario.
     */
    public function destroy(Usuario $usuario)
    {
        // Protección: No permitir que el usuario logueado se elimine a sí mismo
        if ($usuario->id === auth()->id()) {
            return back()->with('toast_error', 'No puedes eliminar tu propia cuenta.');
        }

        // Protección: No dejar eliminar a un Superadmin a menos que sea Superadmin
        if ($usuario->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            return back()->with('toast_error', 'No tienes permiso para eliminar un Super Administrador.');
        }

        try {
            \DB::transaction(function() use ($usuario) {
                $userId = $usuario->id;

                // Eliminar facturas y sus reenvíos para evitar constraints
                $facturasIds = \DB::table('facturas')->where('usuario_id', $userId)->pluck('id');
                if ($facturasIds->isNotEmpty()) {
                    \DB::table('reenvios_factura')->whereIn('factura_id', $facturasIds)->delete();
                    \DB::table('facturas')->where('usuario_id', $userId)->delete();
                }

                // Eliminar devoluciones
                \DB::table('devoluciones')->where('usuario_id', $userId)->delete();

                // Eliminar usos de cupones
                if (\Illuminate\Support\Facades\Schema::hasTable('usos_cupon')) {
                    \DB::table('usos_cupon')->where('usuario_id', $userId)->delete();
                }

                // Eliminar pedidos (cascade borrará items_pedido y envios_pedido)
                \DB::table('pedidos')->where('usuario_id', $userId)->delete();

                // Borrar usuario (direcciones, carritos, lista_deseos se borran por cascade en BD)
                $usuario->delete();
            });

            // Si venimos de la vista de editar, no podemos volver atrás con back() porque dará 404
            return redirect()->route('admin.usuarios.index')->with('toast_success', 'Usuario y todos sus registros eliminados permanentemente.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error eliminando usuario en cascada: ' . $e->getMessage());
            return back()->with('toast_error', 'No se pudo eliminar el usuario porque tiene registros dependientes en otras tablas del sistema que no se pueden borrar automáticamente.');
        }
    }
}
