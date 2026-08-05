<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - eCommerce PyME Panamá
|--------------------------------------------------------------------------
*/

// 1. Ruta pública principal (Catálogo / Tienda / Bienvenida)
Route::get('/', function () {
    return view('welcome');
});

// 2. Ruta /home para clientes autenticados (Redirección directa a dashboard)
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('home');

// 3. Panel de Cliente autenticado
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// 4. Panel de Administración: Exige autenticación y Rol de Administrador ('admin' o 'super_admin')
// Si un Cliente intenta entrar directamente a /admin/dashboard, recibe un error 403 (Prohibido).
Route::prefix('admin')->middleware(['auth', 'role:admin|super_admin|Admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});

// 5. Gestión de Perfil de Usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de Autenticación (Login, Registro, Recuperación de Contraseña)
require __DIR__.'/auth.php';
