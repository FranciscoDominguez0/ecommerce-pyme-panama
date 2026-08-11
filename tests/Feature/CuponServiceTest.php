<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cupon;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\UsoCupon;
use App\Models\Usuario;
use App\Services\CuponService;
use Illuminate\Database\QueryException;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de la lógica de validación y registro de cupones (CuponService).
 *
 * HALLAZGOS documentados:
 *  1. Un cupón de tipo "envio_gratis" es aceptado por validarCupon pero con descuento $0:
 *     el envío gratis real se maneja aparte con PromocionEnvioGratis (evaluarEnvioGratis).
 *  2. El límite por cliente lee la tabla "usos_cupon", pero "registrarUso()" (que la
 *     alimenta) NO se invoca en ningún flujo actual de la aplicación.
 *  3. El descuento de un cupón con "aplica_a = categoria/producto" se calcula sobre el
 *     subtotal total del carrito, no solo sobre los ítems del alcance.
 */
class CuponServiceTest extends BaseAdminTest
{
    // =====================================================================
    //  VALIDACIÓN — activo, vigencia, límites y monto mínimo
    // =====================================================================

    public function test_un_cupon_valido_activo_y_vigente_es_aceptado(): void
    {
        $cupon = Cupon::factory()->create([
            'tipo' => 'porcentaje',
            'valor' => 10,
            'inicio_en' => now()->subDay(),
            'fin_en' => now()->addMonth(),
        ]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertTrue($resultado['valido']);
        $this->assertSame(10.0, $resultado['descuento']);
        $this->assertSame('Cupón aplicado exitosamente.', $resultado['mensaje']);
    }

    public function test_un_cupon_inactivo_es_rechazado(): void
    {
        $cupon = Cupon::factory()->inactivo()->create(['tipo' => 'porcentaje', 'valor' => 10]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertFalse($resultado['valido']);
        $this->assertSame('Este cupón se encuentra inactivo.', $resultado['mensaje']);
    }

    public function test_un_cupon_antes_de_su_fecha_de_inicio_es_rechazado(): void
    {
        $cupon = Cupon::factory()->noIniciado()->create(['fin_en' => now()->addMonth()]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertFalse($resultado['valido']);
        $this->assertSame('Este cupón aún no se encuentra vigente.', $resultado['mensaje']);
    }

    public function test_un_cupon_expirado_es_rechazado(): void
    {
        $cupon = Cupon::factory()->expirado()->create();

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertFalse($resultado['valido']);
        $this->assertSame('Este cupón ha expirado.', $resultado['mensaje']);
    }

    public function test_un_cupon_con_limite_total_agotado_es_rechazado(): void
    {
        $cupon = Cupon::factory()->agotado()->create(['maximo_usos_total' => 5, 'usos_actuales' => 5]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertFalse($resultado['valido']);
        $this->assertSame('Este cupón ha alcanzado el límite máximo de usos disponibles.', $resultado['mensaje']);
    }

    public function test_un_cupon_sin_limite_total_no_se_bloquea_por_usos(): void
    {
        $cupon = Cupon::factory()->create(['maximo_usos_total' => null, 'usos_actuales' => 500]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertTrue($resultado['valido']);
    }

    public function test_un_cupon_con_monto_minimo_no_alcanzado_es_rechazado(): void
    {
        $cupon = Cupon::factory()->create(['monto_minimo' => 100, 'valor' => 10]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 50.0);

        $this->assertFalse($resultado['valido']);
        $this->assertStringContainsString('El monto mínimo de compra para este cupón es de $100.00', $resultado['mensaje']);
    }

    public function test_un_cupon_con_monto_minimo_cubierto_es_aceptado(): void
    {
        $cupon = Cupon::factory()->create(['monto_minimo' => 100, 'valor' => 10]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertTrue($resultado['valido']);
    }

    public function test_un_codigo_inexistente_es_rechazado(): void
    {
        $resultado = app(CuponService::class)->validarCupon('NOEXISTE', 100.0);

        $this->assertFalse($resultado['valido']);
        $this->assertSame('El código de cupón ingresado no existe.', $resultado['mensaje']);
    }

    // =====================================================================
    //  ALCAYCE — aplica_a: categoria y producto
    // =====================================================================

    public function test_un_cupon_de_categoria_solo_aplica_si_el_carrito_contiene_esa_categoria(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Electrónica']);
        $cupon = Cupon::factory()->create(['aplica_a' => 'categoria', 'categoria_id' => $categoria->id, 'valor' => 10]);

        $servicio = app(CuponService::class);

        // El carrito contiene la categoría asignada → válido.
        $this->assertTrue($servicio->validarCupon($cupon->codigo, 100.0, null, [$categoria->id])['valido']);

        // El carrito NO contiene la categoría asignada → rechazado.
        $resultado = $servicio->validarCupon($cupon->codigo, 100.0, null, [999]);
        $this->assertFalse($resultado['valido']);
        $this->assertStringContainsString('solo aplica para productos de la categoría', $resultado['mensaje']);
    }

    public function test_un_cupon_de_producto_solo_aplica_si_el_carrito_contiene_ese_producto(): void
    {
        $producto = Producto::factory()->create();
        $cupon = Cupon::factory()->create(['aplica_a' => 'producto', 'producto_id' => $producto->id, 'valor' => 20]);

        $servicio = app(CuponService::class);

        $this->assertTrue($servicio->validarCupon($cupon->codigo, 100.0, null, [], [$producto->id])['valido']);

        $resultado = $servicio->validarCupon($cupon->codigo, 100.0, null, [], [999]);
        $this->assertFalse($resultado['valido']);
        $this->assertStringContainsString('solo aplica para el producto', $resultado['mensaje']);
    }

    // =====================================================================
    //  CÁLCULO DEL DESCUENTO — según tipo
    // =====================================================================

    public function test_el_descuento_porcentual_se_calcula_sobre_el_subtotal(): void
    {
        $cupon = Cupon::factory()->create(['tipo' => 'porcentaje', 'valor' => 15]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 200.0);

        $this->assertTrue($resultado['valido']);
        $this->assertSame(30.0, $resultado['descuento']);
    }

    public function test_el_descuento_de_monto_fijo_resta_el_valor_indicado(): void
    {
        $cupon = Cupon::factory()->montoFijo()->create(['valor' => 25]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertSame(25.0, $resultado['descuento']);
    }

    public function test_el_descuento_de_monto_fijo_no_supera_el_subtotal(): void
    {
        $cupon = Cupon::factory()->montoFijo()->create(['valor' => 200]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 50.0);

        $this->assertSame(50.0, $resultado['descuento']);
    }

    public function test_un_cupon_de_tipo_envio_gratis_no_genera_descuento_sobre_productos(): void
    {
        // Comportamiento actual: validarCupon acepta el cupón pero su descuento es $0.
        // El envío gratis se gestiona por separado vía PromocionEnvioGratis; un cupón
        // de tipo envio_gratis NO anula el costo de envío en PedidoService.
        $cupon = Cupon::factory()->envioGratis()->create(['valor' => 5]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0);

        $this->assertTrue($resultado['valido']);
        $this->assertSame(0.0, $resultado['descuento']);
    }

    public function test_el_codigo_se_normaliza_a_mayusculas_sin_espacios(): void
    {
        $cupon = Cupon::factory()->create(['codigo' => 'HOLA10']);

        $resultado = app(CuponService::class)->validarCupon('  hola10  ', 100.0);

        $this->assertTrue($resultado['valido']);
        $this->assertSame($cupon->id, $resultado['cupon']->id);
    }

    // =====================================================================
    //  LÍMITE POR CLIENTE — usos_por_cliente vs usos_cupon
    // =====================================================================

    public function test_un_cupon_de_un_solo_uso_no_se_puede_reutilizar_por_el_mismo_usuario(): void
    {
        $cliente = $this->crearCliente();
        $cupon = Cupon::factory()->create(['usos_por_cliente' => 1]);
        UsoCupon::factory()->create(['cupon_id' => $cupon->id, 'usuario_id' => $cliente->id]);

        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0, $cliente->id);

        $this->assertFalse($resultado['valido']);
        $this->assertStringContainsString('límite máximo de 1 uso', $resultado['mensaje']);
    }

    public function test_un_cupon_de_un_solo_uso_lo_puede_usar_otro_usuario(): void
    {
        $clienteUsado = $this->crearCliente();
        $otroCliente = $this->crearCliente();
        $cupon = Cupon::factory()->create(['usos_por_cliente' => 1]);
        UsoCupon::factory()->create(['cupon_id' => $cupon->id, 'usuario_id' => $clienteUsado->id]);

        // El límite es por cliente: un usuario distinto SÍ puede usarlo.
        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0, $otroCliente->id);

        $this->assertTrue($resultado['valido']);
    }

    public function test_un_cupon_con_varios_usos_por_cliente_permite_hasta_ese_limite(): void
    {
        $cliente = $this->crearCliente();
        $cupon = Cupon::factory()->create(['usos_por_cliente' => 2]);
        UsoCupon::factory()->create(['cupon_id' => $cupon->id, 'usuario_id' => $cliente->id]);

        // Un uso registrado → aún permite otro uso.
        $this->assertTrue(app(CuponService::class)->validarCupon($cupon->codigo, 100.0, $cliente->id)['valido']);

        // Dos usos registrados → bloquea el tercero.
        UsoCupon::factory()->create(['cupon_id' => $cupon->id, 'usuario_id' => $cliente->id]);
        $resultado = app(CuponService::class)->validarCupon($cupon->codigo, 100.0, $cliente->id);
        $this->assertFalse($resultado['valido']);
        $this->assertStringContainsString('límite máximo de 2 uso', $resultado['mensaje']);
    }

    // =====================================================================
    //  REGISTRO DE USO — registrarUso (incrementa usos y crea fila en usos_cupon)
    // =====================================================================

    public function test_registrar_uso_incrementa_el_contador_y_crea_el_registro_en_usos_cupon(): void
    {
        $cliente = $this->crearCliente();
        $cupon = Cupon::factory()->create(['usos_actuales' => 0]);
        $pedido = $this->crearPedidoMinimo($cliente);

        $ok = app(CuponService::class)->registrarUso($cupon->id, $cliente->id, $pedido->id, 25.50);

        $this->assertTrue($ok);
        $this->assertSame(1, $cupon->fresh()->usos_actuales);
        $this->assertDatabaseHas('usos_cupon', [
            'cupon_id' => $cupon->id,
            'usuario_id' => $cliente->id,
            'pedido_id' => $pedido->id,
            'descuento_aplicado' => '25.50',
        ]);
    }

    public function test_registrar_uso_no_permite_dos_registros_para_el_mismo_pedido(): void
    {
        // La clave única (cupon_id, pedido_id) de usos_cupon impide duplicar el uso.
        $cliente = $this->crearCliente();
        $cupon = Cupon::factory()->create(['usos_actuales' => 0]);
        $pedido = $this->crearPedidoMinimo($cliente);

        $this->assertTrue(app(CuponService::class)->registrarUso($cupon->id, $cliente->id, $pedido->id, 10.00));

        $this->expectException(QueryException::class);
        app(CuponService::class)->registrarUso($cupon->id, $cliente->id, $pedido->id, 10.00);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Crea un pedido mínimo válido para el usuario indicado (cumple las CHECK constraints).
     */
    protected function crearPedidoMinimo(Usuario $usuario): Pedido
    {
        return Pedido::create([
            'usuario_id' => $usuario->id,
            'numero_pedido' => 'PM-TEST-' . uniqid(),
            'metodo_pago' => 'contra_entrega',
            'subtotal' => 100.00,
            'descuento' => 0.00,
            'costo_envio' => 0.00,
            'itbms_monto' => 7.00,
            'total' => 107.00,
        ]);
    }
}
