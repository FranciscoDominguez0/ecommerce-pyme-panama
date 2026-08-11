<?php

namespace Database\Factories;

use App\Models\Carrito;
use App\Models\ItemCarrito;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemCarrito>
 */
class ItemCarritoFactory extends Factory
{
    protected $model = ItemCarrito::class;

    /**
     * Define el estado por defecto del item del carrito.
     *
     * Cumple las CHECK constraints: cantidad > 0 y precio_unitario >= 0.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'carrito_id' => Carrito::factory(),
            'producto_id' => Producto::factory(),
            'variante_producto_id' => null,
            'cantidad' => 1,
            'precio_unitario' => 10.00,
        ];
    }
}
