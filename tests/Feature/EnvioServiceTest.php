<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Direccion;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\ZonaEnvio;
use App\Services\EnvioService;
use App\Services\PedidoService;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del cálculo de envío (EnvioService) y su aplicación al pedido (PedidoService).
 *
 * HALLAZGOS documentados en estas pruebas:
 *  1. EnvioService coincide contra el "nombre" de la zona, NO contra la columna
 *     "provincias" (aunque el esquema la tenga).
 *  2. Sin zona coincidente o con zona inactiva, el costo de envío es $0.00 (sin error).
 *  3. El checkout no deriva la zona automáticamente desde la provincia de la dirección:
 *     el usuario la elige en el Livewire GestionDirecciones y se guarda en sesión.
 *     El costo termina aplicándose al pedido vía PedidoService::calcularTotales()
 *     y PedidoService::crearDesdeCarrito().
 *  4. PedidoService::calcularTotales NO verifica que la zona recibida esté "activa".
 */
class EnvioServiceTest extends BaseAdminTest
{
    // =====================================================================
    //  EnvioService — Resolución de zona por provincia
    // =====================================================================

    public function test_obtiene_la_zona_por_coincidencia_exacta_del_nombre(): void
    {
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00, 'activo' => true]);
        $servicio = app(EnvioService::class);

        $this->assertSame($zona->id, $servicio->obtenerZonaPorProvincia('Panamá')->id);
        $this->assertTrue($servicio->esZonaActiva('Panamá'));
        $this->assertSame(5.0, $servicio->obtenerCostoEnvio('Panamá'));
    }

    public function test_la_busqueda_es_insensible_a_mayusculas_y_espacios(): void
    {
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Chiriquí', 'costo' => 4.00, 'activo' => true]);
        $servicio = app(EnvioService::class);

        $this->assertSame($zona->id, $servicio->obtenerZonaPorProvincia('  CHIRIQUÍ  ')->id);
    }

    public function test_obtiene_la_zona_por_coincidencia_parcial_del_nombre(): void
    {
        // Coincidencia parcial: la zona se llama "Chiriquí (David / Boquete)" y se
        // busca la provincia "Chiriquí".
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Chiriquí (David / Boquete)', 'costo' => 4.00, 'activo' => true]);
        $servicio = app(EnvioService::class);

        $this->assertSame($zona->id, $servicio->obtenerZonaPorProvincia('Chiriquí')->id);
        $this->assertSame(4.0, $servicio->obtenerCostoEnvio('Chiriquí'));
    }

    public function test_las_provincias_individuales_se_configuran_como_zonas_separadas(): void
    {
        // Modelo real soportado: cada provincia se registra como una zona con su propio
        // "nombre". La columna "provincias" (que podría listar varias por zona) no se usa.
        ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 3.00, 'activo' => true]);
        ZonaEnvio::factory()->create(['nombre' => 'Panamá Oeste', 'costo' => 6.00, 'activo' => true]);

        $servicio = app(EnvioService::class);

        $this->assertSame(3.0, $servicio->obtenerCostoEnvio('Panamá'));
        $this->assertSame(6.0, $servicio->obtenerCostoEnvio('Panamá Oeste'));
    }

    public function test_el_servicio_coincide_por_el_nombre_y_no_por_la_columna_provincias(): void
    {
        // HALLAZGO: aunque la tabla tiene "provincias" (texto, varias provincias por zona),
        // el servicio solo compara contra el "nombre"; la columna "provincias" se ignora.
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Centro', 'costo' => 4.00, 'activo' => true]);
        $zona->forceFill(['provincias' => 'Panamá, Panamá Oeste'])->save();

        $servicio = app(EnvioService::class);

        // "Panamá" está listada en "provincias" pero no coincide con el nombre "Centro".
        $this->assertNull($servicio->obtenerZonaPorProvincia('Panamá'));
        $this->assertSame(0.0, $servicio->obtenerCostoEnvio('Panamá'));
    }

    // =====================================================================
    //  EnvioService — Sin coincidencia, inactivas y valores vacíos
    // =====================================================================

    public function test_sin_zona_coincidente_el_costo_de_envio_es_cero(): void
    {
        $servicio = app(EnvioService::class);

        $this->assertNull($servicio->obtenerZonaPorProvincia('Darién'));
        $this->assertFalse($servicio->esZonaActiva('Darién'));
        // Fallback real: $0.00 (sin error ni "sin envío disponible").
        $this->assertSame(0.0, $servicio->obtenerCostoEnvio('Darién'));
    }

    public function test_una_zona_inactiva_no_se_usa_para_el_costo_de_envio(): void
    {
        $zona = ZonaEnvio::factory()->inactiva()->create(['nombre' => 'Panamá', 'costo' => 5.00]);
        $servicio = app(EnvioService::class);

        // La zona se encuentra, pero al estar inactiva no se aplica su costo.
        $this->assertSame($zona->id, $servicio->obtenerZonaPorProvincia('Panamá')->id);
        $this->assertFalse($servicio->esZonaActiva('Panamá'));
        $this->assertSame(0.0, $servicio->obtenerCostoEnvio('Panamá'));
    }

    public function test_provincia_vacia_o_nula_devuelve_sin_zona(): void
    {
        ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00, 'activo' => true]);
        $servicio = app(EnvioService::class);

        $this->assertNull($servicio->obtenerZonaPorProvincia(''));
        $this->assertNull($servicio->obtenerZonaPorProvincia(null));
        $this->assertFalse($servicio->esZonaActiva(''));
        $this->assertSame(0.0, $servicio->obtenerCostoEnvio(''));
    }

    // =====================================================================
    //  Checkout — Desde la provincia de la dirección hasta el costo del pedido
    // =====================================================================

    public function test_desde_la_provincia_de_la_direccion_se_resuelve_el_costo_de_envio(): void
    {
        $cliente = $this->crearCliente();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Chiriquí', 'costo' => 4.00, 'activo' => true]);
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id, 'provincia' => 'Chiriquí']);

        $servicio = app(EnvioService::class);
        $zonaResuelta = $servicio->obtenerZonaPorProvincia($direccion->provincia);

        $this->assertSame($zona->id, $zonaResuelta->id);
        $this->assertSame(4.0, $servicio->obtenerCostoEnvio($direccion->provincia));
    }

    public function test_el_costo_de_envio_se_aplica_al_total_del_pedido(): void
    {
        $cliente = $this->crearCliente();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00, 'activo' => true]);
        $carrito = $this->crearCarritoConProducto($cliente, 2, 100.00);

        $totales = app(PedidoService::class)->calcularTotales($carrito, $zona, null);

        $this->assertSame(200.0, $totales['subtotal']);
        $this->assertSame(5.0, $totales['costo_envio']);
        $this->assertSame(14.0, $totales['itbms_monto']); // 7% de 200
        $this->assertSame(219.0, $totales['total']);       // 200 + 14 + 5
    }

    public function test_el_pedido_creado_persiste_la_zona_y_el_costo_de_envio(): void
    {
        $cliente = $this->crearCliente();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00, 'activo' => true]);
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id, 'provincia' => 'Panamá']);
        $carrito = $this->crearCarritoConProducto($cliente, 1, 50.00);

        $pedido = app(PedidoService::class)->crearDesdeCarrito(
            $carrito,
            $direccion->id,
            'contra_entrega',
            null,
            $zona,
            null
        );

        $this->assertSame($zona->id, $pedido->zona_envio_id);
        $this->assertSame('5.00', $pedido->costo_envio);
        $this->assertSame('58.50', $pedido->total); // 50 + 3.50 ITBMS + 5 envío
    }

    public function test_sin_zona_seleccionada_el_costo_de_envio_del_pedido_es_cero(): void
    {
        $cliente = $this->crearCliente();
        $carrito = $this->crearCarritoConProducto($cliente, 1, 100.00);

        $totales = app(PedidoService::class)->calcularTotales($carrito, null, null);

        $this->assertSame(0.0, $totales['costo_envio']);
    }

    public function test_el_calculo_de_totales_no_valida_el_estado_activo_de_la_zona(): void
    {
        // HALLAZGO: PedidoService::calcularTotales usa el costo de la zona recibida sin
        // verificar "activo" (a diferencia de EnvioService). Si al checkout se le pasa
        // una zona inactiva, su costo igualmente se aplica al pedido.
        $cliente = $this->crearCliente();
        $zona = ZonaEnvio::factory()->inactiva()->create(['nombre' => 'Panamá', 'costo' => 9.99]);
        $carrito = $this->crearCarritoConProducto($cliente, 1, 100.00);

        $totales = app(PedidoService::class)->calcularTotales($carrito, $zona, null);

        $this->assertSame(9.99, $totales['costo_envio']);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Crea un carrito con un único producto (cantidad y precio indicados).
     */
    protected function crearCarritoConProducto(Usuario $usuario, int $cantidad, float $precio): Carrito
    {
        $producto = Producto::factory()->create(['precio' => $precio, 'stock' => 50]);

        $carrito = Carrito::create([
            'usuario_id' => $usuario->id,
            'descuento_aplicado' => 0.00,
        ]);

        ItemCarrito::create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
        ]);

        return $carrito;
    }
}
