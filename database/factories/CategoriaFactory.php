<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    /**
     * Define el estado por defecto de la categoría.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = $this->faker->unique()->words(2, true);

        return [
            'padre_id' => null,
            'nombre' => ucfirst($nombre),
            'slug' => Str::slug($nombre) . '-' . uniqid(),
            'descripcion' => $this->faker->sentence(),
            'imagen_ruta' => null,
            'activo' => true,
            'orden_visualizacion' => 0,
            'eliminado_en' => null,
        ];
    }
}
