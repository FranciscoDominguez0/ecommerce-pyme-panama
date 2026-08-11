<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\ProductoDelMes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductoDelMes>
 */
class ProductoDelMesFactory extends Factory
{
    protected $model = ProductoDelMes::class;

    /**
     * Define el estado por defecto del Producto del Mes.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'descripcion_mes' => 'Producto destacado del mes',
            'imagen_banner_ruta' => null,
            'descuento_especial' => 20.00,
            'inicio_en' => now()->subDay(),
            'fin_en' => now()->addMonth(),
            'activo' => true,
        ];
    }
}
