<?php

namespace Tests\Feature\Cliente;

use App\Models\Carrito;
use App\Models\ItemCarrito;
use App\Models\ListaDeseos;
use App\Models\Producto;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del módulo LISTA DE DESEOS (FASE 10).
 *
 * Cubre las rutas reales:
 *   GET    /lista-deseos                        → index
 *   POST   /lista-deseos/agregar/{productoId}   → agregar
 *   POST   /lista-deseos/mover-al-carrito/{productoId} → moverAlCarrito
 *   DELETE /lista-deseos/eliminar/{productoId}  → eliminar
 *
 * Esquema verificado: clave primaria compuesta (usuario_id, producto_id) — no hay
 * duplicados por usuario. La lista de deseos está ligada al usuario (sin fallback
 * de sesión), por lo que los invitados son redirigidos al login.
 */
class ListaDeseosTest extends BaseAdminTest
{
    // =====================================================================
    //  ACCESO — invitados
    // =====================================================================

    public function test_un_invitado_es_redirigido_al_login_para_ver_la_lista_de_deseos(): void
    {
        $this->get('/lista-deseos')
            ->assertRedirect(route('login'));
    }

    public function test_un_invitado_es_redirigido_al_login_al_agregar_un_producto(): void
    {
        $producto = Producto::factory()->create();

        $this->post('/lista-deseos/agregar/' . $producto->id)
            ->assertRedirect(route('login'));
    }

    public function test_un_invitado_recibe_401_por_ajax_al_agregar_un_producto(): void
    {
        $producto = Producto::factory()->create();

        $this->postJson('/lista-deseos/agregar/' . $producto->id)
            ->assertStatus(401)
            ->assertJson(['exito' => false, 'requiere_auth' => true]);
    }

    // =====================================================================
    //  AGREGAR
    // =====================================================================

    public function test_un_usuario_puede_agregar_un_producto_a_su_lista_de_deseos(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create();

        $this->actingAs($usuario)
            ->postJson('/lista-deseos/agregar/' . $producto->id)
            ->assertOk()
            ->assertJson(['exito' => true]);

        $this->assertDatabaseHas('lista_deseos', [
            'usuario_id' => $usuario->id,
            'producto_id' => $producto->id,
        ]);
    }

    public function test_no_agrega_un_producto_duplicado_gracias_a_la_clave_primaria_compuesta(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create();

        $this->actingAs($usuario)->postJson('/lista-deseos/agregar/' . $producto->id)->assertOk();
        $this->actingAs($usuario)->postJson('/lista-deseos/agregar/' . $producto->id)->assertOk();

        $this->assertSame(1, ListaDeseos::where('usuario_id', $usuario->id)->where('producto_id', $producto->id)->count());
    }

    public function test_no_permite_agregar_un_producto_inexistente(): void
    {
        $usuario = $this->crearCliente();

        $this->actingAs($usuario)
            ->postJson('/lista-deseos/agregar/999999')
            ->assertStatus(404)
            ->assertJson(['exito' => false]);
    }

    // =====================================================================
    //  LISTADO — solo los productos del usuario
    // =====================================================================

    public function test_la_pagina_muestra_solo_los_productos_del_usuario_autenticado(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $productoA = Producto::factory()->create(['nombre' => 'Producto Exclusivo A']);
        $productoB = Producto::factory()->create(['nombre' => 'Producto Exclusivo B']);

        ListaDeseos::factory()->create(['usuario_id' => $usuarioA->id, 'producto_id' => $productoA->id]);
        ListaDeseos::factory()->create(['usuario_id' => $usuarioB->id, 'producto_id' => $productoB->id]);

        $this->actingAs($usuarioA)
            ->get('/lista-deseos')
            ->assertOk()
            ->assertSee('Producto Exclusivo A')
            ->assertDontSee('Producto Exclusivo B');
    }

    public function test_la_pagina_muestra_el_estado_vacio_cuando_no_hay_productos(): void
    {
        $usuario = $this->crearCliente();

        $this->actingAs($usuario)
            ->get('/lista-deseos')
            ->assertOk()
            ->assertSee('Tu lista de deseos está vacía');
    }

    // =====================================================================
    //  MOVER AL CARRITO
    // =====================================================================

    public function test_mover_un_producto_al_carrito_lo_elimina_de_la_lista_de_deseos(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        ListaDeseos::factory()->create(['usuario_id' => $usuario->id, 'producto_id' => $producto->id]);

        $this->actingAs($usuario)
            ->post('/lista-deseos/mover-al-carrito/' . $producto->id)
            ->assertRedirect(route('cliente.carrito'))
            ->assertSessionHas('success');

        // Ambos efectos: el producto queda en el carrito del usuario y sale de la lista.
        $carrito = Carrito::where('usuario_id', $usuario->id)->first();
        $this->assertNotNull($carrito);
        $this->assertSame(1, $carrito->items()->where('producto_id', $producto->id)->count());
        $this->assertDatabaseMissing('lista_deseos', [
            'usuario_id' => $usuario->id,
            'producto_id' => $producto->id,
        ]);
    }

    public function test_no_mueve_al_carrito_un_producto_agotado_y_conserva_la_lista(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 0]);
        ListaDeseos::factory()->create(['usuario_id' => $usuario->id, 'producto_id' => $producto->id]);

        $this->actingAs($usuario)
            ->post('/lista-deseos/mover-al-carrito/' . $producto->id)
            ->assertRedirect()
            ->assertSessionHas('error');

        // El producto no se agregó al carrito y permanece en la lista de deseos.
        $this->assertSame(0, ItemCarrito::count());
        $this->assertDatabaseHas('lista_deseos', [
            'usuario_id' => $usuario->id,
            'producto_id' => $producto->id,
        ]);
    }

    // =====================================================================
    //  QUITAR — DELETE /lista-deseos/eliminar/{productoId}
    // =====================================================================

    public function test_quitar_un_producto_de_la_lista_no_afecta_el_carrito(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        ListaDeseos::factory()->create(['usuario_id' => $usuario->id, 'producto_id' => $producto->id]);

        // El producto también está en el carrito.
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        $item = ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 1]);

        $this->actingAs($usuario)
            ->delete('/lista-deseos/eliminar/' . $producto->id)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('lista_deseos', [
            'usuario_id' => $usuario->id,
            'producto_id' => $producto->id,
        ]);
        // El item del carrito permanece intacto.
        $this->assertDatabaseHas('items_carrito', ['id' => $item->id]);
    }
}
