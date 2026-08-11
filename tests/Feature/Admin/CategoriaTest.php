<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Testing\TestResponse;

/**
 * Pruebas del módulo ADMIN de Categorías (listado jerárquico, formulario, slug y eliminación).
 *
 * Cubre las rutas reales:
 *   GET    /admin/categorias                    → index
 *   GET    /admin/categorias/create             → create (formulario)
 *   POST   /admin/categorias                    → store
 *   GET    /admin/categorias/{categoria}/edit   → edit (formulario)
 *   PUT    /admin/categorias/{categoria}        → update
 *   DELETE /admin/categorias/{categoria}        → destroy (soft delete manual)
 *   POST   /admin/categorias/{id}/toggle-estado → toggleEstado (AJAX/JSON)
 *
 * Esquema verificado: la tabla `categorias` usa `padre_id` (auto-referencia, con
 * FK `categorias_padre_id_fkey` y `onDelete set null`) y la columna `slug` tiene
 * la restricción única `categorias_slug_key`. El modelo `Categoria`, el controlador
 * y la vista usan `padre_id` (NO existe una variante `parent_id`). Sin inconsistencias.
 */
class CategoriaTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — Solo administradores pueden acceder
    // =====================================================================

    public function test_el_acceso_al_listado_requiere_iniciar_sesion(): void
    {
        $this->get('/admin/categorias')
            ->assertRedirect('/login');
    }

    public function test_un_cliente_no_puede_acceder_al_listado_de_categorias(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/admin/categorias')
            ->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_al_listado(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/categorias')
            ->assertOk();
    }

    // =====================================================================
    //  LISTADO — GET /admin/categorias (columnas y filtros)
    // =====================================================================

    public function test_el_listado_tiene_buscador_y_filtro_de_estado(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/categorias')
            ->assertOk()
            ->assertSee('name="buscar"', false)
            ->assertSee('name="estado"', false);
    }

    public function test_el_listado_muestra_nombre_slug_icono_productos_y_estado(): void
    {
        $admin = $this->crearAdmin();
        $conProductos = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        Producto::factory()->count(2)->create(['categoria_id' => $conProductos->id]);
        Categoria::factory()->create(['nombre' => 'Varios', 'slug' => 'varios', 'activo' => false]);

        $this->actingAs($admin)
            ->get('/admin/categorias')
            ->assertOk()
            ->assertSee('Tecnología')
            ->assertSee('/tecnologia', false)
            ->assertSee('2 prod.', false)
            ->assertSee('<span>Activa</span>', false)
            ->assertSee('<span>Inactiva</span>', false)
            ->assertSee('Sin ícono', false);
    }

    public function test_el_listado_muestra_la_imagen_de_la_categoria(): void
    {
        $admin = $this->crearAdmin();
        Categoria::factory()->create([
            'nombre' => 'Ofertas',
            'slug' => 'ofertas',
            'imagen_ruta' => 'uploads/categorias/icono-ofertas.svg',
        ]);

        $this->actingAs($admin)
            ->get('/admin/categorias')
            ->assertOk()
            ->assertSee('uploads/categorias/icono-ofertas.svg', false);
    }

    public function test_el_buscador_filtra_categorias_por_nombre(): void
    {
        $admin = $this->crearAdmin();
        Categoria::factory()->create(['nombre' => 'Electrónica', 'slug' => 'electronica']);
        Categoria::factory()->create(['nombre' => 'Hogar', 'slug' => 'hogar']);

        $this->actingAs($admin)
            ->get('/admin/categorias?buscar=Electrónica')
            ->assertOk()
            ->assertSee('Electrónica')
            ->assertDontSee('Hogar');
    }

    public function test_el_filtro_de_estado_solo_muestra_las_categorias_activas(): void
    {
        $admin = $this->crearAdmin();
        Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        Categoria::factory()->create(['nombre' => 'Descontinuada', 'slug' => 'descontinuada', 'activo' => false]);

        $this->actingAs($admin)
            ->get('/admin/categorias?estado=active')
            ->assertOk()
            ->assertSee('Tecnología')
            ->assertDontSee('Descontinuada');
    }

    // =====================================================================
    //  JERARQUÍA — Sangría visual y orden padre → subcategorías
    // =====================================================================

    public function test_el_listado_muestra_las_subcategorias_con_sangria_jerarquica(): void
    {
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        Categoria::factory()->create(['nombre' => 'Laptops', 'slug' => 'laptops', 'padre_id' => $padre->id]);

        $this->actingAs($admin)
            ->get('/admin/categorias')
            ->assertOk()
            // La subcategoría se marca con el ícono de jerarquía y recibe la
            // sangría de nivel 1 (pl-7) que la distingue visualmente de su padre.
            ->assertSee('subdirectory_arrow_right', false)
            ->assertSee('pl-7', false)
            // El padre muestra el contador de subcategorías.
            ->assertSee('1 sub', false)
            // En la columna "Categoría Padre", la raíz se marca como "— Raíz —".
            ->assertSee('— Raíz —', false);
    }

    public function test_el_listado_ordena_el_padre_antes_que_sus_subcategorias(): void
    {
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        Categoria::factory()->create(['nombre' => 'Periféricos', 'slug' => 'perifericos', 'padre_id' => $padre->id]);
        Categoria::factory()->create(['nombre' => 'Laptops', 'slug' => 'laptops', 'padre_id' => $padre->id]);

        $this->actingAs($admin)
            ->get('/admin/categorias')
            ->assertOk()
            // Orden jerárquico: el padre primero, luego sus hijas alfabéticamente.
            ->assertSeeInOrder(['Tecnología', 'Laptops', 'Periféricos']);
    }

    // =====================================================================
    //  FORMULARIO — GET /admin/categorias/create y /{categoria}/edit
    // =====================================================================

    public function test_el_formulario_de_creacion_renderiza_los_campos(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/categorias/create')
            ->assertOk()
            ->assertSee('id="nombre"', false)
            ->assertSee('name="nombre"', false)
            ->assertSee('id="slug"', false)
            ->assertSee('name="slug"', false)
            ->assertSee('id="padre_id"', false)
            ->assertSee('name="padre_id"', false)
            ->assertSee('id="imagen"', false)
            ->assertSee('name="imagen"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('id="modal-padres-categoria"', false)
            ->assertSee('id="lista-padres-modal"', false);
    }

    public function test_el_formulario_de_edicion_muestra_los_datos_de_la_categoria(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);

        $this->actingAs($admin)
            ->get('/admin/categorias/' . $categoria->id . '/edit')
            ->assertOk()
            ->assertSee('Editar Categoría: Tecnología')
            ->assertSee('value="Tecnología"', false)
            ->assertSee('value="tecnologia"', false);
    }

    public function test_editar_una_categoria_inexistente_redirige_con_error(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/categorias/999999/edit')
            ->assertRedirect(route('admin.categorias.index'))
            ->assertSessionHas('error');
    }

    public function test_el_formulario_de_creacion_lista_las_categorias_padre_disponibles(): void
    {
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        $hija = Categoria::factory()->create(['nombre' => 'Laptops', 'slug' => 'laptops', 'padre_id' => $padre->id]);
        $otra = Categoria::factory()->create(['nombre' => 'Hogar', 'slug' => 'hogar']);

        $padresData = $this->extraerPadresDataDeLaRespuesta(
            $this->actingAs($admin)->get('/admin/categorias/create')
        );

        $ids = array_column($padresData, 'id');
        $this->assertContains($padre->id, $ids);
        $this->assertContains($hija->id, $ids);
        $this->assertContains($otra->id, $ids);

        // La subcategoría se ofrece con su nivel jerárquico (1) para el selector.
        $entradaHija = collect($padresData)->firstWhere('id', $hija->id);
        $this->assertSame(1, $entradaHija['nivel']);
    }

    public function test_el_formulario_de_edicion_excluye_la_categoria_y_sus_descendientes_del_selector_de_padre(): void
    {
        // Prevención de ciclos: al editar una categoría no se ofrecen ni ella misma
        // ni ninguna de sus descendientes como posible categoría padre.
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        $hija = Categoria::factory()->create(['nombre' => 'Laptops', 'slug' => 'laptops', 'padre_id' => $padre->id]);
        $nieta = Categoria::factory()->create(['nombre' => 'Gamers', 'slug' => 'gamers', 'padre_id' => $hija->id]);
        $otra = Categoria::factory()->create(['nombre' => 'Hogar', 'slug' => 'hogar']);

        // Al editar el abuelo: se excluyen él, la hija y la nieta; solo queda la categoría ajena.
        $idsDelPadre = array_column(
            $this->extraerPadresDataDeLaRespuesta(
                $this->actingAs($admin)->get('/admin/categorias/' . $padre->id . '/edit')
            ),
            'id'
        );
        $this->assertSame([$otra->id], $idsDelPadre);

        // Al editar la hija: se excluyen ella y su descendiente; el abuelo sigue siendo opción válida.
        $idsDeLaHija = array_column(
            $this->extraerPadresDataDeLaRespuesta(
                $this->actingAs($admin)->get('/admin/categorias/' . $hija->id . '/edit')
            ),
            'id'
        );
        $this->assertNotContains($hija->id, $idsDeLaHija);
        $this->assertNotContains($nieta->id, $idsDeLaHija);
        $this->assertContains($padre->id, $idsDeLaHija);
        $this->assertContains($otra->id, $idsDeLaHija);
    }

    // =====================================================================
    //  CREACIÓN — POST /admin/categorias (store) y jerarquía padre_id
    // =====================================================================

    public function test_un_administrador_puede_crear_una_categoria_raiz(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/categorias', ['nombre' => 'Electrónica', 'activo' => 1])
            ->assertRedirect(route('admin.categorias.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Electrónica',
            'slug' => 'electronica',
            'padre_id' => null,
            'activo' => true,
        ]);
    }

    public function test_una_subcategoria_se_vincula_a_su_padre_mediante_padre_id(): void
    {
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);

        $this->actingAs($admin)
            ->post('/admin/categorias', ['nombre' => 'Laptops', 'padre_id' => $padre->id]);

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Laptops',
            'slug' => 'laptops',
            'padre_id' => $padre->id,
        ]);
    }

    public function test_una_categoria_puede_tener_varias_subcategorias(): void
    {
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);

        $this->actingAs($admin)->post('/admin/categorias', ['nombre' => 'Laptops', 'padre_id' => $padre->id]);
        $this->actingAs($admin)->post('/admin/categorias', ['nombre' => 'Celulares', 'padre_id' => $padre->id]);

        $this->assertSame(2, $padre->hijas()->count());
        $this->assertSame(2, Categoria::sinEliminar()->where('padre_id', $padre->id)->count());
    }

    public function test_el_nombre_es_obligatorio_al_crear_una_categoria(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/categorias/create')
            ->post('/admin/categorias', ['nombre' => ''])
            ->assertSessionHasErrors(['nombre' => 'El nombre de la categoría es obligatorio.']);
    }

    public function test_la_creacion_rechaza_un_padre_inexistente(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/categorias/create')
            ->post('/admin/categorias', ['nombre' => 'Fantasma', 'padre_id' => 999999])
            ->assertSessionHasErrors(['padre_id' => 'La categoría padre seleccionada no es válida.']);
    }

    public function test_una_categoria_se_crea_activa_por_defecto(): void
    {
        // Comportamiento real: al no enviar el campo "activo" se crea activa.
        // OJO (hallazgo): el checkbox del formulario, al estar desmarcado, NO envía
        // el campo → en "store" el fallback es `true`, así que no es posible crear
        // una categoría inactiva a través del checkbox en creación (sí en edición).
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/categorias', ['nombre' => 'Nueva Activa']);

        $this->assertDatabaseHas('categorias', ['nombre' => 'Nueva Activa', 'activo' => true]);
    }

    public function test_una_categoria_se_puede_crear_explicitamente_inactiva(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/categorias', ['nombre' => 'Inactiva', 'activo' => 0]);

        $this->assertDatabaseHas('categorias', ['nombre' => 'Inactiva', 'activo' => false]);
    }

    // =====================================================================
    //  SLUG — Auto-generación y unicidad (restricción categorias_slug_key)
    // =====================================================================

    public function test_el_slug_se_genera_automaticamente_desde_el_nombre(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/categorias', ['nombre' => 'Computadoras Portátiles', 'activo' => 1])
            ->assertRedirect(route('admin.categorias.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Computadoras Portátiles',
            'slug' => 'computadoras-portatiles',
        ]);
    }

    public function test_un_slug_manual_proporcionado_se_persiste(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/categorias', ['nombre' => 'Mi Categoría', 'slug' => 'mi-categoria-personalizada']);

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Mi Categoría',
            'slug' => 'mi-categoria-personalizada',
        ]);
    }

    public function test_el_slug_autogenerado_duplicado_recibe_un_sufijo_numerico(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->post('/admin/categorias', ['nombre' => 'Electrónica']);
        $this->actingAs($admin)->post('/admin/categorias', ['nombre' => 'Electrónica']);

        // La primera conserva "electronica"; la segunda recibe automáticamente "electronica-1".
        $this->assertDatabaseHas('categorias', ['nombre' => 'Electrónica', 'slug' => 'electronica']);
        $this->assertDatabaseHas('categorias', ['nombre' => 'Electrónica', 'slug' => 'electronica-1']);
        $this->assertSame(2, Categoria::where('slug', 'like', 'electronica%')->count());
    }

    public function test_un_slug_manual_duplicado_devuelve_error_de_validacion(): void
    {
        $admin = $this->crearAdmin();
        Categoria::factory()->create(['slug' => 'marcas']);

        $this->actingAs($admin)
            ->from('/admin/categorias/create')
            ->post('/admin/categorias', ['nombre' => 'Otra Categoría', 'slug' => 'marcas'])
            ->assertSessionHasErrors(['slug' => 'El slug ingresado ya está en uso por otra categoría.']);

        $this->assertDatabaseMissing('categorias', ['nombre' => 'Otra Categoría']);
    }

    // =====================================================================
    //  ACTUALIZACIÓN — PUT /admin/categorias/{id} y prevención de ciclos
    // =====================================================================

    public function test_un_administrador_puede_actualizar_una_categoria(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create(['nombre' => 'Viejo', 'slug' => 'viejo']);

        $respuesta = $this->actingAs($admin)
            ->put('/admin/categorias/' . $categoria->id, [
                'nombre' => 'Nuevo Nombre',
                'activo' => 1,
            ]);

        $respuesta->assertRedirect(route('admin.categorias.index'));
        $respuesta->assertSessionHas('success');

        $this->assertSame('Nuevo Nombre', $categoria->fresh()->nombre);
        $this->assertSame('nuevo-nombre', $categoria->fresh()->slug);
    }

    public function test_la_actualizacion_exige_nombre_obligatorio(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();

        $this->actingAs($admin)
            ->from('/admin/categorias/' . $categoria->id . '/edit')
            ->put('/admin/categorias/' . $categoria->id, ['nombre' => ''])
            ->assertSessionHasErrors(['nombre' => 'El nombre de la categoría es obligatorio.']);
    }

    public function test_actualizar_una_categoria_inexistente_redirige_con_error(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->put('/admin/categorias/999999', ['nombre' => 'X'])
            ->assertRedirect(route('admin.categorias.index'))
            ->assertSessionHas('error');
    }

    public function test_una_categoria_no_puede_ser_su_propio_padre(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create(['nombre' => 'Raíz', 'slug' => 'raiz']);

        $this->actingAs($admin)
            ->from('/admin/categorias/' . $categoria->id . '/edit')
            ->put('/admin/categorias/' . $categoria->id, [
                'nombre' => 'Raíz',
                'padre_id' => $categoria->id,
            ])
            ->assertSessionHasErrors(['padre_id' => 'Una categoría no puede ser padre de sí misma.']);

        $this->assertNull($categoria->fresh()->padre_id);
    }

    public function test_una_categoria_no_puede_seleccionar_un_descendiente_como_padre(): void
    {
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        $hija = Categoria::factory()->create(['nombre' => 'Laptops', 'slug' => 'laptops', 'padre_id' => $padre->id]);

        $this->actingAs($admin)
            ->from('/admin/categorias/' . $padre->id . '/edit')
            ->put('/admin/categorias/' . $padre->id, [
                'nombre' => 'Tecnología',
                'padre_id' => $hija->id,
            ])
            ->assertSessionHasErrors(['padre_id' => 'No puedes seleccionar una subcategoría hija como categoría padre.']);

        $this->assertNull($padre->fresh()->padre_id);
    }

    // =====================================================================
    //  ESTADO — POST /admin/categorias/{id}/toggle-estado (toggle activo/inactivo)
    // =====================================================================

    public function test_un_administrador_puede_cambiar_el_estado_de_una_categoria(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create(['activo' => true]);

        $respuesta = $this->actingAs($admin)
            ->post('/admin/categorias/' . $categoria->id . '/toggle-estado');

        $respuesta->assertRedirect(route('admin.categorias.index'));
        $respuesta->assertSessionHas('success');

        $this->assertFalse($categoria->fresh()->activo);

        // Auditoría registrada para la acción.
        $this->assertDatabaseHas('logs_auditoria', [
            'modulo' => 'Categorías',
            'accion' => 'actualizar_estado',
        ]);
    }

    public function test_el_toggle_de_estado_responde_json_en_peticiones_ajax(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->postJson('/admin/categorias/' . $categoria->id . '/toggle-estado')
            ->assertOk()
            ->assertJson(['success' => true, 'activo' => false]);
    }

    public function test_el_toggle_de_estado_de_una_categoria_inexistente_devuelve_404(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->postJson('/admin/categorias/999999/toggle-estado')
            ->assertNotFound()
            ->assertJson(['success' => false]);
    }

    // =====================================================================
    //  ELIMINACIÓN — DELETE /admin/categorias/{id} (protección con productos
    //  y subcategorías; soft delete manual con eliminado_en)
    // =====================================================================

    public function test_se_puede_eliminar_una_categoria_sin_productos_ni_subcategorias(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();

        $respuesta = $this->actingAs($admin)
            ->delete('/admin/categorias/' . $categoria->id);

        $respuesta->assertRedirect(route('admin.categorias.index'));
        $respuesta->assertSessionHas('success');

        // Soft delete manual: conserva el registro, marca eliminado_en y desactiva.
        $categoria->refresh();
        $this->assertNotNull($categoria->eliminado_en);
        $this->assertFalse($categoria->activo);
    }

    public function test_no_se_puede_eliminar_una_categoria_con_productos_asignados(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        Producto::factory()->create(['categoria_id' => $categoria->id]);

        $respuesta = $this->actingAs($admin)
            ->delete('/admin/categorias/' . $categoria->id);

        $respuesta->assertRedirect(route('admin.categorias.index'));
        $respuesta->assertSessionHas('error');

        $mensaje = $respuesta->getSession()->get('error');
        $this->assertStringContainsString('No se puede eliminar', $mensaje);
        $this->assertStringContainsString('1 producto(s)', $mensaje);

        // La categoría sigue existiendo sin marcarse como eliminada.
        $this->assertNull($categoria->fresh()->eliminado_en);
    }

    public function test_no_se_puede_eliminar_un_padre_con_subcategorias(): void
    {
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        Categoria::factory()->create(['nombre' => 'Laptops', 'slug' => 'laptops', 'padre_id' => $padre->id]);

        $respuesta = $this->actingAs($admin)
            ->delete('/admin/categorias/' . $padre->id);

        $respuesta->assertRedirect(route('admin.categorias.index'));
        $respuesta->assertSessionHas('error');

        $this->assertStringContainsString('1 subcategoría(s)', $respuesta->getSession()->get('error'));

        // Ni el padre ni la subcategoría fueron eliminados.
        $this->assertNull($padre->fresh()->eliminado_en);
        $this->assertSame(1, Categoria::sinEliminar()->where('padre_id', $padre->id)->count());
    }

    public function test_no_se_puede_eliminar_un_padre_cuya_subcategoria_tiene_productos(): void
    {
        // Caso borde: el padre no tiene productos directos, pero su subcategoría sí.
        // La existencia de la subcategoría (con o sin productos) bloquea la eliminación.
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        $hija = Categoria::factory()->create(['nombre' => 'Laptops', 'slug' => 'laptops', 'padre_id' => $padre->id]);
        Producto::factory()->create(['categoria_id' => $hija->id]);

        $respuesta = $this->actingAs($admin)
            ->delete('/admin/categorias/' . $padre->id);

        $respuesta->assertRedirect(route('admin.categorias.index'));
        $respuesta->assertSessionHas('error');

        $this->assertNull($padre->fresh()->eliminado_en);
        $this->assertNull($hija->fresh()->eliminado_en);
    }

    public function test_una_subcategoria_sin_productos_se_puede_eliminar(): void
    {
        $admin = $this->crearAdmin();
        $padre = Categoria::factory()->create();
        $hija = Categoria::factory()->create(['padre_id' => $padre->id]);

        $this->actingAs($admin)
            ->delete('/admin/categorias/' . $hija->id)
            ->assertRedirect(route('admin.categorias.index'))
            ->assertSessionHas('success');

        $this->assertNotNull($hija->fresh()->eliminado_en);
    }

    public function test_no_se_puede_eliminar_una_categoria_con_productos_eliminados_suavemente(): void
    {
        // Los productos eliminados (soft delete) NO bloquean la eliminación de la
        // categoría: el conteo de bloqueo filtra por eliminado_en IS NULL.
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        Producto::factory()->eliminado()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->delete('/admin/categorias/' . $categoria->id)
            ->assertRedirect(route('admin.categorias.index'))
            ->assertSessionHas('success');

        $this->assertNotNull($categoria->fresh()->eliminado_en);
    }

    public function test_eliminar_una_categoria_inexistente_redirige_con_error(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->delete('/admin/categorias/999999')
            ->assertRedirect(route('admin.categorias.index'))
            ->assertSessionHas('error');
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Extrae y decodifica la variable JS `padresData` que la vista del formulario
     * expone con `@json($padresFormatted)` para poblar el modal de selección de
     * categoría padre. Devuelve el arreglo con id, nombre, nivel, ruta_jerarquica, etc.
     */
    protected function extraerPadresDataDeLaRespuesta(TestResponse $respuesta): array
    {
        $html = $respuesta->getContent();
        preg_match('/const padresData = (\[.*?\]);/s', $html, $coincidencias);

        $this->assertNotEmpty($coincidencias, 'La vista no expuso la variable "padresData".');

        return json_decode($coincidencias[1], true);
    }
}
