<?php

namespace Tests\Unit;

use App\Models\Producto;
use App\Models\Usuario;
use App\Services\CarritoService;
use Tests\Feature\Admin\BaseAdminTest;

class CalculoImpuestosTest extends BaseAdminTest
{
    /**
     * Cálculo Exacto de ITBMS (Mixto)
     */
    public function test_calculo_exacto_de_itbms_en_carrito_con_productos_mixtos(): void
    {
        $usuario = $this->crearCliente();
        
        // Producto 1: 100.00 con ITBMS (7%)
        $productoConImpuesto = Producto::factory()->create([
            'precio' => 100.00, 
            'stock' => 10,
            'aplica_itbms' => true,
        ]);
        
        // Producto 2: 50.00 sin ITBMS (0%)
        $productoSinImpuesto = Producto::factory()->create([
            'precio' => 50.00, 
            'stock' => 10,
            'aplica_itbms' => false,
        ]);

        $carritoService = app(CarritoService::class);
        $carrito = $carritoService->obtenerOCrearCarrito($usuario->id, null);
        
        // Agregar 2 unidades del primero (200.00) y 1 del segundo (50.00)
        $carritoService->agregarProducto($productoConImpuesto->id, null, 2, $usuario->id, null);
        $carritoService->agregarProducto($productoSinImpuesto->id, null, 1, $usuario->id, null);

        // Refresh carrito relationships because agregarProducto does it in a separate instance
        $carrito->refresh();

        // Subtotal = 250.00. Base Imponible ITBMS = 200.00. Impuesto = 14.00. Total = 264.00.
        $this->assertEquals(250.00, $carritoService->calcularSubtotal($carrito));
        $this->assertEquals(14.00, $carritoService->calcularSubtotalEItbms($carrito)['itbms']);
        $this->assertEquals(264.00, $carritoService->calcularTotal($carrito)['total']);
    }
}
