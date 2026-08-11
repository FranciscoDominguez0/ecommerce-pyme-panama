<?php

namespace Database\Factories;

use App\Models\ListaDeseos;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<ListaDeseos>
 */
class ListaDeseosFactory extends Factory
{
    protected $model = ListaDeseos::class;

    /**
     * Define el estado por defecto del registro de lista de deseos.
     *
     * La tabla usa clave primaria compuesta (usuario_id, producto_id).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => fn () => Usuario::create([
                'nombre' => 'Cliente',
                'apellido' => 'Deseos',
                'email' => 'deseos.' . uniqid() . '@example.com',
                'password_hash' => Hash::make('secret123'),
                'telefono' => '60000000',
            ])->id,
            'producto_id' => Producto::factory(),
            'creado_en' => now(),
        ];
    }
}
