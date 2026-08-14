<?php

namespace Tests\Unit;

use App\Models\Factura;
use App\Models\Pedido;
use App\Models\ReenvioFactura;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de las relaciones del modelo Factura / ReenvioFactura (FASE 15).
 *
 * NOTA: la relación real del modelo se llama `reenvios()` (no `reenviosFactura()`),
 * y es la que se verifica aquí.
 */
class FacturaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_factura_pertenece_a_un_pedido(): void
    {
        $factura = Factura::factory()->create();

        $this->assertInstanceOf(Pedido::class, $factura->pedido);
        $this->assertSame($factura->pedido_id, $factura->pedido->id);
    }

    public function test_factura_pertenece_a_un_usuario(): void
    {
        $factura = Factura::factory()->create();

        $this->assertInstanceOf(Usuario::class, $factura->usuario);
        $this->assertSame($factura->usuario_id, $factura->usuario->id);
    }

    public function test_factura_tiene_muchos_reenvios(): void
    {
        $factura = Factura::factory()->create();
        $reenvioUno = ReenvioFactura::factory()->create(['factura_id' => $factura->id]);
        $reenvioDos = ReenvioFactura::factory()->create(['factura_id' => $factura->id]);

        $reenvios = $factura->reenvios;

        $this->assertCount(2, $reenvios, 'La factura debe tener sus 2 reenvíos asociados.');
        $this->assertTrue($reenvios->contains('id', $reenvioUno->id));
        $this->assertTrue($reenvios->contains('id', $reenvioDos->id));
        $this->assertInstanceOf(ReenvioFactura::class, $reenvios->first());
    }

    public function test_reenvio_factura_pertenece_a_una_factura(): void
    {
        $factura = Factura::factory()->create();
        $reenvio = ReenvioFactura::factory()->create(['factura_id' => $factura->id]);

        $this->assertInstanceOf(Factura::class, $reenvio->factura);
        $this->assertSame($factura->id, $reenvio->factura->id);
    }
}
