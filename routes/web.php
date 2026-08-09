<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\PromocionController;
use App\Http\Controllers\Admin\ZonaEnvioController;
use App\Http\Controllers\Cliente\CarritoController;
use App\Http\Controllers\Cliente\CatalogoController;
use App\Http\Controllers\Cliente\ListaDeseosController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - eCommerce PyME Panamá
|--------------------------------------------------------------------------
*/

// 1. Rutas públicas de la Tienda (Catálogo, Producto, Bienvenida, Carrito)
Route::get('/', function () {
    $destacados = \App\Models\Producto::with(['categoria', 'imagenes', 'variantes'])
        ->sinEliminar()
        ->where('activo', true)
        ->where('destacado', true)
        ->take(8)
        ->get();

    $marcasDistribuidores = \App\Models\Brand::where('verified', true)
        ->where(function ($q) {
            $q->whereNotNull('image_path')->orWhereNotNull('image');
        })
        ->orderBy('is_suggested', 'desc')
        ->orderBy('name', 'asc')
        ->get();

    return view('welcome', compact('destacados', 'marcasDistribuidores'));
})->name('inicio');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('cliente.catalogo');
Route::get('/producto/{slug?}', [CatalogoController::class, 'show'])->name('cliente.producto.detalle');
Route::post('/producto/notificar-stock', [CatalogoController::class, 'solicitarNotificacionStock'])->name('cliente.producto.notificar-stock');
Route::get('/terminos-y-condiciones', function () { return view('paginas.terminos'); })->name('terminos');

// Carrito de Compras
Route::get('/carrito', [CarritoController::class, 'index'])->name('cliente.carrito');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('cliente.carrito.agregar');
Route::post('/carrito/actualizar/{id}', [CarritoController::class, 'actualizarCantidad'])->name('cliente.carrito.actualizar');
Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('cliente.carrito.eliminar');
Route::post('/carrito/aplicar-cupon', [CarritoController::class, 'aplicarCupon'])->name('cliente.carrito.aplicar-cupon');
Route::post('/carrito/remover-cupon', [CarritoController::class, 'removerCupon'])->name('cliente.carrito.remover-cupon');

// Lista de Deseos
Route::get('/lista-deseos', [ListaDeseosController::class, 'index'])->name('cliente.lista-deseos');
Route::post('/lista-deseos/agregar/{productoId}', [ListaDeseosController::class, 'agregar'])->name('cliente.lista-deseos.agregar');
Route::post('/lista-deseos/mover-al-carrito/{productoId}', [ListaDeseosController::class, 'moverAlCarrito'])->name('cliente.lista-deseos.mover-al-carrito');
Route::delete('/lista-deseos/eliminar/{productoId}', [ListaDeseosController::class, 'eliminar'])->name('cliente.lista-deseos.eliminar');

// 2. Ruta /home para clientes autenticados (Redirección directa a dashboard)
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('home');

// 3. Panel de Cliente autenticado
Route::get('/dashboard', [CatalogoController::class, 'index'])->middleware(['auth'])->name('dashboard');

