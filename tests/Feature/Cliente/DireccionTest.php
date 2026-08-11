<?php

namespace Tests\Feature\Cliente;

use App\Models\Direccion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del módulo DIRECCIONES DEL CLIENTE (FASE 6).
 *
 * ARQUITECTURA VERIFICADA: tras el refactor, las operaciones de alta/edición/
 * eliminación/predeterminada viven en el componente reutilizable
 * App\Livewire\GestionDirecciones (compartido con el checkout en modo "compact").
 * El controlador (DireccionController) únicamente renderiza la vista
 * cliente/perfil/direcciones.blade.php, que monta ese componente.
 *
 * Ruta HTTP real:
 *   GET /mi-cuenta/direcciones → cliente.perfil.direcciones (DireccionController@index)
 *
 * NO existen rutas HTTP de PUT/DELETE/predeterminada: el CRUD se invoca vía métodos
 * públicos del componente Livewire (guardar, iniciarEdicion, eliminar, establecerPredeterminada).
 *
 * HALLAZGO: el esquema de "direcciones" NO tiene columna "telefono" (el teléfono vive
 * en "usuarios.telefono") y el formulario tampoco expone un campo teléfono.
 */
class DireccionTest extends BaseAdminTest
{
    // =====================================================================
    //  RUTA / VISTA — GET /mi-cuenta/direcciones
    // =====================================================================

    public function test_el_acceso_a_mis_direcciones_requiere_iniciar_sesion(): void
    {
        $this->get('/mi-cuenta/direcciones')
            ->assertRedirect('/login');
    }

    public function test_un_cliente_autenticado_puede_ver_la_pagina_de_direcciones(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get(route('cliente.perfil.direcciones'))
            ->assertOk()
            ->assertSee('Direcciones de Envío')
            ->assertSee('No tienes direcciones guardadas');
    }

    public function test_no_existen_rutas_http_para_editar_eliminar_o_marcar_predeterminada(): void
    {
        // El CRUD vive en el componente Livewire; no hay rutas HTTP directas.
        $cliente = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($cliente)
            ->put('/mi-cuenta/direcciones/' . $direccion->id, [])
            ->assertNotFound();

        $this->actingAs($cliente)
            ->delete('/mi-cuenta/direcciones/' . $direccion->id)
            ->assertNotFound();

        $this->actingAs($cliente)
            ->post('/mi-cuenta/direcciones/' . $direccion->id . '/predeterminada', [])
            ->assertNotFound();
    }

    // =====================================================================
    //  LISTADO — componente Livewire (solo direcciones del usuario)
    // =====================================================================

    public function test_el_listado_muestra_las_direcciones_del_usuario_con_su_alias(): void
    {
        $cliente = $this->crearCliente();
        Direccion::factory()->create(['usuario_id' => $cliente->id, 'alias' => 'Hogar']);
        Direccion::factory()->create(['usuario_id' => $cliente->id, 'alias' => 'Trabajo']);

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->assertSee('Hogar')
            ->assertSee('Trabajo');
    }

    public function test_un_usuario_solo_ve_sus_propias_direcciones_en_el_listado(): void
    {
        // AISLAMIENTO: el usuario A no puede ver las direcciones del usuario B.
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        Direccion::factory()->create(['usuario_id' => $usuarioA->id, 'alias' => 'Casa A']);
        Direccion::factory()->create(['usuario_id' => $usuarioB->id, 'alias' => 'Casa B']);

        Livewire::actingAs($usuarioA)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->assertSee('Casa A')
            ->assertDontSee('Casa B');
    }

    public function test_la_direccion_predeterminada_muestra_la_insignia_y_las_demas_el_boton(): void
    {
        $cliente = $this->crearCliente();
        Direccion::factory()->create(['usuario_id' => $cliente->id, 'alias' => 'Casa', 'es_predeterminada' => true]);
        Direccion::factory()->create(['usuario_id' => $cliente->id, 'alias' => 'Oficina', 'es_predeterminada' => false]);

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->assertSee('Predeterminada')
            ->assertSee('Establecer como predeterminada');
    }

    // =====================================================================
    //  FORMULARIO — campos renderizados
    // =====================================================================

    public function test_el_formulario_renderiza_los_campos_de_direccion(): void
    {
        $cliente = $this->crearCliente();

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->call('abrirNueva')
            ->assertSee('id="alias"', false)
            ->assertSee('name="nombre_receptor"', false)
            ->assertSee('name="provincia"', false)
            ->assertSee('name="distrito"', false)
            ->assertSee('name="corregimiento"', false)
            ->assertSee('id="direccion_exacta"', false)
            ->assertSee('id="referencia"', false)
            ->assertSee('id="es_predeterminada"', false);
    }

    public function test_el_formulario_no_incluye_un_campo_de_telefono(): void
    {
        // HALLAZGO: la tabla "direcciones" no tiene columna "telefono" (el teléfono vive
        // en "usuarios.telefono") y el formulario tampoco lo expone. La mención de un
        // campo "teléfono" en la especificación es un desajuste spec/esquema.
        $cliente = $this->crearCliente();

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->call('abrirNueva')
            ->assertDontSee('name="telefono"', false)
            ->assertDontSee('id="telefono"', false);
    }

