<?php

namespace Database\Seeders;

use App\Models\ZonaEnvio;
use Illuminate\Database\Seeder;

class ZonaEnvioSeeder extends Seeder
{
    /**
     * Seed initial Panamanian shipping zones.
     */
    public function run(): void
    {
        $zonas = [
            ['nombre' => 'Panamá (Ciudad y Centro)', 'costo' => 3.50, 'activo' => true],
            ['nombre' => 'Panamá Oeste (Arraiján / La Chorrera)', 'costo' => 4.50, 'activo' => true],
            ['nombre' => 'Coclé (Penonomé / Aguadulce)', 'costo' => 5.50, 'activo' => true],
            ['nombre' => 'Colón', 'costo' => 5.00, 'activo' => true],
            ['nombre' => 'Chiriquí (David / Boquete)', 'costo' => 6.50, 'activo' => true],
            ['nombre' => 'Herrera', 'costo' => 5.50, 'activo' => true],
            ['nombre' => 'Los Santos', 'costo' => 5.50, 'activo' => true],
            ['nombre' => 'Veraguas (Santiago)', 'costo' => 6.00, 'activo' => true],
            ['nombre' => 'Bocas del Toro', 'costo' => 8.00, 'activo' => true],
            ['nombre' => 'Darién', 'costo' => 9.00, 'activo' => true],
        ];

        foreach ($zonas as $zona) {
            ZonaEnvio::firstOrCreate(
                ['nombre' => $zona['nombre']],
                ['costo' => $zona['costo'], 'activo' => $zona['activo']]
            );
        }
    }
}