// 4. Panel de Administración: Exige autenticación y Rol de Administrador ('admin' o 'super_admin')
Route::prefix('admin')->middleware(['auth', 'role:admin|super_admin|Admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Módulo de Categorías
    Route::post('/categorias/{id}/toggle-estado', [CategoriaController::class, 'toggleEstado'])->name('admin.categorias.toggle-estado');
    Route::resource('categorias', CategoriaController::class)->names('admin.categorias');

    // Módulo de Marcas (Brands)
    Route::post('/brands/{brand}/toggle-suggested', [BrandController::class, 'toggleSuggested'])->name('admin.brands.toggle-suggested');
    Route::resource('brands', BrandController::class)->names('admin.brands');

    // Módulo de Productos y Variantes
    Route::get('/productos', [ProductoController::class, 'index'])->name('admin.productos.index');
    Route::get('/productos/crear', [ProductoController::class, 'create'])->name('admin.productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])->name('admin.productos.store');
    Route::get('/productos/{id}/editar', [ProductoController::class, 'edit'])->name('admin.productos.edit');
    Route::put('/productos/{id}', [ProductoController::class, 'update'])->name('admin.productos.update');
    Route::patch('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('admin.productos.destroy');

    // Módulo de Zonas de Envío (FASE 9)
    Route::get('/configuracion/zonas-envio', [ZonaEnvioController::class, 'index'])->name('admin.zonas-envio.index');
    Route::post('/configuracion/zonas-envio', [ZonaEnvioController::class, 'store'])->name('admin.zonas-envio.store');
    Route::put('/configuracion/zonas-envio/{zonaEnvio}', [ZonaEnvioController::class, 'update'])->name('admin.zonas-envio.update');
    Route::post('/configuracion/zonas-envio/{zonaEnvio}/toggle', [ZonaEnvioController::class, 'toggle'])->name('admin.zonas-envio.toggle');
    Route::delete('/configuracion/zonas-envio/{zonaEnvio}', [ZonaEnvioController::class, 'destroy'])->name('admin.zonas-envio.destroy');

    // Módulo de Cupones y Promociones (FASE 11)
    Route::get('/promociones/cupones', [CuponController::class, 'index'])->name('admin.promociones.cupones');
    Route::get('/promociones/cupones/crear', [CuponController::class, 'create'])->name('admin.promociones.cupones.crear');
    Route::post('/promociones/cupones', [CuponController::class, 'store'])->name('admin.promociones.cupones.guardar');
    Route::get('/promociones/cupones/{id}/editar', [CuponController::class, 'edit'])->name('admin.promociones.cupones.editar');
    Route::put('/promociones/cupones/{id}', [CuponController::class, 'update'])->name('admin.promociones.cupones.actualizar');
    Route::post('/promociones/cupones/{id}/toggle', [CuponController::class, 'toggleEstado'])->name('admin.promociones.cupones.toggle');
    Route::delete('/promociones/cupones/{id}', [CuponController::class, 'destroy'])->name('admin.promociones.cupones.eliminar');

    // Promociones de Envío Gratis
    Route::get('/promociones/envio-gratis', [PromocionController::class, 'envioGratisIndex'])->name('admin.promociones.envio-gratis');
    Route::post('/promociones/envio-gratis', [PromocionController::class, 'envioGratisStore'])->name('admin.promociones.envio-gratis.guardar');
    Route::put('/promociones/envio-gratis/{id}', [PromocionController::class, 'envioGratisUpdate'])->name('admin.promociones.envio-gratis.actualizar');
    Route::post('/promociones/envio-gratis/{id}/toggle', [PromocionController::class, 'envioGratisToggle'])->name('admin.promociones.envio-gratis.toggle');
    Route::delete('/promociones/envio-gratis/{id}', [PromocionController::class, 'envioGratisDestroy'])->name('admin.promociones.envio-gratis.eliminar');

    // Producto del Mes
    Route::get('/promociones/producto-del-mes', [PromocionController::class, 'productoDelMesIndex'])->name('admin.promociones.producto-del-mes');
    Route::post('/promociones/producto-del-mes', [PromocionController::class, 'productoDelMesStore'])->name('admin.promociones.producto-del-mes.guardar');
    Route::post('/promociones/producto-del-mes/{id}/toggle', [PromocionController::class, 'productoDelMesToggle'])->name('admin.promociones.producto-del-mes.toggle');
    Route::delete('/promociones/producto-del-mes/{id}', [PromocionController::class, 'productoDelMesDestroy'])->name('admin.promociones.producto-del-mes.eliminar');
});

// 5. Gestión de Perfil de Usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de Autenticación (Login, Registro, Recuperación de Contraseña)
require __DIR__.'/auth.php';
