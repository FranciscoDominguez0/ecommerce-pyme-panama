<?php

namespace App\Providers;

use App\Services\CarritoService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CarritoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');
        } elseif ($this->app->environment('local')) {
            @ini_set('max_execution_time', '120');
        } else {
            // Forzar HTTPS en producción (VPS) para arreglar Mixed Content (iconos y CSS rotos)
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
    }
}
