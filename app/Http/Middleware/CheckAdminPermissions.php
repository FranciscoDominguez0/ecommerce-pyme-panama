<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminPermissions
{
    /**
     * Mapeo de rutas a permisos de Spatie.
     * Si una ruta está aquí, se verificará el permiso.
     */
    protected $routePermissions = [
        'admin.dashboard' => 'admin.dashboard',
        
        // Categorías
        'admin.categorias.index' => 'admin.categorias.ver',
        'admin.categorias.show' => 'admin.categorias.ver',
        'admin.categorias.create' => 'admin.categorias.crear',
        'admin.categorias.store' => 'admin.categorias.crear',
        'admin.categorias.edit' => 'admin.categorias.editar',
        'admin.categorias.update' => 'admin.categorias.editar',
        'admin.categorias.toggle-estado' => 'admin.categorias.editar',
        'admin.categorias.destroy' => 'admin.categorias.eliminar',

        // Marcas
        'admin.brands.index' => 'admin.marcas.ver',
        'admin.brands.show' => 'admin.marcas.ver',
        'admin.brands.create' => 'admin.marcas.crear',
        'admin.brands.store' => 'admin.marcas.crear',
        'admin.brands.edit' => 'admin.marcas.editar',
        'admin.brands.update' => 'admin.marcas.editar',
        'admin.brands.destroy' => 'admin.marcas.eliminar',

        // Productos
        'admin.productos.index' => 'admin.productos.ver',
        'admin.productos.create' => 'admin.productos.crear',
        'admin.productos.store' => 'admin.productos.crear',
        'admin.productos.edit' => 'admin.productos.editar',
        'admin.productos.update' => 'admin.productos.editar',
        'admin.productos.destroy' => 'admin.productos.eliminar',
        
        // Pedidos
        'admin.pedidos.index' => 'admin.pedidos.ver',
        'admin.pedidos.detalle' => 'admin.pedidos.ver',
        'admin.pedidos.estado' => 'admin.pedidos.gestionar',
        'admin.pedidos.avanzar-estado' => 'admin.pedidos.gestionar',
        'admin.pedidos.aprobar-pago' => 'admin.pedidos.gestionar',
        'admin.pedidos.rechazar-pago' => 'admin.pedidos.gestionar',
        'admin.pedidos.envio.update' => 'admin.pedidos.gestionar',
        
        // Zonas de envío
        'admin.zonas-envio.index' => 'admin.zonas.ver',
        'admin.zonas-envio.store' => 'admin.zonas.gestionar',
        'admin.zonas-envio.update' => 'admin.zonas.gestionar',
        'admin.zonas-envio.toggle' => 'admin.zonas.gestionar',
        'admin.zonas-envio.destroy' => 'admin.zonas.gestionar',

        // Cupones
        'admin.promociones.cupones' => 'admin.cupones.ver',
        'admin.promociones.cupones.crear' => 'admin.cupones.gestionar',
        'admin.promociones.cupones.guardar' => 'admin.cupones.gestionar',
        'admin.promociones.cupones.editar' => 'admin.cupones.gestionar',
        'admin.promociones.cupones.actualizar' => 'admin.cupones.gestionar',
        'admin.promociones.cupones.toggle' => 'admin.cupones.gestionar',
        'admin.promociones.cupones.eliminar' => 'admin.cupones.gestionar',

        // Promociones Envío Gratis
        'admin.promociones.envio-gratis' => 'admin.promociones.ver',
        'admin.promociones.envio-gratis.guardar' => 'admin.promociones.gestionar',
        'admin.promociones.envio-gratis.actualizar' => 'admin.promociones.gestionar',
        'admin.promociones.envio-gratis.toggle' => 'admin.promociones.gestionar',
        'admin.promociones.envio-gratis.eliminar' => 'admin.promociones.gestionar',

        // Promociones Producto del Mes
        'admin.promociones.producto-del-mes' => 'admin.promociones.ver',
        'admin.promociones.producto-del-mes.guardar' => 'admin.promociones.gestionar',
        'admin.promociones.producto-del-mes.toggle' => 'admin.promociones.gestionar',
        'admin.promociones.producto-del-mes.eliminar' => 'admin.promociones.gestionar',
        
        // Usuarios y Roles
        'admin.usuarios.index' => 'admin.usuarios.gestionar',
        'admin.usuarios.por-rol' => 'admin.usuarios.gestionar',
        'admin.usuarios.create' => 'admin.usuarios.gestionar',
        'admin.usuarios.store' => 'admin.usuarios.gestionar',
        'admin.usuarios.edit' => 'admin.usuarios.gestionar',
        'admin.usuarios.update' => 'admin.usuarios.gestionar',
        'admin.usuarios.roles.store' => 'admin.usuarios.gestionar',
        'admin.usuarios.roles-permisos' => 'admin.usuarios.gestionar',
        'admin.usuarios.update-permisos' => 'admin.usuarios.gestionar',

        // Cliente: Perfil y Pedidos
        'cliente.perfil.datos' => 'cliente.perfil.ver',
        'cliente.perfil.datos.update' => 'cliente.perfil.editar',
        'cliente.perfil.foto.update' => 'cliente.perfil.editar',
        'cliente.perfil.password' => 'cliente.perfil.editar',
        'cliente.perfil.password.update' => 'cliente.perfil.editar',
        'cliente.perfil.direcciones' => 'cliente.perfil.ver',
        'cliente.perfil.pedidos.index' => 'cliente.pedidos.ver',
        'cliente.perfil.pedidos.detalle' => 'cliente.pedidos.ver',

        // Cliente: Checkout
        'cliente.checkout.direccion' => 'cliente.checkout',
        'cliente.checkout.pago' => 'cliente.checkout',
        'cliente.checkout.guardar-pago' => 'cliente.checkout',
        'cliente.checkout.confirmacion' => 'cliente.checkout',
        'cliente.checkout.procesar' => 'cliente.checkout',

        // Cliente: Lista de deseos
        'cliente.lista-deseos' => 'cliente.lista-deseos',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Los Superadmins tienen bypass general de permisos porque interceptamos el Gate
        // en AppServiceProvider. Aún así, la validación se delega a can().
        
        $routeName = $request->route()->getName();
        
        if ($routeName && isset($this->routePermissions[$routeName])) {
            $permission = $this->routePermissions[$routeName];
            if (!Auth::user()->can($permission)) {
                abort(403, 'No tienes permisos (' . $permission . ') para realizar esta acción.');
            }
        }

        return $next($request);
    }
}
