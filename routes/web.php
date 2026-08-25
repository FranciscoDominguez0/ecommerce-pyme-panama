<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\EnvioPedidoController;
use App\Http\Controllers\Admin\DevolucionController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PedidoController as AdminPedidoController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\PromocionController;
use App\Http\Controllers\Admin\ZonaEnvioController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Admin\FacturaController as AdminFacturaController;
use App\Http\Controllers\Cliente\FacturaController as ClienteFacturaController;
use App\Http\Controllers\Cliente\CarritoController;
use App\Http\Controllers\Cliente\CatalogoController;
use App\Http\Controllers\Cliente\CheckoutController;
use App\Http\Controllers\Cliente\ListaDeseosController;
use App\Http\Controllers\Cliente\PedidoController as ClientePedidoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - eCommerce PyME Panamá
|--------------------------------------------------------------------------
*/

// 1. Rutas públicas de la Tienda (Catálogo, Producto, Bienvenida, Carrito)
Route::get('/', function () {
    $destacados = \App\Models\Producto::with(['categoria', 'imagenes', 'variantes.opciones.tipo', 'promocionesProductoDelMes'])
        ->destacados()
        ->take(8)
        ->get();

    $marcasDistribuidores = \App\Models\Brand::verified()->get();

    return view('welcome', compact('destacados', 'marcasDistribuidores'));
})->name('inicio');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('cliente.catalogo');
Route::get('/producto/{slug?}', [CatalogoController::class, 'show'])->name('cliente.producto.detalle');
Route::post('/producto/notificar-stock', [CatalogoController::class, 'solicitarNotificacionStock'])->name('cliente.producto.notificar-stock');
Route::get('/terminos-y-condiciones', function () {
    return view('paginas.terminos'); })->name('terminos');

// Carrito de Compras
Route::get('/carrito', [CarritoController::class, 'index'])->name('cliente.carrito');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('cliente.carrito.agregar');
Route::post('/carrito/actualizar/{id}', [CarritoController::class, 'actualizarCantidad'])->name('cliente.carrito.actualizar');
Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('cliente.carrito.eliminar');
Route::post('/carrito/aplicar-cupon', [CarritoController::class, 'aplicarCupon'])->name('cliente.carrito.aplicar-cupon');
Route::post('/carrito/remover-cupon', [CarritoController::class, 'removerCupon'])->name('cliente.carrito.remover-cupon');

// Checkout
Route::middleware(['auth', \App\Http\Middleware\CheckAdminPermissions::class])->group(function () {
    Route::get('/checkout/direccion', [CheckoutController::class, 'direccion'])->name('cliente.checkout.direccion');
    Route::get('/checkout/pago', [CheckoutController::class, 'pago'])->name('cliente.checkout.pago');
    Route::post('/checkout/pago', [CheckoutController::class, 'guardarPago'])->name('cliente.checkout.guardar-pago');
    Route::get('/checkout/confirmacion', [CheckoutController::class, 'confirmacion'])->name('cliente.checkout.confirmacion');
    Route::post('/checkout/confirmacion', [CheckoutController::class, 'procesar'])->name('cliente.checkout.procesar');
});

// Lista de Deseos
Route::get('/lista-deseos', [ListaDeseosController::class, 'index'])->name('cliente.lista-deseos');
Route::post('/lista-deseos/agregar/{productoId}', [ListaDeseosController::class, 'agregar'])->name('cliente.lista-deseos.agregar');
Route::post('/lista-deseos/mover-al-carrito/{productoId}', [ListaDeseosController::class, 'moverAlCarrito'])->name('cliente.lista-deseos.mover-al-carrito');
Route::delete('/lista-deseos/eliminar/{productoId}', [ListaDeseosController::class, 'eliminar'])->name('cliente.lista-deseos.eliminar');

