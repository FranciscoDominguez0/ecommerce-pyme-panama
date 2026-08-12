<?php

namespace Tests\Unit;

use App\Models\Cupon;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\CarritoService;
use Tests\Feature\Admin\BaseAdminTest;

class CalculoDescuentosTest extends BaseAdminTest
{
    /**
     * Cálculo de Descuentos (Porcentaje vs Fijo)
     */
    public function test_calculo_de_descuentos_por_porcentaje_y_monto_fijo(): void
    {
        $usuario = $this->crearCliente();
        $carritoService = app(CarritoService::class);
        
        $producto = Producto::factory()->create(['precio' => 200.00, 'stock' => 10, 'aplica_itbms' => true]);
        
        // --- Escenario 1: Porcentaje ---
        $cuponPorcentaje = Cupon::factory()->create([
            'codigo' => 'DESC10',
            'tipo' => 'porcentaje',
            'valor' => 10, // 10%
            'monto_minimo' => 0
        ]);

        $carrito = $carritoService->obtenerOCrearCarrito($usuario->id, null);
        $carritoService->agregarProducto($producto->id, null, 1, $usuario->id, null); // 200.00
        $carrito->refresh();
        
        $resultadoPorcentaje = $carritoService->aplicarCupon($carrito, 'DESC10', $usuario->id);
        
        $this->assertTrue($resultadoPorcentaje['valido']);
        $this->assertEquals(20.00, $resultadoPorcentaje['descuento']); // 10% de 200 = 20
        $carrito->refresh();
        
        // El ITBMS en este sistema se calcula sobre el subtotal bruto (200.00 => ITBMS = 14.00)
        $this->assertEquals(14.00, $carritoService->calcularSubtotalEItbms($carrito)['itbms']);

        // --- Escenario 2: Fijo ---
        $carritoService->removerCupon($carrito);
        $carrito->refresh();
        $cuponFijo = Cupon::factory()->create([
            'codigo' => 'FIJO50',
            'tipo' => 'monto_fijo',
            'valor' => 50, // $50 exactos
            'monto_minimo' => 0
        ]);
        
        $resultadoFijo = $carritoService->aplicarCupon($carrito, 'FIJO50', $usuario->id);
        
        $this->assertTrue($resultadoFijo['valido']);
        $this->assertEquals(50.00, $resultadoFijo['descuento']);
        $carrito->refresh();
        
        // El ITBMS en este sistema se calcula sobre el subtotal bruto (200.00 => ITBMS = 14.00)
        $this->assertEquals(14.00, $carritoService->calcularSubtotalEItbms($carrito)['itbms']);
    }
}
