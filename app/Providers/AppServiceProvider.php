<?php

namespace App\Providers;

use App\Services\CarritoService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra servicios del contenedor de dependencias.
     */
    public function register(): void
    {
        $this->app->singleton(CarritoService::class);
    }

    /**
     * Configura el entorno de la aplicación al arrancar.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');
        } elseif ($this->app->environment('local')) {
            @ini_set('max_execution_time', '120');
        } else {
            // En producción forzamos HTTPS para evitar Mixed Content (iconos y CSS rotos en VPS)
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Models\Usuario::observe(\App\Observers\UsuarioObserver::class);
        \App\Models\Producto::observe(\App\Observers\ProductoObserver::class);
        \App\Models\Pedido::observe(\App\Observers\PedidoObserver::class);
        \App\Models\MovimientoInventario::observe(\App\Observers\MovimientoInventarioObserver::class);
        \App\Models\VarianteProducto::observe(\App\Observers\VarianteProductoObserver::class);
        \App\Models\Role::observe(\App\Observers\RoleObserver::class);
        \App\Models\Permission::observe(\App\Observers\PermissionObserver::class);
        \App\Models\Brand::observe(\App\Observers\BrandObserver::class);
        \App\Models\Categoria::observe(\App\Observers\CategoriaObserver::class);

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                if ($event->user instanceof \App\Models\Usuario) {
                    // Actualizar silenciosamente sin disparar eventos extra que causen loops
                    \Illuminate\Support\Facades\DB::table('usuarios')
                        ->where('id', $event->user->id)
                        ->update([
                            'ultimo_login_en' => now(),
                            'ultimo_login_ip' => request()->ip()
                        ]);
                }
            }
        );
    }
}
