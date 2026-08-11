<?php

namespace Tests\Feature\Cliente;

use App\Models\Carrito;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Services\CarritoService;
use Illuminate\Support\Str;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del módulo CARRITO (FASE 10) — rutas HTTP y lógica de CarritoService.
 *
 * Cubre las rutas reales:
 *   GET    /carrito                → index (renderiza el widget CarritoWidget)
 *   POST   /carrito/agregar        → agregar
 *   POST   /carrito/actualizar/{id}→ actualizarCantidad
 *   DELETE /carrito/eliminar/{id}  → eliminar
 *   POST   /carrito/aplicar-cupon  → aplicarCupon
 *
 * Esquema verificado: "carritos" permite carritos de invitado (sesion_id) por la
 * CHECK constraint "carrito_owner"; "items_carrito" tiene unique en
 * (carrito_id, producto_id, variante_producto_id) y CHECKs cantidad > 0 y
 * precio_unitario >= 0.
 *
 * HALLAZGO: las rutas HTTP de actualizar/eliminar NO validan la propiedad del item
 * (CarritoService busca por id sin filtrar por usuario). El widget Livewire sí filtra.
 */
class CarritoTest extends BaseAdminTest
{
    // =====================================================================
    //  ACCESO — invitados y autenticados
    // =====================================================================

    public function test_el_carrito_es_accesible_para_invitados(): void
    {
        $this->get('/carrito')
            ->assertOk()
            ->assertSee('Tu carrito está vacío');
    }

    public function test_el_carrito_es_accesible_para_usuarios_autenticados(): void
    {
        $usuario = $this->crearCliente();

        $this->actingAs($usuario)
            ->get('/carrito')
            ->assertOk();
    }

    // =====================================================================
    //  AGREGAR — POST /carrito/agregar
    // =====================================================================

