<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\ImagenProducto;
use App\Models\OpcionVariante;
use App\Models\Producto;
use App\Models\TipoVariante;
use App\Models\VarianteProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductosSeeder extends Seeder
{
    /**
     * Llena la base de datos con los atributos principales, opciones oficiales y productos.
     */
    public function run(): void
    {
        // Limpiar tablas de variantes previas si existen
        DB::table('variante_opciones')->delete();
        DB::table('variantes_producto')->delete();
        DB::table('opciones_variante')->delete();
        DB::table('tipos_variante')->delete();

        // Definición completa de los 19 Atributos Principales y sus Opciones
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
                'opciones' => ['Negro', 'Blanco', 'Plata', 'Gris', 'Azul', 'Rojo', 'Verde', 'Dorado', 'Morado', 'Rosa']
            ],
            'Capacidad de almacenamiento' => [
                'opciones' => ['64 GB', '128 GB', '256 GB', '512 GB', '1 TB', '2 TB', '4 TB']
            ],
            'Memoria RAM' => [
                'opciones' => ['4 GB', '8 GB', '16 GB', '32 GB', '64 GB', '128 GB']
            ],
            'Tamaño' => [
                'opciones' => ['XS', 'S', 'M', 'L', 'XL']
            ],
            'Longitud' => [
                'opciones' => ['0.5 m', '1 m', '2 m', '3 m', '5 m', '10 m']
            ],
            'Tipo de conexión' => [
                'opciones' => ['USB-A', 'USB-C', 'Micro USB', 'Lightning', 'HDMI', 'DisplayPort', 'VGA', 'DVI', 'RJ45']
            ],
            'Potencia' => [
                'opciones' => ['18 W', '20 W', '25 W', '45 W', '65 W', '100 W', '120 W']
            ],
            'Frecuencia' => [
                'opciones' => ['60 Hz', '75 Hz', '120 Hz', '144 Hz', '165 Hz', '240 Hz', '360 Hz']
            ],
            'Resolución' => [
                'opciones' => ['HD', 'Full HD', '2K', 'QHD', '4K', '8K']
            ],
            'Tamaño de pantalla' => [
                'opciones' => ['13"', '14"', '15.6"', '16"', '17.3"', '24"', '27"', '32"']
            ],
            'Procesador' => [
                'opciones' => ['Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'Intel Core i9', 'Ryzen 3', 'Ryzen 5', 'Ryzen 7', 'Ryzen 9', 'Apple M1', 'Apple M2', 'Apple M3']
            ],
            'Tarjeta gráfica' => [
                'opciones' => ['Integrada', 'RTX 3050', 'RTX 3060', 'RTX 4060', 'RTX 4070', 'RTX 4080', 'RTX 4090']
            ],
            'Sistema operativo' => [
                'opciones' => ['Windows 11', 'Windows 10', 'Linux', 'macOS', 'FreeDOS', 'Android', 'iOS']
            ],
            'Distribución del teclado' => [
                'opciones' => ['Español', 'Inglés', 'Inglés US', 'Mecánico', 'Membrana']
            ],
            'Tipo de switch' => [
                'opciones' => ['Red', 'Blue', 'Brown', 'Black', 'Silver']
            ],
            'Voltaje' => [
                'opciones' => ['110 V', '220 V', '110-220 V']
            ],
            'Compatibilidad' => [
                'opciones' => ['iPhone', 'Android', 'Windows', 'macOS', 'Linux', 'PlayStation', 'Xbox', 'Nintendo Switch']
            ],
            'Material' => [
                'opciones' => ['Plástico', 'Aluminio', 'Acero', 'Silicona', 'Vidrio', 'Cuero']
            ],
            'Garantía' => [
                'opciones' => ['3 meses', '6 meses', '1 año', '2 años', '3 años']
            ],
        ];

        // Insertar en la BD y mapear IDs
        $opcionesGuardadas = [];
        foreach ($atributos as $nombreTipo => $data) {
            $tipo = TipoVariante::create(['nombre' => $nombreTipo]);
            foreach ($data['opciones'] as $opc) {
                $hex = isset($data['hex'][$opc]) ? $data['hex'][$opc] : null;
                $opcionModelo = OpcionVariante::create([
                    'tipo_variante_id' => $tipo->id,
                    'valor' => $opc,
                    'valor_hex' => $hex,
                ]);
                $opcionesGuardadas[$nombreTipo][$opc] = $opcionModelo->id;
            }
        }

        // Categorías existentes
        $catComputadora = Categoria::where('slug', 'computadora')->first() ?? Categoria::first();
        $catAccesorios = Categoria::where('slug', 'accesorios')->first() ?? $catComputadora;
        $catImpresoras = Categoria::where('slug', 'impresoras')->first() ?? $catComputadora;

        // Producto 1: MacBook Air 13" M2 (Con Variantes oficiales)
        $prod1 = Producto::updateOrCreate(
            ['slug' => 'macbook-air-m2'],
            [
                'categoria_id' => $catComputadora?->id ?? 1,
                'nombre' => 'MacBook Air 13" Apple Chip M2 (2024)',
                'sku' => 'MBA-M2-BASE',
                'descripcion_corta' => 'Increíblemente delgada y rápida con el chip M2 de Apple. Pantalla Liquid Retina de 13.6 pulgadas y hasta 18 horas de batería.',
                'descripcion' => "La nueva MacBook Air cuenta con el chip M2 de última generación, batería de hasta 18 horas, pantalla Liquid Retina de 13.6 pulgadas con 500 nits de brillo y cámara FaceTime HD de 1080p.\n\nIncluye puerto de carga MagSafe 3, dos puertos Thunderbolt y entrada para audífonos de 3.5 mm.",
                'precio' => 1199.00,
                'precio_oferta' => 1099.00,
                'oferta_activa' => true,
                'stock' => 28,
                'stock_minimo' => 5,
                'destacado' => true,
                'activo' => true,
                'aplica_itbms' => true,
            ]
        );

        // Imágenes del Producto 1
        ImagenProducto::where('producto_id', $prod1->id)->delete();
        ImagenProducto::create(['producto_id' => $prod1->id, 'ruta' => 'laptop_mac', 'es_principal' => true, 'orden' => 1]);
        ImagenProducto::create(['producto_id' => $prod1->id, 'ruta' => 'keyboard', 'es_principal' => false, 'orden' => 2]);
        ImagenProducto::create(['producto_id' => $prod1->id, 'ruta' => 'cable', 'es_principal' => false, 'orden' => 3]);

        // Variantes reales del Producto 1
        $var1 = VarianteProducto::create([
            'producto_id' => $prod1->id,
            'sku' => 'MBA-M2-NEG-256',
            'precio' => 1099.00,
            'stock' => 10,
            'imagen_ruta' => 'laptop_mac',
            'activo' => true,
        ]);
        $var1->opciones()->attach([
            $opcionesGuardadas['Color']['Negro'],
            $opcionesGuardadas['Capacidad de almacenamiento']['256 GB'],
            $opcionesGuardadas['Memoria RAM']['8 GB'],
        ]);

        $var2 = VarianteProducto::create([
            'producto_id' => $prod1->id,
            'sku' => 'MBA-M2-NEG-512',
            'precio' => 1299.00,
            'stock' => 8,
            'imagen_ruta' => 'laptop_mac',
            'activo' => true,
        ]);
        $var2->opciones()->attach([
            $opcionesGuardadas['Color']['Negro'],
            $opcionesGuardadas['Capacidad de almacenamiento']['512 GB'],
            $opcionesGuardadas['Memoria RAM']['16 GB'],
        ]);

        $var3 = VarianteProducto::create([
            'producto_id' => $prod1->id,
            'sku' => 'MBA-M2-PLA-256',
            'precio' => 1099.00,
            'stock' => 6,
            'imagen_ruta' => 'laptop_mac',
            'activo' => true,
        ]);
        $var3->opciones()->attach([
            $opcionesGuardadas['Color']['Plata'],
            $opcionesGuardadas['Capacidad de almacenamiento']['256 GB'],
            $opcionesGuardadas['Memoria RAM']['8 GB'],
        ]);

        $var4 = VarianteProducto::create([
            'producto_id' => $prod1->id,
            'sku' => 'MBA-M2-AZU-512',
            'precio' => 1299.00,
            'stock' => 4,
            'imagen_ruta' => 'laptop_mac',
            'activo' => true,
        ]);
        $var4->opciones()->attach([
            $opcionesGuardadas['Color']['Azul'],
            $opcionesGuardadas['Capacidad de almacenamiento']['512 GB'],
            $opcionesGuardadas['Memoria RAM']['16 GB'],
        ]);

        // Producto 2: Auriculares Sony WH-1000XM5
        $prod2 = Producto::updateOrCreate(
            ['slug' => 'sony-wh-1000xm5'],
            [
                'categoria_id' => $catAccesorios?->id ?? 1,
                'nombre' => 'Auriculares Sony WH-1000XM5 Noise Cancelling',
                'sku' => 'SNY-WH-XM5',
                'descripcion_corta' => 'Líder en cancelación de ruido con dos procesadores y 8 micrófonos. Calidad de audio excepcional Hi-Res.',
                'descripcion' => "Cancelación de ruido líder en la industria, sonido excepcional con procesador V1, llamadas manos libres cristalinas y hasta 30 horas de autonomía de batería con carga rápida.",
                'precio' => 399.00,
                'precio_oferta' => 349.00,
                'oferta_activa' => true,
                'stock' => 15,
                'stock_minimo' => 3,
                'destacado' => true,
                'activo' => true,
                'aplica_itbms' => true,
            ]
        );
        ImagenProducto::where('producto_id', $prod2->id)->delete();
        ImagenProducto::create(['producto_id' => $prod2->id, 'ruta' => 'headphones', 'es_principal' => true, 'orden' => 1]);

        // Variantes Producto 2
        $varSony1 = VarianteProducto::create([
            'producto_id' => $prod2->id,
            'sku' => 'SNY-WH-XM5-NEG',
            'precio' => 349.00,
            'stock' => 10,
            'imagen_ruta' => 'headphones',
            'activo' => true,
        ]);
        $varSony1->opciones()->attach([$opcionesGuardadas['Color']['Negro'], $opcionesGuardadas['Garantía']['1 año']]);

        $varSony2 = VarianteProducto::create([
            'producto_id' => $prod2->id,
            'sku' => 'SNY-WH-XM5-PLA',
            'precio' => 349.00,
            'stock' => 5,
            'imagen_ruta' => 'headphones',
            'activo' => true,
        ]);
        $varSony2->opciones()->attach([$opcionesGuardadas['Color']['Plata'], $opcionesGuardadas['Garantía']['1 año']]);

        // Producto 3: Impresora Multifuncional Epson EcoTank
        $prod3 = Producto::updateOrCreate(
            ['slug' => 'epson-ecotank-l3250'],
            [
                'categoria_id' => $catImpresoras?->id ?? 1,
                'nombre' => 'Impresora Multifuncional Epson EcoTank L3250 WiFi',
                'sku' => 'EPS-L3250-WF',
                'descripcion_corta' => 'Sistema continuo de tanques de tinta. Imprime miles de páginas a costo ultra bajo con conexión inalámbrica.',
                'descripcion' => "Impresora, copiadora y escáner inalámbrico con tecnología EcoTank sin cartuchos. Incluye botellas de tinta para hasta 4,500 páginas en negro y 7,500 en color.",
                'precio' => 219.00,
                'precio_oferta' => null,
                'oferta_activa' => false,
                'stock' => 4,
                'stock_minimo' => 5,
                'destacado' => false,
                'activo' => true,
                'aplica_itbms' => true,
            ]
        );
        ImagenProducto::where('producto_id', $prod3->id)->delete();
        ImagenProducto::create(['producto_id' => $prod3->id, 'ruta' => 'print', 'es_principal' => true, 'orden' => 1]);

        // Producto 4: Monitor Gaming ASUS ROG 27"
        $prod4 = Producto::updateOrCreate(
            ['slug' => 'monitor-gaming-asus-rog-27'],
            [
                'categoria_id' => $catComputadora?->id ?? 1,
                'nombre' => 'Monitor Gaming ASUS ROG Strix 27" QHD 170Hz',
                'sku' => 'ASUS-XG27AQ',
                'descripcion_corta' => 'Pantalla Fast IPS QHD (2560x1440), 170Hz, 1ms G-Sync Compatible y HDR400 para juego competitivo.',
                'descripcion' => "Monitor gaming profesional para eSports con panel Fast IPS de 27 pulgadas, tasa de refresco fluida de 170Hz y compatibilidad total G-Sync.",
                'precio' => 489.00,
                'precio_oferta' => 429.00,
                'oferta_activa' => true,
                'stock' => 2,
                'stock_minimo' => 3,
                'destacado' => true,
                'activo' => true,
                'aplica_itbms' => true,
            ]
        );
        ImagenProducto::where('producto_id', $prod4->id)->delete();
        ImagenProducto::create(['producto_id' => $prod4->id, 'ruta' => 'monitor', 'es_principal' => true, 'orden' => 1]);

        // Producto 5: Samsung Galaxy Watch 6 Pro
        $prod5 = Producto::updateOrCreate(
            ['slug' => 'samsung-galaxy-watch-6-pro'],
            [
                'categoria_id' => $catAccesorios?->id ?? 1,
                'nombre' => 'Samsung Galaxy Watch 6 Pro 47mm Bluetooth',
                'sku' => 'SM-R960NZK',
                'descripcion_corta' => 'Bisel giratorio físico, cristal de zafiro y monitor de salud avanzado con electrocardiograma y sueño.',
                'descripcion' => "Reloj inteligente premium con cuerpo de acero inoxidable, sensores biométricos de precisión y pagos contactless con NFC.",
                'precio' => 289.00,
                'precio_oferta' => null,
                'oferta_activa' => false,
                'stock' => 0,
                'stock_minimo' => 4,
                'destacado' => false,
                'activo' => false,
                'aplica_itbms' => true,
            ]
        );
        ImagenProducto::where('producto_id', $prod5->id)->delete();
        ImagenProducto::create(['producto_id' => $prod5->id, 'ruta' => 'watch', 'es_principal' => true, 'orden' => 1]);
    }
}
