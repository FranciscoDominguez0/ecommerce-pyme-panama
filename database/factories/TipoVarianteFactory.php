<?php

namespace Database\Factories;

use App\Models\TipoVariante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoVariante>
 */
class TipoVarianteFactory extends Factory
{
    protected $model = TipoVariante::class;

    /**
     * Define el estado por defecto del tipo de variante (Color, Talla, etc.).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement(['Color', 'Talla', 'Capacidad', 'Memoria']),
        ];
    }
}
