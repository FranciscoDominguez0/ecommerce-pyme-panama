<?php

namespace Database\Factories;

use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemPedido>
 */
class ItemPedidoFactory extends Factory
{
    protected $model = ItemPedido::class;

    /**
     * Define el estado por defecto del item del pedido.
     *
     * Cumple las CHECK constraints: cantidad > 0 y precio_unitario >= 0.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::factory(),
            'producto_id' => Producto::factory(),
            'variante_producto_id' => null,
            'cantidad' => 1,
            'precio_unitario' => 10.00,
            'subtotal' => 10.00,
        ];
    }
}