    // =====================================================================
    //  CREACIÓN — guardar (nueva dirección)
    // =====================================================================

    public function test_un_usuario_puede_crear_una_direccion_vinculada_a_su_cuenta(): void
    {
        $cliente = $this->crearCliente();

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->set('alias', 'Casa')
            ->set('nombreReceptor', 'Juan Pérez')
            ->set('provincia', 'Panamá')
            ->set('distrito', 'Panamá')
            ->set('corregimiento', 'San Felipe')
            ->set('direccionExacta', 'Calle 50, Edificio Alpha, Apto 3B')
            ->set('referencia', 'Frente al parque')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('direcciones', [
            'usuario_id' => $cliente->id,
            'alias' => 'Casa',
            'nombre_receptor' => 'Juan Pérez',
            'provincia' => 'Panamá',
            'distrito' => 'Panamá',
            'corregimiento' => 'San Felipe',
            'direccion_exacta' => 'Calle 50, Edificio Alpha, Apto 3B',
            'referencia' => 'Frente al parque',
            'es_predeterminada' => false,
        ]);
    }

    public function test_la_primera_direccion_creada_no_es_predeterminada_automaticamente(): void
    {
        // Comportamiento real: es_predeterminada solo se marca si el checkbox va activo.
        $cliente = $this->crearCliente();

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->set('alias', 'Casa')
            ->set('nombreReceptor', 'Juan')
            ->set('provincia', 'Panamá')
            ->set('distrito', 'Panamá')
            ->set('corregimiento', 'San Felipe')
            ->set('direccionExacta', 'Calle 1')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('direcciones', [
            'usuario_id' => $cliente->id,
            'alias' => 'Casa',
            'es_predeterminada' => false,
        ]);
    }

    public function test_crear_una_direccion_marcada_como_predeterminada_desmarca_las_demas_del_usuario(): void
    {
        $cliente = $this->crearCliente();
        $anterior = Direccion::factory()->create(['usuario_id' => $cliente->id, 'alias' => 'Casa', 'es_predeterminada' => true]);

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->set('alias', 'Oficina')
            ->set('nombreReceptor', 'Juan')
            ->set('provincia', 'Panamá')
            ->set('distrito', 'Panamá')
            ->set('corregimiento', 'San Felipe')
            ->set('direccionExacta', 'Calle 2')
            ->set('esPredeterminada', true)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertFalse($anterior->fresh()->es_predeterminada);
        $this->assertSame(1, Direccion::where('usuario_id', $cliente->id)->where('es_predeterminada', true)->count());
    }

    // =====================================================================
    //  PREDETERMINADA — establecerPredeterminada
    // =====================================================================

    public function test_marcar_una_direccion_como_predeterminada(): void
    {
        $cliente = $this->crearCliente();
        $direccionA = Direccion::factory()->create(['usuario_id' => $cliente->id, 'alias' => 'Casa']);
        $direccionB = Direccion::factory()->create(['usuario_id' => $cliente->id, 'alias' => 'Oficina']);

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->call('establecerPredeterminada', $direccionB->id)
            ->assertHasNoErrors();

        $this->assertFalse($direccionA->fresh()->es_predeterminada);
        $this->assertTrue($direccionB->fresh()->es_predeterminada);
        $this->assertSame(1, Direccion::where('usuario_id', $cliente->id)->where('es_predeterminada', true)->count());
    }

    public function test_marcar_predeterminada_no_afecta_a_otros_usuarios(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $dirA = Direccion::factory()->create(['usuario_id' => $usuarioA->id, 'alias' => 'Casa A']);
        $dirA2 = Direccion::factory()->create(['usuario_id' => $usuarioA->id, 'alias' => 'Oficina A']);
        $dirB = Direccion::factory()->create(['usuario_id' => $usuarioB->id, 'alias' => 'Casa B', 'es_predeterminada' => true]);

        Livewire::actingAs($usuarioA)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->call('establecerPredeterminada', $dirA2->id);

        $this->assertFalse($dirA->fresh()->es_predeterminada);
        $this->assertTrue($dirA2->fresh()->es_predeterminada);
        // La dirección predeterminada del usuario B permanece intacta.
        $this->assertTrue($dirB->fresh()->es_predeterminada);
    }

    // =====================================================================
    //  ACTUALIZACIÓN — iniciarEdicion + guardar
    // =====================================================================

