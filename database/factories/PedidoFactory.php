<?php

namespace Database\Factories;

use App\Models\Direccion;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    /**
     * Define el estado por defecto de un pedido.
     *
     * Nota: al crear un pedido se disparan los triggers de la base de datos
     * (estado inicial "pendiente" y número de pedido cuando va vacío).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => fn () => Usuario::create([
                'nombre' => 'Cliente',
                'apellido' => 'Pedido',
                'email' => 'pedido.' . uniqid() . '@example.com',
                'password_hash' => Hash::make('secret123'),
                'telefono' => '60000000',
            ])->id,
            'direccion_id' => Direccion::factory(),
            'cupon_id' => null,
            'zona_envio_id' => null,
            'numero_pedido' => 'PM-TEST-' . uniqid(),
            'metodo_pago' => 'contra_entrega',
            'subtotal' => 100.00,
            'descuento' => 0.00,
            'costo_envio' => 0.00,
            'itbms_monto' => 7.00,
            'total' => 107.00,
            'notas_cliente' => null,
            'comprobante_pago_ruta' => null,
            'eliminado_en' => null,
        ];
    }
}
