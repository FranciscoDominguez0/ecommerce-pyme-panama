<?php

namespace Database\Seeders;

use App\Models\OpcionVariante;
use App\Models\TipoVariante;
use Illuminate\Database\Seeder;

/**
 * Siembra los ATRIBUTOS de variante (tipos_variante + opciones_variante)
 * usados por el constructor de variantes del panel admin.
 *
 * Este seeder NO crea productos ni variantes de producto: solo los atributos
 * (Color, Capacidad de almacenamiento, Memoria RAM, etc.) y sus opciones.
 *
 * Es idempotente: usa updateOrCreate por (tipo_variante_id, valor), por lo que
 * se puede ejecutar las veces que sea necesario sin duplicar ni romper nada.
 */
class AtributosVarianteSeeder extends Seeder
{
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
                    'Grafito' => '#374151',
                    'Naranja' => '#F97316',
                    'Amarillo' => '#EAB308',
                    'Menta' => '#34D399',
                    'Lavanda' => '#A78BFA',
                    'Marino' => '#1D4ED8',
                    'Transparente' => '#E2E8F0',
                ],
                'opciones' => [
                    'Negro', 'Blanco', 'Plata', 'Gris', 'Azul', 'Rojo', 'Verde', 'Dorado',
                    'Morado', 'Rosa', 'Grafito', 'Naranja', 'Amarillo', 'Menta', 'Lavanda',
                    'Marino', 'Transparente',
                ],
            ],
            'Capacidad de almacenamiento' => [
                'opciones' => ['64 GB', '128 GB', '256 GB', '512 GB', '1 TB', '2 TB', '4 TB', '8 TB', '16 TB', '500 GB'],
            ],
            'Memoria RAM' => [
                'opciones' => ['2 GB', '4 GB', '8 GB', '16 GB', '32 GB', '64 GB', '96 GB', '128 GB', '192 GB'],
            ],
            'Tamaño' => [
                'opciones' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            ],
            'Longitud' => [
                'opciones' => ['0.5 m', '1 m', '2 m', '3 m', '5 m', '10 m', '15 m', '20 m', '30 m', '50 m'],
            ],
            'Tipo de conexión' => [
                'opciones' => ['USB-A', 'USB-C', 'Micro USB', 'Lightning', 'HDMI', 'DisplayPort', 'VGA', 'DVI', 'RJ45', 'Thunderbolt 4'],
            ],
            'Potencia' => [
                'opciones' => ['15 W', '18 W', '20 W', '25 W', '30 W', '45 W', '65 W', '100 W', '120 W', '240 W'],
            ],
            'Frecuencia' => [
                'opciones' => ['60 Hz', '75 Hz', '120 Hz', '144 Hz', '165 Hz', '240 Hz', '360 Hz', '480 Hz'],
            ],
            'Resolución' => [
                'opciones' => ['HD', 'Full HD', '2K', 'QHD', '4K', '8K'],
            ],
            'Tamaño de pantalla' => [
                'opciones' => ['13"', '14"', '15.6"', '16"', '17.3"', '18"', '20"', '24"', '27"', '32"', '34"', '49"'],
            ],
            'Procesador' => [
                'opciones' => [
                    'Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'Intel Core i9',
                    'Intel Core Ultra 5', 'Intel Core Ultra 7',
                    'Ryzen 3', 'Ryzen 5', 'Ryzen 7', 'Ryzen 9', 'Ryzen AI 9',
                    'Apple M1', 'Apple M2', 'Apple M3', 'Apple M4',
                ],
            ],
            'Tarjeta gráfica' => [
                'opciones' => [
                    'Integrada', 'GTX 1650',
                    'RTX 3050', 'RTX 3060', 'RTX 4060', 'RTX 4070', 'RTX 4080', 'RTX 4090',
                    'RTX 5060', 'RTX 5070', 'RTX 5070 Ti', 'RTX 5080', 'RTX 5090',
                    'RX 6600', 'RX 7600', 'RX 7600 XT', 'RX 7800 XT', 'RX 7900 XT',
                ],
            ],
            'Sistema operativo' => [
                'opciones' => ['Windows 11', 'Windows 11 Pro', 'Windows 10', 'Linux', 'macOS', 'ChromeOS', 'FreeDOS', 'Android', 'iOS'],
            ],
            'Distribución del teclado' => [
                'opciones' => ['Español', 'Inglés', 'Inglés US', 'Mecánico', 'Membrana'],
            ],
            'Tipo de switch' => [
                'opciones' => ['Red', 'Blue', 'Brown', 'Black', 'Silver', 'Speed Silver', 'Opto-mecánico'],
            ],
            'Voltaje' => [
                'opciones' => ['110 V', '220 V', '110-220 V'],
            ],
            'Compatibilidad' => [
                'opciones' => ['iPhone', 'Android', 'iOS', 'Windows', 'macOS', 'Linux', 'PlayStation', 'Xbox', 'Nintendo Switch'],
            ],
            'Material' => [
                'opciones' => ['Plástico', 'Aluminio', 'Acero', 'Silicona', 'Vidrio', 'Cuero', 'Fibra de carbono'],
            ],
            'Garantía' => [
                'opciones' => ['3 meses', '6 meses', '1 año', '2 años', '3 años'],
            ],
            'Tipo de panel' => [
                'opciones' => ['IPS', 'VA', 'TN', 'OLED', 'Mini LED'],
            ],
            'Relación de aspecto' => [
                'opciones' => ['16:9', '16:10', '21:9', '32:9', '4:3'],
            ],
            'Conectividad' => [
                'opciones' => ['WiFi 5', 'WiFi 6', 'WiFi 6E', 'Bluetooth 4.2', 'Bluetooth 5.0', 'Bluetooth 5.3', 'Ethernet Gigabit', '4G LTE', '5G', 'NFC'],
            ],
            'Capacidad de batería' => [
                'opciones' => ['3000 mAh', '4000 mAh', '4500 mAh', '5000 mAh', '6000 mAh', '10000 mAh', '20000 mAh', '30000 mAh'],
            ],
            'Núcleos' => [
                'opciones' => ['4 núcleos', '6 núcleos', '8 núcleos', '12 núcleos', '16 núcleos', '24 núcleos'],
            ],
            'Velocidad de lectura' => [
                'opciones' => ['500 MB/s', '1000 MB/s', '2000 MB/s', '3500 MB/s', '5000 MB/s', '7000 MB/s'],
            ],
            'Formato' => [
                'opciones' => ['M.2 2280', 'SATA 2.5"', '3.5"', 'NVMe', 'mSATA', 'SO-DIMM', 'DIMM', 'microSD', 'SD'],
            ],
            'Nivel de resistencia al agua' => [
                'opciones' => ['IPX4', 'IPX5', 'IPX7', 'IP68'],
            ],
            'Cantidad de puertos' => [
                'opciones' => ['1 puerto', '2 puertos', '3 puertos', '4 puertos', '6 puertos', '8 puertos'],
            ],
            'Certificación' => [
                'opciones' => ['80 Plus Bronze', '80 Plus Silver', '80 Plus Gold', '80 Plus Platinum', '80 Plus Titanium'],
            ],
            'Retroiluminación' => [
                'opciones' => ['RGB', 'Blanco', 'Rojo', 'Azul', 'Sin retroiluminación'],
            ],
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
