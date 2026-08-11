<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\VarianteProducto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VarianteProducto>
 */
class VarianteProductoFactory extends Factory
{
    protected $model = VarianteProducto::class;

    /**
     * Define el estado por defecto de una variante de producto.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('VAR-####-????')),
            'precio' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 50),
            'imagen_ruta' => null,
            'activo' => true,
        ];
    }
}
