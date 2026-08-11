<?php

namespace Database\Factories;

use App\Models\Direccion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Direccion>
 */
class DireccionFactory extends Factory
{
    protected $model = Direccion::class;

    /**
     * Define el estado por defecto de una dirección de envío.
     *
     * Usuario por defecto: el modelo "Usuario" no tiene factory propio, por lo que
     * se crea uno inline; en las pruebas se suele sobreescribir con "usuario_id".
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => fn () => Usuario::create([
                'nombre' => 'Cliente',
                'apellido' => 'Dirección',
                'email' => 'direccion.' . uniqid() . '@example.com',
                'password_hash' => Hash::make('secret123'),
                'telefono' => '60000000',
            ])->id,
            'alias' => 'Casa',
            'nombre_receptor' => $this->faker->name(),
            'provincia' => 'Panamá',
            'distrito' => 'Panamá',
            'corregimiento' => 'San Felipe',
            'direccion_exacta' => $this->faker->streetAddress(),
            'referencia' => null,
            'es_predeterminada' => false,
        ];
    }
}
