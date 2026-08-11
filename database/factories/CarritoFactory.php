<?php

namespace Database\Factories;

use App\Models\Carrito;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Carrito>
 */
class CarritoFactory extends Factory
{
    protected $model = Carrito::class;

    /**
     * Define el estado por defecto del carrito (ligado a un usuario autenticado).
     *
     * Cumple la CHECK constraint "carrito_owner": requiere usuario_id o sesion_id.
     * Para carritos de invitado, usar el estado "paraSesion()".
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => fn () => Usuario::create([
                'nombre' => 'Cliente',
                'apellido' => 'Carrito',
                'email' => 'carrito.' . uniqid() . '@example.com',
                'password_hash' => Hash::make('secret123'),
                'telefono' => '60000000',
            ])->id,
            'cupon_id' => null,
            'sesion_id' => null,
            'descuento_aplicado' => 0.00,
        ];
    }

    /**
     * Indica que el carrito pertenece a una sesión de invitado (sin usuario).
     */
    public function paraSesion(string $sesionId): static
    {
        return $this->state(fn (array $atributos) => [
            'usuario_id' => null,
            'sesion_id' => $sesionId,
        ]);
    }
}
