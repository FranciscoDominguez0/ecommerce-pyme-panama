<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RolController extends Controller
{
    /**
     * Muestra la pantalla principal de Roles, con contadores de usuarios y permisos.
     */
    public function index()
    {
        // Obtener los roles principales
        $roles = Role::withCount(['users', 'permissions'])->get();
        
        // Obtener usuarios recientes de cualquier rol para el panel inferior
        $usuariosRecientes = \App\Models\Usuario::with('roles')
            ->orderBy('creado_en', 'desc')
            ->take(5)
            ->get();
            
        return view('admin.usuarios.index', compact('roles', 'usuariosRecientes'));
    }

    /**
     * Muestra la configuración de permisos para un rol específico.
     */
    public function permisos(Role $rol)
    {
        // Agrupar todos los permisos por su módulo
        $modulos = Permission::all()->groupBy('modulo');
        
        // Permisos actuales del rol (array de nombres o IDs)
        $permisosRol = $rol->permissions->pluck('name')->toArray();
        
        return view('admin.usuarios.roles-permisos', compact('rol', 'modulos', 'permisosRol'));
    }

    /**
     * Actualiza los permisos de un rol específico.
     */
    public function updatePermisos(Request $request, Role $rol)
    {
        // Autorización basada en permisos
        \Illuminate\Support\Facades\Gate::authorize('admin.usuarios.editar');

        $request->validate([
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,name'
        ]);

        $permisos = $request->input('permisos', []);
        
        // Sincronizar permisos usando nombres (recomendado por Spatie)
        $rol->syncPermissions($permisos);

        return redirect()->back()->with('toast_success', 'Permisos actualizados correctamente.');
    }

    /**
     * Crea un nuevo rol.
     */
    public function store(Request $request)
    {
        // Autorización basada en permisos
        \Illuminate\Support\Facades\Gate::authorize('admin.usuarios.crear');

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Role::create([
            'nombre' => $request->name,
            'name' => $request->name,
            'descripcion' => $request->descripcion,
            'guard_name' => 'web'
        ]);

        return redirect()->back()->with('toast_success', 'Rol creado correctamente. Ya puedes asignarle permisos.');
    }

    /**
     * Actualiza un rol existente.
     */
    public function update(Request $request, Role $rol)
    {
        // Autorización basada en permisos
        \Illuminate\Support\Facades\Gate::authorize('admin.usuarios.editar');

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $rol->id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        $rol->update([
            'nombre' => $request->name,
            'name' => $request->name,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->back()->with('toast_success', 'Rol actualizado correctamente.');
    }

    /**
     * Elimina un rol.
     */
    public function destroy(Role $rol)
    {
        // Autorización basada en permisos
        \Illuminate\Support\Facades\Gate::authorize('admin.usuarios.eliminar');

        // Validar si el rol tiene usuarios asignados antes de eliminarlo
        if ($rol->users()->count() > 0) {
            return redirect()->back()->with('toast_error', 'No puedes eliminar un rol que tiene usuarios asignados. Reasigna a los usuarios primero.');
        }

        $rol->delete();

        return redirect()->route('admin.usuarios.index')->with('toast_success', 'Rol eliminado correctamente.');
    }
}
