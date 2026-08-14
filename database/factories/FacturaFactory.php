<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Factura>
 */
class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    /**
     * Define el estado por defecto de una factura.
     *
     * La factura se genera desde un pedido real (PedidoFactory), por lo que el
     * usuario y los totales por defecto quedan consistentes con ese pedido.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::factory(),
            'usuario_id' => fn (array $atributos) => Pedido::find($atributos['pedido_id'])->usuario_id,
            'numero' => fn (array $atributos) => 'F-' . date('Y') . '-' . str_pad((string) $atributos['pedido_id'], 4, '0', STR_PAD_LEFT),
            'metodo_pago' => 'contra_entrega',
            'referencia_pago_externo' => null,
            'subtotal' => 100.00,
            'descuento' => 0.00,
            'costo_envio' => 0.00,
            'itbms_tasa' => 7.00,
            'itbms_monto' => 7.00,
            'total' => 107.00,
            'estado' => 'emitida',
            'pdf_ruta' => null,
            'emitida_en' => now(),
        ];
    }

    /**
     * Indica que la factura fue anulada.
     */
    public function anulada(): static
    {
        return $this->state(fn (array $atributos) => [
            'estado' => 'anulada',
        ]);
    }
}
