<?php

namespace Database\Factories;

use App\Models\PromocionEnvioGratis;
use App\Models\ZonaEnvio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromocionEnvioGratis>
 */
class PromocionEnvioGratisFactory extends Factory
{
    protected $model = PromocionEnvioGratis::class;

    /**
     * Define el estado por defecto de la promoción de envío gratis.
     *
     * Nota: "inicio_en" y "fin_en" son obligatorios en el esquema (no nullable);
     * la factory los completa con una ventana vigente.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zona_envio_id' => ZonaEnvio::factory(),
            'monto_minimo' => 50.00,
            'inicio_en' => now()->subDay(),
            'fin_en' => now()->addMonth(),
            'activo' => true,
        ];
    }

    /**
     * Indica que la promoción está inactiva.
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $atributos) => ['activo' => false]);
    }
}
