<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogoFijoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Sembrando exactamente 5 productos reales y detallados por cada subcategoría...");

        $categorias = Categoria::all();
        // Encontrar categorias que no son padres (hojas)
        $padresIds = $categorias->pluck('padre_id')->filter()->unique()->toArray();
        $categoriasHojas = $categorias->reject(function ($cat) use ($padresIds) {
            return in_array($cat->id, $padresIds);
        });

        $gtiaPanama = "Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.";

        // Diccionario de 5 productos reales por slug de categoria
        $productosNombres = [
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
            'software-y-licencias' => ['Microsoft Office 365 Personal (1 Año)', 'Windows 11 Pro OEM', 'Adobe Creative Cloud (1 Año)', 'Kaspersky Total Security 1 PC', 'Norton 360 Deluxe 5 Dispositivos'],
            
            // Servicios
            'armado-de-pc' => ['Armado Básico de PC de Oficina', 'Armado de PC Gamer Profesional', 'Armado Custom Liquid Cooling', 'Migración de Componentes a Nuevo Case', 'Actualización General de Hardware'],
            'mantenimiento-y-limpieza-de-equipos' => ['Mantenimiento Preventivo de Laptop', 'Limpieza Profunda de PC Gamer', 'Cambio de Pasta Térmica Premium', 'Mantenimiento de Servidor Pequeño', 'Limpieza de Periféricos y Setup'],
            'instalacion-de-redes' => ['Instalación de Router y Configuración Wi-Fi', 'Cableado de Red Estructurado (Por punto)', 'Instalación de Sistema Mesh', 'Configuración de Switch Administrable', 'Auditoría de Seguridad de Red Local'],
            'recuperacion-de-datos' => ['Recuperación de Disco Duro Dañado Nivel 1', 'Rescate de SSD NVMe con Fallo Lógico', 'Recuperación de Memoria USB/SD', 'Respaldo de Información Preventivo (500GB)', 'Clonación de Sistema Operativo a SSD'],
            'formateo-e-instalacion-de-software' => ['Formateo de Windows 11 + Controladores', 'Instalación Limpia de macOS', 'Formateo con Respaldo de Datos (Hasta 100GB)', 'Instalación de Suite de Oficina Completa', 'Desinfección de Virus y Malware Avanzado'],
            'soporte-tecnico-a-domicilio' => ['Soporte Técnico Residencial (1 Hora)', 'Visita Técnica Empresarial (Diagnóstico)', 'Configuración de Dispositivos Inteligentes', 'Soporte Remoto Inmediato', 'Asesoría de Compra Tecnológica'],
        ];

        // Productos genéricos si la categoría no está mapeada arriba
        $fallback = ['Producto Excelente A', 'Producto Innovador B', 'Modelo Profesional C', 'Edición Especial D', 'Versión Económica E'];

        $count = 0;
        foreach ($categoriasHojas as $categoria) {
            $nombres = $productosNombres[$categoria->slug] ?? $fallback;
            $esServicio = in_array($categoria->padre_id, [15]) || str_contains($categoria->slug, 'servicio');

            foreach ($nombres as $index => $nombre) {
                // Generar specs ultra detalladas dependiendo del tipo
                $specs = $this->generarSpecsDetalladas($categoria->slug, $nombre, $esServicio);
                
                $marcaNombre = explode(' ', $nombre)[0]; 
                if (in_array($marcaNombre, ['Apple', 'Samsung', 'Dell', 'HP', 'Lenovo', 'ASUS', 'Acer', 'Sony', 'Microsoft', 'Logitech'])) {
                    $brand = Brand::where('name', $marcaNombre)->first();
                } else {
                    $brand = Brand::inRandomOrder()->first();
                }

                $precio = $esServicio ? rand(25, 150) : rand(50, 1500) + 0.99;

                Producto::updateOrCreate(
                    ["sku" => strtoupper(substr(Str::slug($categoria->nombre), 0, 3)) . '-' . ($index + 1) . '00' . rand(1,9)],
                    [
                        "slug" => Str::slug($nombre . ' ' . rand(10,99)), // Sufijo para asegurar unicidad
                        "categoria_id" => $categoria->id,
                        "brand_id" => $brand->id ?? 1,
                        "nombre" => $nombre,
                        "descripcion_corta" => $esServicio ? "Servicio profesional de {$nombre}." : "Excelente {$nombre} con altas prestaciones y calidad garantizada.",
                        "descripcion" => $esServicio ? "Brindamos el mejor servicio para: {$nombre}. Contamos con profesionales altamente capacitados para resolver tus necesidades tecnológicas con garantía y rapidez." : "El {$nombre} está diseñado para ofrecer el máximo rendimiento en su categoría. Con materiales de primera calidad y tecnología de punta, superará todas tus expectativas. Ideal para uso diario o profesional.",
                        "marca" => $brand->name ?? 'Genérica',
                        "modelo" => Str::limit($nombre, 15, ''),
                        "precio" => $precio,
                        "stock" => $esServicio ? 999 : rand(5, 50),
                        "stock_minimo" => 2,
                        "destacado" => rand(1, 100) > 80,
                        "activo" => true,
                        "aplica_itbms" => true,
                        "especificaciones" => $specs,
                        "peso" => $esServicio ? 0 : (rand(1, 50) / 10),
                        "dimension_largo" => $esServicio ? 0 : rand(10, 40),
                        "dimension_ancho" => $esServicio ? 0 : rand(5, 30),
                        "dimension_alto" => $esServicio ? 0 : rand(1, 20),
                        "garantia_info" => [
                            "nombre" => $esServicio ? "Garantía de Servicio PayMe" : "Garantía Extendida del Fabricante", 
                            "duracion" => $esServicio ? "30 Días" : "1 Año", 
                            "contacto" => $gtiaPanama
                        ],
                    ]
                );
                $count++;
            }
        }

        $this->command->info("✅ CatalogoFijoSeeder finalizado. Se sembraron {$count} productos en todas las subcategorías.");
    }

    private function generarSpecsDetalladas($slug, $nombre, $esServicio)
    {
        if ($esServicio) {
            return [
                ["grupo" => "Detalles del Servicio", "atributos" => [
                    ["clave" => "Tipo", "valor" => "Servicio Técnico Especializado"],
                    ["clave" => "Cobertura", "valor" => "A domicilio o en sucursal"],
                    ["clave" => "Tiempo Estimado", "valor" => rand(1, 4) . " Horas hábiles"]
                ]],
                ["grupo" => "Requisitos", "atributos" => [
                    ["clave" => "Agendamiento", "valor" => "Requiere cita previa"],
                    ["clave" => "Diagnóstico", "valor" => "Incluye revisión inicial gratuita"]
                ]]
            ];
        }

        // Para productos físicos, generar specs basadas en el slug
        $specs = [
            ["grupo" => "Especificaciones Generales", "atributos" => [
                ["clave" => "Modelo", "valor" => $nombre],
                ["clave" => "Año de Lanzamiento", "valor" => (string)rand(2022, 2024)],
                ["clave" => "Condición", "valor" => "Nuevo, en caja sellada"]
            ]]
        ];

        if (str_contains($slug, 'laptop') || str_contains($slug, 'computadora')) {
            $specs[] = ["grupo" => "Procesador y Rendimiento", "atributos" => [["clave" => "CPU", "valor" => "Última Generación Multi-núcleo"], ["clave" => "Memoria RAM", "valor" => rand(8, 32) . " GB Alta Velocidad"], ["clave" => "Almacenamiento", "valor" => "SSD NVMe ultrarrápido"]]];
            $specs[] = ["grupo" => "Pantalla y Gráficos", "atributos" => [["clave" => "Tamaño", "valor" => "Pantalla de Alta Resolución"], ["clave" => "Gráficos", "valor" => "Dedicados / Integrados Avanzados"]]];
        } elseif (str_contains($slug, 'smartphone') || str_contains($slug, 'tablet')) {
            $specs[] = ["grupo" => "Pantalla y Batería", "atributos" => [["clave" => "Tecnología", "valor" => "OLED / AMOLED de 120Hz"], ["clave" => "Batería", "valor" => rand(4000, 6000) . " mAh con carga rápida"]]];
            $specs[] = ["grupo" => "Cámaras", "atributos" => [["clave" => "Principal", "valor" => rand(48, 200) . " MP con estabilización óptica"], ["clave" => "Frontal", "valor" => "Alta resolución para videollamadas"]]];
        } elseif (str_contains($slug, 'monitor') || str_contains($slug, 'televisor')) {
            $specs[] = ["grupo" => "Calidad de Imagen", "atributos" => [["clave" => "Resolución", "valor" => "4K UHD / QHD"], ["clave" => "Tasa de Refresco", "valor" => rand(60, 240) . " Hz"], ["clave" => "HDR", "valor" => "Soporte para alto rango dinámico"]]];
        } elseif (str_contains($slug, 'audifonos') || str_contains($slug, 'audio')) {
            $specs[] = ["grupo" => "Acústica", "atributos" => [["clave" => "Cancelación de Ruido", "valor" => "Activa Inteligente (ANC)"], ["clave" => "Autonomía", "valor" => rand(20, 50) . " horas de reproducción continua"]]];
        } else {
            $specs[] = ["grupo" => "Características Destacadas", "atributos" => [
                ["clave" => "Materiales", "valor" => "Construcción premium resistente"],
                ["clave" => "Certificaciones", "valor" => "CE, FCC, RoHS"],
                ["clave" => "Compatibilidad", "valor" => "Universal plug & play"]
            ]];
        }

        return $specs;
    }
}