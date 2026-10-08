<?php

namespace Database\Factories;

use App\Models\CourierSucursal;
use Illuminate\Database\Eloquent\Factories\Factory;


class CourierSucursalFactory extends Factory
{
    protected $model = CourierSucursal::class;


    public function definition(): array
    {
        return [
            'zona' => $this->faker->city(),
            'courier' => 'Uno Express',
            'sucursal' => $this->faker->company(),
            'direccion' => $this->faker->address(),
            'tarifa_uno_hasta_7lb' => $this->faker->randomFloat(2, 5, 20),
            'activo' => true,
        ];
    }
}
