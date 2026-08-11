<?php

namespace Database\Factories;

use App\Models\Cupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cupon>
 */
class CuponFactory extends Factory
{
    protected $model = Cupon::class;

    /**
     * Define el estado por defecto del cupón (vigente, activo, catálogo completo).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => strtoupper(Str::random(8)),
            'tipo' => 'porcentaje',
            'valor' => 10.00,
            'monto_minimo' => 0.00,
            'maximo_usos_total' => null,
            'usos_por_cliente' => 1,
            'usos_actuales' => 0,
            'activo' => true,
            'inicio_en' => now()->subDay(),
            'fin_en' => now()->addMonth(),
            'aplica_a' => 'catalogo',
            'categoria_id' => null,
            'producto_id' => null,
        ];
    }

    /**
     * Indica que el cupón está inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $atributos) => ['activo' => false]);
    }

    /**
     * Indica que el cupón ya expiró (fin_en en el pasado).
     */
    public function expirado(): static
    {
        return $this->state(fn (array $atributos) => [
            'inicio_en' => now()->subMonth(),
            'fin_en' => now()->subDay(),
        ]);
    }

    /**
     * Indica que el cupón aún no inicia (inicio_en en el futuro).
     */
    public function noIniciado(): static
    {
        return $this->state(fn (array $atributos) => [
            'inicio_en' => now()->addDay(),
            'fin_en' => now()->addMonth(),
        ]);
    }

    /**
     * Indica que el cupón alcanzó su límite total de usos.
     */
    public function agotado(): static
    {
        return $this->state(fn (array $atributos) => [
            'maximo_usos_total' => 5,
            'usos_actuales' => 5,
        ]);
    }

    /**
     * Indica que el cupón es de monto fijo.
     */
    public function montoFijo(): static
    {
        return $this->state(fn (array $atributos) => ['tipo' => 'monto_fijo']);
    }

    /**
     * Indica que el cupón es de envío gratis.
     */
    public function envioGratis(): static
    {
        return $this->state(fn (array $atributos) => ['tipo' => 'envio_gratis']);
    }
}
