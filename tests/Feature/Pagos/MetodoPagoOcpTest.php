<?php

namespace Tests\Feature\Pagos;

use App\Models\Configuracion;
use App\Models\Pedido;
use App\Services\PagoService;
use App\Services\Pagos\Contracts\MetodoPagoInterface;
use App\Services\Pagos\DTOs\ResultadoPago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class MetodoPagoOcpTest extends TestCase
{
    use RefreshDatabase;

    public function test_pago_service_carga_los_cuatro_metodos_predeterminados(): void
    {
        $pagoService = app(PagoService::class);

        $this->assertInstanceOf(MetodoPagoInterface::class, $pagoService->obtener('stripe'));
        $this->assertInstanceOf(MetodoPagoInterface::class, $pagoService->obtener('yappy'));
        $this->assertInstanceOf(MetodoPagoInterface::class, $pagoService->obtener('transferencia'));
        $this->assertInstanceOf(MetodoPagoInterface::class, $pagoService->obtener('contra_entrega'));

        $this->assertContains('stripe', $pagoService->idsActivos());
        $this->assertContains('yappy', $pagoService->idsActivos());
        $this->assertContains('transferencia', $pagoService->idsActivos());
        $this->assertContains('contra_entrega', $pagoService->idsActivos());
    }

    public function test_se_puede_extender_un_nuevo_metodo_de_pago_sin_modificar_codigo_existente_ocp(): void
    {
        $pagoService = app(PagoService::class);

        // Simulamos un nuevo método de pago (ejemplo: PayPal / PagueloFacil)
        $nuevoMetodo = new class implements MetodoPagoInterface {
            public function id(): string { return 'paypal_custom'; }
            public function nombre(): string { return 'PayPal Internacional'; }
            public function descripcion(): string { return 'Paga con tu saldo o tarjeta PayPal'; }
            public function icono(): string { return 'payments'; }
            public function estaActivo(): bool { return true; }
            public function reglasValidacion(Request $request): array { return []; }
            public function extraerDatosSesion(Request $request): array { return ['paypal_order_id' => '12345']; }
            public function procesar(array $contexto): ResultadoPago {
                return ResultadoPago::exito(['custom_tx_id' => 'paypal_tx_999'], 'paypal_tx_999');
            }
            public function soportaReembolso(): bool { return true; }
            public function reembolsar(Pedido $pedido, ?float $monto = null, ?string $motivo = null): array {
                return ['exito' => true, 'mensaje' => 'Reembolso procesado en PayPal', 'refund_id' => 'ref_123', 'monto' => $monto ?? 10.0];
            }
            public function vistaSubformulario(): ?string { return null; }
        };

        // El orquestador lo registra sin modificar ninguna clase existente
        $pagoService->registrar($nuevoMetodo);

        $this->assertNotNull($pagoService->obtener('paypal_custom'));
        $this->assertContains('paypal_custom', $pagoService->idsActivos());

        // Se procesa mediante polimorfismo
        $resultado = $pagoService->procesar('paypal_custom', ['total' => 100.0]);
        $this->assertTrue($resultado->exitoso);
        $this->assertSame('paypal_tx_999', $resultado->referenciaTransaccion);
        $this->assertSame('paypal_tx_999', $resultado->metadatos['custom_tx_id']);
    }

    public function test_metodos_desactivados_en_configuracion_no_aparecen_en_ids_activos(): void
    {
        Configuracion::guardar('pagos.yappy.activo', '0', 'pagos');

        $pagoService = app(PagoService::class);
        $this->assertFalse($pagoService->obtener('yappy')->estaActivo());
        $this->assertNotContains('yappy', $pagoService->idsActivos());
    }
}
