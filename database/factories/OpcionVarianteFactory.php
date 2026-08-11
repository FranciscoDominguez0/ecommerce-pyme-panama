<?php

namespace Database\Factories;

use App\Models\OpcionVariante;
use App\Models\TipoVariante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpcionVariante>
 */
class OpcionVarianteFactory extends Factory
{
    protected $model = OpcionVariante::class;

    /**
     * Define el estado por defecto de una opción de variante (Rojo, XL, 128GB).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_variante_id' => TipoVariante::factory(),
            'valor' => $this->faker->unique()->randomElement(['Negro', 'Blanco', 'Rojo', 'Azul', 'S', 'M', 'L', 'XL', '64GB', '128GB', '256GB']),
            'valor_hex' => null,
        ];
    }
}
