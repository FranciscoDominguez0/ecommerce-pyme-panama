<?php

namespace Tests\Feature\Envios;

use App\Models\CourierSucursal;
use App\Models\ZonaEnvio;
use Tests\Feature\Admin\BaseAdminTest;

class EnvioCalculadoraTest extends BaseAdminTest
{
    public function test_calcula_retiro_en_local_como_gratuito(): void
    {
        $response = $this->postJson(route('envio.calcular'), [
            'metodo_entrega' => 'retiro_local',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'metodo_entrega' => 'retiro_local',
                'costo_envio' => 0.0,
                'peso_total_lbs' => 0,
            ]);
    }

    public function test_calcula_delivery_con_peso_y_recargo(): void
    {
        $zona = ZonaEnvio::factory()->create([
            'nombre' => 'Ciudad de Panamá',
            'costo' => 5.00,
            'activo' => true,
        ]);

        // 12 libras de peso total -> excede 10 lbs por 2 lbs -> recargo = 2 * 0.50 = $1.00 -> total = $6.00
        // En kg: 12 / 2.20462 = 5.443 kg -> peso = 5.45 kg
        $response = $this->postJson(route('envio.calcular'), [
            'metodo_entrega' => 'delivery',
            'zona_envio_id' => $zona->id,
            'items' => [
                [
                    'peso' => 5.45,
                    'cantidad' => 1,
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertEquals(6.5, $response->json('costo_envio'));
        $this->assertEquals(13, $response->json('peso_total_lbs'));
    }

    public function test_calcula_courier_con_tarifa_base_y_recargo_por_exceso_de_7_libras(): void
    {
        $sucursal = CourierSucursal::create([
            'zona' => 'Chiriquí',
            'courier' => 'UnoExpress',
            'sucursal' => 'David Centro',
            'direccion' => 'Calle Central',
            'tarifa_uno_hasta_7lb' => 4.50,
            'activo' => true,
        ]);

        // 10 lbs -> 10 - 7 = 3 lbs extras * $0.50 = $1.50 -> total = 4.50 + 1.50 = $6.00
        // 10 lbs en kg: 10 / 2.20462 = 4.5359 kg
        $response = $this->postJson(route('envio.calcular'), [
            'metodo_entrega' => 'retiro_courier',
            'courier_sucursal_id' => $sucursal->id,
            'items' => [
                [
                    'peso' => 4.54,
                    'cantidad' => 1,
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertEquals(11, $response->json('peso_total_lbs'));
        // 11 - 7 = 4 extras * 0.50 = 2.00 -> 4.50 + 2.00 = 6.50
        $this->assertEquals(6.5, $response->json('costo_envio'));
    }

    public function test_devuelve_zonas_couriers_y_sucursales(): void
    {
        CourierSucursal::create([
            'zona' => 'Coclé',
            'courier' => 'Fletes Chavale',
            'sucursal' => 'Penonomé',
            'direccion' => 'Av. Central',
            'tarifa_uno_hasta_7lb' => 5.00,
            'activo' => true,
        ]);

        $resZonas = $this->getJson(route('envio.zonas-courier'));
        $resZonas->assertOk()->assertJsonFragment(['Coclé']);

        $resCouriers = $this->getJson(route('envio.couriers-por-zona', ['zona' => 'Coclé']));
        $resCouriers->assertOk()->assertJsonFragment(['Fletes Chavale']);

        $resSuc = $this->getJson(route('envio.sucursales', ['zona' => 'Coclé', 'courier' => 'Fletes Chavale']));
        $resSuc->assertOk();
        $this->assertCount(1, $resSuc->json('sucursales'));
    }
}
