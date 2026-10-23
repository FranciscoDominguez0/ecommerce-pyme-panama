<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(
        protected UsuarioService $usuarioService
    ) {}

    // Listado de usuarios filtrado por rol
    public function index(Request $request, Role $rol): View
    {
        $busqueda = trim($request->input('buscar', ''));

        $query = Usuario::whereHas('roles', function ($q) use ($rol) {
            $q->where('roles.id', $rol->id);
        });

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('unaccent(nombre) ILIKE unaccent(?)', ["%{$busqueda}%"])
                  ->orWhereRaw('unaccent(apellido) ILIKE unaccent(?)', ["%{$busqueda}%"])
                  ->orWhereRaw('unaccent(email) ILIKE unaccent(?)', ["%{$busqueda}%"]);
            });
        }

        $usuarios = $query->orderBy('creado_en', 'desc')->paginate(10)->withQueryString();
        
        return view('admin.usuarios.detalle-rol', compact('rol', 'usuarios', 'busqueda'));
    }

    // Muestra el detalle completo de un usuario
    public function show(Usuario $usuario): View
    {
        $usuario->load([
            'roles', 
            'direcciones',
            'pedidos' => function ($q) {
                $q->orderBy('creado_en', 'desc')->take(10);
            },
            'facturas' => function ($q) {
                $q->orderBy('creado_en', 'desc')->take(5);
            }
        ]);

        $metricas = $this->usuarioService->calcularMetricasUsuario($usuario);

        return view('admin.usuarios.show', array_merge(
            compact('usuario'),
            $metricas
        ));
    }

    // Muestra formulario de creación
    public function create(Role $rol): View
    {
        $roles = Role::all();
        return view('admin.usuarios.form', compact('rol', 'roles'));
    }

    // Guarda el nuevo usuario
    public function store(Request $request, Role $rol): RedirectResponse
    {
        $validados = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'telefono' => 'nullable|string|max:20',
            'rol_id' => 'required|exists:roles,id',
            'estado' => 'nullable|boolean'
        ]);

        $activo = $request->has('estado');
        $this->usuarioService->crear($validados, $activo);

        return redirect()->route('admin.usuarios.por-rol', $validados['rol_id'])
                         ->with('toast_success', 'Usuario creado correctamente y se le ha enviado un correo para establecer su contraseña.');
    }

    // Muestra formulario de edición
    public function edit(Usuario $usuario): View
    {
        $roles = Role::all();
        $rol = $usuario->roles->first();
        return view('admin.usuarios.form', compact('usuario', 'rol', 'roles'));
    }

    // Actualiza un usuario existente
    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $validados = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'password' => 'nullable|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'rol_id' => 'required|exists:roles,id',
            'estado' => 'nullable|boolean'
        ]);

        $activo = $request->has('estado');
        $this->usuarioService->actualizar($usuario, $validados, $activo);

        return redirect()->route('admin.usuarios.por-rol', $validados['rol_id'])
                         ->with('toast_success', 'Usuario actualizado correctamente.');
    }

    // Elimina un usuario con comprobaciones de seguridad
    public function destroy(Usuario $usuario): RedirectResponse
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('toast_error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($usuario->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            return back()->with('toast_error', 'No tienes permiso para eliminar un Super Administrador.');
        }

        try {
            $this->usuarioService->eliminarEnCascada($usuario);

            return redirect()->route('admin.usuarios.index')
                             ->with('toast_success', 'Usuario y todos sus registros eliminados permanentemente.');
        } catch (\Exception $e) {
            Log::error('Error eliminando usuario en cascada: ' . $e->getMessage());
            return back()->with('toast_error', 'No se pudo eliminar el usuario porque tiene registros dependientes que impiden su borrado.');
        }
    }
}
