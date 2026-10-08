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
        $this->command->info("Sembrando productos fijos con especificaciones detalladas...");

        $catLaptops = Categoria::where("slug", "laptops")->first();
        $catSmartphones = Categoria::where("slug", "smartphones")->first();
        $catAudifonos = Categoria::where("slug", "audifonos")->first();
        $catMonitores = Categoria::where("slug", "monitores")->first();
        $catImpresoras = Categoria::where("slug", "impresoras-y-escaneres")->first();

        $marcas = Brand::all()->keyBy("slug")->all();

        $gtiaPanama = "Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.";

        $productos = [
            // LAPTOPS
            [
                "cat" => $catLaptops, "brand" => $marcas["apple"] ?? null,
                "nombre" => "Apple MacBook Air M2 13.6 Pulgadas",
                "sku" => "APP-MBA-M2-256",
                "desc_corta" => "Laptop ultraligera con chip M2, 8GB RAM, 256GB SSD y batería de hasta 18 horas.",
                "desc" => "Rediseñada para el revolucionario chip M2, la MacBook Air es increíblemente delgada y resistente. Ofrece un rendimiento excepcional sin ventilador (totalmente silenciosa) y hasta 18 horas de batería. Ideal para trabajar, jugar y crear en cualquier lugar con su impresionante pantalla Liquid Retina y cámara FaceTime HD 1080p.",
                "precio" => 1099.00, "peso" => 1.24, "largo" => 30.41, "ancho" => 21.50, "alto" => 1.13,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Apple"], ["clave" => "Modelo", "valor" => "MacBook Air M2 2022"], ["clave" => "Número de Parte", "valor" => "MLXW3LL/A"], ["clave" => "Color", "valor" => "Gris Espacial"]]],
                    ["grupo" => "Procesador y Rendimiento", "atributos" => [["clave" => "Chip", "valor" => "Apple M2"], ["clave" => "Núcleos CPU", "valor" => "8 (4 rendimiento y 4 eficiencia)"], ["clave" => "Núcleos GPU", "valor" => "8 núcleos"], ["clave" => "Neural Engine", "valor" => "16 núcleos"]]],
                    ["grupo" => "Memoria y Almacenamiento", "atributos" => [["clave" => "Memoria RAM", "valor" => "8 GB Unificada"], ["clave" => "Almacenamiento", "valor" => "256 GB SSD PCIe"]]],
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "13.6 Pulgadas"], ["clave" => "Tipo", "valor" => "Liquid Retina (IPS)"], ["clave" => "Resolución", "valor" => "2560 x 1664 a 224 ppi"], ["clave" => "Brillo", "valor" => "500 nits"], ["clave" => "Gama de colores", "valor" => "Amplia gama de colores (P3) y True Tone"]]],
                    ["grupo" => "Conectividad", "atributos" => [["clave" => "Puertos", "valor" => "2 x Thunderbolt / USB 4, 1 x MagSafe 3, 1 x Jack 3.5mm"], ["clave" => "Conectividad Inalámbrica", "valor" => "Wi-Fi 6 (802.11ax) y Bluetooth 5.0"]]],
                    ["grupo" => "Cámara y Audio", "atributos" => [["clave" => "Cámara", "valor" => "FaceTime HD de 1080p"], ["clave" => "Audio", "valor" => "Sistema de 4 bocinas con Audio Espacial y Dolby Atmos"], ["clave" => "Micrófonos", "valor" => "Sistema de 3 micrófonos con tecnología beamforming direccional"]]]
                ],
                "garantia" => ["nombre" => "Garantía Limitada Apple", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],
            [
                "cat" => $catLaptops, "brand" => $marcas["apple"] ?? null,
                "nombre" => "Apple MacBook Pro M3 14 Pulgadas",
                "sku" => "APP-MBP-M3-14",
                "desc_corta" => "Potencia Pro con el chip M3, 8GB RAM, 512GB SSD y pantalla Liquid Retina XDR.",
                "desc" => "La MacBook Pro de 14 pulgadas da un salto gigante gracias al chip M3. Cuenta con una deslumbrante pantalla Liquid Retina XDR, cámara FaceTime HD de 1080p y sistema de audio de seis bocinas. Su batería te acompaña todo el día con hasta 22 horas de autonomía, sin perder rendimiento al estar desconectada.",
                "precio" => 1599.00, "peso" => 1.55, "largo" => 31.26, "ancho" => 22.12, "alto" => 1.55,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Apple"], ["clave" => "Modelo", "valor" => "MacBook Pro 14 M3"], ["clave" => "Color", "valor" => "Plata"]]],
                    ["grupo" => "Procesador", "atributos" => [["clave" => "Chip", "valor" => "Apple M3"], ["clave" => "CPU", "valor" => "8 núcleos"], ["clave" => "GPU", "valor" => "10 núcleos con Ray Tracing por hardware"]]],
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "14.2 Pulgadas"], ["clave" => "Tipo", "valor" => "Liquid Retina XDR (Mini-LED)"], ["clave" => "Resolución", "valor" => "3024 x 1964"], ["clave" => "Brillo Máximo", "valor" => "1600 nits (HDR)"], ["clave" => "Tasa de refresco", "valor" => "ProMotion de 120Hz"]]],
                    ["grupo" => "Conectividad", "atributos" => [["clave" => "Puertos", "valor" => "2x Thunderbolt / USB 4, HDMI, Ranura SDXC, MagSafe 3, Jack 3.5mm"], ["clave" => "Redes", "valor" => "Wi-Fi 6E (802.11ax) y Bluetooth 5.3"]]]
                ],
                "garantia" => ["nombre" => "Garantía Profesional Apple", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],
            [
                "cat" => $catLaptops, "brand" => $marcas["dell"] ?? null,
                "nombre" => "Dell XPS 13 9315 Evo",
                "sku" => "DELL-XPS-9315",
                "desc_corta" => "Laptop premium ultrafina, pantalla InfinityEdge FHD+, Intel Core i7 12va Gen.",
                "desc" => "Diseño increíblemente delgado y liviano. La XPS 13 de Dell utiliza aluminio mecanizado CNC para una robustez premium. Procesador Intel Core de 12va generación con certificación Evo, que asegura reactivación instantánea y batería prolongada.",
                "precio" => 1299.00, "peso" => 1.17, "largo" => 29.53, "ancho" => 19.90, "alto" => 1.39,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Dell"], ["clave" => "Serie", "valor" => "XPS"], ["clave" => "Modelo", "valor" => "9315"]]],
                    ["grupo" => "Procesador", "atributos" => [["clave" => "Modelo CPU", "valor" => "Intel Core i7-1250U"], ["clave" => "Núcleos", "valor" => "10 (2 P-cores + 8 E-cores)"], ["clave" => "Frecuencia Turbo", "valor" => "Hasta 4.70 GHz"]]],
                    ["grupo" => "Memoria y Almacenamiento", "atributos" => [["clave" => "RAM", "valor" => "16 GB LPDDR5 5200 MHz (Integrada)"], ["clave" => "Almacenamiento", "valor" => "512 GB PCIe NVMe M.2 SSD"]]],
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "13.4 Pulgadas"], ["clave" => "Tipo", "valor" => "InfinityEdge Anti-Reflejo"], ["clave" => "Resolución", "valor" => "FHD+ (1920 x 1200)"], ["clave" => "Brillo", "valor" => "500 nits"]]]
                ],
                "garantia" => ["nombre" => "Garantía Premium Dell", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],
            [
                "cat" => $catLaptops, "brand" => $marcas["lenovo"] ?? null,
                "nombre" => "Lenovo ThinkPad X1 Carbon Gen 11",
                "sku" => "LEN-X1C-G11",
                "desc_corta" => "La laptop empresarial definitiva. Ultra duradera, ligera y con rendimiento extremo.",
                "desc" => "Certificación militar MIL-STD-810H. La fibra de carbono hace a esta ThinkPad extremadamente ligera y virtualmente indestructible. Teclado a prueba de derrames con el legendario TrackPoint rojo.",
                "precio" => 1499.00, "peso" => 1.12, "largo" => 31.56, "ancho" => 22.25, "alto" => 1.53,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Lenovo"], ["clave" => "Línea", "valor" => "ThinkPad"], ["clave" => "Modelo", "valor" => "X1 Carbon Gen 11"]]],
                    ["grupo" => "Procesador", "atributos" => [["clave" => "CPU", "valor" => "Intel Core i7-1355U vPro"], ["clave" => "Núcleos", "valor" => "10 Núcleos"]]],
                    ["grupo" => "Seguridad", "atributos" => [["clave" => "Biometría", "valor" => "Lector de huellas dactilares match-on-chip"], ["clave" => "Cámara", "valor" => "FHD 1080p con ThinkShutter (Obturador de privacidad)"]]],
                    ["grupo" => "Resistencia", "atributos" => [["clave" => "Certificación", "valor" => "MIL-STD-810H del Departamento de Defensa de EE.UU."]]]
                ],
                "garantia" => ["nombre" => "Garantía Comercial Lenovo", "duracion" => "3 Años On-Site", "contacto" => $gtiaPanama]
            ],
            [
                "cat" => $catLaptops, "brand" => $marcas["asus"] ?? null,
                "nombre" => "ASUS ROG Zephyrus G14",
                "sku" => "ASUS-G14-RTX4060",
                "desc_corta" => "Laptop Gamer compacta de 14\" con AMD Ryzen 9 y NVIDIA RTX 4060.",
                "desc" => "Domina cualquier juego en un factor de forma ultraportátil. La Zephyrus G14 incorpora enfriamiento de metal líquido y la asombrosa pantalla ROG Nebula de 165Hz para una fluidez perfecta.",
                "precio" => 1649.00, "peso" => 1.65, "largo" => 31.20, "ancho" => 22.70, "alto" => 1.85,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "ASUS"], ["clave" => "Línea", "valor" => "Republic of Gamers (ROG)"], ["clave" => "Modelo", "valor" => "Zephyrus G14 (2023)"]]],
                    ["grupo" => "Procesamiento", "atributos" => [["clave" => "Procesador", "valor" => "AMD Ryzen 9 7940HS (Hasta 5.2 GHz)"], ["clave" => "Memoria RAM", "valor" => "16 GB DDR5 4800MHz"], ["clave" => "Almacenamiento", "valor" => "1 TB PCIe 4.0 NVMe M.2"]]],
                    ["grupo" => "Gráficos", "atributos" => [["clave" => "Tarjeta de Video", "valor" => "NVIDIA GeForce RTX 4060 Laptop GPU"], ["clave" => "VRAM", "valor" => "8 GB GDDR6"], ["clave" => "TGP", "valor" => "125W con Dynamic Boost"]]],
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "14 Pulgadas ROG Nebula Display"], ["clave" => "Resolución", "valor" => "QHD+ (2560 x 1600, 16:10)"], ["clave" => "Tasa de refresco", "valor" => "165 Hz con G-Sync"]]]
                ],
                "garantia" => ["nombre" => "Garantía ASUS ROG", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],

            // SMARTPHONES
            [
                "cat" => $catSmartphones, "brand" => $marcas["apple"] ?? null,
                "nombre" => "Apple iPhone 15 Pro Max 256GB Titanium",
                "sku" => "APP-IP15PM-256",
                "desc_corta" => "Marco de titanio aeroespacial, chip A17 Pro y el sistema de cámaras más avanzado.",
                "desc" => "El primer iPhone diseñado con titanio de calidad aeroespacial, la misma aleación usada en las naves enviadas a Marte. A17 Pro desata un rendimiento gráfico que desafía a las consolas de videojuegos. Sistema de cámaras Pro con el zoom óptico más potente de Apple.",
                "precio" => 1199.00, "peso" => 0.221, "largo" => 15.99, "ancho" => 7.67, "alto" => 0.83,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Apple"], ["clave" => "Modelo", "valor" => "iPhone 15 Pro Max"], ["clave" => "Material", "valor" => "Titanio y Ceramic Shield"], ["clave" => "Color", "valor" => "Titanio Natural"]]],
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "6.7 Pulgadas"], ["clave" => "Tecnología", "valor" => "Super Retina XDR OLED con ProMotion (120Hz)"], ["clave" => "Resolución", "valor" => "2796 x 1290"], ["clave" => "Características", "valor" => "Dynamic Island, Always-On Display"]]],
                    ["grupo" => "Cámaras", "atributos" => [["clave" => "Cámara Principal", "valor" => "48 MP, f/1.78, OIS de segunda generación"], ["clave" => "Ultra Gran Angular", "valor" => "12 MP, f/2.2, 120°"], ["clave" => "Teleobjetivo", "valor" => "12 MP, f/2.8, Zoom óptico 5x"], ["clave" => "Grabación de Video", "valor" => "4K a 60 fps, ProRes, Log, Spatial Video"]]],
                    ["grupo" => "Rendimiento", "atributos" => [["clave" => "Procesador", "valor" => "A17 Pro (3 nm)"], ["clave" => "Almacenamiento", "valor" => "256 GB"], ["clave" => "Sistema Operativo", "valor" => "iOS 17"]]],
                    ["grupo" => "Conectividad y Batería", "atributos" => [["clave" => "Puerto", "valor" => "USB-C (USB 3 con soporte hasta 10Gb/s)"], ["clave" => "Red", "valor" => "5G, Wi-Fi 6E"], ["clave" => "Resistencia", "valor" => "IP68 (Sumergible hasta 6 metros por 30 min)"]]]
                ],
                "garantia" => ["nombre" => "Garantía Limitada Apple", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],
            [
                "cat" => $catSmartphones, "brand" => $marcas["samsung"] ?? null,
                "nombre" => "Samsung Galaxy S24 Ultra 512GB",
                "sku" => "SAM-S24U-512",
                "desc_corta" => "Galaxy AI integrado, marco de titanio, S Pen y cámara principal de 200MP.",
                "desc" => "Experimenta la nueva era de la inteligencia artificial móvil con Galaxy AI. Traduce llamadas en tiempo real, resume notas instantáneamente y edita fotos como un profesional. Incluye el icónico S Pen y un marco de titanio súper resistente.",
                "precio" => 1299.00, "peso" => 0.232, "largo" => 16.23, "ancho" => 7.90, "alto" => 0.86,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Samsung"], ["clave" => "Modelo", "valor" => "Galaxy S24 Ultra"], ["clave" => "Material", "valor" => "Titanio (Corning Gorilla Armor)"]]],
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "6.8 Pulgadas planas"], ["clave" => "Tipo", "valor" => "Dynamic LTPO AMOLED 2X"], ["clave" => "Refresco", "valor" => "120Hz Adaptativo"], ["clave" => "Brillo", "valor" => "2600 nits (pico)"]]],
                    ["grupo" => "Cámaras", "atributos" => [["clave" => "Principal", "valor" => "200 MP, OIS"], ["clave" => "Teleobjetivo 1", "valor" => "50 MP (Periscopio 5x Zoom Óptico)"], ["clave" => "Teleobjetivo 2", "valor" => "10 MP (3x Zoom Óptico)"], ["clave" => "Ultra Ancha", "valor" => "12 MP, 120°"]]],
                    ["grupo" => "Internos", "atributos" => [["clave" => "Procesador", "valor" => "Qualcomm Snapdragon 8 Gen 3 for Galaxy (4 nm)"], ["clave" => "RAM", "valor" => "12 GB LPDDR5X"], ["clave" => "Batería", "valor" => "5000 mAh (Carga rápida 45W)"]]],
                    ["grupo" => "Extras", "atributos" => [["clave" => "Stylus", "valor" => "S Pen integrado con Bluetooth"], ["clave" => "IA", "valor" => "Galaxy AI (Circle to Search, Live Translate, etc)"]]]
                ],
                "garantia" => ["nombre" => "Garantía Oficial Samsung", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],
            [
                "cat" => $catSmartphones, "brand" => $marcas["google"] ?? null,
                "nombre" => "Google Pixel 8 Pro 128GB",
                "sku" => "GGL-PX8P-128",
                "desc_corta" => "La mejor fotografía computacional y 7 años de actualizaciones garantizadas.",
                "desc" => "El hardware de Google combinado con su legendario software de cámara e IA. El Pixel 8 Pro trae herramientas únicas como Magic Editor y Best Take, además de incluir un termómetro físico integrado. Soporte extendido líder en la industria de hasta 7 años.",
                "precio" => 999.00, "peso" => 0.213, "largo" => 16.26, "ancho" => 7.65, "alto" => 0.88,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Google"], ["clave" => "Modelo", "valor" => "Pixel 8 Pro"]]],
                    ["grupo" => "Procesamiento", "atributos" => [["clave" => "Chipset", "valor" => "Google Tensor G3 (4 nm)"], ["clave" => "Seguridad", "valor" => "Coprocesador Titan M2"], ["clave" => "RAM", "valor" => "12 GB"]]],
                    ["grupo" => "Sensores Únicos", "atributos" => [["clave" => "Termómetro", "valor" => "Sensor de temperatura para objetos e infrarrojo"]]],
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "6.7 Pulgadas"], ["clave" => "Tipo", "valor" => "LTPO OLED (Super Actua display)"], ["clave" => "Refresco", "valor" => "1Hz - 120Hz"]]]
                ],
                "garantia" => ["nombre" => "Garantía Google Hardware", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],

            // AUDÍFONOS
            [
                "cat" => $catAudifonos, "brand" => $marcas["sony"] ?? null,
                "nombre" => "Sony WH-1000XM5 Noise Cancelling",
                "sku" => "SNY-WH-XM5",
                "desc_corta" => "Cancelación de ruido premium líder del mercado con procesadores V1 y QN1.",
                "desc" => "Sobresaliente sonido y el mayor silencio. Con 4 micrófonos en cada auricular (8 en total), el procesador V1 desata el máximo potencial del procesador QN1 para cancelar ruido externo de manera inigualable. Extremadamente cómodos para uso durante todo el día.",
                "precio" => 349.00, "peso" => 0.250, "largo" => 18.0, "ancho" => 15.0, "alto" => 7.0,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Sony"], ["clave" => "Modelo", "valor" => "WH-1000XM5"], ["clave" => "Tipo", "valor" => "Auriculares Cerrados, Dinámicos (Over-Ear)"]]],
                    ["grupo" => "Audio y ANC", "atributos" => [["clave" => "Unidad de diafragma", "valor" => "30 mm especial"], ["clave" => "Cancelación de Ruido (ANC)", "valor" => "Sí, líder en la industria con Auto NC Optimizer"], ["clave" => "Hi-Res Audio", "valor" => "Soportado (LDAC)"], ["clave" => "DSEE Extreme", "valor" => "Sí (Restauración de audio por IA)"]]],
                    ["grupo" => "Batería", "atributos" => [["clave" => "Duración", "valor" => "Hasta 30 horas (con ANC) / 40 horas (sin ANC)"], ["clave" => "Carga Rápida", "valor" => "3 minutos brindan 3 horas de reproducción"], ["clave" => "Conexión", "valor" => "USB-C"]]],
                    ["grupo" => "Características Inteligentes", "atributos" => [["clave" => "Speak-to-Chat", "valor" => "Pausa la música automáticamente al hablar"], ["clave" => "Conexión Multipunto", "valor" => "Conecta dos dispositivos Bluetooth al mismo tiempo"]]]
                ],
                "garantia" => ["nombre" => "Garantía Sony Electronics", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],
            [
                "cat" => $catAudifonos, "brand" => $marcas["apple"] ?? null,
                "nombre" => "Apple AirPods Pro (2.ª generación)",
                "sku" => "APP-AIRP2-USBC",
                "desc_corta" => "Cancelación activa de ruido hasta 2 veces mejor, modo ambiente y audio espacial.",
                "desc" => "Remasterizados en cada nota. El chip H2 ofrece cancelación inteligente de ruido y un sonido tridimensional increíble. Ahora con estuche de carga USB-C que incluye altavoz integrado y gancho para correa, para que nunca los pierdas.",
                "precio" => 249.00, "peso" => 0.051, "largo" => 6.06, "ancho" => 4.52, "alto" => 2.17,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Apple"], ["clave" => "Modelo", "valor" => "AirPods Pro (2da gen con USB-C)"], ["clave" => "Tipo", "valor" => "In-Ear True Wireless (TWS)"]]],
                    ["grupo" => "Audio", "atributos" => [["clave" => "Chip", "valor" => "Chip H2 de Apple"], ["clave" => "Cancelación de Ruido", "valor" => "Activa (Hasta 2x superior a la gen 1)"], ["clave" => "Audio Espacial", "valor" => "Personalizado con seguimiento dinámico de la cabeza"], ["clave" => "Modos de escucha", "valor" => "Transparencia Adaptativa y Audio Adaptativo"]]],
                    ["grupo" => "Batería", "atributos" => [["clave" => "Auriculares", "valor" => "Hasta 6 horas con ANC"], ["clave" => "Estuche", "valor" => "Hasta 30 horas con ANC"], ["clave" => "Carga", "valor" => "MagSafe, Apple Watch, Base Qi o USB-C"]]],
                    ["grupo" => "Físico", "atributos" => [["clave" => "Resistencia", "valor" => "IP54 (Polvo, sudor y agua) para audífonos y estuche"], ["clave" => "Controles", "valor" => "Control táctil (deslizar para volumen)"]]]
                ],
                "garantia" => ["nombre" => "Garantía Apple", "duracion" => "1 Año", "contacto" => $gtiaPanama]
            ],

            // MONITORES
            [
                "cat" => $catMonitores, "brand" => $marcas["samsung"] ?? null,
                "nombre" => "Samsung Odyssey G7 32\" Curvo 240Hz",
                "sku" => "SAM-G7-32",
                "desc_corta" => "Curvatura inmersiva 1000R, QLED y unos increíbles 240Hz para eSports.",
                "desc" => "Iguala la curva del ojo humano. La curvatura 1000R de este Odyssey G7 te envuelve por completo. Combina tecnología QLED para colores realistas, resolución QHD detallada y una impresionante velocidad de 240Hz para no perder ningún fotograma.",
                "precio" => 699.00, "peso" => 8.2, "largo" => 71.0, "ancho" => 30.5, "alto" => 59.4,
                "specs" => [
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "32 Pulgadas (16:9)"], ["clave" => "Curvatura", "valor" => "1000R (Máxima inmersión)"], ["clave" => "Tipo de Panel", "valor" => "VA con Quantum Dot (QLED)"], ["clave" => "Resolución", "valor" => "QHD (2560 x 1440)"]]],
                    ["grupo" => "Juegos", "atributos" => [["clave" => "Tasa de refresco", "valor" => "240 Hz"], ["clave" => "Tiempo de Respuesta", "valor" => "1ms (GtG)"], ["clave" => "Tecnología VRR", "valor" => "G-Sync Compatible y FreeSync Premium Pro"]]],
                    ["grupo" => "Imagen", "atributos" => [["clave" => "HDR", "valor" => "VESA DisplayHDR 600"], ["clave" => "Brillo máximo", "valor" => "600 cd/m²"]]],
                    ["grupo" => "Estética", "atributos" => [["clave" => "Iluminación", "valor" => "Core Lighting (LED RGB Trasero)"]]]
                ],
                "garantia" => ["nombre" => "Garantía de Pantalla Samsung", "duracion" => "3 Años", "contacto" => $gtiaPanama]
            ],
            [
                "cat" => $catMonitores, "brand" => $marcas["dell"] ?? null,
                "nombre" => "Dell UltraSharp 27\" 4K USB-C Hub",
                "sku" => "DELL-U2723QE",
                "desc_corta" => "Precisión de color absoluta y contraste 2000:1 con tecnología IPS Black.",
                "desc" => "El monitor soñado para creativos y profesionales. Dell es pionero en la tecnología IPS Black que permite niveles de negro el doble de profundos que un panel IPS normal. El Hub USB-C suministra 90W de energía, reduciendo el desorden de cables en tu escritorio.",
                "precio" => 649.00, "peso" => 6.64, "largo" => 61.1, "ancho" => 18.5, "alto" => 38.5,
                "specs" => [
                    ["grupo" => "Pantalla", "atributos" => [["clave" => "Tamaño", "valor" => "27 Pulgadas (16:9)"], ["clave" => "Resolución", "valor" => "4K UHD (3840 x 2160)"], ["clave" => "Tipo de Panel", "valor" => "IPS Black Technology"], ["clave" => "Precisión de Color", "valor" => "100% sRGB, 98% DCI-P3 (Delta-E < 2)"]]],
                    ["grupo" => "Rendimiento", "atributos" => [["clave" => "Contraste", "valor" => "2000:1 (Mejora drástica de negros)"], ["clave" => "Brillo", "valor" => "400 nits"], ["clave" => "Tasa de Refresco", "valor" => "60 Hz (Uso profesional/oficina)"]]],
                    ["grupo" => "Conectividad (Hub)", "atributos" => [["clave" => "Suministro de energía", "valor" => "Hasta 90W por USB-C"], ["clave" => "Puertos", "valor" => "HDMI, DisplayPort 1.4, RJ45 (Ethernet), 5x USB 3.2, Salida de audio"]]],
                    ["grupo" => "Ergonomía", "atributos" => [["clave" => "Ajustes", "valor" => "Altura, Inclinación, Giro y Pivote (Retrato)"]]]
                ],
                "garantia" => ["nombre" => "Garantía Dell Premium Panel Exchange", "duracion" => "3 Años", "contacto" => $gtiaPanama]
            ],

            // IMPRESORAS
            [
                "cat" => $catImpresoras, "brand" => $marcas["epson"] ?? null,
                "nombre" => "Epson EcoTank L3250 Inalámbrica",
                "sku" => "EPS-L3250",
                "desc_corta" => "Multifuncional a color con sistema de tanques de tinta y Wi-Fi Direct.",
                "desc" => "Dile adiós a los cartuchos. Imprime hasta 4,500 páginas en negro y 7,500 páginas a color con las botellas de tinta incluidas de bajo costo. Ideal para familias, estudiantes y pequeñas oficinas. Conectividad inalámbrica para imprimir desde el smartphone con Epson Smart Panel.",
                "precio" => 219.00, "peso" => 3.9, "largo" => 37.5, "ancho" => 34.7, "alto" => 17.9,
                "specs" => [
                    ["grupo" => "General", "atributos" => [["clave" => "Marca", "valor" => "Epson"], ["clave" => "Modelo", "valor" => "EcoTank L3250"], ["clave" => "Funciones", "valor" => "Imprime, Copia, Escanea"]]],
                    ["grupo" => "Impresión", "atributos" => [["clave" => "Tecnología", "valor" => "Inyección de Tinta (MicroPiezo)"], ["clave" => "Resolución Máxima", "valor" => "5760 x 1440 dpi"], ["clave" => "Velocidad (Negro)", "valor" => "Hasta 33 ppm (borrador)"], ["clave" => "Velocidad (Color)", "valor" => "Hasta 15 ppm (borrador)"]]],
                    ["grupo" => "Escáner", "atributos" => [["clave" => "Tipo", "valor" => "Cama plana con sensor de líneas CIS de color"], ["clave" => "Resolución Óptica", "valor" => "1200 x 2400 dpi"]]],
                    ["grupo" => "Conectividad", "atributos" => [["clave" => "Estándar", "valor" => "USB de alta velocidad"], ["clave" => "Inalámbrica", "valor" => "Wi-Fi (802.11 b/g/n), Wi-Fi Direct"], ["clave" => "Impresión móvil", "valor" => "Epson Smart Panel App"]]]
                ],
                "garantia" => ["nombre" => "Garantía Limitada Extendida Epson", "duracion" => "2 Años", "contacto" => $gtiaPanama]
            ]
        ];

        foreach ($productos as $p) {
            Producto::updateOrCreate(
                ["sku" => $p["sku"]],
                [
                    "slug" => Str::slug($p["nombre"]),
                    "categoria_id" => $p["cat"]->id ?? 1,
                    "brand_id" => $p["brand"]->id ?? 1,
                    "nombre" => $p["nombre"],
                    "sku" => $p["sku"],
                    "descripcion_corta" => $p["desc_corta"],
                    "descripcion" => $p["desc"],
                    "marca" => $p["brand"]->name ?? "Genérica",
                    "modelo" => explode(" ", $p["nombre"])[1] ?? "N/A",
                    "precio" => $p["precio"],
                    "stock" => rand(5, 50),
                    "stock_minimo" => 2,
                    "destacado" => rand(1, 100) > 60,
                    "activo" => true,
                    "aplica_itbms" => true,
                    "especificaciones" => json_encode($p["specs"], JSON_UNESCAPED_UNICODE),
                    "peso" => $p["peso"],
                    "dimension_largo" => $p["largo"],
                    "dimension_ancho" => $p["ancho"],
                    "dimension_alto" => $p["alto"],
                    "garantia_info" => json_encode($p["garantia"], JSON_UNESCAPED_UNICODE),
                ]
            );
        }

        $this->command->info("✅ CatalogoFijoSeeder finalizado. Productos extremadamente detallados sembrados.");
    }
}
