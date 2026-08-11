<?php

namespace Database\Factories;

use App\Models\Cupon;
use App\Models\Pedido;
use App\Models\UsoCupon;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<UsoCupon>
 */
class UsoCuponFactory extends Factory
{
    protected $model = UsoCupon::class;

    /**
     * Define el estado por defecto de un registro de uso de cupón.
     *
     * Se crean inline el usuario y el pedido (FK obligatorias), a menos que se
     * sobreescriban con "usuario_id", "cupon_id" y/o "pedido_id".
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cupon_id' => Cupon::factory(),
            'usuario_id' => fn () => Usuario::create([
                'nombre' => 'Cliente',
                'apellido' => 'Uso Cupón',
                'email' => 'uso.' . uniqid() . '@example.com',
                'password_hash' => Hash::make('secret123'),
                'telefono' => '60000000',
            ])->id,
            'pedido_id' => fn (array $atributos) => Pedido::create([
                'usuario_id' => $atributos['usuario_id'],
                'numero_pedido' => 'PM-TEST-' . uniqid(),
                'metodo_pago' => 'contra_entrega',
                'subtotal' => 0.00,
                'descuento' => 0.00,
                'costo_envio' => 0.00,
                'itbms_monto' => 0.00,
                'total' => 0.00,
            ])->id,
            'descuento_aplicado' => 5.00,
            'creado_en' => now(),
        ];
    }
}
