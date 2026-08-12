<?php

namespace Tests\Unit;

use App\Models\Direccion;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\CarritoService;
use App\Services\PedidoService;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Admin\BaseAdminTest;

class DescuentoInventarioTest extends BaseAdminTest
{
    /**
     * Descuento de Inventario Preciso
     */
    public function test_descuento_de_inventario_preciso_al_confirmar_pedido(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carritoService = app(CarritoService::class);
        $carrito = $carritoService->obtenerOCrearCarrito($usuario->id, null);
        
        // Agregamos 3 unidades
        $carritoService->agregarProducto($producto->id, null, 3, $usuario->id, null);
        $carrito->refresh();

        // Convertir carrito a pedido
        app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'transferencia', null, null, null);

        // Verificar stock de producto (Debería quedar 7)
        $producto->refresh();
        $this->assertEquals(7, $producto->stock);
        
        // Verificar registro de auditoría en movimientos_inventario
        $movimiento = DB::table('movimientos_inventario')
            ->where('producto_id', $producto->id)
            ->where('tipo', 'salida')
            ->first();
            
        $this->assertNotNull($movimiento);
        $this->assertEquals(3, $movimiento->cantidad);
        $this->assertEquals(10, $movimiento->stock_antes);
        $this->assertEquals(7, $movimiento->stock_despues);
    }

    /**
     * Prevención de venta sin stock suficiente
     */
    public function test_prevencion_de_venta_sin_stock_suficiente(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 5]);
        $carritoService = app(CarritoService::class);
        
        // Intentar agregar 6 unidades (stock es 5)
        $resultado = $carritoService->agregarProducto($producto->id, null, 6, $usuario->id, null);
        
        $this->assertFalse($resultado['exito']);
        $this->assertStringContainsString('Solo quedan 5 unidades disponibles en stock.', $resultado['mensaje']);
        
        // El stock del producto debe permanecer intacto
        $producto->refresh();
        $this->assertEquals(5, $producto->stock);
    }
}
