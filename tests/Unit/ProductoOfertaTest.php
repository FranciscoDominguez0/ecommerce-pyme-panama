<?php

namespace Tests\Unit;

use App\Models\Producto;
use Tests\TestCase;

/**
 * Pruebas unitarias de la lógica de ofertas del modelo Producto.
 * No requieren base de datos: instancian el modelo directo con atributos en memoria.
 */
class ProductoOfertaTest extends TestCase
{
    public function test_tiene_oferta_valida_cuando_todos_los_campos_son_correctos(): void
    {
        $producto = new Producto([
            'oferta_activa'    => true,
            'precio_oferta'    => 49.99,
            'oferta_inicio_en' => now()->subDay(),
            'oferta_fin_en'    => now()->addDay(),
        ]);

        $this->assertTrue($producto->tieneOfertaValida());
    }

    public function test_tiene_oferta_valida_sin_fechas_de_rango(): void
    {
        // Sin fechas de inicio/fin la oferta es indefinidamente válida.
        $producto = new Producto([
            'oferta_activa' => true,
            'precio_oferta' => 49.99,
        ]);

        $this->assertTrue($producto->tieneOfertaValida());
    }

    public function test_no_tiene_oferta_valida_cuando_ya_expiró(): void
    {
        $producto = new Producto([
            'oferta_activa' => true,
            'precio_oferta' => 49.99,
            'oferta_fin_en' => now()->subMinute(),
        ]);

        $this->assertFalse($producto->tieneOfertaValida());
    }

    public function test_no_tiene_oferta_valida_cuando_todavia_no_comienza(): void
    {
        $producto = new Producto([
            'oferta_activa'    => true,
            'precio_oferta'    => 49.99,
            'oferta_inicio_en' => now()->addDay(),
        ]);

        $this->assertFalse($producto->tieneOfertaValida());
    }

    public function test_no_tiene_oferta_valida_cuando_precio_oferta_es_cero(): void
    {
        $producto = new Producto([
            'oferta_activa' => true,
            'precio_oferta' => 0,
        ]);

        $this->assertFalse($producto->tieneOfertaValida());
    }

    public function test_no_tiene_oferta_valida_cuando_precio_oferta_es_nulo(): void
    {
        $producto = new Producto([
            'oferta_activa' => true,
            'precio_oferta' => null,
        ]);

        $this->assertFalse($producto->tieneOfertaValida());
    }

    public function test_no_tiene_oferta_valida_cuando_oferta_no_está_activa(): void
    {
        $producto = new Producto([
            'oferta_activa' => false,
            'precio_oferta' => 49.99,
        ]);

        $this->assertFalse($producto->tieneOfertaValida());
    }

    public function test_porcentaje_de_descuento_se_calcula_correctamente(): void
    {
        $producto = new Producto([
            'precio'        => 100.00,
            'oferta_activa' => true,
            'precio_oferta' => 75.00,
        ]);

        $this->assertEquals(25, $producto->porcentajeDescuentoPromocional());
    }

    public function test_porcentaje_de_descuento_es_cero_sin_oferta(): void
    {
        $producto = new Producto([
            'precio'        => 100.00,
            'oferta_activa' => false,
        ]);

        $this->assertEquals(0, $producto->porcentajeDescuentoPromocional());
    }
}
