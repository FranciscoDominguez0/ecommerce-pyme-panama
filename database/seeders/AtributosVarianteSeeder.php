<?php

namespace Database\Seeders;

use App\Models\OpcionVariante;
use App\Models\TipoVariante;
use Illuminate\Database\Seeder;

class AtributosVarianteSeeder extends Seeder
{
    /**
     * Siembra los atributos principales y sus opciones para el constructor de variantes.
     */
    public function run(): void
    {
        $atributos = [
            'Color' => [
                'hex' => [
                    'Negro' => '#0F172A',
                    'Blanco' => '#F8FAFC',
                    'Plata' => '#E2E8F0',
                    'Gris' => '#64748B',
                    'Azul' => '#2563EB',
                    'Rojo' => '#DC2626',
                    'Verde' => '#16A34A',
                    'Dorado' => '#D97706',
                    'Morado' => '#7C3AED',
                    'Rosa' => '#DB2777',
                ],
                'opciones' => ['Negro', 'Blanco', 'Plata', 'Gris', 'Azul', 'Rojo', 'Verde', 'Dorado', 'Morado', 'Rosa'],
            ],
            'Capacidad de almacenamiento' => ['opciones' => ['64 GB', '128 GB', '256 GB', '512 GB', '1 TB', '2 TB', '4 TB']],
            'Memoria RAM' => ['opciones' => ['4 GB', '8 GB', '16 GB', '32 GB', '64 GB', '128 GB']],
            'Tamaño' => ['opciones' => ['XS', 'S', 'M', 'L', 'XL']],
            'Longitud' => ['opciones' => ['0.5 m', '1 m', '2 m', '3 m', '5 m', '10 m']],
            'Tipo de conexión' => ['opciones' => ['USB-A', 'USB-C', 'Micro USB', 'Lightning', 'HDMI', 'DisplayPort', 'VGA', 'DVI', 'RJ45']],
            'Potencia' => ['opciones' => ['18 W', '20 W', '25 W', '45 W', '65 W', '100 W', '120 W']],
            'Frecuencia' => ['opciones' => ['60 Hz', '75 Hz', '120 Hz', '144 Hz', '165 Hz', '240 Hz', '360 Hz']],
            'Resolución' => ['opciones' => ['HD', 'Full HD', '2K', 'QHD', '4K', '8K']],
            'Tamaño de pantalla' => ['opciones' => ['13"', '14"', '15.6"', '16"', '17.3"', '24"', '27"', '32"']],
            'Procesador' => ['opciones' => ['Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'Intel Core i9', 'Ryzen 3', 'Ryzen 5', 'Ryzen 7', 'Ryzen 9', 'Apple M1', 'Apple M2', 'Apple M3']],
            'Tarjeta gráfica' => ['opciones' => ['Integrada', 'RTX 3050', 'RTX 3060', 'RTX 4060', 'RTX 4070', 'RTX 4080', 'RTX 4090']],
            'Sistema operativo' => ['opciones' => ['Windows 11', 'Windows 10', 'Linux', 'macOS', 'FreeDOS', 'Android', 'iOS']],
            'Distribución del teclado' => ['opciones' => ['Español', 'Inglés', 'Inglés US', 'Mecánico', 'Membrana']],
            'Tipo de switch' => ['opciones' => ['Red', 'Blue', 'Brown', 'Black', 'Silver']],
            'Voltaje' => ['opciones' => ['110 V', '220 V', '110-220 V']],
            'Compatibilidad' => ['opciones' => ['iPhone', 'Android', 'Windows', 'macOS', 'Linux', 'PlayStation', 'Xbox', 'Nintendo Switch']],
            'Material' => ['opciones' => ['Plástico', 'Aluminio', 'Acero', 'Silicona', 'Vidrio', 'Cuero']],
            'Garantía' => ['opciones' => ['3 meses', '6 meses', '1 año', '2 años', '3 años']],
        ];

        foreach ($atributos as $nombreTipo => $data) {
            $tipo = TipoVariante::updateOrCreate(
                ['nombre' => $nombreTipo],
                ['nombre' => $nombreTipo]
            );

            foreach ($data['opciones'] as $opc) {
                OpcionVariante::updateOrCreate(
                    ['tipo_variante_id' => $tipo->id, 'valor' => $opc],
                    ['valor' => $opc, 'valor_hex' => $data['hex'][$opc] ?? null]
                );
            }
        }

        $this->command->info('Atributos principales y opciones creados/actualizados correctamente.');
    }
}
