<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogoFijoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Sembrando catálogo de productos y servicios con orden prioritario...");

        // Limpiar tabla productos para garantizar asignación de IDs limpia y determinista
        DB::statement('TRUNCATE TABLE productos RESTART IDENTITY CASCADE');

        $categorias = Categoria::all();
        // Encontrar categorías que no son padres (hojas)
        $padresIds = $categorias->pluck('padre_id')->filter()->unique()->toArray();
        $categoriasHojas = $categorias->reject(function ($cat) use ($padresIds) {
            return in_array($cat->id, $padresIds);
        });

        $gtiaPanama = "Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.";

        // Asegurar marcas oficiales para servicios y licencias
        $brandPayMe = Brand::firstOrCreate(['name' => 'PayMe Services'], ['slug' => 'payme-services', 'verified' => true]);
        $brandMicrosoft = Brand::firstOrCreate(['name' => 'Microsoft'], ['slug' => 'microsoft', 'verified' => true]);
        $brandAdobe = Brand::firstOrCreate(['name' => 'Adobe'], ['slug' => 'adobe', 'verified' => true]);
        $brandKaspersky = Brand::firstOrCreate(['name' => 'Kaspersky'], ['slug' => 'kaspersky', 'verified' => true]);
        $brandNorton = Brand::firstOrCreate(['name' => 'Norton'], ['slug' => 'norton', 'verified' => true]);

        // Catálogo de productos físicos organizados por slug de subcategoría
        $productosFisicos = [
            'laptops' => ['Apple MacBook Air M2 13.6"', 'Dell XPS 13 9315', 'Lenovo ThinkPad X1 Carbon', 'HP Spectre x360 14', 'Acer Swift 3 OLED'],
            'laptops-gamer' => ['ASUS ROG Zephyrus G14', 'Acer Predator Helios 300', 'MSI Katana GF66', 'Lenovo Legion 5 Pro', 'HP Omen 16'],
            'computadoras-de-escritorio' => ['Dell Inspiron Desktop', 'HP Envy Desktop', 'Lenovo IdeaCentre 3', 'Apple Mac mini M2', 'ASUS ExpertCenter'],
            'procesadores' => ['Intel Core i9-13900K', 'AMD Ryzen 9 7950X', 'Intel Core i7-13700K', 'AMD Ryzen 7 7800X3D', 'Intel Core i5-13600K'],
            'tarjetas-graficas' => ['NVIDIA GeForce RTX 4090', 'NVIDIA GeForce RTX 4080', 'AMD Radeon RX 7900 XTX', 'NVIDIA GeForce RTX 4070', 'AMD Radeon RX 7800 XT'],
            'memorias-ram' => ['Corsair Vengeance 32GB DDR5', 'Kingston Fury Beast 16GB DDR4', 'G.Skill Trident Z5 32GB RGB', 'Crucial RAM 16GB DDR5', 'TeamGroup T-Force Delta 32GB'],
            'fuentes-de-poder' => ['Corsair RM850x 850W Gold', 'EVGA SuperNOVA 750 G5', 'Seasonic Focus GX-850', 'Cooler Master MWE Gold 650 V2', 'Thermaltake Toughpower GF1 750W'],
            'almacenamiento-interno' => ['Samsung 990 Pro 1TB NVMe', 'WD Black SN850X 2TB', 'Crucial P3 Plus 1TB PCIe 4.0', 'Kingston NV2 1TB M.2', 'Seagate FireCuda 530 2TB'],
            'teclados' => ['Logitech MX Keys Advanced', 'Razer BlackWidow V3 Pro', 'Corsair K70 RGB MK.2', 'Keychron K2 Wireless', 'HyperX Alloy Origins Core'],
            'mouse' => ['Logitech MX Master 3S', 'Razer DeathAdder V3 Pro', 'SteelSeries Rival 5', 'Corsair Dark Core RGB', 'Glorious Model O Wireless'],
            'audifonos' => ['Sony WH-1000XM5 Noise Cancelling', 'Apple AirPods Pro (2.ª generación)', 'Bose QuietComfort Ultra', 'Sennheiser Momentum 4 Wireless', 'JBL Tour One M2'],
            'monitores' => ['LG UltraGear 27" Nano IPS', 'Samsung Odyssey G7 32" Curvo', 'Dell UltraSharp 27" 4K', 'ASUS ROG Swift 27" 360Hz', 'BenQ PD2700U 27" 4K Diseñadores'],
            'audio' => ['Sonos Era 300 Altavoz Espacial', 'Bose SoundLink Flex Bluetooth', 'JBL Charge 5 Portátil', 'Ultimate Ears Megaboom 3', 'Marshall Emberton II'],
            'routers' => ['TP-Link Archer AX73 Wi-Fi 6', 'ASUS RT-AX86U Pro', 'Netgear Nighthawk AX12', 'Linksys Velop Pro 6E', 'Amazon eero 6+ Mesh'],
            'switches-y-adaptadores' => ['TP-Link 8-Port Gigabit Switch', 'Netgear 5-Port Gigabit Switch', 'D-Link 8-Port Desktop Switch', 'Ugreen USB-C Hub 6-in-1', 'Anker USB-C to Ethernet Adapter'],
            'cables-y-conectores' => ['Anker Powerline III USB-C 100W', 'Belkin Ultra HD HDMI 2.1 Cable', 'Ugreen Ethernet Cat8 3m', 'AmazonBasics DisplayPort 1.4', 'Apple Cable USB-C a Lightning'],
            'almacenamiento' => ['SanDisk Extreme Portable SSD 1TB', 'Samsung T7 Shield 2TB', 'WD My Passport 2TB HDD', 'Seagate Expansion 4TB HDD', 'Crucial X8 1TB Portable SSD'],
            'cargadores' => ['Anker Nano II 65W GaN', 'Ugreen 100W GaN Cargador Rápido', 'Belkin BoostCharge Pro 3-in-1', 'Apple Adaptador de corriente 20W', 'Samsung 45W Super Fast Wall Charger'],
            'power-banks' => ['Anker PowerCore 10000mAh', 'Xiaomi Mi Power Bank 3 20000mAh', 'Baseus 65W 20000mAh Power Bank', 'Ugreen 145W Power Bank 25000mAh', 'INIU 10000mAh Portable Charger'],
            'fundas-y-protectores' => ['Spigen Tough Armor Case', 'OtterBox Defender Series Pro', 'UAG Plasma Series Case', 'Caseology Parallax Cover', 'ESR Clear Case con HaloLock'],
            'smartphones' => ['Apple iPhone 15 Pro Max 256GB', 'Samsung Galaxy S24 Ultra 512GB', 'Google Pixel 8 Pro 128GB', 'Xiaomi 14 Pro 256GB', 'OnePlus 12 512GB'],
            'tablets' => ['Apple iPad Pro 12.9" M2', 'Samsung Galaxy Tab S9 Ultra', 'Xiaomi Pad 6 128GB', 'Lenovo Tab P11 Pro Gen 2', 'Apple iPad Air (5.ª generación)'],
            'smartwatches' => ['Apple Watch Series 9 GPS', 'Samsung Galaxy Watch 6 Classic', 'Garmin Fenix 7 Sapphire Solar', 'Amazfit GTR 4 Smartwatch', 'Fitbit Versa 4 Fitness'],
            'televisores' => ['LG OLED C3 55" 4K Smart TV', 'Samsung Neo QLED 65" QN90C', 'Sony Bravia XR 55" A80L OLED', 'TCL 6-Series 65" Mini-LED', 'Hisense U8K 55" ULED 4K'],
            'impresoras-y-escaneres' => ['Epson EcoTank L3250 Inalámbrica', 'HP Smart Tank 5105', 'Canon PIXMA G3110', 'Brother HL-L2350DW Láser', 'Epson EcoTank L8180 Fotográfica'],
            'consolas' => ['Sony PlayStation 5 Edición Disco', 'Microsoft Xbox Series X 1TB', 'Nintendo Switch OLED 64GB', 'Valve Steam Deck OLED 512GB', 'ASUS ROG Ally Z1 Extreme'],
            'sillas-y-muebles-gamer' => ['Secretlab Titan Evo 2022', 'Corsair T3 Rush Gaming Chair', 'Razer Iskur Silla Ergonómica', 'DXRacer Air Series Mesh', 'Herman Miller Embody Gaming'],
            'camaras-y-seguridad' => ['Ring Video Doorbell Pro 2', 'Arlo Pro 4 Spotlight Camera', 'Google Nest Cam Indoor/Outdoor', 'Wyze Cam v3 1080p', 'TP-Link Tapo C200 Pan/Tilt'],
        ];

        // Catálogo de servicios técnicos y software digital
        $serviciosCatalogo = [
            'armado-de-pc' => [
                'Armado Básico de PC de Oficina',
                'Armado de PC Gamer Profesional',
                'Armado Custom Liquid Cooling',
                'Migración de Componentes a Nuevo Case',
                'Actualización General de Hardware'
            ],
            'mantenimiento-y-limpieza-de-equipos' => [
                'Mantenimiento Preventivo de Laptop',
                'Limpieza Profunda de PC Gamer',
                'Cambio de Pasta Térmica Premium',
                'Mantenimiento de Servidor Pequeño',
                'Limpieza de Periféricos y Setup'
            ],
            'instalacion-de-redes' => [
                'Instalación de Router y Configuración Wi-Fi',
                'Cableado de Red Estructurado (Por punto)',
                'Instalación de Sistema Mesh',
                'Configuración de Switch Administrable',
                'Auditoría de Seguridad de Red Local'
            ],
            'recuperacion-de-datos' => [
                'Recuperación de Disco Duro Dañado Nivel 1',
                'Rescate de SSD NVMe con Fallo Lógico',
                'Recuperación de Memoria USB/SD',
                'Respaldo de Información Preventivo (500GB)',
                'Clonación de Sistema Operativo a SSD'
            ],
            'formateo-e-instalacion-de-software' => [
                'Formateo de Windows 11 + Controladores',
                'Instalación Limpia de macOS',
                'Formateo con Respaldo de Datos (Hasta 100GB)',
                'Instalación de Suite de Oficina Completa',
                'Desinfección de Virus y Malware Avanzado'
            ],
            'soporte-tecnico-a-domicilio' => [
                'Soporte Técnico Residencial (1 Hora)',
                'Visita Técnica Empresarial (Diagnóstico)',
                'Configuración de Dispositivos Inteligentes',
                'Soporte Remoto Inmediato',
                'Asesoría de Compra Tecnológica'
            ],
            'software-y-licencias' => [
                'Microsoft Office 365 Personal (1 Año)',
                'Windows 11 Pro OEM',
                'Adobe Creative Cloud (1 Año)',
                'Kaspersky Total Security 1 PC',
                'Norton 360 Deluxe 5 Dispositivos'
            ],
        ];

        $slugsServicios = array_keys($serviciosCatalogo);

        // Separamos las categorías en Físicas y Servicios
        $categoriasFisicas = $categoriasHojas->reject(function ($cat) use ($slugsServicios) {
            return in_array($cat->slug, $slugsServicios);
        })->values();

        $categoriasServicios = $categoriasHojas->filter(function ($cat) use ($slugsServicios) {
            return in_array($cat->slug, $slugsServicios);
        })->values();

        $count = 0;

        // ══════════════════════════════════════════════════════════════════════════════════
        // 1. PRIMERO: Insertamos los Servicios y Software Digital (para que queden con IDs base
        //    y en el orden natural "id desc" los productos físicos siempre salgan arriba primero)
        // ══════════════════════════════════════════════════════════════════════════════════
        $this->command->info("1/2. Sembrando servicios y software digital con especificaciones y garantías especializadas...");
        
        foreach ($categoriasServicios as $categoria) {
            $nombres = $serviciosCatalogo[$categoria->slug] ?? [];

            foreach ($nombres as $index => $nombre) {
                $datosServicio = $this->generarDatosServicioDetallados(
                    $categoria->slug,
                    $nombre,
                    $index,
                    $brandPayMe,
                    $brandMicrosoft,
                    $brandAdobe,
                    $brandKaspersky,
                    $brandNorton
                );

                Producto::updateOrCreate(
                    ["sku" => $datosServicio['sku']],
                    [
                        "slug" => Str::slug($nombre . '-' . $categoria->slug . '-' . ($index + 1)),
                        "categoria_id" => $categoria->id,
                        "brand_id" => $datosServicio['brand_id'],
                        "nombre" => $nombre,
                        "descripcion_corta" => $datosServicio['descripcion_corta'],
                        "descripcion" => $datosServicio['descripcion'],
                        "marca" => $datosServicio['marca'],
                        "modelo" => $datosServicio['modelo'],
                        "precio" => $datosServicio['precio'],
                        "precio_oferta" => $datosServicio['precio_oferta'],
                        "oferta_activa" => $datosServicio['oferta_activa'],
                        "stock" => 999, // Disponibilidad continua para servicios
                        "stock_minimo" => 1,
                        "destacado" => $datosServicio['destacado'],
                        "activo" => true,
                        "es_digital" => true,
                        "aplica_itbms" => true,
                        "especificaciones" => $datosServicio['especificaciones'],
                        "peso" => null,
                        "dimension_largo" => null,
                        "dimension_ancho" => null,
                        "dimension_alto" => null,
                        "garantia_info" => $datosServicio['garantia_info'],
                    ]
                );
                $count++;
            }
        }

        // ══════════════════════════════════════════════════════════════════════════════════
        // 2. SEGUNDO: Insertamos los Productos Físicos (reciben los IDs más recientes y altos,
        //    garantizando que en la tienda y catálogo salgan de primero siempre)
        // ══════════════════════════════════════════════════════════════════════════════════
        $this->command->info("2/2. Sembrando catálogo de productos físicos con dimensiones, fletes y garantías...");

        $fallback = ['Producto Excelente A', 'Producto Innovador B', 'Modelo Profesional C', 'Edición Especial D', 'Versión Económica E'];

        foreach ($categoriasFisicas as $categoria) {
            $nombres = $productosFisicos[$categoria->slug] ?? $fallback;

            foreach ($nombres as $index => $nombre) {
                $specs = $this->generarSpecsFisicas($categoria->slug, $nombre);

                $marcaNombre = explode(' ', $nombre)[0];
                if (in_array($marcaNombre, ['Apple', 'Samsung', 'Dell', 'HP', 'Lenovo', 'ASUS', 'Acer', 'Sony', 'Logitech', 'Razer', 'Corsair', 'Kingston'])) {
                    $brand = Brand::where('name', $marcaNombre)->first() ?? Brand::first();
                } else {
                    $brand = Brand::inRandomOrder()->first() ?? Brand::first();
                }

                $precio = rand(45, 1200) + 0.99;
                $esOferta = rand(1, 100) > 75;

                Producto::updateOrCreate(
                    ["sku" => strtoupper(substr(Str::slug($categoria->nombre), 0, 3)) . '-' . $categoria->id . '-' . ($index + 1)],
                    [
                        "slug" => Str::slug($nombre . '-' . $categoria->slug . '-' . ($index + 1)),
                        "categoria_id" => $categoria->id,
                        "brand_id" => $brand->id ?? 1,
                        "nombre" => $nombre,
                        "descripcion_corta" => "Excelente {$nombre} con altas prestaciones, materiales premium y calidad garantizada.",
                        "descripcion" => "El {$nombre} está diseñado para ofrecer el máximo rendimiento en su categoría. Con materiales de primera calidad y tecnología de punta, superará todas tus expectativas. Ideal para uso intensivo diario, profesional o gaming.",
                        "marca" => $brand->name ?? 'Genérica',
                        "modelo" => Str::limit($nombre, 18, ''),
                        "precio" => $precio,
                        "precio_oferta" => $esOferta ? round($precio * 0.88, 2) : null,
                        "oferta_activa" => $esOferta,
                        "stock" => rand(5, 45),
                        "stock_minimo" => 3,
                        "destacado" => rand(1, 100) > 80,
                        "activo" => true,
                        "es_digital" => false,
                        "aplica_itbms" => true,
                        "especificaciones" => $specs,
                        "peso" => (rand(3, 48) / 10), // 0.3 a 4.8 kg
                        "dimension_largo" => rand(12, 45),
                        "dimension_ancho" => rand(8, 32),
                        "dimension_alto" => rand(2, 22),
                        "garantia_info" => [
                            "nombre" => "Garantía Oficial del Fabricante",
                            "duracion" => "1 Año",
                            "contacto" => $gtiaPanama,
                        ],
                    ]
                );
                $count++;
            }
        }

        $this->command->info("✅ CatalogoFijoSeeder completado. Se sembraron {$count} productos.");
    }

    /**
     * Genera datos ricos y detallados para servicios técnicos y licencias de software.
     */
    private function generarDatosServicioDetallados(
        string $slug,
        string $nombre,
        int $index,
        Brand $brandPayMe,
        Brand $brandMicrosoft,
        Brand $brandAdobe,
        Brand $brandKaspersky,
        Brand $brandNorton
    ): array {
        $skuPrefix = match ($slug) {
            'armado-de-pc' => 'SRV-ARM',
            'mantenimiento-y-limpieza-de-equipos' => 'SRV-MNT',
            'instalacion-de-redes' => 'SRV-RED',
            'recuperacion-de-datos' => 'SRV-DAT',
            'formateo-e-instalacion-de-software' => 'SRV-SO',
            'soporte-tecnico-a-domicilio' => 'SRV-SOP',
            'software-y-licencias' => 'LIC-SFT',
            default => 'SRV-GEN',
        };

        $sku = "{$skuPrefix}-0" . ($index + 1);

        // Asignación de marca oficial según el tipo
        $brand = $brandPayMe;
        $marcaNombre = 'PayMe Tech Solutions';
        if (str_contains($nombre, 'Microsoft') || str_contains($nombre, 'Windows') || str_contains($nombre, 'Office')) {
            $brand = $brandMicrosoft;
            $marcaNombre = 'Microsoft';
        } elseif (str_contains($nombre, 'Adobe')) {
            $brand = $brandAdobe;
            $marcaNombre = 'Adobe';
        } elseif (str_contains($nombre, 'Kaspersky')) {
            $brand = $brandKaspersky;
            $marcaNombre = 'Kaspersky';
        } elseif (str_contains($nombre, 'Norton')) {
            $brand = $brandNorton;
            $marcaNombre = 'Norton';
        }

        // Configuración específica de cada servicio
        switch ($nombre) {
            case 'Armado Básico de PC de Oficina':
                return [
                    'sku' => $sku,
                    'brand_id' => $brand->id,
                    'marca' => $marcaNombre,
                    'modelo' => 'Oficina & Estudio',
                    'precio' => 35.00,
                    'precio_oferta' => null,
                    'oferta_activa' => false,
                    'destacado' => false,
                    'descripcion_corta' => 'Ensamble confiable de computadora para oficina con enrutado limpio de cables y prueba de arranque.',
                    'descripcion' => 'Montaje profesional completo para computadoras de oficina o trabajo diario. Realizamos la instalación antiestática de motherboard, procesador, memoria RAM, fuente de poder y almacenamiento. Incluye acomodo de cableado para optimizar el flujo térmico interno, actualización básica de BIOS y verificación de inicio en frío.',
                    'especificaciones' => [
                        ['grupo' => 'Modalidad y Entrega', 'atributos' => [
                            ['clave' => 'Modalidad', 'valor' => 'En Taller PayMe Panamá'],
                            ['clave' => 'Tiempo Estimado', 'valor' => '2 a 3 Horas hábiles'],
                            ['clave' => 'Requisitos', 'valor' => 'Entregar componentes en sus empaques'],
                        ]],
                        ['grupo' => 'Qué Incluye', 'atributos' => [
                            ['clave' => 'Protección', 'valor' => 'Mesa de trabajo y pulsera antiestática ESD'],
                            ['clave' => 'Cable Management', 'valor' => 'Enrutado por canaletas del case'],
                            ['clave' => 'Prueba de Inicio', 'valor' => 'POST y reconocimiento en BIOS'],
                            ['clave' => 'Devolución', 'valor' => 'Empaques y accesorios sobrantes devueltos'],
                        ]],
                    ],
                    'garantia_info' => [
                        'nombre' => 'Garantía de Ensamble PayMe',
                        'duracion' => '60 Días',
                        'contacto' => 'Soporte y reajuste directo en nuestro taller PayMe Panamá o vía WhatsApp.',
                    ],
                ];

            case 'Armado de PC Gamer Profesional':
                return [
                    'sku' => $sku,
                    'brand_id' => $brand->id,
                    'marca' => $marcaNombre,
                    'modelo' => 'Gaming & Streaming',
                    'precio' => 65.00,
                    'precio_oferta' => 55.00,
                    'oferta_activa' => true,
                    'destacado' => true,
                    'descripcion_corta' => 'Montaje gamer de alta gama con cable management invisible, actualización de BIOS y benchmarking.',
                    'descripcion' => 'El ensamble definitivo para tu setup gamer. Instalación cuidadosa de tarjetas gráficas pesadas con soporte anti-sag, radiadores AIO (hasta 360mm), sincronización ARGB de ventiladores y enrutado estético de cableado. Actualizamos la BIOS a la versión más estable, configuramos perfiles XMP/EXPO de RAM y realizamos 30 minutos de pruebas de estrés térmico.',
                    'especificaciones' => [
                        ['grupo' => 'Modalidad y Entrega', 'atributos' => [
                            ['clave' => 'Modalidad', 'valor' => 'Taller PayMe Tech Lab'],
                            ['clave' => 'Tiempo Estimado', 'valor' => '4 a 6 Horas hábiles'],
                            ['clave' => 'Requisitos', 'valor' => 'Componentes gamer completos'],
                        ]],
                        ['grupo' => 'Detalles del Montaje', 'atributos' => [
                            ['clave' => 'Cable Management', 'valor' => 'Enrutado estético invisible'],
                            ['clave' => 'Pasta Térmica', 'valor' => 'Compuesto premium Arctic MX-6'],
                            ['clave' => 'Perfiles de Memoria', 'valor' => 'Activación de perfil XMP / EXPO / DOCP'],
                            ['clave' => 'Pruebas de Estrés', 'valor' => 'Cinebench R23 y FurMark (30 min)'],
                        ]],
                        ['grupo' => 'Informe Técnico', 'atributos' => [
                            ['clave' => 'Reporte', 'valor' => 'Curva de ventilación y temperaturas registradas'],
                        ]],
                    ],
                    'garantia_info' => [
                        'nombre' => 'Garantía de Ensamble y Temperaturas',
                        'duracion' => '60 Días',
                        'contacto' => 'Atención técnica prioritaria para cualquier consulta de estabilidad o iluminación.',
                    ],
                ];

            case 'Mantenimiento Preventivo de Laptop':
                return [
                    'sku' => $sku,
                    'brand_id' => $brand->id,
                    'marca' => $marcaNombre,
                    'modelo' => 'Portátil / Laptop',
                    'precio' => 35.00,
                    'precio_oferta' => 29.99,
                    'oferta_activa' => true,
                    'destacado' => true,
                    'descripcion_corta' => 'Limpieza profunda de turbinas, disipador y cambio de pasta térmica de alta conductividad.',
                    'descripcion' => 'Previene el sobrecalentamiento y la lentitud por thermal throttling en tu portátil. Desarmamos el equipo con espátulas especiales, removemos el polvo acumulado en turbinas y radiadores, y reemplazamos la pasta térmica seca de fábrica por Arctic MX-6. Reduce temperaturas entre 10°C y 18°C y alarga la vida de tu equipo.',
                    'especificaciones' => [
                        ['grupo' => 'Detalles del Servicio', 'atributos' => [
                            ['clave' => 'Modalidad', 'valor' => 'Taller PayMe (Con opción de Delivery)'],
                            ['clave' => 'Tiempo Estimado', 'valor' => '2 a 4 Horas hábiles'],
                            ['clave' => 'Frecuencia Sugerida', 'valor' => 'Cada 6 meses'],
                        ]],
                        ['grupo' => 'Procedimiento Técnico', 'atributos' => [
                            ['clave' => 'Apertura Segura', 'valor' => 'Sin marcas ni rotura de pestañas'],
                            ['clave' => 'Higienización', 'valor' => 'Aire seco antiestático y alcohol isopropílico'],
                            ['clave' => 'Pasta Térmica', 'valor' => 'Arctic MX-6 de alta conductividad'],
                            ['clave' => 'Prueba de Carga', 'valor' => 'Comprobación de temperaturas pre y post'],
                        ]],
                    ],
                    'garantia_info' => [
                        'nombre' => 'Garantía de Servicio PayMe',
                        'duracion' => '30 Días',
                        'contacto' => 'Soporte y revisión directa en tienda presentando tu comprobante.',
                    ],
                ];

            case 'Formateo de Windows 11 + Controladores':
                return [
                    'sku' => $sku,
                    'brand_id' => $brand->id,
                    'marca' => $marcaNombre,
                    'modelo' => 'Windows 11 64-bit',
                    'precio' => 30.00,
                    'precio_oferta' => 25.00,
                    'oferta_activa' => true,
                    'destacado' => true,
                    'descripcion_corta' => 'Instalación limpia de Windows 11 Pro, controladores oficiales, optimización y utilidades esenciales.',
                    'descripcion' => 'Devuelve la fluidez del primer día a tu computadora. Realizamos la instalación limpia y oficial de Windows 11 Pro de 64 bits, aplicamos las últimas actualizaciones acumulativas de seguridad y configuramos los controladores oficiales certificados por el fabricante. Incluye navegadores optimizados, lector PDF y utilidades básicas.',
                    'especificaciones' => [
                        ['grupo' => 'Detalles del Servicio', 'atributos' => [
                            ['clave' => 'Sistema Operativo', 'valor' => 'Windows 11 Pro (64 bits)'],
                            ['clave' => 'Tiempo Estimado', 'valor' => '1.5 a 3 Horas hábiles'],
                            ['clave' => 'Modalidad', 'valor' => 'Taller o Remoto'],
                        ]],
                        ['grupo' => 'Qué Incluye', 'atributos' => [
                            ['clave' => 'Drivers Oficiales', 'valor' => 'Chipset, Audio, Wi-Fi y Gráfica dedicados'],
                            ['clave' => 'Optimización', 'valor' => 'Desactivación de telemetría y apps en segundo plano'],
                            ['clave' => 'Utilidades Básicas', 'valor' => 'Navegadores, lector PDF, WinRAR y códecs'],
                        ]],
                    ],
                    'garantia_info' => [
                        'nombre' => 'Garantía de Sistema Operativo',
                        'duracion' => '30 Días',
                        'contacto' => 'Soporte remoto o presencial para resolver cualquier duda del sistema.',
                    ],
                ];

            case 'Microsoft Office 365 Personal (1 Año)':
                return [
                    'sku' => $sku,
                    'brand_id' => $brand->id,
                    'marca' => $marcaNombre,
                    'modelo' => 'Suscripción Digital 1 Año',
                    'precio' => 54.99,
                    'precio_oferta' => 49.99,
                    'oferta_activa' => true,
                    'destacado' => true,
                    'descripcion_corta' => 'Licencia oficial por 1 año con Word, Excel, PowerPoint, Outlook y 1TB en OneDrive para 1 usuario.',
                    'descripcion' => 'Suscripción genuina de Microsoft 365 Personal. Incluye las aplicaciones premium de productividad de Office siempre actualizadas a la última versión, más 1TB de almacenamiento seguro en la nube de OneDrive con detección de ransomware. Válido para usar en hasta 5 dispositivos simultáneos (PC, Mac, tablet y móvil).',
                    'especificaciones' => [
                        ['grupo' => 'Detalles de la Licencia', 'atributos' => [
                            ['clave' => 'Tipo', 'valor' => 'Licencia 100% Digital Oficial'],
                            ['clave' => 'Duración', 'valor' => '1 Año de suscripción completa'],
                            ['clave' => 'Dispositivos', 'valor' => 'Hasta 5 simultáneos (PC, Mac, iOS, Android)'],
                            ['clave' => 'Nube', 'valor' => '1 TB de almacenamiento en OneDrive'],
                        ]],
                        ['grupo' => 'Aplicaciones Incluidas', 'atributos' => [
                            ['clave' => 'Programas', 'valor' => 'Word, Excel, PowerPoint, Outlook, OneNote'],
                            ['clave' => 'Activación', 'valor' => 'Canje directo en setup.office.com'],
                        ]],
                    ],
                    'garantia_info' => [
                        'nombre' => 'Garantía de Activación Oficial',
                        'duracion' => '1 Año',
                        'contacto' => 'Soporte directo de canje y activación vía WhatsApp PayMe Panamá.',
                    ],
                ];

            case 'Windows 11 Pro OEM':
                return [
                    'sku' => $sku,
                    'brand_id' => $brand->id,
                    'marca' => $marcaNombre,
                    'modelo' => 'Licencia Digital Permanente',
                    'precio' => 29.99,
                    'precio_oferta' => 24.99,
                    'oferta_activa' => true,
                    'destacado' => true,
                    'descripcion_corta' => 'Clave digital genuina de por vida para 1 PC con BitLocker, Escritorio Remoto y Hyper-V.',
                    'descripcion' => 'Licencia digital oficial OEM de Windows 11 Pro para 1 equipo. Habilita todas las funciones avanzadas para empresas y desarrolladores: cifrado BitLocker, conexión por Escritorio Remoto nativo, máquinas virtuales con Hyper-V y soporte de hasta 2TB de RAM. Activación instantánea vinculada a la placa madre del equipo.',
                    'especificaciones' => [
                        ['grupo' => 'Detalles de la Licencia', 'atributos' => [
                            ['clave' => 'Tipo', 'valor' => 'Licencia Digital OEM (De por vida)'],
                            ['clave' => 'Arquitectura', 'valor' => '64 Bits'],
                            ['clave' => 'Idioma', 'valor' => 'Multilenguaje (Español / Inglés)'],
                            ['clave' => 'Entrega', 'valor' => 'Envío inmediato por correo electrónico y factura'],
                        ]],
                    ],
                    'garantia_info' => [
                        'nombre' => 'Garantía de Activación Permanente',
                        'duracion' => 'De por vida',
                        'contacto' => 'Activación garantizada con soporte directo de PayMe Panamá.',
                    ],
                ];

            default:
                // Plantilla enriquecida genérica para los demás servicios
                $precio = rand(25, 95) + 0.00;
                return [
                    'sku' => $sku,
                    'brand_id' => $brand->id,
                    'marca' => $marcaNombre,
                    'modelo' => 'Servicio Especializado',
                    'precio' => $precio,
                    'precio_oferta' => null,
                    'oferta_activa' => false,
                    'destacado' => $index === 0,
                    'descripcion_corta' => "Servicio profesional de {$nombre} con técnicos certificados y garantía de satisfacción.",
                    'descripcion' => "Brindamos atención técnica especializada para: {$nombre}. Contamos con personal capacitado, herramientas profesionales y protocolos de diagnóstico rigurosos para resolver cualquier necesidad informática o de configuración con total rapidez y respaldo.",
                    'especificaciones' => [
                        ['grupo' => 'Detalles del Servicio', 'atributos' => [
                            ['clave' => 'Modalidad', 'valor' => 'En Taller, A Domicilio o Remoto según el caso'],
                            ['clave' => 'Tiempo Estimado', 'valor' => rand(1, 4) . ' Horas hábiles'],
                            ['clave' => 'Diagnóstico', 'valor' => 'Revisión y cotización previa sin costo'],
                            ['clave' => 'Cobertura', 'valor' => 'Ciudad de Panamá y Panamá Oeste'],
                        ]],
                        ['grupo' => 'Compromiso PayMe', 'atributos' => [
                            ['clave' => 'Seguridad', 'valor' => 'Estricta confidencialidad en manejo de datos'],
                            ['clave' => 'Herramientas', 'valor' => 'Instrumental antiestático y software certificado'],
                            ['clave' => 'Informe', 'valor' => 'Explicación clara del procedimiento realizado'],
                        ]],
                    ],
                    'garantia_info' => [
                        'nombre' => 'Garantía de Servicio PayMe',
                        'duracion' => '30 Días',
                        'contacto' => 'Atención técnica directa y soporte post-servicio a través de PayMe Panamá.',
                    ],
                ];
        }
    }

    /**
     * Genera especificaciones técnicas realistas para productos físicos según categoría.
     */
    private function generarSpecsFisicas(string $slug, string $nombre): array
    {
        $specs = [
            ["grupo" => "Especificaciones Generales", "atributos" => [
                ["clave" => "Modelo", "valor" => $nombre],
                ["clave" => "Año de Lanzamiento", "valor" => (string)rand(2023, 2025)],
                ["clave" => "Condición", "valor" => "Nuevo, en empaque sellado original"]
            ]]
        ];

        if (str_contains($slug, 'laptop') || str_contains($slug, 'computadora')) {
            $specs[] = ["grupo" => "Procesador y Rendimiento", "atributos" => [
                ["clave" => "CPU", "valor" => "Última Generación Multi-núcleo"],
                ["clave" => "Memoria RAM", "valor" => rand(8, 32) . " GB DDR5 / LPDDR5X"],
                ["clave" => "Almacenamiento", "valor" => "SSD NVMe M.2 de alta velocidad"]
            ]];
            $specs[] = ["grupo" => "Pantalla y Conectividad", "atributos" => [
                ["clave" => "Pantalla", "valor" => "Panel IPS / OLED antirreflejo"],
                ["clave" => "Puertos", "valor" => "USB-C Thunderbolt / HDMI 2.1 / Jack 3.5mm"]
            ]];
        } elseif (str_contains($slug, 'smartphone') || str_contains($slug, 'tablet')) {
            $specs[] = ["grupo" => "Pantalla y Autonomía", "atributos" => [
                ["clave" => "Tecnología", "valor" => "OLED / AMOLED 120Hz adaptativo"],
                ["clave" => "Batería", "valor" => rand(4500, 6000) . " mAh con carga rápida"]
            ]];
            $specs[] = ["grupo" => "Cámaras", "atributos" => [
                ["clave" => "Cámara Principal", "valor" => rand(50, 200) . " MP con estabilización óptica (OIS)"],
                ["clave" => "Video", "valor" => "Grabación en 4K / 8K"]
            ]];
        } elseif (str_contains($slug, 'monitor') || str_contains($slug, 'televisor')) {
            $specs[] = ["grupo" => "Calidad de Imagen", "atributos" => [
                ["clave" => "Resolución", "valor" => "4K UHD (3840 x 2160) / QHD"],
                ["clave" => "Tasa de Refresco", "valor" => rand(75, 240) . " Hz"],
                ["clave" => "Gama de Colores", "valor" => "99% sRGB / DCI-P3 con HDR10"]
            ]];
        } elseif (str_contains($slug, 'audifonos') || str_contains($slug, 'audio')) {
            $specs[] = ["grupo" => "Acústica y Batería", "atributos" => [
                ["clave" => "Cancelación de Ruido", "valor" => "Cancelación Activa Inteligente (ANC)"],
                ["clave" => "Autonomía", "valor" => rand(25, 55) . " horas de reproducción continua"],
                ["clave" => "Conectividad", "valor" => "Bluetooth 5.3 Multipunto de baja latencia"]
            ]];
        } else {
            $specs[] = ["grupo" => "Construcción y Compatibilidad", "atributos" => [
                ["clave" => "Materiales", "valor" => "Construcción duradera de grado premium"],
                ["clave" => "Certificaciones", "valor" => "CE, FCC, RoHS"],
                ["clave" => "Compatibilidad", "valor" => "Universal Plug & Play"]
            ]];
        }

        return $specs;
    }
}