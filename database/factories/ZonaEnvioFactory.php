<?php

namespace Database\Factories;

use App\Models\ZonaEnvio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ZonaEnvio>
 */
class ZonaEnvioFactory extends Factory
{
    protected $model = ZonaEnvio::class;

    /**
     * Define el estado por defecto de la zona de envío.
     *
     * Nota: el modelo solo administra "nombre", "costo" y "activo"; las columnas
     * "provincias" y "tiempo_estimado" del esquema no se gestionan en la app actual.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provinciasPanama = [
            'Bocas del Toro',
            'Coclé',
            'Colón',
            'Chiriquí',
            'Darién',
            'Herrera',
            'Los Santos',
            'Panamá',
            'Panamá Oeste',
            'Veraguas',
        ];

        return [
            'nombre' => $this->faker->randomElement($provinciasPanama),
            'costo' => $this->faker->randomFloat(2, 0, 20),
            'activo' => true,
        ];
    }

    /**
     * Indica que la zona de envío está inactiva (no se aplica su tarifa).
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $atributos) => [
            'activo' => false,
        ]);
    }
}
