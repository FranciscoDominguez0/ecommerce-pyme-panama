<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\CarritoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CarritoTest extends TestCase
{
    /**
     * Prueba el cálculo de totales y desglose con ITBMS.
     */
    public function test_calculo_totales_con_itbms(): void
    {
        $service = app(CarritoService::class);
        $sesionId = 'test-session-' . uniqid();

        // 1. Obtener producto existente en base de datos
        $producto = Producto::sinEliminar()->where('activo', true)->where('stock', '>', 5)->first();
        if (!$producto) {
            $this->markTestSkipped('No hay productos disponibles para prueba.');
        }

        $res = $service->agregarProducto($producto->id, null, 2, null, $sesionId);
        $this->assertTrue($res['exito']);

        $carrito = $service->obtenerOCrearCarrito(null, $sesionId);
        $this->assertEquals(1, $carrito->items->count());
        $this->assertEquals(2, $carrito->cantidad_total);

        $totales = $service->calcularTotal($carrito, 5.00);
        $this->assertGreaterThan(0, $totales['total']);
        $this->assertEquals(5.00, $totales['envio']);

        // Limpiar
        $carrito->items()->delete();
        $carrito->delete();
    }

    /**
     * Prueba la validación de stock disponible.
     */
    public function test_validacion_stock_insuficiente(): void
    {
        $service = app(CarritoService::class);
        $sesionId = 'test-session-stock-' . uniqid();

        $producto = Producto::sinEliminar()->where('activo', true)->where('stock', '>', 0)->first();
        if (!$producto) {
            $this->markTestSkipped('No hay productos disponibles para prueba.');
        }

        $cantidadExcesiva = $producto->stock + 50;
        $res = $service->agregarProducto($producto->id, null, $cantidadExcesiva, null, $sesionId);

        $this->assertFalse($res['exito']);
        $this->assertStringContainsString('No es posible agregar', $res['mensaje']);
    }

    /**
     * Prueba la fusión de carritos de sesión y usuario.
     */
    public function test_fusion_carritos_sesion_y_usuario(): void
    {
        $service = app(CarritoService::class);
        $sesionId = 'guest-session-' . uniqid();

        $producto = Producto::sinEliminar()->where('activo', true)->where('stock', '>', 5)->first();
        if (!$producto) {
            $this->markTestSkipped('No hay productos disponibles para prueba.');
        }

        // Agregar al carrito de visitante
        $service->agregarProducto($producto->id, null, 1, null, $sesionId);

        // Crear o buscar usuario de prueba
        $usuario = Usuario::first();
        if (!$usuario) {
            $this->markTestSkipped('No hay usuarios registrados.');
        }

        // Fusionar carritos
        $carritoUsuario = $service->fusionarCarritos($sesionId, $usuario->id);

        $this->assertNotNull($carritoUsuario);
        $this->assertEquals($usuario->id, $carritoUsuario->usuario_id);

        // Verificar que el carrito de sesión fue eliminado
        $carritoSesion = Carrito::where('sesion_id', $sesionId)->first();
        $this->assertNull($carritoSesion);
    }
}