// 2. Ruta /home para clientes autenticados (Redirección dinámica)
Route::get('/home', function () {
    $user = auth()->user();
    if ($user) {
        $isCustomer = $user->hasRole('cliente') || $user->roles->isEmpty();
        if (!$isCustomer) {
            return redirect()->route('admin.dashboard');
        }
    }
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('home');

// 3. Perfil del Cliente: Datos, Direcciones, Pedidos
Route::middleware(['auth', \App\Http\Middleware\CheckAdminPermissions::class])->prefix('mi-cuenta')->name('cliente.perfil.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Cliente\PerfilController::class, 'edit'])->name('datos');
    Route::put('/perfil', [\App\Http\Controllers\Cliente\PerfilController::class, 'update'])->name('datos.update');
    Route::post('/perfil/foto', [\App\Http\Controllers\Cliente\PerfilController::class, 'updateFoto'])->name('foto.update');
    Route::put('/password', [\App\Http\Controllers\Cliente\PerfilController::class, 'updatePassword'])->name('password.update');
    Route::put('/2fa', [\App\Http\Controllers\Cliente\PerfilController::class, 'actualizarDosFactores'])->name('2fa.update');

    Route::get('/password', function () {
        return view('cliente.perfil.password');
    })->name('password');

    Route::get('/direcciones', [\App\Http\Controllers\Cliente\DireccionController::class, 'index'])->name('direcciones');

    Route::get('/mis-pedidos', [ClientePedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/mis-pedidos/{id}', [ClientePedidoController::class, 'detalle'])->name('pedidos.detalle');
    Route::get('/mis-pedidos/{id}/devolucion', [\App\Http\Controllers\Cliente\DevolucionController::class, 'create'])->name('pedidos.devolucion.create');
    Route::post('/mis-pedidos/{id}/devolucion', [\App\Http\Controllers\Cliente\DevolucionController::class, 'store'])->name('pedidos.devolucion.store');
    
    Route::post('/mis-pedidos/{id}/confirmar-recepcion', [ClientePedidoController::class, 'confirmarRecepcion'])->name('pedidos.confirmar-recepcion');
});

Route::middleware('auth')->group(function () {
    Route::get('/mis-facturas', [ClienteFacturaController::class, 'index'])->name('cliente.facturas.index');
    Route::get('/mis-facturas/{factura}/pdf', [ClienteFacturaController::class, 'descargarPdf'])->name('cliente.facturas.pdf');
});

// 4. Panel de Cliente autenticado
Route::get('/dashboard', function () {
    $productos = \App\Models\Producto::with(['categoria', 'imagenes', 'variantes.opciones.tipo'])
        ->sinEliminar()
        ->activos()
        ->take(8)
        ->get();
    return view('dashboard', compact('productos'));
})->middleware(['auth'])->name('dashboard');

// 4. Panel de Administración: Exige autenticación y Rol de Administrador ('admin' o 'super_admin')
Route::prefix('admin')->middleware(['auth', 'role:admin|super_admin|Admin', \App\Http\Middleware\CheckAdminPermissions::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Módulo de Reportes y Estadísticas
    Route::prefix('reportes')->name('admin.reportes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReporteController::class, 'index'])->name('index');
        Route::get('/exportar-excel', [\App\Http\Controllers\Admin\ReporteController::class, 'exportarExcel'])->name('exportar-excel');
        Route::get('/exportar-pdf', [\App\Http\Controllers\Admin\ReporteController::class, 'exportarPdf'])->name('exportar-pdf');
    });

    // Módulo de Perfil Administrativo
    Route::prefix('perfil')->name('admin.perfil')->group(function () {
        Route::get('/', [PerfilController::class, 'index']);
        Route::put('/datos', [PerfilController::class, 'actualizarDatos'])->name('.datos.update');
        Route::post('/foto', [PerfilController::class, 'actualizarFoto'])->name('.foto.update');
        Route::put('/password', [PerfilController::class, 'actualizarPassword'])->name('.password.update');
        Route::put('/2fa', [PerfilController::class, 'actualizarDosFactores'])->name('.2fa.update');
        Route::delete('/sesiones', [PerfilController::class, 'cerrarSesiones'])->name('.sesiones.destroy');
    });

    // Módulo de Categorías
    Route::post('/categorias/{id}/toggle-estado', [CategoriaController::class, 'toggleEstado'])->name('admin.categorias.toggle-estado');
    Route::resource('categorias', CategoriaController::class)->names('admin.categorias');

    // Módulo de Pedidos
    Route::get('/pedidos', [AdminPedidoController::class, 'index'])->name('admin.pedidos.index');
    Route::get('/pedidos/{id}', [AdminPedidoController::class, 'detalle'])->name('admin.pedidos.detalle');
    Route::post('/pedidos/{id}/estado', [AdminPedidoController::class, 'cambiarEstado'])->name('admin.pedidos.estado');
    Route::post('/pedidos/{id}/avanzar-estado', [AdminPedidoController::class, 'avanzarEstado'])->name('admin.pedidos.avanzar-estado');
    Route::post('/pedidos/{id}/aprobar-pago', [AdminPedidoController::class, 'aprobarPago'])->name('admin.pedidos.aprobar-pago');
    Route::post('/pedidos/{id}/rechazar-pago', [AdminPedidoController::class, 'rechazarPago'])->name('admin.pedidos.rechazar-pago');
    Route::put('/pedidos/{id}/envio', [EnvioPedidoController::class, 'update'])->name('admin.pedidos.envio.update');

    // Módulo de Marcas (Brands)
    Route::resource('brands', BrandController::class)->names('admin.brands');

    // Módulo de Facturas
    Route::get('/facturas', [AdminFacturaController::class, 'index'])->name('admin.facturas.index');
    Route::get('/facturas/{factura}', [AdminFacturaController::class, 'show'])->name('admin.facturas.show');
    Route::get('/facturas/{factura}/pdf', [AdminFacturaController::class, 'descargarPdf'])->name('admin.facturas.pdf');
    Route::post('/facturas/{factura}/reenviar', [AdminFacturaController::class, 'reenviar'])->name('admin.facturas.reenviar');

    // Módulo de Inventario
    Route::prefix('inventario')->name('admin.inventario.')->group(function () {
        // Historial de movimientos (index)
        Route::get('/', [InventarioController::class, 'index'])->name('index');
        // Stock actual
        Route::get('/stock', [InventarioController::class, 'stock'])->name('stock');
        // Registrar entrada
        Route::get('/entrada', [InventarioController::class, 'entradaForm'])->name('entrada.form');
        Route::post('/entrada', [InventarioController::class, 'entrada'])->name('entrada');
        // Registrar salida
        Route::get('/salida', [InventarioController::class, 'salidaForm'])->name('salida.form');
        Route::post('/salida', [InventarioController::class, 'salida'])->name('salida');
        // Ajuste manual
        Route::get('/ajuste', [InventarioController::class, 'ajusteForm'])->name('ajuste.form');
        Route::post('/ajuste', [InventarioController::class, 'ajuste'])->name('ajuste');
        // AJAX helpers
        Route::get('/variantes/{productoId}', [InventarioController::class, 'variantesPorProducto'])->name('variantes');
        Route::get('/stock-actual/{productoId}/{varianteId?}', [InventarioController::class, 'stockProducto'])->name('stock-actual');
    });

    // Módulo de Productos y Variantes
    Route::get('/productos/exportar-excel', [ProductoController::class, 'exportarExcel'])->name('admin.productos.exportar-excel');
    Route::get('/productos/exportar-pdf', [ProductoController::class, 'exportarPdf'])->name('admin.productos.exportar-pdf');
    Route::get('/productos', [ProductoController::class, 'index'])->name('admin.productos.index');
    Route::get('/productos/crear', [ProductoController::class, 'create'])->name('admin.productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])->name('admin.productos.store');
    Route::get('/productos/{id}/editar', [ProductoController::class, 'edit'])->name('admin.productos.edit');
    Route::put('/productos/{id}', [ProductoController::class, 'update'])->name('admin.productos.update');
    Route::patch('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('admin.productos.destroy');

    // Módulo de Devoluciones
    Route::get('/devoluciones', [DevolucionController::class, 'index'])->name('admin.devoluciones.index');
    Route::post('/devoluciones/{id}/aprobar', [DevolucionController::class, 'aprobar'])->name('admin.devoluciones.aprobar');
    Route::post('/devoluciones/{id}/rechazar', [DevolucionController::class, 'rechazar'])->name('admin.devoluciones.rechazar');

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

    // Módulo de Usuarios, Roles y Permisos (FASE 18)
    Route::prefix('usuarios')->name('admin.usuarios.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RolController::class, 'index'])->name('index');
        
        // Usuarios por rol
        Route::get('/roles/{rol}', [\App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('por-rol');
        Route::get('/roles/{rol}/crear', [\App\Http\Controllers\Admin\UsuarioController::class, 'create'])->name('create');
        Route::post('/roles/{rol}', [\App\Http\Controllers\Admin\UsuarioController::class, 'store'])->name('store');
        
        // Editar usuario
        Route::get('/{usuario}/editar', [\App\Http\Controllers\Admin\UsuarioController::class, 'edit'])->name('edit');
        Route::put('/{usuario}', [\App\Http\Controllers\Admin\UsuarioController::class, 'update'])->name('update');
        Route::delete('/{usuario}', [\App\Http\Controllers\Admin\UsuarioController::class, 'destroy'])->name('destroy');
        
        // Roles (Crear y Permisos)
        Route::post('/roles', [\App\Http\Controllers\Admin\RolController::class, 'store'])->name('roles.store');
        Route::get('/roles/{rol}/permisos', [\App\Http\Controllers\Admin\RolController::class, 'permisos'])->name('roles-permisos');
        Route::put('/roles/{rol}/permisos', [\App\Http\Controllers\Admin\RolController::class, 'updatePermisos'])->name('update-permisos');
    });

    // Módulo de Configuración General
    Route::prefix('configuracion')->name('admin.configuracion.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.configuracion.general');
        })->name('index');
        
        Route::get('/general', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'general'])->name('general');
        Route::put('/general', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'guardarGeneral'])->name('general.guardar');
        
        Route::get('/pagos', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'pagos'])->name('pagos');
        Route::put('/pagos', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'guardarPagos'])->name('pagos.guardar');
        
        Route::get('/impuestos', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'impuestos'])->name('impuestos');
        Route::put('/impuestos', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'guardarImpuestos'])->name('impuestos.guardar');
    });

    // Módulo de Auditoría del Sistema
    Route::get('/auditoria', [\App\Http\Controllers\Admin\AuditoriaController::class, 'index'])->name('admin.auditoria.index');
    Route::get('/auditoria/{id}', [\App\Http\Controllers\Admin\AuditoriaController::class, 'show'])->name('admin.auditoria.show');
});

// 5. Gestión de Perfil de Usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de Autenticación (Login, Registro, Recuperación de Contraseña)
require __DIR__ . '/auth.php';