    public function test_un_usuario_puede_agregar_un_producto_al_carrito(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 25.00, 'stock' => 10]);

        $this->actingAs($usuario)
            ->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 2])
            ->assertOk()
            ->assertJson(['exito' => true]);

        $carrito = Carrito::where('usuario_id', $usuario->id)->first();
        $this->assertNotNull($carrito);
        $this->assertDatabaseHas('items_carrito', [
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => '25.00',
        ]);
    }

    public function test_un_invitado_puede_agregar_un_producto_con_carrito_de_sesion(): void
    {
        $producto = Producto::factory()->create(['stock' => 5]);

        $this->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 1])
            ->assertOk();

        // Por la CHECK "carrito_owner" el carrito de invitado se guarda con sesion_id.
        $carrito = Carrito::whereNull('usuario_id')->whereNotNull('sesion_id')->first();
        $this->assertNotNull($carrito);
        $this->assertDatabaseHas('items_carrito', [
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ]);
    }

    public function test_no_permite_agregar_mas_cantidad_de_la_que_hay_en_stock(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 3]);

        $this->actingAs($usuario)
            ->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 4])
            ->assertStatus(422)
            ->assertJson(['exito' => false]);

        $this->assertDatabaseMissing('items_carrito', ['producto_id' => $producto->id]);
    }

    public function test_se_puede_agregar_exactamente_hasta_el_stock_disponible(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 3]);

        $this->actingAs($usuario)
            ->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 3])
            ->assertOk();

        $this->assertDatabaseHas('items_carrito', ['producto_id' => $producto->id, 'cantidad' => 3]);
    }

    public function test_no_permite_agregar_un_producto_agotado(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 0]);

        $this->actingAs($usuario)
            ->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 1])
            ->assertStatus(422)
            ->assertJson(['exito' => false]);
    }

    public function test_no_permite_agregar_un_producto_inactivo(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->inactivo()->create(['stock' => 5]);

        $this->actingAs($usuario)
            ->postJson('/carrito/agregar', ['producto_id' => $producto->id])
            ->assertStatus(422)
            ->assertJson(['exito' => false]);
    }

    public function test_agregar_el_mismo_producto_incrementa_la_cantidad_del_item_existente(): void
    {
        // Respeta la restricción UNIQUE (carrito_id, producto_id, variante_producto_id):
        // no crea filas duplicadas, sino que suma la cantidad.
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($usuario)->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 2])->assertOk();
        $this->actingAs($usuario)->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 3])->assertOk();

        $this->assertSame(1, ItemCarrito::where('producto_id', $producto->id)->count());
        $this->assertDatabaseHas('items_carrito', ['producto_id' => $producto->id, 'cantidad' => 5]);
    }

    // =====================================================================
    //  PRECIO CONGELADO
    // =====================================================================

    public function test_el_precio_unitario_queda_congelado_al_momento_de_agregar(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);

        $this->actingAs($usuario)
            ->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 1])
            ->assertOk();

        // El precio del producto cambia DESPUÉS de agregarlo al carrito.
        $producto->update(['precio' => 250.00]);

        $item = ItemCarrito::where('producto_id', $producto->id)->first();
        $this->assertSame('100.00', $item->precio_unitario); // precio congelado
    }

    // =====================================================================
    //  ACTUALIZAR CANTIDAD — POST /carrito/actualizar/{id}
    // =====================================================================

    public function test_actualizar_la_cantidad_persiste_en_la_base_de_datos(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        $item = ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($usuario)
            ->postJson('/carrito/actualizar/' . $item->id, ['cantidad' => 5])
            ->assertOk()
            ->assertJson(['exito' => true]);

        $this->assertSame(5, $item->fresh()->cantidad);
    }

    public function test_no_permite_actualizar_a_una_cantidad_mayor_al_stock(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 3]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        $item = ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($usuario)
            ->postJson('/carrito/actualizar/' . $item->id, ['cantidad' => 4])
            ->assertStatus(422);

        $this->assertSame(2, $item->fresh()->cantidad);
    }

    public function test_se_puede_fijar_la_cantidad_exacta_al_stock_disponible(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 3]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        $item = ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 1]);

        $this->actingAs($usuario)
            ->postJson('/carrito/actualizar/' . $item->id, ['cantidad' => 3])
            ->assertOk();

        $this->assertSame(3, $item->fresh()->cantidad);
    }

    public function test_al_fijar_la_cantidad_en_cero_se_elimina_el_item(): void
    {
        // Comportamiento real: actualizarCantidad con cantidad <= 0 elimina el item
        // (la CHECK items_carrito_cantidad_check exige cantidad > 0; no se guarda 0).
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        $item = ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($usuario)
            ->postJson('/carrito/actualizar/' . $item->id, ['cantidad' => 0])
            ->assertOk();

        $this->assertDatabaseMissing('items_carrito', ['id' => $item->id]);
    }

    public function test_al_fijar_una_cantidad_negativa_se_elimina_el_item(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        $item = ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($usuario)
            ->postJson('/carrito/actualizar/' . $item->id, ['cantidad' => -3])
            ->assertOk();

        $this->assertDatabaseMissing('items_carrito', ['id' => $item->id]);
    }

    // =====================================================================
    //  ELIMINAR — DELETE /carrito/eliminar/{id}
    // =====================================================================

    public function test_eliminar_un_item_del_carrito(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        $item = ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($usuario)
            ->deleteJson('/carrito/eliminar/' . $item->id)
            ->assertOk()
            ->assertJson(['exito' => true]);

        $this->assertDatabaseMissing('items_carrito', ['id' => $item->id]);
    }

    // =====================================================================
    //  PERSISTENCIA — carrito en la base de datos (no en la sesión PHP)
    // =====================================================================

    public function test_el_carrito_del_usuario_se_recupera_desde_la_base_de_datos(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['nombre' => 'Teclado Persistente', 'stock' => 10]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 1, 'precio_unitario' => 15.00]);

        // Nueva petición con "sesión vacía": los items salen de la BD por usuario_id.
        $this->actingAs($usuario)
            ->get('/carrito')
            ->assertOk()
            ->assertSee('Teclado Persistente')
            ->assertSee('$15.00', false);
    }

    // =====================================================================
    //  FUSIÓN DE CARRITO DE INVITADO → USUARIO (login)
    // =====================================================================

    public function test_fusionar_carritos_une_el_carrito_de_sesion_al_carrito_del_usuario(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $carritoSesion = Carrito::factory()->paraSesion('sesion-merge-test')->create();
        ItemCarrito::factory()->create(['carrito_id' => $carritoSesion->id, 'producto_id' => $producto->id, 'cantidad' => 3]);

        $carritoUsuario = app(CarritoService::class)->fusionarCarritos('sesion-merge-test', $usuario->id);

        $this->assertSame($usuario->id, $carritoUsuario->usuario_id);
        $this->assertSame(3, $carritoUsuario->items()->where('producto_id', $producto->id)->first()->cantidad);
        // El carrito temporal de la sesión se elimina.
        $this->assertDatabaseMissing('carritos', ['id' => $carritoSesion->id]);
    }

    public function test_el_carrito_de_invitado_se_fusiona_al_iniciar_sesion(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 25.00, 'stock' => 10]);

        // 1. El invitado agrega un producto (el middleware asigna un sesion_id).
        $this->postJson('/carrito/agregar', ['producto_id' => $producto->id, 'cantidad' => 2])
            ->assertOk();

        $carritoSesion = Carrito::whereNull('usuario_id')->whereNotNull('sesion_id')->first();
        $this->assertNotNull($carritoSesion);
        $this->assertSame(2, $carritoSesion->items()->first()->cantidad);
        $sesionIdInvitado = $carritoSesion->sesion_id;

        // 2. El invitado inicia sesión manteniendo la misma sesión (misma cookie).
        $this->withCookie(session()->getName(), $sesionIdInvitado);

        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard'));

        // 3. El item ahora pertenece al carrito del usuario y el de sesión fue eliminado.
        $carritoUsuario = Carrito::where('usuario_id', $usuario->id)->first();
        $this->assertNotNull($carritoUsuario);
        $itemUsuario = $carritoUsuario->items()->where('producto_id', $producto->id)->first();
        $this->assertNotNull($itemUsuario);
        $this->assertSame(2, $itemUsuario->cantidad);
        $this->assertDatabaseMissing('carritos', ['id' => $carritoSesion->id]);
    }

    // =====================================================================
    //  SEGURIDAD (IDOR) — un usuario no puede operar items del carrito ajeno
    // =====================================================================

    public function test_un_usuario_no_puede_actualizar_la_cantidad_de_un_item_de_otro_usuario(): void
    {
        // Usuario A NO puede modificar un item del carrito del usuario B vía HTTP.
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 50]);
        $carritoB = Carrito::factory()->create(['usuario_id' => $usuarioB->id]);
        $itemB = ItemCarrito::factory()->create(['carrito_id' => $carritoB->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($usuarioA)
            ->postJson('/carrito/actualizar/' . $itemB->id, ['cantidad' => 9])
            ->assertStatus(422)
            ->assertJson(['exito' => false]);

        // El item del usuario B permanece intacto.
        $this->assertSame(2, $itemB->fresh()->cantidad);
    }

    public function test_un_usuario_no_puede_eliminar_un_item_de_otro_usuario(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 50]);
        $carritoB = Carrito::factory()->create(['usuario_id' => $usuarioB->id]);
        $itemB = ItemCarrito::factory()->create(['carrito_id' => $carritoB->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($usuarioA)
            ->deleteJson('/carrito/eliminar/' . $itemB->id)
            ->assertStatus(422)
            ->assertJson(['exito' => false]);

        $this->assertDatabaseHas('items_carrito', ['id' => $itemB->id]);
    }

    public function test_un_usuario_si_puede_operar_sus_propios_items_despues_del_fix(): void
    {
        // Las operaciones legítimas del dueño siguen funcionando tras el fix.
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 50]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        $item = ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->actingAs($usuario)
            ->postJson('/carrito/actualizar/' . $item->id, ['cantidad' => 5])
            ->assertOk()
            ->assertJson(['exito' => true]);

        $this->assertSame(5, $item->fresh()->cantidad);

        $this->actingAs($usuario)
            ->deleteJson('/carrito/eliminar/' . $item->id)
            ->assertOk()
            ->assertJson(['exito' => true]);

        $this->assertDatabaseMissing('items_carrito', ['id' => $item->id]);
    }

    public function test_la_sesion_de_un_invitado_no_puede_operar_items_de_otra_sesion(): void
    {
        // Los carritos de invitado (sesion_id) también quedan protegidos.
        $sesionA = 'A' . Str::random(39);
        $sesionB = 'B' . Str::random(39);
        $producto = Producto::factory()->create(['stock' => 50]);
        $carritoSesionB = Carrito::factory()->paraSesion($sesionB)->create();
        $itemB = ItemCarrito::factory()->create(['carrito_id' => $carritoSesionB->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        // El invitado de la sesión A intenta modificar un item del carrito de la sesión B.
        // Se habilita withCredentials para que postJson/deleteJson envíen la cookie de sesión.
        $this->withCredentials = true;
        $nombreCookie = session()->getName();
        session()->setId($sesionA);
        $this->withCookie($nombreCookie, $sesionA);

        $this->postJson('/carrito/actualizar/' . $itemB->id, ['cantidad' => 9])
            ->assertStatus(422)
            ->assertJson(['exito' => false]);

        $this->deleteJson('/carrito/eliminar/' . $itemB->id)
            ->assertStatus(422)
            ->assertJson(['exito' => false]);

        $this->assertSame(2, $itemB->fresh()->cantidad);
        $this->assertDatabaseHas('items_carrito', ['id' => $itemB->id]);
    }

    public function test_el_invitado_dueno_puede_operar_su_propio_carrito_de_sesion(): void
    {
        $sesionDueno = 'D' . Str::random(39);
        $producto = Producto::factory()->create(['stock' => 50]);
        $carritoSesion = Carrito::factory()->paraSesion($sesionDueno)->create();
        $item = ItemCarrito::factory()->create(['carrito_id' => $carritoSesion->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

        $this->withCredentials = true;
        $nombreCookie = session()->getName();
        session()->setId($sesionDueno);
        $this->withCookie($nombreCookie, $sesionDueno);

        $this->postJson('/carrito/actualizar/' . $item->id, ['cantidad' => 7])
            ->assertOk()
            ->assertJson(['exito' => true]);

        $this->assertSame(7, $item->fresh()->cantidad);
    }
}
