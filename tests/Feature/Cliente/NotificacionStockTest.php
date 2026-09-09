<?php

namespace Tests\Feature\Cliente;

use App\Models\Categoria;
use App\Models\NotificacionStock;
use App\Models\Producto;
use Tests\TestCase;

/**
 * Pruebas del endpoint de notificación de stock agotado.
 * POST /producto/notificar-stock
 */
class NotificacionStockTest extends TestCase
{
    public function test_registra_email_para_notificacion_de_stock(): void
    {
        $categoria = Categoria::factory()->create();
        $producto  = Producto::factory()->create(['categoria_id' => $categoria->id, 'stock' => 0]);
        $email     = 'cliente_' . uniqid() . '@ejemplo.com';

        $this->postJson('/producto/notificar-stock', [
            'producto_id' => $producto->id,
            'email'       => $email,
        ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notificaciones_stock', [
            'producto_id' => $producto->id,
            'email'       => $email,
            'notificado'  => false,
        ]);
    }

    public function test_no_crea_duplicado_para_el_mismo_email_y_producto(): void
    {
        $categoria = Categoria::factory()->create();
        $producto  = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $email     = 'cliente_' . uniqid() . '@ejemplo.com';

        $payload = ['producto_id' => $producto->id, 'email' => $email];

        $this->postJson('/producto/notificar-stock', $payload)->assertOk();
        $this->postJson('/producto/notificar-stock', $payload)->assertOk();

        $this->assertEquals(1, NotificacionStock::where('producto_id', $producto->id)->where('email', $email)->count());
    }

    public function test_normaliza_el_email_a_minusculas(): void
    {
        $categoria = Categoria::factory()->create();
        $producto  = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $random    = uniqid();
        $emailEnviado = 'Cliente_' . $random . '@EJEMPLO.COM';
        $emailEsperado = 'cliente_' . strtolower($random) . '@ejemplo.com';

        $this->postJson('/producto/notificar-stock', [
            'producto_id' => $producto->id,
            'email'       => $emailEnviado,
        ])->assertOk();

        $this->assertDatabaseHas('notificaciones_stock', [
            'producto_id' => $producto->id,
            'email'       => $emailEsperado,
        ]);
    }

    public function test_cliente_ya_notificado_puede_re_suscribirse_sin_crear_duplicado(): void
    {
        $categoria = Categoria::factory()->create();
        $producto  = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $email     = 'cliente_' . uniqid() . '@ejemplo.com';

        // Simulamos que ya fue notificado previamente.
        NotificacionStock::create([
            'producto_id' => $producto->id,
            'email'       => $email,
            'notificado'  => true,
        ]);

        $this->postJson('/producto/notificar-stock', [
            'producto_id' => $producto->id,
            'email'       => $email,
        ])->assertOk();

        // No debe crear un segundo registro y debe restablecerse notificado a false
        $this->assertEquals(1, NotificacionStock::where('producto_id', $producto->id)->where('email', $email)->count());
        $this->assertDatabaseHas('notificaciones_stock', [
            'producto_id' => $producto->id,
            'email'       => $email,
            'notificado'  => false,
        ]);
    }

    public function test_falla_con_email_inválido(): void
    {
        $categoria = Categoria::factory()->create();
        $producto  = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->postJson('/producto/notificar-stock', [
            'producto_id' => $producto->id,
            'email'       => 'no-es-un-email',
        ])->assertUnprocessable();
    }

    public function test_falla_sin_producto_id(): void
    {
        $this->postJson('/producto/notificar-stock', [
            'email' => 'cliente@ejemplo.com',
        ])->assertUnprocessable();
    }

    public function test_falla_con_producto_inexistente(): void
    {
        $this->postJson('/producto/notificar-stock', [
            'producto_id' => 99999,
            'email'       => 'cliente@ejemplo.com',
        ])->assertUnprocessable();
    }
}
