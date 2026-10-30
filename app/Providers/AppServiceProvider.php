<?php

namespace App\Providers;

use App\Services\CarritoService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra servicios del contenedor de dependencias.
     */
    public function register(): void
    {
        $this->app->singleton(CarritoService::class);
        $this->app->singleton(\App\Services\PagoService::class);
    }

    /**
     * Configura el entorno de la aplicación al arrancar.
     */
    public function boot(): void
    {
        // Idioma global para fechas relativas en español (ej. "hace 3 minutos" en vez de "3 minutes ago")
        \Carbon\Carbon::setLocale(config('app.locale', 'es'));

        if ($this->app->runningInConsole()) {
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');
        } elseif ($this->app->environment('local')) {
            @ini_set('max_execution_time', '120');
        } else {
            // En producción forzamos HTTPS para evitar Mixed Content (iconos y CSS rotos en VPS)
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Limitadores de tasa para prevenir ataques de fuerza bruta y registros automatizados
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

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

        // Proveedor de datos para el menú móvil de clientes (categorías principales)
        \Illuminate\Support\Facades\View::composer('layouts.partials.cliente.mobile-menu', function ($view) {
            $globalCats = \App\Models\Categoria::whereNull('padre_id')->orderBy('nombre')->get();
            $view->with('globalCats', $globalCats);
        });

        // Proveedor de contadores de badges para la barra lateral administrativa (con caché breve para agilizar la navegación)
        \Illuminate\Support\Facades\View::composer('layouts.partials.admin.sidebar', function ($view) {
            $nuevosPedidosCount = \Illuminate\Support\Facades\Cache::remember('sidebar_pedidos_count', 15, function () {
                return \App\Models\Pedido::whereNull('leido_en')->count();
            });

            $nuevasDevolucionesCount = \Illuminate\Support\Facades\Cache::remember('sidebar_devoluciones_count', 15, function () {
                return \App\Models\Devolucion::where('estado', 'pendiente')->count();
            });

            $view->with([
                'nuevosPedidosCount' => $nuevosPedidosCount,
                'nuevasDevolucionesCount' => $nuevasDevolucionesCount,
            ]);
        });
    }
}
