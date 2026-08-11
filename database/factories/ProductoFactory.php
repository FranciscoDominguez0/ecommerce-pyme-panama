<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * Define el estado por defecto del producto.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = $this->faker->unique()->words(3, true);

        return [
            'categoria_id' => Categoria::factory(),
            'brand_id' => null,
            'nombre' => ucfirst($nombre),
            'slug' => Str::slug($nombre) . '-' . uniqid(),
            'descripcion' => $this->faker->paragraph(),
            'descripcion_corta' => $this->faker->sentence(),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####-????')),
            'marca' => $this->faker->company(),
            'modelo' => $this->faker->bothify('MOD-####'),
            'precio' => $this->faker->randomFloat(2, 10, 1000),
            'precio_oferta' => null,
            'oferta_activa' => false,
            'oferta_inicio_en' => null,
            'oferta_fin_en' => null,
            'stock' => 10,
            'stock_minimo' => 5,
            'destacado' => false,
            'activo' => true,
            'aplica_itbms' => true,
            'eliminado_en' => null,
        ];
    }

    /**
     * Indica que el producto está inactivo (no visible en tienda).
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $atributos) => [
            'activo' => false,
        ]);
    }

    /**
     * Indica que el producto está agotado (stock en cero).
     */
    public function agotado(): static
    {
        return $this->state(fn (array $atributos) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indica que el producto fue eliminado suavemente.
     */
    public function eliminado(): static
    {
        return $this->state(fn (array $atributos) => [
            'eliminado_en' => now(),
        ]);
    }
}
