<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Cliente\CatalogoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - eCommerce PyME Panamá
|--------------------------------------------------------------------------
*/

// 1. Rutas públicas de la Tienda (Catálogo, Producto, Bienvenida)
Route::get('/', function () {
    $destacados = \App\Models\Producto::with(['categoria', 'imagenes', 'variantes'])
        ->sinEliminar()
        ->where('activo', true)
        ->where('destacado', true)
        ->take(8)
        ->get();
    return view('welcome', compact('destacados'));
})->name('inicio');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('cliente.catalogo');
Route::get('/producto/{slug?}', [CatalogoController::class, 'show'])->name('cliente.producto.detalle');
Route::post('/producto/notificar-stock', [CatalogoController::class, 'solicitarNotificacionStock'])->name('cliente.producto.notificar-stock');

// 2. Ruta /home para clientes autenticados (Redirección directa a dashboard)
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('home');

// 3. Panel de Cliente autenticado
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

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
});

// 5. Gestión de Perfil de Usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de Autenticación (Login, Registro, Recuperación de Contraseña)
require __DIR__.'/auth.php';