    public function test_un_usuario_puede_actualizar_su_propia_direccion(): void
    {
        $cliente = $this->crearCliente();
        $direccion = Direccion::factory()->create([
            'usuario_id' => $cliente->id,
            'alias' => 'Casa',
            'nombre_receptor' => 'Antiguo Receptor',
        ]);

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->call('iniciarEdicion', $direccion->id)
            ->set('alias', 'Trabajo')
            ->set('nombreReceptor', 'Nuevo Receptor')
            ->call('guardar')
            ->assertHasNoErrors();

        $direccion->refresh();
        $this->assertSame('Trabajo', $direccion->alias);
        $this->assertSame('Nuevo Receptor', $direccion->nombre_receptor);
        $this->assertSame($cliente->id, $direccion->usuario_id);
    }

    // =====================================================================
    //  ELIMINACIÓN — eliminar (soft delete con eliminado_en)
    // =====================================================================

    public function test_un_usuario_puede_eliminar_su_direccion_con_soft_delete(): void
    {
        $cliente = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id]);

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->call('eliminar', $direccion->id)
            ->assertHasNoErrors();

        $this->assertNotNull($direccion->fresh()->eliminado_en);
    }

    public function test_una_direccion_eliminada_ya_no_aparece_en_el_listado(): void
    {
        $cliente = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id, 'alias' => 'Hogar']);

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->call('eliminar', $direccion->id);

        // Nueva instancia del componente para leer el listado fresco.
        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->assertDontSee('Hogar')
            ->assertSee('No tienes direcciones guardadas');
    }

    // =====================================================================
    //  VALIDACIÓN — campos obligatorios
    // =====================================================================

    public function test_la_validacion_exige_los_campos_obligatorios(): void
    {
        $cliente = $this->crearCliente();

        Livewire::actingAs($cliente)
            ->test(\App\Livewire\GestionDirecciones::class)
            ->set('alias', '')
            ->set('nombreReceptor', '')
            ->set('provincia', '')
            ->set('distrito', '')
            ->set('corregimiento', '')
            ->set('direccionExacta', '')
            ->call('guardar')
            ->assertHasErrors([
                'alias',
                'nombreReceptor',
                'provincia',
                'distrito',
                'corregimiento',
                'direccionExacta',
            ]);
    }

    // =====================================================================
    //  AISLAMIENTO DE USUARIO — seguridad (solo el dueño puede operar)
    // =====================================================================

    public function test_un_usuario_no_puede_editar_la_direccion_de_otro_usuario(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $dirB = Direccion::factory()->create(['usuario_id' => $usuarioB->id, 'alias' => 'Casa B', 'nombre_receptor' => 'Receptor B']);

        $this->esperarNoEncontrada(function () use ($usuarioA, $dirB) {
            Livewire::actingAs($usuarioA)
                ->test(\App\Livewire\GestionDirecciones::class)
                ->call('iniciarEdicion', $dirB->id);
        });

        // Los datos del usuario B no fueron tocados.
        $this->assertSame('Receptor B', $dirB->fresh()->nombre_receptor);
    }

    public function test_un_usuario_no_puede_eliminar_la_direccion_de_otro_usuario(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $dirB = Direccion::factory()->create(['usuario_id' => $usuarioB->id]);

        $this->esperarNoEncontrada(function () use ($usuarioA, $dirB) {
            Livewire::actingAs($usuarioA)
                ->test(\App\Livewire\GestionDirecciones::class)
                ->call('eliminar', $dirB->id);
        });

        $this->assertNull($dirB->fresh()->eliminado_en);
    }

    public function test_un_usuario_no_puede_marcar_como_predeterminada_la_direccion_de_otro_usuario(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $dirB = Direccion::factory()->create(['usuario_id' => $usuarioB->id, 'es_predeterminada' => false]);

        $this->esperarNoEncontrada(function () use ($usuarioA, $dirB) {
            Livewire::actingAs($usuarioA)
                ->test(\App\Livewire\GestionDirecciones::class)
                ->call('establecerPredeterminada', $dirB->id);
        });

        $this->assertFalse($dirB->fresh()->es_predeterminada);
    }

    public function test_un_usuario_no_puede_sobrescribir_la_direccion_de_otro_usuario_via_guardar(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $dirB = Direccion::factory()->create(['usuario_id' => $usuarioB->id, 'alias' => 'Casa B', 'nombre_receptor' => 'Receptor B']);

        $this->esperarNoEncontrada(function () use ($usuarioA, $dirB) {
            Livewire::actingAs($usuarioA)
                ->test(\App\Livewire\GestionDirecciones::class)
                ->set('editandoId', $dirB->id)
                ->set('alias', 'Hackeada')
                ->set('nombreReceptor', 'Hackeada')
                ->set('provincia', 'Panamá')
                ->set('distrito', 'Panamá')
                ->set('corregimiento', 'San Felipe')
                ->set('direccionExacta', 'Calle Hack 1')
                ->call('guardar');
        });

        $dirB->refresh();
        $this->assertSame('Casa B', $dirB->alias);
        $this->assertSame('Receptor B', $dirB->nombre_receptor);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Ejecuta el callback esperando un ModelNotFoundException (firstOrFail del componente).
     */
    protected function esperarNoEncontrada(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Se esperaba ModelNotFoundException y la operación no lanzó ninguna excepción.');
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        }
    }
}
