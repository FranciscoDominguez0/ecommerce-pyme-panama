<?php

namespace Database\Seeders;

use App\Models\ZonaEnvio;
use App\Models\PromocionEnvioGratis;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ZonaEnvioSeeder extends Seeder
{
    /**
     * Registra las zonas de envío de Panamá y promociones base de envío gratis.
     */
    public function run(): void
    {
        $zonas = [
            ['nombre' => 'Panamá', 'costo' => 3.50, 'activo' => true],
            ['nombre' => 'Panamá Oeste', 'costo' => 4.50, 'activo' => true],
            ['nombre' => 'Colón', 'costo' => 5.00, 'activo' => true],
            ['nombre' => 'Coclé', 'costo' => 5.50, 'activo' => true],
            ['nombre' => 'Chiriquí', 'costo' => 6.50, 'activo' => true],
            ['nombre' => 'Veraguas', 'costo' => 6.00, 'activo' => true],
            ['nombre' => 'Herrera', 'costo' => 6.00, 'activo' => true],
            ['nombre' => 'Los Santos', 'costo' => 6.00, 'activo' => true],
            ['nombre' => 'Bocas del Toro', 'costo' => 7.50, 'activo' => true],
            ['nombre' => 'Darién', 'costo' => 8.00, 'activo' => true],
        ];

        foreach ($zonas as $z) {
            ZonaEnvio::updateOrCreate(
                ['nombre' => $z['nombre']],
                ['costo' => $z['costo'], 'activo' => $z['activo']]
            );
        }

        // Promociones demo de envío gratis por zona
        $zonaPanama = ZonaEnvio::where('nombre', 'Panamá')->first();
        if ($zonaPanama) {
            PromocionEnvioGratis::updateOrCreate(
                ['zona_envio_id' => $zonaPanama->id],
                [
                    'monto_minimo' => 50.00,
                    'inicio_en' => Carbon::now()->subDays(5),
                    'fin_en' => Carbon::now()->addYear(),
                    'activo' => true,
                    'creado_en' => Carbon::now(),
                ]
            );
        }

        $zonaOeste = ZonaEnvio::where('nombre', 'Panamá Oeste')->first();
        if ($zonaOeste) {
            PromocionEnvioGratis::updateOrCreate(
                ['zona_envio_id' => $zonaOeste->id],
                [
                    'monto_minimo' => 65.00,
                    'inicio_en' => Carbon::now()->subDays(5),
                    'fin_en' => Carbon::now()->addYear(),
                    'activo' => true,
                    'creado_en' => Carbon::now(),
                ]
            );
        }

        $zonaChiriqui = ZonaEnvio::where('nombre', 'Chiriquí')->first();
        if ($zonaChiriqui) {
            PromocionEnvioGratis::updateOrCreate(
                ['zona_envio_id' => $zonaChiriqui->id],
                [
                    'monto_minimo' => 80.00,
                    'inicio_en' => Carbon::now()->subDays(5),
                    'fin_en' => Carbon::now()->addYear(),
                    'activo' => true,
                    'creado_en' => Carbon::now(),
                ]
            );
        }
    }
}
