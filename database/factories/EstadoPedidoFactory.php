<?php

namespace Database\Factories;

use App\Models\EstadoPedido;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstadoPedido>
 */
class EstadoPedidoFactory extends Factory
{
    protected $model = EstadoPedido::class;

    /**
     * Define el estado por defecto del historial de estados del pedido.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::factory(),
            'usuario_id' => null,
            'estado' => 'pendiente',
            'comentario' => null,
        ];
    }
}
