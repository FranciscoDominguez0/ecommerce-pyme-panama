<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\ReenvioFactura;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReenvioFactura>
 */
class ReenvioFacturaFactory extends Factory
{
    protected $model = ReenvioFactura::class;

    /**
     * Define el estado por defecto de un reenvío de factura.
     *
     * Por defecto el reenvío va a la factura y al correo de su usuario.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'factura_id' => Factura::factory(),
            'usuario_id' => fn (array $atributos) => Factura::find($atributos['factura_id'])?->usuario_id,
            'email_destino' => fn (array $atributos) => Factura::find($atributos['factura_id'])?->usuario?->email ?? 'reenvio@example.com',
            'mensaje_personalizado' => null,
            'enviado_en' => now(),
        ];
    }
}
