<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Categoria;
use App\Models\OpcionVariante;
use App\Models\Producto;
use App\Models\TipoVariante;
use App\Models\VarianteProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogoFijoSeeder extends Seeder
{
    /**
     * Siembra el catálogo fijo de productos y servicios leyendo directamente
     * las marcas, categorías y atributos existentes en la base de datos:
     * - Servicios técnicos y licencias digitales (IDs 1-35, exentos de flete).
     * - Productos físicos (IDs 36-175, flete por dimensiones y peso).
     *
     * Reglas estrictas:
     * 1. Consulta las marcas directamente de la tabla brands (Brand::all()).
     * 2. Consulta las categorías directamente de la tabla categorias (Categoria::all()).
     * 3. Consulta y relaciona atributos de variantes desde tipos_variante y opciones_variante.
     * 4. Todo es determinista: sin llamadas a rand(), sin Faker y sin archivos JSON externos.
     * 5. Máximo 2 variantes por producto en los productos seleccionados.
     */
    public function run(): void
    {
        $this->command->info("Limpiando tablas de catálogo...");

        DB::statement('TRUNCATE TABLE variantes_producto RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE productos RESTART IDENTITY CASCADE');

        // Consultar marcas y categorías directamente de la base de datos
        $marcasDB = Brand::all()->keyBy(fn($b) => mb_strtolower($b->name));
        $categoriasDB = Categoria::all()->keyBy('slug');

        $gtiaPanama = "Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.";

        $totalServicios = 0;
        $totalFisicos = 0;
        $totalVariantes = 0;

        // ══════════════════════════════════════════════════════════════════════════════════
        // 1. PRIMERO: Servicios y Software Digital (IDs 1 a 35)
        // ══════════════════════════════════════════════════════════════════════════════════
        $this->command->info("1/2. Sembrando 35 servicios y licencias leyendo marcas y categorías de la base de datos...");

        foreach ($this->catalogoServicios() as $item) {
            $categoria = $categoriasDB->get($item['cat']) ?? Categoria::where('slug', $item['cat'])->first();
            if (!$categoria) {
                $this->command->warn("Categoría {$item['cat']} no encontrada en la base de datos.");
                continue;
            }

            // Marca leída directamente de la base de datos
            $brand = $marcasDB->get(mb_strtolower($item['marca'])) ?? Brand::where('name', 'ILIKE', $item['marca'])->first();

            $precio = (float) $item['precio'];
            $precioOferta = isset($item['precio_oferta']) && $item['precio_oferta'] !== null ? (float) $item['precio_oferta'] : null;

            $producto = Producto::create([
                'sku' => $item['sku'],
                'slug' => Str::slug($item['nombre'] . '-' . $item['sku']),
                'categoria_id' => $categoria->id,
                'brand_id' => $brand?->id,
                'nombre' => $item['nombre'],
                'descripcion_corta' => $item['desc_corta'],
                'descripcion' => $item['desc'],
                'marca' => $brand?->name ?? $item['marca'],
                'modelo' => $item['modelo'],
                'precio' => $precio,
                'precio_oferta' => $precioOferta,
                'oferta_activa' => $precioOferta !== null,
                'stock' => (int) ($item['stock'] ?? 999),
                'stock_minimo' => 1,
                'destacado' => (bool) ($item['destacado'] ?? false),
                'activo' => true,
                'es_digital' => true,
                'aplica_itbms' => true,
                'especificaciones' => $item['specs'] ?? [],
                'peso' => null,
                'dimension_largo' => null,
                'dimension_ancho' => null,
                'dimension_alto' => null,
                'garantia_info' => $item['garantia'] ?? [
                    'nombre' => 'Garantía de Servicio Técnico Oficial',
                    'duracion' => '30 Días',
                    'contacto' => $gtiaPanama,
                ],
            ]);

            // Asignar variantes leyendo atributos desde la base de datos
            if (!empty($item['variantes']) && is_array($item['variantes'])) {
                $stockSuma = 0;
                foreach (array_slice($item['variantes'], 0, 2) as $varData) {
                    $variante = VarianteProducto::create([
                        'producto_id' => $producto->id,
                        'sku' => $varData['sku'],
                        'precio' => (float) $varData['precio'],
                        'stock' => (int) ($varData['stock'] ?? 999),
                        'activo' => true,
                    ]);

                    if (!empty($varData['opcion']) && count($varData['opcion']) === 2) {
                        [$tipoNombre, $opcValor] = $varData['opcion'];
                        $tipo = TipoVariante::firstOrCreate(['nombre' => $tipoNombre]);
                        $opcion = OpcionVariante::firstOrCreate([
                            'tipo_variante_id' => $tipo->id,
                            'valor' => $opcValor,
                        ]);
                        $variante->opciones()->attach([$opcion->id]);
                    }

                    $stockSuma += (int) ($varData['stock'] ?? 999);
                    $totalVariantes++;
                }
                $producto->update(['stock' => $stockSuma]);
            }

            $totalServicios++;
        }

        // ══════════════════════════════════════════════════════════════════════════════════
        // 2. SEGUNDO: Productos Físicos (IDs 36 a 175)
        // ══════════════════════════════════════════════════════════════════════════════════
        $this->command->info("2/2. Sembrando 140 productos físicos con marcas y atributos de la base de datos...");

        foreach ($this->catalogoFisicos() as $item) {
            $categoria = $categoriasDB->get($item['cat']) ?? Categoria::where('slug', $item['cat'])->first();
            if (!$categoria) {
                $this->command->warn("Categoría {$item['cat']} no encontrada en la base de datos.");
                continue;
            }

            // Marca leída directamente de la base de datos
            $brand = $marcasDB->get(mb_strtolower($item['marca'])) ?? Brand::where('name', 'ILIKE', $item['marca'])->first();

            $precio = (float) $item['precio'];
            $precioOferta = isset($item['precio_oferta']) && $item['precio_oferta'] !== null ? (float) $item['precio_oferta'] : null;

            $producto = Producto::create([
                'sku' => $item['sku'],
                'slug' => Str::slug($item['nombre'] . '-' . $item['sku']),
                'categoria_id' => $categoria->id,
                'brand_id' => $brand?->id,
                'nombre' => $item['nombre'],
                'descripcion_corta' => $item['desc_corta'],
                'descripcion' => $item['desc'],
                'marca' => $brand?->name ?? $item['marca'],
                'modelo' => $item['modelo'],
                'precio' => $precio,
                'precio_oferta' => $precioOferta,
                'oferta_activa' => $precioOferta !== null,
                'stock' => (int) ($item['stock'] ?? 15),
                'stock_minimo' => 3,
                'destacado' => (bool) ($item['destacado'] ?? false),
                'activo' => true,
                'es_digital' => false,
                'aplica_itbms' => true,
                'especificaciones' => $item['specs'] ?? [],
                'peso' => (float) $item['peso'],
                'dimension_largo' => (float) $item['largo'],
                'dimension_ancho' => (float) $item['ancho'],
                'dimension_alto' => (float) $item['alto'],
                'garantia_info' => [
                    'nombre' => 'Garantía Oficial del Fabricante',
                    'duracion' => '1 Año',
                    'contacto' => $gtiaPanama,
                ],
            ]);

            // Asignar variantes leyendo atributos desde la base de datos
            if (!empty($item['variantes']) && is_array($item['variantes'])) {
                $stockSuma = 0;
                foreach (array_slice($item['variantes'], 0, 2) as $varData) {
                    $variante = VarianteProducto::create([
                        'producto_id' => $producto->id,
                        'sku' => $varData['sku'],
                        'precio' => (float) $varData['precio'],
                        'stock' => (int) ($varData['stock'] ?? 10),
                        'activo' => true,
                    ]);

                    if (!empty($varData['opcion']) && count($varData['opcion']) === 2) {
                        [$tipoNombre, $opcValor] = $varData['opcion'];
                        $tipo = TipoVariante::firstOrCreate(['nombre' => $tipoNombre]);
                        $opcion = OpcionVariante::firstOrCreate([
                            'tipo_variante_id' => $tipo->id,
                            'valor' => $opcValor,
                        ]);
                        $variante->opciones()->attach([$opcion->id]);
                    }

                    $stockSuma += (int) ($varData['stock'] ?? 10);
                    $totalVariantes++;
                }
                $producto->update(['stock' => $stockSuma]);
            }

            $totalFisicos++;
        }

        $this->command->info("✅ CatalogoFijoSeeder completado con éxito:");
        $this->command->info("   • Servicios sembrados: {$totalServicios}");
        $this->command->info("   • Productos físicos sembrados: {$totalFisicos}");
        $this->command->info("   • Total productos: " . ($totalServicios + $totalFisicos));
        $this->command->info("   • Total variantes creadas: {$totalVariantes}");
    }

    /**
     * Catálogo determinista de 35 servicios y licencias digitales.
     */
    protected function catalogoServicios(): array
    {
        return array (
  0 => 
  array (
    'cat' => 'armado-de-pc',
    'sku' => 'SRV-ARM-1',
    'nombre' => 'Armado Básico de PC de Oficina',
    'marca' => 'ASUS',
    'modelo' => 'ASUS-BUILD-BASIC',
    'precio' => 45,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Ensamblaje profesional de componentes de oficina con pruebas de encendido e instalación de BIOS.',
    'desc' => 'Servicio profesional de armado de computadoras de oficina. Incluye montaje de placa madre, procesador con disipador, memoria RAM, unidad de almacenamiento y fuente de poder en chasis estándar. Se realiza ordenamiento básico de cableado, actualización a la última versión estable de BIOS y prueba de encendido durante 30 minutos.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 a 48 Horas hábiles',
      'Pruebas' => 'POST y estabilidad de BIOS',
      'Compatibilidad' => 'Plataformas Intel y AMD estándar',
      'Modalidad' => 'Taller técnico especializado',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  1 => 
  array (
    'cat' => 'armado-de-pc',
    'sku' => 'SRV-ARM-2',
    'nombre' => 'Armado de PC Gamer Profesional',
    'marca' => 'Corsair',
    'modelo' => 'CORSAIR-BUILD-PRO',
    'precio' => 85,
    'precio_oferta' => 75,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Montaje de alto rendimiento gamer con gestión premium de cables y optimización de curvas de ventilación.',
    'desc' => 'Montaje exhaustivo para equipos gamer de alta gama. Incluye instalación de tarjetas gráficas pesadas con soporte anti-sag, configuración de ventiladores ARGB/PWM, optimización de curvas térmicas en BIOS, habilitación de perfiles XMP/EXPO y pruebas de estrés térmico con Cinebench y 3DMark durante 45 minutos.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 Horas hábiles',
      'Pruebas' => 'Estrés térmico y estabilidad de memorias',
      'Compatibilidad' => 'Chasis ATX / E-ATX / Micro-ATX',
      'Modalidad' => 'Taller técnico especializado',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '60 Días',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'SRV-ARM-2-V1',
        'precio' => 85,
        'stock' => 999,
        'opcion' => 
        array (
          0 => 'Garantía',
          1 => '1 año',
        ),
      ),
      1 => 
      array (
        'sku' => 'SRV-ARM-2-V2',
        'precio' => 110,
        'stock' => 999,
        'opcion' => 
        array (
          0 => 'Garantía',
          1 => '2 años',
        ),
      ),
    ),
  ),
  2 => 
  array (
    'cat' => 'armado-de-pc',
    'sku' => 'SRV-ARM-3',
    'nombre' => 'Armado Custom Liquid Cooling',
    'marca' => 'Cooler Master',
    'modelo' => 'CM-HYDRO-CUSTOM',
    'precio' => 160,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Montaje especializado de refrigeración líquida personalizada con doblado de tubos y prueba de fugas de 24h.',
    'desc' => 'Servicio de ensamblaje artesanal para sistemas con refrigeración líquida custom. Incluye curvado de tubos rígidos o flexibles, purgado del circuito con líquido dieléctrico refrigerante, prueba de presión de aire y prueba de hermeticidad continua de 24 horas para garantizar cero fugas.',
    'specs' => 
    array (
      'Tiempo Estimado' => '48 a 72 Horas hábiles',
      'Pruebas' => 'Prueba de estanqueidad 24h',
      'Compatibilidad' => 'Sistemas Hard Tube y Soft Tube',
      'Modalidad' => 'Taller técnico especializado',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '90 Días',
    ),
    'variantes' => NULL,
  ),
  3 => 
  array (
    'cat' => 'armado-de-pc',
    'sku' => 'SRV-ARM-4',
    'nombre' => 'Migración de Componentes a Nuevo Chasis',
    'marca' => 'NZXT',
    'modelo' => 'NZXT-CASE-MIGRATE',
    'precio' => 60,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Desensamblaje cuidadoso de tu equipo actual y traspaso completo a un nuevo gabinete con limpieza previa.',
    'desc' => 'Traspaso integral de tus componentes existentes a un nuevo chasis o gabinete. Desmontamos pieza por pieza con protección antiestática, limpiamos el polvo acumulado y reconstruimos el sistema en el nuevo case asegurando un flujo de aire óptimo y cableado limpio.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 Horas hábiles',
      'Incluye' => 'Desensamble + Limpieza + Ensamble',
      'Modalidad' => 'Taller técnico especializado',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  4 => 
  array (
    'cat' => 'armado-de-pc',
    'sku' => 'SRV-ARM-5',
    'nombre' => 'Actualización General de Hardware',
    'marca' => 'MSI',
    'modelo' => 'MSI-HW-UPGRADE',
    'precio' => 40,
    'precio_oferta' => 35,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Instalación de nueva GPU, procesador, fuente o almacenamiento con actualización de drivers y firmware.',
    'desc' => 'Upgrade técnico de componentes para potenciar tu computadora. Instalamos la pieza seleccionada (tarjeta gráfica, fuente modular, kit de RAM, SSD M.2 o CPU), actualizamos la BIOS si la nueva generación lo requiere e instalamos los últimos controladores oficiales verificados.',
    'specs' => 
    array (
      'Tiempo Estimado' => 'Mismo día (4 a 6 horas)',
      'Incluye' => 'Montaje + Drivers + Verificación',
      'Modalidad' => 'Taller técnico especializado',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  5 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-1',
    'nombre' => 'Mantenimiento Preventivo de Laptop',
    'marca' => 'Dell',
    'modelo' => 'DELL-CARE-LAPTOP',
    'precio' => 35,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Limpieza de ventiladores, disipador y cambio de pasta térmica para bajar temperaturas y eliminar ruidos.',
    'desc' => 'Limpieza interna profunda para laptops de uso personal o empresarial. Desarmamos el módulo térmico, retiramos pelusas y polvo del extractor, aplicamos pasta térmica de alta conductividad y lubricamos los ventiladores para restaurar un funcionamiento silencioso.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 Horas hábiles',
      'Pasta Térmica' => 'Compuesto cerámico de alto rendimiento',
      'Beneficio' => 'Reducción de 8°C a 15°C en CPU/GPU',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  6 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-2',
    'nombre' => 'Limpieza Profunda de PC Gamer',
    'marca' => 'Corsair',
    'modelo' => 'CORSAIR-DEEP-CLEAN',
    'precio' => 55,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Desensamble de componentes, soplado con aire ionizado, cambio de pasta térmica y limpieza de radiadores.',
    'desc' => 'Mantenimiento exhaustivo diseñado para computadoras de alto consumo térmico. Retiramos tarjeta gráfica y radiadores, limpiamos filtros magnéticos, aplicamos solvente dieléctrico en ventiladores y renovamos los compuestos térmicos en CPU y GPU.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 a 48 Horas hábiles',
      'Modalidad' => 'Taller técnico especializado',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  7 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-3',
    'nombre' => 'Cambio de Pasta Térmica y Thermal Pads',
    'marca' => 'Cooler Master',
    'modelo' => 'CM-THERMAL-REFRESH',
    'precio' => 30,
    'precio_oferta' => 25,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Sustitución de almohadillas térmicas resecas y pasta térmica vieja en tarjetas gráficas y procesadores.',
    'desc' => 'Reemplazo de pads térmicos de memoria VRAM y VRM con almohadillas de alta conductividad, además de aplicación fresca de pasta térmica en el procesador. Ideal para tarjetas gráficas y consolas con más de 2 años de uso intensivo.',
    'specs' => 
    array (
      'Tiempo Estimado' => 'Mismo día (4 horas)',
      'Materiales' => 'Thermal pads de 1.0mm a 2.5mm según fabricante',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  8 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-4',
    'nombre' => 'Mantenimiento Preventivo de Servidor / Rack',
    'marca' => 'HP',
    'modelo' => 'HP-PROLIANT-SERVICE',
    'precio' => 130,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Inspección técnica, limpieza de fuentes redundantes, ventilación de chasis 1U/2U y revisión de arreglos RAID.',
    'desc' => 'Servicio para infraestructura empresarial. Limpieza técnica en seco de chasis de servidor tipo rack o torre, revisión de sensores térmicos, verificación del estado de salud de discos en RAID y limpieza de bahías hot-swap.',
    'specs' => 
    array (
      'Tiempo Estimado' => 'Coordinación en horario programado',
      'Modalidad' => 'In situ / Taller técnico',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '60 Días',
    ),
    'variantes' => NULL,
  ),
  9 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-5',
    'nombre' => 'Limpieza y Desinfección de Periféricos y Setup',
    'marca' => 'Razer',
    'modelo' => 'RAZER-PERIPHERAL-SPA',
    'precio' => 25,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Retiro de keycaps de teclado, limpieza por ultrasonido, desinfección de mouse pads y pantallas.',
    'desc' => 'Tratamiento de estética y desinfección para tus herramientas diarias de trabajo o juego. Limpiamos a fondo el teclado mecánico desmontando teclas, lavamos mousepads con productos que preservan el deslizamiento y limpiamos monitores sin rayar el tratamiento antirreflejo.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 Horas hábiles',
      'Productos' => 'Espumas antiestáticas e isopropílico al 99%',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '15 Días',
    ),
    'variantes' => NULL,
  ),
  10 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-1',
    'nombre' => 'Instalación y Configuración de Router Wi-Fi',
    'marca' => 'TP-Link',
    'modelo' => 'TPLINK-ROUTER-SETUP',
    'precio' => 40,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Puesta en marcha de router de alta velocidad, separación de bandas 2.4G/5G y protección con WPA3.',
    'desc' => 'Configuración profesional de tu router doméstico o de oficina. Ajustamos los canales de radiofrecuencia menos congestionados de tu zona, activamos seguridad WPA3, configuramos red de invitados aislada y optimizamos las reglas de QoS para streaming y videojuegos.',
    'specs' => 
    array (
      'Tiempo Estimado' => '1 a 2 Horas',
      'Seguridad' => 'WPA2/WPA3 Personal / DNS seguro',
      'Modalidad' => 'Domicilio o Remoto asistido',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  11 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-2',
    'nombre' => 'Cableado de Red Estructurado (Por punto)',
    'marca' => 'Ubiquiti',
    'modelo' => 'UBIQUITI-POINT-CAT6',
    'precio' => 35,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Tirada de cable Cat6 100% cobre con conectorización RJ45 certificada y canalización estética.',
    'desc' => 'Instalación de punto de red ethernet cableado para oficinas o casas. Incluye cable de categoría 6 100% cobre certificado, jacks modulares hembra tipo keystone, faceplate de pared y certificación de continuidad.',
    'specs' => 
    array (
      'Cable' => 'UTP Cat6 100% Cobre',
      'Velocidad Soportada' => 'Hasta 10 Gbps (a 55m)',
      'Certificación' => 'Test de continuidad y pérdida de retorno',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '90 Días',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'SRV-RED-2-V1',
        'precio' => 35,
        'stock' => 999,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '5 m',
        ),
      ),
      1 => 
      array (
        'sku' => 'SRV-RED-2-V2',
        'precio' => 55,
        'stock' => 999,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '15 m',
        ),
      ),
    ),
  ),
  12 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-3',
    'nombre' => 'Instalación y Calibración de Sistema Mesh',
    'marca' => 'Netgear',
    'modelo' => 'NETGEAR-MESH-DEPLOY',
    'precio' => 65,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Ubicación estratégica de nodos satélite para cobertura sin zonas muertas en casas de varios pisos.',
    'desc' => 'Despliegue de sistemas Wi-Fi en malla (Mesh). Realizamos mapa de calor previo para definir la ubicación de cada nodo, configuramos backhaul cableado o inalámbrico dedicado y probamos roaming transparente en todo el perímetro.',
    'specs' => 
    array (
      'Tiempo Estimado' => '2 a 3 Horas',
      'Cobertura Objetivo' => 'Hasta 400 m²',
      'Modalidad' => 'Visita técnica in situ',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  13 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-4',
    'nombre' => 'Configuración de Switch Administrable y VLANs',
    'marca' => 'D-Link',
    'modelo' => 'DLINK-VLAN-ADMIN',
    'precio' => 80,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Segmentación de tráfico por redes virtuales (VLAN), agregación de enlaces y prioridad de voz/cámaras.',
    'desc' => 'Servicio de ingeniería de red para empresas y locales comerciales. Configuramos switches L2/L3 con segmentación de VLAN para separar terminales de venta (POS), computadoras de oficina, cámaras IP de seguridad y Wi-Fi público de clientes.',
    'specs' => 
    array (
      'Tiempo Estimado' => '2 a 4 Horas',
      'Protocolos' => '802.1Q, LACP, Spanning Tree (RSTP)',
      'Modalidad' => 'In situ / Remoto',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '45 Días',
    ),
    'variantes' => NULL,
  ),
  14 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-5',
    'nombre' => 'Auditoría de Seguridad de Red Local y Firewall',
    'marca' => 'Zyxel',
    'modelo' => 'ZYXEL-SEC-AUDIT',
    'precio' => 95,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Escaneo de puertos abiertos, detección de intrusos y endurecimiento de contraseñas de red.',
    'desc' => 'Evaluación de vulnerabilidades en la red corporativa. Escaneamos dispositivos conectados, verificamos puertos expuestos hacia Internet sin cifrar, cerramos accesos Telnet o HTTP y entregamos un informe con recomendaciones de blindaje de seguridad.',
    'specs' => 
    array (
      'Tiempo Estimado' => '1 Día hábil',
      'Entregable' => 'Reporte PDF con matriz de riesgos y solución',
      'Modalidad' => 'Remoto / In situ',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  15 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-1',
    'nombre' => 'Recuperación de Disco Duro Dañado Nivel 1',
    'marca' => 'Seagate',
    'modelo' => 'SEAGATE-RESCUE-L1',
    'precio' => 90,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Extracción de archivos en discos mecánicos con sectores defectuosos o tablas de particiones corruptas.',
    'desc' => 'Rescate de documentos, fotos y carpetas en discos HDD con fallos lógicos o sectores dañados. Empleamos herramientas forenses de clonado bit a bit para no fatigar el disco y recomponer la estructura de archivos en un medio de almacenamiento seguro.',
    'specs' => 
    array (
      'Tiempo Estimado' => '48 a 72 Horas',
      'Condición' => 'Fallo lógico / Sectores reasignados',
      'Política' => 'Si no hay archivos recuperables, no se cobra el rescate',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '15 Días',
    ),
    'variantes' => NULL,
  ),
  16 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-2',
    'nombre' => 'Rescate de SSD NVMe con Fallo Lógico',
    'marca' => 'Western Digital',
    'modelo' => 'WD-SSD-RESCUE',
    'precio' => 110,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Recuperación en unidades de estado sólido no reconocidas o formateadas accidentalmente.',
    'desc' => 'Diagnóstico y extracción de información en discos SSD M.2 y SATA con tabla de partición GPT perdida, formato involuntario o corrupción del sistema de archivos NTFS/APFS/EXT4.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 a 48 Horas',
      'Tecnología' => 'Soporte PCIe Gen3 / Gen4 / Gen5',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '15 Días',
    ),
    'variantes' => NULL,
  ),
  17 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-3',
    'nombre' => 'Recuperación de Memoria USB / Tarjeta SD',
    'marca' => 'SanDisk',
    'modelo' => 'SANDISK-FLASH-RESCUE',
    'precio' => 45,
    'precio_oferta' => 39,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Rescate de fotos y videos de memorias que piden formato o tienen archivos ocultos por virus.',
    'desc' => 'Solución rápida para memorias flash extraíbles utilizadas en cámaras, celulares o llaves USB que muestran error de lectura o archivos con accesos directos maliciosos.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 Horas hábiles',
      'Formatos' => 'SD, microSD, CF, USB 2.0/3.0',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '15 Días',
    ),
    'variantes' => NULL,
  ),
  18 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-4',
    'nombre' => 'Plan de Respaldo Preventivo de Información (500GB)',
    'marca' => 'Crucial',
    'modelo' => 'CRUCIAL-BACKUP-PLAN',
    'precio' => 40,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Copia de seguridad estructurada de archivos vitales a disco externo o nube con verificación de hash.',
    'desc' => 'Copia de seguridad profesional sin perder carpetas personales, perfiles de correo Outlook, favoritos del navegador ni licencias instaladas. Verificamos la integridad de los datos mediante suma de comprobación.',
    'specs' => 
    array (
      'Capacidad Incluida' => 'Hasta 500 GB',
      'Integridad' => 'Verificación de checksum archivo por archivo',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  19 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-5',
    'nombre' => 'Clonación de Sistema Operativo a SSD',
    'marca' => 'Kingston',
    'modelo' => 'KINGSTON-CLONE-EXP',
    'precio' => 35,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Migra todo tu Windows o macOS con programas y claves a un nuevo SSD sin tener que reinstalar.',
    'desc' => 'Duplicación exacta de tu disco viejo a un nuevo SSD de mayor velocidad o capacidad. Mantiene intactos todos tus programas instalados, configuraciones, contraseñas y archivos sin necesidad de reinstalar desde cero.',
    'specs' => 
    array (
      'Tiempo Estimado' => '2 a 4 Horas',
      'Compatibilidad' => 'Windows 10/11 y macOS',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  20 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-1',
    'nombre' => 'Formateo e Instalación Limpia de Windows 11',
    'marca' => 'Microsoft',
    'modelo' => 'MS-WIN11-CLEAN-INSTALL',
    'precio' => 35,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Instalación oficial de Windows 11 con controladores certificados, optimización y utilitarios esenciales.',
    'desc' => 'Reinstalación desde cero de Windows 11 con medios oficiales de Microsoft. Incluye particionado seguro, instalación de controladores oficiales validados por el fabricante del equipo, utilitarios de lectura PDF, descompresores y optimización de arranque.',
    'specs' => 
    array (
      'Tiempo Estimado' => 'Mismo día (3 a 5 horas)',
      'Sistema' => 'Windows 11 Home / Pro 64-bit',
      'Incluye' => 'Drivers + Actualizaciones + Apps base',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  21 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-2',
    'nombre' => 'Instalación Limpia y Configuración de macOS',
    'marca' => 'Apple',
    'modelo' => 'APPLE-MACOS-RESTORE',
    'precio' => 45,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Restauración oficial del sistema macOS compatible con tu Mac con vinculación segura de Apple ID.',
    'desc' => 'Restauración de fábrica para computadoras MacBook, iMac o Mac mini. Instalamos la última versión oficial compatible de macOS, desvinculamos cuentas anteriores de forma segura y dejamos el equipo listo para su nuevo usuario.',
    'specs' => 
    array (
      'Tiempo Estimado' => '4 a 6 Horas',
      'Compatibilidad' => 'Macs con Apple Silicon e Intel',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  22 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-3',
    'nombre' => 'Formateo con Respaldo de Datos (Hasta 100GB)',
    'marca' => 'Lenovo',
    'modelo' => 'LENOVO-FORMAT-BACKUP',
    'precio' => 50,
    'precio_oferta' => 45,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Salvamos tus fotos y documentos antes de formatear y los reubicamos en tu nuevo sistema limpio.',
    'desc' => 'Tranquilidad total: respaldamos tus carpetas de Documentos, Escritorio, Descargas y Fotos antes de borrar el disco. Reinstalamos el sistema operativo completamente limpio y regresamos tus archivos a sus ubicaciones originales.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 Horas hábiles',
      'Capacidad Respaldo' => 'Hasta 100 GB incluidos',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  23 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-4',
    'nombre' => 'Instalación y Activación de Software de Oficina',
    'marca' => 'Microsoft',
    'modelo' => 'MS-OFFICE-CONFIG',
    'precio' => 25,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Configuración de Word, Excel, PowerPoint y sincronización de correo empresarial en Outlook.',
    'desc' => 'Puesta a punto de programas de ofimática. Instalamos la suite oficial, sincronizamos tus cuentas corporativas de correo con protocolo IMAP o Exchange y configuramos firmas automáticas y plantillas.',
    'specs' => 
    array (
      'Tiempo Estimado' => '1 a 2 Horas',
      'Modalidad' => 'Remoto o Taller',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  24 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-5',
    'nombre' => 'Desinfección Profunda de Virus y Spyware',
    'marca' => 'Kaspersky',
    'modelo' => 'KASP-MALWARE-CLEAN',
    'precio' => 40,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Eliminación de troyanos, mineros ocultos, secuestradores de navegador y limpieza de registro.',
    'desc' => 'Limpieza de amenazas informáticas sin formatear. Detectamos y eliminamos troyanos, cryptominers que consumen tu procesador al 100%, adware que abre publicidad no deseada y reparamos el registro de Windows afectado.',
    'specs' => 
    array (
      'Tiempo Estimado' => '24 Horas hábiles',
      'Herramientas' => 'Scanners con base de firmas actualizada',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  25 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-1',
    'nombre' => 'Soporte Técnico Residencial (1 Hora)',
    'marca' => 'HP',
    'modelo' => 'HP-HOME-SUPPORT-1H',
    'precio' => 45,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Visita de un técnico calificado a tu casa para resolver problemas de lentitud, impresoras o internet.',
    'desc' => 'Un especialista de PayMe Panamá visita tu residencia para atender fallas de conectividad, configuración de impresoras en red, lentitud en computadoras y asesoría técnica directa con atención personalizada.',
    'specs' => 
    array (
      'Duración' => '60 Minutos in situ',
      'Cobertura' => 'Ciudad de Panamá y áreas metropolitanas',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '15 Días',
    ),
    'variantes' => NULL,
  ),
  26 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-2',
    'nombre' => 'Visita Técnica Empresarial (Diagnóstico PyME)',
    'marca' => 'Dell',
    'modelo' => 'DELL-CORP-DIAGNOSTIC',
    'precio' => 80,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Revisión en tu empresa de estaciones de trabajo, impresoras compartidas y puntos de venta.',
    'desc' => 'Diagnóstico profesional para oficinas y comercios de hasta 15 equipos. Evaluamos el estado operativo del parque informático, la velocidad de la red local, el estado de respaldos y elaboramos un plan de mejoras prioritarias.',
    'specs' => 
    array (
      'Duración' => 'Hasta 2 Horas in situ',
      'Entregable' => 'Diagnóstico detallado con cotización de soluciones',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  27 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-3',
    'nombre' => 'Instalación y Conexión de Dispositivos Smart Home',
    'marca' => 'Google',
    'modelo' => 'GOOGLE-SMART-SETUP',
    'precio' => 50,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Vinculación de altavoces inteligentes, cerraduras, luces y cámaras a tu asistente de voz.',
    'desc' => 'Automatiza tu hogar sin complicaciones. Configuramos asistentes inteligentes, asociamos luces inteligentes, enchufes y programamos rutinas de encendido y apagado para tu comodidad.',
    'specs' => 
    array (
      'Tiempo Estimado' => '1.5 a 2 Horas',
      'Dispositivos' => 'Hasta 5 dispositivos inteligentes incluidos',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  28 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-4',
    'nombre' => 'Soporte Técnico Remoto Inmediato (45 Min)',
    'marca' => 'Lenovo',
    'modelo' => 'LENOVO-QUICK-REMOTE',
    'precio' => 30,
    'precio_oferta' => 25,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Asistencia vía TeamViewer/AnyDesk en minutos para configurar software, correos o resolver fallas.',
    'desc' => 'Atención ágil sin esperas. Conexión cifrada directa a tu pantalla con tu autorización para corregir problemas de drivers, instalación de programas, errores de certificados o configuración de impresoras USB.',
    'specs' => 
    array (
      'Tiempo Estimado' => 'Atención en menos de 30 minutos',
      'Requisito' => 'Conexión a internet activa en el equipo',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '15 Días',
    ),
    'variantes' => NULL,
  ),
  29 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-5',
    'nombre' => 'Asesoría Personalizada de Compra Tecnológica',
    'marca' => 'ASUS',
    'modelo' => 'ASUS-TECH-ADVISORY',
    'precio' => 35,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Te ayudamos a elegir la laptop, PC o componentes ideales para tu presupuesto sin malgastar dinero.',
    'desc' => 'Sesión de consultoría 1 a 1 de 45 minutos. Analizamos tus necesidades reales de trabajo, estudio o gaming, comparamos opciones de mercado y te entregamos una lista de compras con los mejores precios y compatibilidad garantizada.',
    'specs' => 
    array (
      'Duración' => '45 Minutos vía videollamada / llamada',
      'Beneficio' => 'Ahorro promedio del 20% en compras de hardware',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de Servicio PayMe',
      'duracion' => '30 Días',
    ),
    'variantes' => NULL,
  ),
  30 => 
  array (
    'cat' => 'software-y-licencias',
    'sku' => 'SRV-SFT-1',
    'nombre' => 'Microsoft 365 Personal (Licencia Digital)',
    'marca' => 'Microsoft',
    'modelo' => 'MS-365-PERSONAL',
    'precio' => 69.99,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Suscripción oficial con Word, Excel, PowerPoint y 1TB de almacenamiento en la nube de OneDrive.',
    'desc' => 'Licencia genuina de Microsoft 365 para un usuario. Utilizable en hasta 5 dispositivos simultáneamente (PC, Mac, iPhone, Android). Incluye las versiones premium de las apps de Office siempre actualizadas y 1.000 GB de almacenamiento seguro en OneDrive.',
    'specs' => 
    array (
      'Entrega' => 'Inmediata vía correo electrónico',
      'Compatibilidad' => 'Windows 10/11, macOS, iOS, Android',
      'Nube' => '1 TB en OneDrive incluido',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía Oficial del Fabricante',
      'duracion' => '1 Año',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'SRV-SFT-1-V1',
        'precio' => 69.99,
        'stock' => 999,
        'opcion' => 
        array (
          0 => 'Garantía',
          1 => '1 año',
        ),
      ),
      1 => 
      array (
        'sku' => 'SRV-SFT-1-V2',
        'precio' => 129.99,
        'stock' => 999,
        'opcion' => 
        array (
          0 => 'Garantía',
          1 => '2 años',
        ),
      ),
    ),
  ),
  31 => 
  array (
    'cat' => 'software-y-licencias',
    'sku' => 'SRV-SFT-2',
    'nombre' => 'Windows 11 Pro OEM Clave de Activación',
    'marca' => 'Microsoft',
    'modelo' => 'MS-WIN11-PRO-OEM',
    'precio' => 149,
    'precio_oferta' => 129,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Licencia vitalicia para 1 PC con BitLocker, Escritorio Remoto y funciones avanzadas de seguridad.',
    'desc' => 'Clave original de producto para Windows 11 Profesional. Se vincula permanentemente a la placa madre de tu equipo. Habilita cifrado de disco BitLocker, virtualización Hyper-V y conexión por Escritorio Remoto.',
    'specs' => 
    array (
      'Tipo de Licencia' => 'OEM Digital Permanente',
      'Arquitectura' => '64-bit',
      'Idioma' => 'Multilenguaje (Español incluido)',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía Oficial del Fabricante',
      'duracion' => 'Vitalicia (1 PC)',
    ),
    'variantes' => NULL,
  ),
  32 => 
  array (
    'cat' => 'software-y-licencias',
    'sku' => 'SRV-SFT-3',
    'nombre' => 'Adobe Creative Cloud Todas las Aplicaciones',
    'marca' => 'Adobe',
    'modelo' => 'ADOBE-CC-ALL-APPS',
    'precio' => 299,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Acceso completo a Photoshop, Illustrator, Premiere Pro, After Effects y 100GB en la nube.',
    'desc' => 'Plan completo con más de 20 herramientas de creación digital de Adobe. Incluye Photoshop, Illustrator, InDesign, Premiere Pro, After Effects, Lightroom, tipografías de Adobe Fonts y 100GB de almacenamiento en la nube.',
    'specs' => 
    array (
      'Entrega' => 'Activación en cuenta personal de Adobe',
      'Compatibilidad' => 'Windows y macOS',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía Oficial del Fabricante',
      'duracion' => '1 Año',
    ),
    'variantes' => NULL,
  ),
  33 => 
  array (
    'cat' => 'software-y-licencias',
    'sku' => 'SRV-SFT-4',
    'nombre' => 'Kaspersky Total Security Antivirus',
    'marca' => 'Kaspersky',
    'modelo' => 'KASP-TOTAL-SEC-1Y',
    'precio' => 39.99,
    'precio_oferta' => 34.99,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Máxima protección contra ransomware, pagos bancarios seguros y VPN de navegación privada.',
    'desc' => 'Solución integral de ciberseguridad para tu equipo personal o laboral. Protege tus transacciones bancarias en un navegador aislado, bloquea páginas de phishing, administra tus contraseñas y frena ataques de ransomware en tiempo real.',
    'specs' => 
    array (
      'Entrega' => 'Código alfanumérico instantáneo',
      'Compatibilidad' => 'Windows, Mac, Android, iOS',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía Oficial del Fabricante',
      'duracion' => '1 Año',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'SRV-SFT-4-V1',
        'precio' => 39.99,
        'stock' => 999,
        'opcion' => 
        array (
          0 => 'Garantía',
          1 => '1 año',
        ),
      ),
      1 => 
      array (
        'sku' => 'SRV-SFT-4-V2',
        'precio' => 69.99,
        'stock' => 999,
        'opcion' => 
        array (
          0 => 'Garantía',
          1 => '2 años',
        ),
      ),
    ),
  ),
  34 => 
  array (
    'cat' => 'software-y-licencias',
    'sku' => 'SRV-SFT-5',
    'nombre' => 'Norton 360 Deluxe 5 Dispositivos',
    'marca' => 'Norton',
    'modelo' => 'NORTON-360-DELUXE',
    'precio' => 49.99,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Seguridad completa para hasta 5 dispositivos con VPN segura, 50GB en nube y control parental.',
    'desc' => 'Protección multicapa para toda tu familia. Defiende contra virus y malware en 5 equipos simultáneos, incluye VPN ilimitada para conectarte con seguridad en redes Wi-Fi públicas y monitoreo de la Dark Web para alertarte de filtraciones de datos.',
    'specs' => 
    array (
      'Entrega' => 'Licencia digital oficial',
      'Capacidad' => 'Hasta 5 dispositivos',
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía Oficial del Fabricante',
      'duracion' => '1 Año',
    ),
    'variantes' => NULL,
  ),
);
    }

    /**
     * Catálogo determinista de 140 productos físicos organizados por subcategoría.
     */
    protected function catalogoFisicos(): array
    {
        return array (
  0 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-1',
    'nombre' => 'Apple MacBook Air 13.6" M2',
    'marca' => 'Apple',
    'modelo' => 'MLY33LL/A',
    'precio' => 1099.0,
    'precio_oferta' => 999.0,
    'stock' => 13,
    'destacado' => true,
    'desc_corta' => 'Chip M2 ultraeficiente, pantalla Liquid Retina de 13.6 pulgadas y hasta 18 horas de batería.',
    'desc' => 'La laptop más delgada y portátil de Apple. Diseñada en aluminio unibody ultraligero con chip M2 de 8 núcleos de CPU y 8 núcleos de GPU. Su pantalla Liquid Retina ofrece 500 nits de brillo y compatibilidad con 1.000 millones de colores.',
    'peso' => 1.24,
    'largo' => 30.41,
    'ancho' => 21.5,
    'alto' => 1.13,
    'specs' => 
    array (
      'Pantalla' => '13.6" Liquid Retina',
      'Procesador' => 'Apple M2 (8 núcleos)',
      'Batería' => 'Hasta 18 horas',
      'Puertos' => '2x Thunderbolt / MagSafe 3',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'LAP-2-1-V1',
        'precio' => 1099.0,
        'stock' => 8,
        'opcion' => 
        array (
          0 => 'Memoria RAM',
          1 => '16 GB',
        ),
      ),
      1 => 
      array (
        'sku' => 'LAP-2-1-V2',
        'precio' => 1399.0,
        'stock' => 5,
        'opcion' => 
        array (
          0 => 'Memoria RAM',
          1 => '32 GB',
        ),
      ),
    ),
  ),
  1 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-2',
    'nombre' => 'Dell XPS 13 Plus 9320',
    'marca' => 'Dell',
    'modelo' => 'XPS-9320-i7',
    'precio' => 1299.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => true,
    'desc_corta' => 'Diseño minimalista con barra táctil capacitiva, pantalla InfinityEdge FHD+ e Intel Core i7.',
    'desc' => 'Portátil de alta gama empresarial construido en aluminio mecanizado CNC. Integra touchpad háptico de vidrio sin bordes visibles, procesador Intel Core i7 de 13.ª generación y teclado de borde a borde.',
    'peso' => 1.26,
    'largo' => 29.53,
    'ancho' => 19.91,
    'alto' => 1.52,
    'specs' => 
    array (
      'Pantalla' => '13.4" FHD+ InfinityEdge',
      'Procesador' => 'Intel Core i7-1360P',
      'RAM' => '16 GB LPDDR5',
      'Almacenamiento' => '512 GB SSD NVMe',
    ),
    'variantes' => NULL,
  ),
  2 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-3',
    'nombre' => 'Lenovo ThinkPad X1 Carbon Gen 11',
    'marca' => 'Lenovo',
    'modelo' => '21HM002DUS',
    'precio' => 1499.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Chasis de fibra de carbono probado bajo estándares militares con teclado legendario ThinkPad.',
    'desc' => 'El estándar de oro para ejecutivos y programadores. Certificado bajo 12 pruebas de grado militar MIL-STD-810H. Cuenta con lector de huellas biométrico, obturador de privacidad en cámara y procesador Intel Core i7.',
    'peso' => 1.12,
    'largo' => 31.56,
    'ancho' => 22.25,
    'alto' => 1.53,
    'specs' => 
    array (
      'Pantalla' => '14.0" WUXGA Antirreflejo',
      'Procesador' => 'Intel Core i7-1365U',
      'RAM' => '16 GB',
      'Seguridad' => 'dTPM 2.0 / Lector Huellas',
    ),
    'variantes' => NULL,
  ),
  3 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-4',
    'nombre' => 'HP Spectre x360 14 2-in-1',
    'marca' => 'HP',
    'modelo' => '14-ef2013dx',
    'precio' => 1199.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Convertible premium con bisagra de 360°, pantalla táctil OLED 3K2K y lápiz digital recargable.',
    'desc' => 'Laptop y tablet en un solo equipo de corte biselado de lujo. Su pantalla OLED ofrece negros perfectos con certificación VESA DisplayHDR True Black 400. Incluye cámara web inteligente de 5MP.',
    'peso' => 1.36,
    'largo' => 29.8,
    'ancho' => 22.04,
    'alto' => 1.69,
    'specs' => 
    array (
      'Pantalla' => '13.5" OLED 3K2K Táctil',
      'Procesador' => 'Intel Core i7-1355U',
      'Audio' => 'Bang & Olufsen Quad Speakers',
    ),
    'variantes' => NULL,
  ),
  4 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-5',
    'nombre' => 'Acer Swift Go 14 OLED',
    'marca' => 'Acer',
    'modelo' => 'SFG14-71-78LN',
    'precio' => 799.0,
    'precio_oferta' => 749.0,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Increíble pantalla OLED 2.8K de 90Hz, procesador Intel de 13.ª gen y peso pluma de 1.25 kg.',
    'desc' => 'Rendimiento y pantalla asombrosa al mejor precio. Cuenta con panel OLED calibrado al 100% DCI-P3, doble ventilador TwinAir para bajas temperaturas y conectividad Wi-Fi 6E.',
    'peso' => 1.25,
    'largo' => 31.29,
    'ancho' => 21.79,
    'alto' => 1.49,
    'specs' => 
    array (
      'Pantalla' => '14.0" 2.8K OLED 90Hz',
      'Procesador' => 'Intel Core i7-13700H',
      'Almacenamiento' => '512 GB PCIe 4.0',
    ),
    'variantes' => NULL,
  ),
  5 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-1',
    'nombre' => 'ASUS ROG Zephyrus G14 Gaming',
    'marca' => 'ASUS',
    'modelo' => 'GA402XY-G14',
    'precio' => 1599.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => true,
    'desc_corta' => 'Potencia pura en 14 pulgadas: AMD Ryzen 9, GeForce RTX 4080 y pantalla Nebula Display 165Hz.',
    'desc' => 'La laptop gaming compacta más premiada del mundo. Equipada con cámara de vapor, metal líquido sobre CPU y GPU, pantalla ROG Nebula QHD+ de 165Hz con tiempo de respuesta de 3ms y switch MUX.',
    'peso' => 1.72,
    'largo' => 31.2,
    'ancho' => 22.7,
    'alto' => 1.95,
    'specs' => 
    array (
      'Pantalla' => '14" QHD+ 165Hz Nebula',
      'Procesador' => 'AMD Ryzen 9 7940HS',
      'Gráfica' => 'NVIDIA RTX 4080 12GB',
      'RAM' => '32 GB DDR5',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'LAP-3-1-V1',
        'precio' => 1599.0,
        'stock' => 6,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'LAP-3-1-V2',
        'precio' => 1629.0,
        'stock' => 4,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
  ),
  6 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-2',
    'nombre' => 'Acer Predator Helios 16',
    'marca' => 'Acer',
    'modelo' => 'PH16-71-72YG',
    'precio' => 1399.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Refrigeración con ventiladores metálicos AeroBlade 3D y tarjeta gráfica RTX 4070 a 140W.',
    'desc' => 'Máquina de combate para eSports. Su pantalla de 16 pulgadas WQXGA a 240Hz elimina el desenfoque en shooters competitivos. Cuenta con teclado retroiluminado RGB por tecla.',
    'peso' => 2.6,
    'largo' => 35.77,
    'ancho' => 27.87,
    'alto' => 2.69,
    'specs' => 
    array (
      'Pantalla' => '16" WQXGA 240Hz 3ms',
      'Procesador' => 'Intel Core i7-13700HX',
      'Gráfica' => 'RTX 4070 8GB GDDR6',
    ),
    'variantes' => NULL,
  ),
  7 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-3',
    'nombre' => 'MSI Katana 15 B13V',
    'marca' => 'MSI',
    'modelo' => 'KATANA-15-B13VGK',
    'precio' => 1099.0,
    'precio_oferta' => 999.0,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Excelente relación precio-rendimiento con Intel Core i7 de 13.ª gen y gráficos RTX 4060.',
    'desc' => 'Inspirada en el filo de una espada samurái. Integra sistema Cooler Boost 5 con 2 ventiladores y 6 heatpipes de cobre compartidos para mantener temperaturas bajo control.',
    'peso' => 2.25,
    'largo' => 35.9,
    'ancho' => 25.9,
    'alto' => 2.49,
    'specs' => 
    array (
      'Pantalla' => '15.6" FHD 144Hz IPS',
      'Procesador' => 'Intel Core i7-13620H',
      'Gráfica' => 'RTX 4060 8GB',
    ),
    'variantes' => NULL,
  ),
  8 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-4',
    'nombre' => 'Lenovo Legion Pro 5 Gen 8',
    'marca' => 'Lenovo',
    'modelo' => '16IRX8-82WK',
    'precio' => 1449.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Potenciado por el chip de IA Lenovo LA1 y pantalla PureSight Gaming de 16 pulgadas 240Hz.',
    'desc' => 'Construida para rendir sin piedad. El chip de Inteligencia Artificial Legion Coldfront 5.0 ajusta los voltajes y ventilación en tiempo real para exprimir hasta el último FPS.',
    'peso' => 2.55,
    'largo' => 36.34,
    'ancho' => 26.03,
    'alto' => 2.68,
    'specs' => 
    array (
      'Pantalla' => '16" WQXGA 240Hz 500 nits',
      'Procesador' => 'AMD Ryzen 7 7745HX',
      'Gráfica' => 'RTX 4070 8GB',
    ),
    'variantes' => NULL,
  ),
  9 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-5',
    'nombre' => 'HP Omen 16 Gaming Laptop',
    'marca' => 'HP',
    'modelo' => '16-wf0019nr',
    'precio' => 1299.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Diseño sobrio y elegante con teclado de respuesta rápida y sonido afinado por Bang & Olufsen.',
    'desc' => 'Elegante por fuera, monstruo gamer por dentro. Posee tecnología OMEN Tempest Cooling con flujo de aire en 4 direcciones y software OMEN Gaming Hub.',
    'peso' => 2.37,
    'largo' => 36.9,
    'ancho' => 25.94,
    'alto' => 2.35,
    'specs' => 
    array (
      'Pantalla' => '16.1" QHD 165Hz IPS',
      'Procesador' => 'Intel Core i7-13700HX',
      'RAM' => '16 GB DDR5 4800MHz',
    ),
    'variantes' => NULL,
  ),
  10 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-1',
    'nombre' => 'Apple Mac mini M2',
    'marca' => 'Apple',
    'modelo' => 'MMFJ3LL/A',
    'precio' => 599.0,
    'precio_oferta' => NULL,
    'stock' => 17,
    'destacado' => true,
    'desc_corta' => 'Mini PC revolucionario: potencia gigantesca en un cuerpo cuadrado de apenas 19.7 cm de lado.',
    'desc' => 'El computador de escritorio más compacto y eficiente. Con chip M2 de Apple capaz de editar múltiples transmisiones de video en 4K y 8K ProRes simultáneas.',
    'peso' => 1.18,
    'largo' => 19.7,
    'ancho' => 19.7,
    'alto' => 3.58,
    'specs' => 
    array (
      'Procesador' => 'Apple M2 (8 CPU / 10 GPU)',
      'RAM' => '8 GB Unificada',
      'Puertos' => '2x Thunderbolt 4 / 2x USB-A / HDMI',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'DESK-4-1-V1',
        'precio' => 599.0,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '256 GB',
        ),
      ),
      1 => 
      array (
        'sku' => 'DESK-4-1-V2',
        'precio' => 799.0,
        'stock' => 7,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '512 GB',
        ),
      ),
    ),
  ),
  11 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-2',
    'nombre' => 'Dell Inspiron 3020 Desktop',
    'marca' => 'Dell',
    'modelo' => 'I3020-7212BLK',
    'precio' => 649.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'PC de torre para oficina y hogar con procesador Intel Core i7 de 13.ª generación.',
    'desc' => 'Confiabilidad probada para productividad multitarea continua. Incluye teclado y mouse óptico Dell originales, unidad SSD PCIe rápida y múltiples puertos frontales.',
    'peso' => 4.9,
    'largo' => 32.43,
    'ancho' => 15.4,
    'alto' => 29.2,
    'specs' => 
    array (
      'Procesador' => 'Intel Core i7-13700',
      'RAM' => '16 GB DDR4',
      'Almacenamiento' => '512 GB NVMe SSD',
    ),
    'variantes' => NULL,
  ),
  12 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-3',
    'nombre' => 'HP Envy Desktop TE02',
    'marca' => 'HP',
    'modelo' => 'TE02-1054',
    'precio' => 899.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Chasis con acceso sin herramientas, refrigeración optimizada y expansión para tarjetas gráficas.',
    'desc' => 'Computadora de alto rendimiento para creadores de contenido, edición de fotos y video. Fácilmente ampliable sin tornillos en bahías de discos y slots de memoria RAM.',
    'peso' => 6.2,
    'largo' => 39.6,
    'ancho' => 16.5,
    'alto' => 38.3,
    'specs' => 
    array (
      'Procesador' => 'Intel Core i7-13700',
      'RAM' => '32 GB DDR5',
      'Almacenamiento' => '1 TB NVMe SSD',
    ),
    'variantes' => NULL,
  ),
  13 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-4',
    'nombre' => 'Lenovo IdeaCentre 3 07IAB7',
    'marca' => 'Lenovo',
    'modelo' => '90SM0084US',
    'precio' => 479.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Torre delgada y silenciosa con procesador Intel Core i5 ideal para teletrabajo y estudio.',
    'desc' => 'Equipo estilizado que ahorra espacio en escritorios pequeños. Ofrece conectividad Wi-Fi 6 integrada, Bluetooth 5.2 y 7 puertos USB distribuidos entre el frontal y la parte posterior.',
    'peso' => 4.2,
    'largo' => 30.35,
    'ancho' => 10.0,
    'alto' => 27.48,
    'specs' => 
    array (
      'Procesador' => 'Intel Core i5-12400',
      'RAM' => '8 GB DDR4',
      'Almacenamiento' => '256 GB SSD',
    ),
    'variantes' => NULL,
  ),
  14 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-5',
    'nombre' => 'ASUS ExpertCenter D5 SFF',
    'marca' => 'ASUS',
    'modelo' => 'D500SD-I58512B0X',
    'precio' => 549.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Formato compacto Small Form Factor (SFF) con grado de durabilidad militar para empresas.',
    'desc' => 'Diseñado para durar en entornos de oficina y puntos de venta con uso continuo las 24 horas del día. Placa madre de grado industrial con condensadores sólidos japoneses al 100%.',
    'peso' => 5.0,
    'largo' => 30.8,
    'ancho' => 9.46,
    'alto' => 33.96,
    'specs' => 
    array (
      'Procesador' => 'Intel Core i5-12500',
      'Formato' => 'Small Form Factor',
      'Seguridad' => 'Chip TPM 2.0',
    ),
    'variantes' => NULL,
  ),
  15 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-1',
    'nombre' => 'Intel Core i9-14900K 24 Núcleos',
    'marca' => 'Intel',
    'modelo' => 'BX8071514900K',
    'precio' => 549.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => true,
    'desc_corta' => 'Hasta 6.0 GHz de frecuencia máxima con 24 núcleos (8P + 16E) y 32 hilos de procesamiento.',
    'desc' => 'El buque insignia de Intel para gaming extremo y renderizado 3D sin concesiones. Compatible con placas madre LGA1700 de las series 600 y 700 con soporte PCIe 5.0 y memorias DDR5.',
    'peso' => 0.1,
    'largo' => 4.5,
    'ancho' => 3.75,
    'alto' => 0.5,
    'specs' => 
    array (
      'Núcleos' => '24 (8 Rendimiento / 16 Eficiencia)',
      'Frecuencia Máxima' => '6.0 GHz',
      'Socket' => 'LGA1700',
      'Caché' => '36 MB Intel Smart Cache',
    ),
    'variantes' => NULL,
  ),
  16 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-2',
    'nombre' => 'AMD Ryzen 9 7950X3D 16 Núcleos',
    'marca' => 'AMD',
    'modelo' => '100-100000908WOF',
    'precio' => 589.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => true,
    'desc_corta' => 'Tecnología 3D V-Cache masiva con 144MB de caché total: el procesador definitivo para videojuegos.',
    'desc' => 'Arquitectura Zen 4 en 5nm con tecnología AMD 3D V-Cache apilada en vertical. Ofrece un rendimiento imbatible en tasas de cuadros por segundo en títulos AAA y simulación pesada.',
    'peso' => 0.12,
    'largo' => 4.0,
    'ancho' => 4.0,
    'alto' => 0.6,
    'specs' => 
    array (
      'Núcleos' => '16 Núcleos / 32 Hilos',
      'Caché L3' => '128 MB 3D V-Cache',
      'Socket' => 'AM5',
      'TDP' => '120W',
    ),
    'variantes' => NULL,
  ),
  17 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-3',
    'nombre' => 'Intel Core i7-14700K 20 Núcleos',
    'marca' => 'Intel',
    'modelo' => 'BX8071514700K',
    'precio' => 399.0,
    'precio_oferta' => 379.0,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => '20 núcleos y hasta 5.6 GHz: equilibrio magistral para gaming competitivo y edición de video 4K.',
    'desc' => 'Con 4 núcleos de eficiencia adicionales respecto a su generación previa, el i7-14700K entrega un salto notorio en productividad multitarea manteniendo temperaturas razonables con enfriamiento AIO.',
    'peso' => 0.1,
    'largo' => 4.5,
    'ancho' => 3.75,
    'alto' => 0.5,
    'specs' => 
    array (
      'Núcleos' => '20 (8P + 12E) / 28 Hilos',
      'Frecuencia' => 'Hasta 5.6 GHz',
      'Socket' => 'LGA1700',
    ),
    'variantes' => NULL,
  ),
  18 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-4',
    'nombre' => 'AMD Ryzen 7 7800X3D 8 Núcleos',
    'marca' => 'AMD',
    'modelo' => '100-100000910WOF',
    'precio' => 449.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'El rey indiscutible del gaming puro: 8 núcleos Zen 4 con 96MB de caché L3 y bajo consumo.',
    'desc' => 'La CPU más recomendada por analistas y jugadores de eSports. Su diseño de un solo chiplet CCD elimina latencias entre núcleos y exprime el potencial de cualquier tarjeta gráfica de alta gama.',
    'peso' => 0.11,
    'largo' => 4.0,
    'ancho' => 4.0,
    'alto' => 0.6,
    'specs' => 
    array (
      'Núcleos' => '8 Núcleos / 16 Hilos',
      'Caché' => '96 MB L3 3D V-Cache',
      'Socket' => 'AM5',
    ),
    'variantes' => NULL,
  ),
  19 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-5',
    'nombre' => 'Intel Core i5-14600K 14 Núcleos',
    'marca' => 'Intel',
    'modelo' => 'BX8071514600K',
    'precio' => 299.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'El rey de la gama media para ensamble gamer: 14 núcleos, 20 hilos y hasta 5.3 GHz con overclock.',
    'desc' => 'Desempeño superior en gaming sin requerir disipadores gigantescos ni fuentes de mil vatios. Soporta memorias DDR4 y DDR5 para adaptarse a cualquier presupuesto.',
    'peso' => 0.09,
    'largo' => 4.5,
    'ancho' => 3.75,
    'alto' => 0.5,
    'specs' => 
    array (
      'Núcleos' => '14 (6P + 8E) / 20 Hilos',
      'Frecuencia' => 'Hasta 5.3 GHz',
      'Socket' => 'LGA1700',
    ),
    'variantes' => NULL,
  ),
  20 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-1',
    'nombre' => 'ASUS ROG Strix GeForce RTX 4090 24GB',
    'marca' => 'ASUS',
    'modelo' => 'ROG-STRIX-RTX4090-O24G',
    'precio' => 1899.0,
    'precio_oferta' => NULL,
    'stock' => 4,
    'destacado' => true,
    'desc_corta' => 'La tarjeta gráfica más potente jamás construida: arquitectura Ada Lovelace y 24GB GDDR6X.',
    'desc' => 'El pináculo del hardware gráfico. Disipador gigante de 3.5 slots con marco de metal fundido a presión, ventiladores Axial-tech mejorados con un 23% más de flujo de aire y diseño de alimentación de 24+4 fases.',
    'peso' => 2.5,
    'largo' => 35.76,
    'ancho' => 14.93,
    'alto' => 7.01,
    'specs' => 
    array (
      'Memoria' => '24 GB GDDR6X 384-bit',
      'Ray Tracing' => '4.ª Gen Tensor / 3.ª Gen RT',
      'Puertos' => '2x HDMI 2.1a / 3x DisplayPort 1.4a',
    ),
    'variantes' => NULL,
  ),
  21 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-2',
    'nombre' => 'Gigabyte GeForce RTX 4080 Super Gaming OC',
    'marca' => 'Gigabyte',
    'modelo' => 'GV-N408SGAMING-OC-16GD',
    'precio' => 1049.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Juega en 4K con trazado de rayos completo y DLSS 3.5 con generación de fotogramas por IA.',
    'desc' => 'Sistema de refrigeración WINDFORCE con tres ventiladores de 110mm de giro alterno, placa de cobre compuesto en contacto directo con la GPU y placa trasera metálica de refuerzo.',
    'peso' => 1.8,
    'largo' => 34.2,
    'ancho' => 15.0,
    'alto' => 7.5,
    'specs' => 
    array (
      'Memoria' => '16 GB GDDR6X 256-bit',
      'Núcleos CUDA' => '10240',
      'Interfaz' => 'PCIe 4.0 x16',
    ),
    'variantes' => NULL,
  ),
  22 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-3',
    'nombre' => 'MSI GeForce RTX 4070 Ti Super Ventus 3X',
    'marca' => 'MSI',
    'modelo' => 'RTX-4070TIS-VENTUS-3X',
    'precio' => 829.0,
    'precio_oferta' => 799.0,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Actualizada a 16GB VRAM y bus de 256 bits para dominar resolución 1440p y 4K con soltura.',
    'desc' => 'Diseño sobrio en tonos plata y negro sin luces distractivas. Ventiladores TORX Fan 4.0 con aspas unidas por pares para concentrar la presión estática hacia el radiador.',
    'peso' => 1.45,
    'largo' => 30.8,
    'ancho' => 12.0,
    'alto' => 5.2,
    'specs' => 
    array (
      'Memoria' => '16 GB GDDR6X',
      'Consumo' => '285W',
      'Recomendación Fuente' => '700W',
    ),
    'variantes' => NULL,
  ),
  23 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-4',
    'nombre' => 'AMD Radeon RX 7900 XTX 24GB',
    'marca' => 'AMD',
    'modelo' => 'RX-7900XTX-24G',
    'precio' => 949.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Arquitectura RDNA 3 con tecnología de chiplets, 24GB de memoria y soporte DisplayPort 2.1.',
    'desc' => 'La mejor opción en rasterización pura para gaming 4K sin cuellos de botella de memoria de video. Incluye AMD FidelityFX Super Resolution (FSR 3) con Frame Generation abierto.',
    'peso' => 1.8,
    'largo' => 28.7,
    'ancho' => 13.5,
    'alto' => 5.1,
    'specs' => 
    array (
      'Memoria' => '24 GB GDDR6 384-bit',
      'Infinity Cache' => '96 MB',
      'Salidas' => 'DisplayPort 2.1 / HDMI 2.1',
    ),
    'variantes' => NULL,
  ),
  24 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-5',
    'nombre' => 'ASUS TUF Gaming Radeon RX 7800 XT',
    'marca' => 'ASUS',
    'modelo' => 'TUF-RX7800XT-O16G',
    'precio' => 529.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Durabilidad legendaria TUF: condensadores de grado militar y 16GB de memoria para 1440p.',
    'desc' => 'Tarjeta gráfica construida para resistir años de uso ininterrumpido. Doble rodamiento de bolas en ventiladores que duplica su vida útil y switch Dual BIOS para alternar entre perfil silencioso y rendimiento.',
    'peso' => 1.35,
    'largo' => 31.98,
    'ancho' => 15.09,
    'alto' => 5.92,
    'specs' => 
    array (
      'Memoria' => '16 GB GDDR6 256-bit',
      'Refrigeración' => 'Triple ventilador Axial-tech',
      'Conectores' => '2x PCIe 8-pin',
    ),
    'variantes' => NULL,
  ),
  25 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-1',
    'nombre' => 'Corsair Vengeance RGB 32GB (2x16GB) DDR5 6000MHz',
    'marca' => 'Corsair',
    'modelo' => 'CMH32GX5M2B6000C30',
    'precio' => 119.0,
    'precio_oferta' => NULL,
    'stock' => 25,
    'destacado' => true,
    'desc_corta' => 'Latencia ultra baja CL30, iluminación dinámica de diez zonas RGB y perfiles XMP 3.0 / EXPO.',
    'desc' => 'Optimizado para las plataformas más recientes de Intel y AMD. Su disipador de aluminio sólido mantiene los módulos fríos mientras que el software iCUE permite crear efectos luminosos sincronizados con tus juegos.',
    'peso' => 0.15,
    'largo' => 13.8,
    'ancho' => 4.4,
    'alto' => 0.7,
    'specs' => 
    array (
      'Capacidad' => '32 GB (2x 16 GB)',
      'Velocidad' => 'DDR5-6000 MHz',
      'Latencia' => 'CL30-36-36-76',
      'Voltaje' => '1.40V',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'RAM-8-1-V1',
        'precio' => 119.0,
        'stock' => 14,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'RAM-8-1-V2',
        'precio' => 124.0,
        'stock' => 11,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
  ),
  26 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-2',
    'nombre' => 'Kingston Fury Beast 32GB (2x16GB) DDR5 5600MHz',
    'marca' => 'Kingston',
    'modelo' => 'KF556C40BBK2-32',
    'precio' => 99.0,
    'precio_oferta' => 89.0,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Disipador de calor de perfil bajo ideal para gabinetes compactos o disipadores de aire grandes.',
    'desc' => 'Overclocking automático Plug N Play a 4800MT/s o perfil XMP 3.0 a 5600MT/s con certificación Intel y AMD. Estabilidad probada al 100% en fábrica a velocidad nominal.',
    'peso' => 0.12,
    'largo' => 13.33,
    'ancho' => 3.49,
    'alto' => 0.66,
    'specs' => 
    array (
      'Capacidad' => '32 GB Kit',
      'Velocidad' => 'DDR5 5600 MT/s',
      'Perfil' => 'Intel XMP 3.0',
    ),
    'variantes' => NULL,
  ),
  27 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-3',
    'nombre' => 'Crucial Pro 32GB (2x16GB) DDR5 5600MHz',
    'marca' => 'Crucial',
    'modelo' => 'CP2K16G56C46U5',
    'precio' => 89.0,
    'precio_oferta' => NULL,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Estabilidad pura sin luces: disipador negro mate de bajo perfil para estaciones de trabajo.',
    'desc' => 'Diseñado por Micron, uno de los tres únicos fabricantes de chips de memoria del planeta. Compatibilidad universal sin complicaciones de configuración.',
    'peso' => 0.11,
    'largo' => 13.33,
    'ancho' => 3.12,
    'alto' => 0.7,
    'specs' => 
    array (
      'Capacidad' => '32 GB (2x16GB)',
      'Velocidad' => '5600 MHz',
      'Voltaje' => '1.1V estándar',
    ),
    'variantes' => NULL,
  ),
  28 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-4',
    'nombre' => 'Adata XPG Lancer Blade RGB 32GB DDR5 6000MHz',
    'marca' => 'Adata',
    'modelo' => 'AX5U6000C3016G-SLAB',
    'precio' => 109.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Diseño compacto de solo 33mm de altura con difusor de luz geométrico y ECC integrado.',
    'desc' => 'Perfecta para ensambles en chasis pequeños (SFF) donde el espacio sobre el socket es crítico. Incluye circuito integrado de administración de energía (PMIC) para voltajes estables.',
    'peso' => 0.13,
    'largo' => 13.33,
    'ancho' => 3.3,
    'alto' => 0.75,
    'specs' => 
    array (
      'Capacidad' => '32 GB Kit',
      'Velocidad' => '6000 MT/s',
      'Latencia' => 'CL30',
    ),
    'variantes' => NULL,
  ),
  29 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-5',
    'nombre' => 'Kingston Fury Impact 16GB DDR4 3200MHz SO-DIMM',
    'marca' => 'Kingston',
    'modelo' => 'KF432S20IB/16',
    'precio' => 45.0,
    'precio_oferta' => NULL,
    'stock' => 22,
    'destacado' => false,
    'desc_corta' => 'Actualiza tu laptop o mini PC a la máxima velocidad DDR4 con overclocking automático.',
    'desc' => 'Módulo de memoria SO-DIMM para portátiles de gaming y trabajo. Se ajusta automáticamente a la velocidad más alta permitida por la BIOS del sistema sin requerir configuración manual.',
    'peso' => 0.03,
    'largo' => 6.96,
    'ancho' => 3.0,
    'alto' => 0.38,
    'specs' => 
    array (
      'Formato' => 'SO-DIMM 260 pines',
      'Capacidad' => '16 GB',
      'Frecuencia' => '3200 MHz DDR4',
      'Voltaje' => '1.2V',
    ),
    'variantes' => NULL,
  ),
  30 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-1',
    'nombre' => 'Corsair RM850x 850W 80 Plus Gold Modular',
    'marca' => 'Corsair',
    'modelo' => 'CP-9020200-NA',
    'precio' => 139.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => true,
    'desc_corta' => 'Condensadores 100% japoneses a 105°C, modo de ventilador Zero RPM y cableado totalmente modular.',
    'desc' => 'La fuente de alimentación más recomendada de su categoría. Certificada 80 PLUS Gold con eficiencia energética de hasta el 90%. El ventilador de levitación magnética de 135mm no gira a cargas medias y bajas.',
    'peso' => 1.7,
    'largo' => 16.0,
    'ancho' => 15.0,
    'alto' => 8.6,
    'specs' => 
    array (
      'Potencia' => '850 Watts',
      'Certificación' => '80 PLUS Gold',
      'Modular' => '100% Modular',
      'Garantía' => '10 Años',
    ),
    'variantes' => NULL,
  ),
  31 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-2',
    'nombre' => 'EVGA SuperNOVA 750 GT 80 Plus Gold',
    'marca' => 'EVGA',
    'modelo' => '220-GT-0750-Y1',
    'precio' => 109.0,
    'precio_oferta' => 99.0,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Excelente relación calidad-precio con modo Auto ECO para operación completamente silenciosa.',
    'desc' => 'Construcción robusta con protecciones eléctricas completas (OVP, UVP, OCP, OPP, SCP, OTP). Cableado plano totalmente modular para un ensamblaje limpio y ordenado.',
    'peso' => 1.6,
    'largo' => 15.0,
    'ancho' => 15.0,
    'alto' => 8.5,
    'specs' => 
    array (
      'Potencia' => '750 Watts',
      'Ventilador' => '135mm Fluid Dynamic Bearing',
      'Modular' => 'Totalmente modular',
    ),
    'variantes' => NULL,
  ),
  32 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-3',
    'nombre' => 'Cooler Master MWE Gold 850 V2 ATX 3.0',
    'marca' => 'Cooler Master',
    'modelo' => 'MPE-8501-AFAAG-3US',
    'precio' => 129.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Preparada para tarjetas gráficas de última generación con cable nativo 12VHPWR PCIe 5.0.',
    'desc' => 'Soporta picos transitorios de potencia de hasta el 200% según el estándar ATX 3.0 de Intel. Incluye cable dedicado 12V-2x6 de 16 pines sin necesidad de usar adaptadores.',
    'peso' => 1.75,
    'largo' => 16.0,
    'ancho' => 15.0,
    'alto' => 8.6,
    'specs' => 
    array (
      'Potencia' => '850W',
      'Estándar' => 'ATX 3.0 / PCIe 5.0 Nativo',
      'Certificación' => '80 Plus Gold',
    ),
    'variantes' => NULL,
  ),
  33 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-4',
    'nombre' => 'Thermaltake Toughpower GF1 750W Gold',
    'marca' => 'Thermaltake',
    'modelo' => 'PS-TPD-0750FNFAGU-1',
    'precio' => 99.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Ondulación ultra baja de menos de 30mV y regulación de voltaje estricta de ±2%.',
    'desc' => 'Entrega de energía limpia y precisa para proteger placas madre y tarjetas gráficas de gama alta. Ventilador hidráulico con interruptor Smart Zero Fan.',
    'peso' => 1.55,
    'largo' => 16.0,
    'ancho' => 15.0,
    'alto' => 8.6,
    'specs' => 
    array (
      'Potencia' => '750W',
      'Eficiencia' => '80 Plus Gold',
      'Topología' => 'LLC + DC to DC',
    ),
    'variantes' => NULL,
  ),
  34 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-5',
    'nombre' => 'Corsair SF750 750W Platinum Formato SFX',
    'marca' => 'Corsair',
    'modelo' => 'CP-9020186-NA',
    'precio' => 169.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'La reina indiscutible del formato mini-ITX: 750W en tamaño ultracompacto con eficiencia Platinum.',
    'desc' => 'Diseñada específicamente para ensambles en gabinetes pequeños Small Form Factor. Cables individuales con mallado tipo paracord flexible y soporte adaptador SFX a ATX incluido.',
    'peso' => 0.9,
    'largo' => 10.0,
    'ancho' => 12.5,
    'alto' => 6.35,
    'specs' => 
    array (
      'Formato' => 'SFX Compacto',
      'Potencia' => '750W',
      'Certificación' => '80 PLUS Platinum',
      'Cables' => 'Enmallado individual',
    ),
    'variantes' => NULL,
  ),
  35 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-1',
    'nombre' => 'Samsung 990 PRO NVMe M.2 SSD PCIe 4.0',
    'marca' => 'Samsung',
    'modelo' => 'MZ-V9P2T0B/AM',
    'precio' => 179.0,
    'precio_oferta' => NULL,
    'stock' => 35,
    'destacado' => true,
    'desc_corta' => 'Velocidades de vértigo de hasta 7450 MB/s de lectura: el SSD PCIe 4.0 definitivo.',
    'desc' => 'Rendimiento supremo para gaming y cargas pesadas de datos. Equipado con controlador Pascal en 8nm con revestimiento de níquel para disipación térmica y software Samsung Magician.',
    'peso' => 0.05,
    'largo' => 8.0,
    'ancho' => 2.2,
    'alto' => 0.23,
    'specs' => 
    array (
      'Lectura' => 'Hasta 7450 MB/s',
      'Escritura' => 'Hasta 6900 MB/s',
      'Formato' => 'M.2 2280 NVMe 2.0',
      'DRAM' => 'Cache LPDDR4 dedicada',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'SSD-10-1-V1',
        'precio' => 109.0,
        'stock' => 20,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '1 TB',
        ),
      ),
      1 => 
      array (
        'sku' => 'SSD-10-1-V2',
        'precio' => 179.0,
        'stock' => 15,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '2 TB',
        ),
      ),
    ),
  ),
  36 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-2',
    'nombre' => 'Western Digital WD_BLACK SN850X 1TB M.2',
    'marca' => 'Western Digital',
    'modelo' => 'WDS100T2X0E',
    'precio' => 94.0,
    'precio_oferta' => 84.0,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Velocidades de hasta 7300 MB/s con modo Game Mode 2.0 para carga instantánea de videojuegos.',
    'desc' => 'Totalmente compatible con PC y PlayStation 5. Minimiza las pantallas de carga y elimina los tirones en juegos de mundo abierto de nueva generación.',
    'peso' => 0.04,
    'largo' => 8.0,
    'ancho' => 2.2,
    'alto' => 0.24,
    'specs' => 
    array (
      'Lectura' => '7300 MB/s',
      'Interfaz' => 'PCIe Gen4 x4',
      'Resistencia' => '600 TBW',
    ),
    'variantes' => NULL,
  ),
  37 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-3',
    'nombre' => 'Crucial T500 2TB Gen4 NVMe SSD',
    'marca' => 'Crucial',
    'modelo' => 'CT2000T500SSD8',
    'precio' => 159.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Tecnología Micron TLC de 232 capas con hasta 7400 MB/s y excelente eficiencia energética.',
    'desc' => 'Ideal para creadores de video en resoluciones 4K/8K y estaciones de renderizado. Acelera la edición en Premiere Pro y tiempos de arranque del sistema.',
    'peso' => 0.05,
    'largo' => 8.0,
    'ancho' => 2.2,
    'alto' => 0.23,
    'specs' => 
    array (
      'Capacidad' => '2 TB',
      'Lectura' => '7400 MB/s',
      'Escritura' => '7000 MB/s',
    ),
    'variantes' => NULL,
  ),
  38 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-4',
    'nombre' => 'Kingston KC3000 PCIe 4.0 NVMe 1TB',
    'marca' => 'Kingston',
    'modelo' => 'SKC3000S/1024G',
    'precio' => 89.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Disipador térmico delgado de grafeno y aluminio para máxima estabilidad en laptops y placas base.',
    'desc' => 'Potenciado por el controlador Phison E18 de 8 canales. Gran resistencia a escrituras continuas y durabilidad excepcional comprobada.',
    'peso' => 0.04,
    'largo' => 8.0,
    'ancho' => 2.2,
    'alto' => 0.22,
    'specs' => 
    array (
      'Velocidad' => '7000 MB/s lectura / 6000 MB/s escritura',
      'Disipador' => 'Lámina de grafeno y aluminio',
    ),
    'variantes' => NULL,
  ),
  39 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-5',
    'nombre' => 'Seagate FireCuda 530 2TB NVMe SSD',
    'marca' => 'Seagate',
    'modelo' => 'ZP2000GM3A013',
    'precio' => 169.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Resistencia legendaria de 2550 TBW con 3 años de servicio de recuperación de datos Rescue incluidos.',
    'desc' => 'Construido para soportar escritura intensa sin degradarse. Permite reescribir el 70% de la capacidad del disco todos los días durante 5 años continuos.',
    'peso' => 0.05,
    'largo' => 8.0,
    'ancho' => 2.2,
    'alto' => 0.35,
    'specs' => 
    array (
      'Capacidad' => '2 TB',
      'Resistencia' => '2550 TBW',
      'Servicio' => 'Rescue Data Recovery 3 años',
    ),
    'variantes' => NULL,
  ),
  40 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-1',
    'nombre' => 'Logitech G PRO X TKL Teclado Mecánico',
    'marca' => 'Logitech G',
    'modelo' => '920-012128',
    'precio' => 179.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => true,
    'desc_corta' => 'Diseñado junto a profesionales de eSports: conectividad inalámbrica LIGHTSPEED y switches de precisión.',
    'desc' => 'Teclado para torneos sin teclado numérico (Tenkeyless). Cuenta con teclas PBT de doble inyección resistentes al desgaste, rueda de volumen metálica dedicada y estuche rígido de viaje incluido.',
    'peso' => 0.96,
    'largo' => 35.2,
    'ancho' => 15.0,
    'alto' => 3.4,
    'specs' => 
    array (
      'Formato' => 'TKL (Tenkeyless)',
      'Conexión' => 'LIGHTSPEED 1ms / Bluetooth / USB-C',
      'Batería' => 'Hasta 50 horas con RGB',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'KB-12-1-V1',
        'precio' => 179.0,
        'stock' => 9,
        'opcion' => 
        array (
          0 => 'Tipo de switch',
          1 => 'Red',
        ),
      ),
      1 => 
      array (
        'sku' => 'KB-12-1-V2',
        'precio' => 179.0,
        'stock' => 7,
        'opcion' => 
        array (
          0 => 'Tipo de switch',
          1 => 'Blue',
        ),
      ),
    ),
  ),
  41 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-2',
    'nombre' => 'Razer Huntsman V3 Pro TKL',
    'marca' => 'Razer',
    'modelo' => 'RZ03-04980100-R3U1',
    'precio' => 219.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Switches analógicos ópticos Gen-2 con punto de actuación ajustable de 0.1mm a 4.0mm y Rapid Trigger.',
    'desc' => 'La máxima ventaja competitiva en Counter-Strike y Valorant. El modo Rapid Trigger permite pulsar y soltar teclas instantáneamente sin esperar el recorrido de reinicio.',
    'peso' => 0.88,
    'largo' => 36.3,
    'ancho' => 13.9,
    'alto' => 3.9,
    'specs' => 
    array (
      'Actuación' => 'Ajustable 0.1 a 4.0 mm',
      'Tecnología' => 'Rapid Trigger / 1000Hz polling',
      'Material' => 'Aluminio cepillado 5052',
    ),
    'variantes' => NULL,
  ),
  42 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-3',
    'nombre' => 'Corsair K70 RGB PRO Mecánico',
    'marca' => 'Corsair',
    'modelo' => 'CH-9109410-NA',
    'precio' => 149.0,
    'precio_oferta' => 129.0,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Tecnología de hiperprocesamiento AXON a 8000Hz y switches CHERRY MX Red originales.',
    'desc' => 'Estructura icónica de aluminio anodizado negro con reposamuñecas magnético suave. Su interruptor trasero Tournament Switch desactiva macros y fija iluminación estática para juego limpio.',
    'peso' => 1.15,
    'largo' => 44.4,
    'ancho' => 16.6,
    'alto' => 4.0,
    'specs' => 
    array (
      'Switches' => 'CHERRY MX Red mecánicos',
      'Polling Rate' => '8000 Hz AXON',
      'Formato' => 'Completo 100%',
    ),
    'variantes' => NULL,
  ),
  43 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-4',
    'nombre' => 'SteelSeries Apex Pro TKL (2023)',
    'marca' => 'SteelSeries',
    'modelo' => '64856',
    'precio' => 189.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Pantalla OLED inteligente integrada y switches mecánicos OmniPoint 2.0 magnéticos ajustables.',
    'desc' => 'Muestra información de tus juegos en su pantalla OLED, ajusta el volumen o la configuración del teclado al vuelo sin salirte de tu partida.',
    'peso' => 0.96,
    'largo' => 35.55,
    'ancho' => 12.8,
    'alto' => 4.2,
    'specs' => 
    array (
      'Pantalla' => 'OLED Smart Display',
      'Switches' => 'OmniPoint 2.0 Hall Effect',
      'Teclas' => 'PBT Double Shot',
    ),
    'variantes' => NULL,
  ),
  44 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-5',
    'nombre' => 'Redragon K552 Kumara RGB Mecánico',
    'marca' => 'Redragon',
    'modelo' => 'K552-RGB',
    'precio' => 42.0,
    'precio_oferta' => NULL,
    'stock' => 24,
    'destacado' => false,
    'desc_corta' => 'El teclado mecánico más popular de Latinoamérica: compacto, ultra resistente y switches Outemu.',
    'desc' => 'Construcción sólida en plástico ABS reforzado y placa de acero interior. Switches Outemu intercambiables tipo Hot-Swap e iluminación RGB con 18 modos preconfigurados.',
    'peso' => 0.85,
    'largo' => 35.4,
    'ancho' => 12.3,
    'alto' => 3.7,
    'specs' => 
    array (
      'Switches' => 'Outemu Red / Blue Hot-Swap',
      'Conexión' => 'USB chapado en oro',
      'Formato' => 'TKL 87 teclas',
    ),
    'variantes' => NULL,
  ),
  45 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-1',
    'nombre' => 'Logitech G Pro X Superlight 2',
    'marca' => 'Logitech G',
    'modelo' => '910-006628',
    'precio' => 149.0,
    'precio_oferta' => NULL,
    'stock' => 22,
    'destacado' => true,
    'desc_corta' => 'Pesando solo 60 gramos: sensor HERO 2 de 32000 DPI y switches híbridos óptico-mecánicos LIGHTFORCE.',
    'desc' => 'El mouse preferido por la élite mundial de shooters. Su tasa de sondeo de 4000Hz (0.25ms) entrega una precisión milimétrica sin latencia perceptible. Carga mediante USB-C con hasta 95 horas de autonomía.',
    'peso' => 0.06,
    'largo' => 12.5,
    'ancho' => 6.35,
    'alto' => 4.0,
    'specs' => 
    array (
      'Peso' => '60 g',
      'Sensor' => 'HERO 2 (32.000 DPI / 500 IPS)',
      'Switches' => 'LIGHTFORCE híbridos',
      'Conexión' => 'LIGHTSPEED 4K',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'MOU-13-1-V1',
        'precio' => 149.0,
        'stock' => 12,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'MOU-13-1-V2',
        'precio' => 149.0,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
  ),
  46 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-2',
    'nombre' => 'Razer DeathAdder V3 Pro Wireless',
    'marca' => 'Razer',
    'modelo' => 'RZ01-04630100-R3U1',
    'precio' => 139.0,
    'precio_oferta' => 124.0,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Forma ergonómica legendaria refinada: 63g de peso y sensor óptico Focus Pro de 30.000 DPI.',
    'desc' => 'La forma ergonómica que ha ganado más campeonatos de esports en la historia, ahora en un chasis ultraligero sin perforaciones. Desliza suavemente sobre cualquier superficie con sus patas 100% de PTFE.',
    'peso' => 0.06,
    'largo' => 12.8,
    'ancho' => 6.8,
    'alto' => 4.4,
    'specs' => 
    array (
      'Sensor' => 'Focus Pro 30K Optical',
      'Batería' => 'Hasta 90 horas',
      'Switches' => 'Gen-3 Ópticos (90 millones de clics)',
    ),
    'variantes' => NULL,
  ),
  47 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-3',
    'nombre' => 'SteelSeries Aerox 3 Wireless (2022)',
    'marca' => 'SteelSeries',
    'modelo' => '62612',
    'precio' => 79.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Diseño ultra liviano de 68 gramos con perforaciones de panal y protección IP54 AquaBarrier.',
    'desc' => 'Protegido contra salpicaduras de líquidos, polvo y grasa gracias a su recubrimiento interior certificado IP54. Doble conectividad inalámbrica Quantum 2.0 (2.4GHz y Bluetooth 5.0).',
    'peso' => 0.07,
    'largo' => 12.05,
    'ancho' => 6.7,
    'alto' => 3.79,
    'specs' => 
    array (
      'Protección' => 'IP54 resistente al agua y polvo',
      'Peso' => '68 g',
      'Sensor' => 'TrueMove Air 18000 DPI',
    ),
    'variantes' => NULL,
  ),
  48 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-4',
    'nombre' => 'Corsair Scimitar RGB Elite MMO',
    'marca' => 'Corsair',
    'modelo' => 'CH-9304211-NA',
    'precio' => 74.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => '12 botones laterales ajustables mediante sistema Key Slider para dominar juegos MMO y MOBA.',
    'desc' => 'Personaliza tu botonera lateral deslizando el bloque numérico para que calce exactamente con el pulgar de tu mano. Sensor óptico nativo de 18000 DPI de PixArt.',
    'peso' => 0.12,
    'largo' => 11.94,
    'ancho' => 7.7,
    'alto' => 4.24,
    'specs' => 
    array (
      'Botones' => '17 botones totalmente programables',
      'Sensor' => '18.000 DPI óptico',
      'Sistema' => 'Key Slider patentado',
    ),
    'variantes' => NULL,
  ),
  49 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-5',
    'nombre' => 'HyperX Pulsefire Haste 2 Wireless',
    'marca' => 'HyperX',
    'modelo' => '6N0A7AA',
    'precio' => 59.0,
    'precio_oferta' => NULL,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Estructura sólida de 61g sin orificios, sensor HyperX 26K y hasta 100 horas de juego continuo.',
    'desc' => 'Respuesta ultrarrápida sin necesidad de agujeros en la carcasa. Incluye cinta de agarre antideslizante (grip tape) troquelada a medida para ambos laterales y botones principales.',
    'peso' => 0.06,
    'largo' => 12.43,
    'ancho' => 6.68,
    'alto' => 3.82,
    'specs' => 
    array (
      'Peso' => '61 g',
      'Batería' => '100 Horas',
      'Sensor' => 'HyperX 26K (26.000 DPI / 650 IPS)',
    ),
    'variantes' => NULL,
  ),
  50 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-1',
    'nombre' => 'Sony WH-1000XM5 Cancelación de Ruido',
    'marca' => 'Sony',
    'modelo' => 'WH1000XM5/B',
    'precio' => 349.0,
    'precio_oferta' => 329.0,
    'stock' => 14,
    'destacado' => true,
    'desc_corta' => 'Los mejores audífonos con cancelación de ruido del mercado: procesador V1 y 8 micrófonos dedicados.',
    'desc' => 'Sumérgete en silencio absoluto. Su tecnología de cancelación de ruido líder en la industria adapta el filtrado acústico a la presión atmosférica y a tu entorno en tiempo real. Soporta códec LDAC de alta resolución y llamadas cristalinas.',
    'peso' => 0.25,
    'largo' => 22.5,
    'ancho' => 18.0,
    'alto' => 7.5,
    'specs' => 
    array (
      'Autonomía' => 'Hasta 30 horas con ANC',
      'Carga Rápida' => '3 min = 3 horas de música',
      'Códecs' => 'LDAC, AAC, SBC',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'AUD-14-1-V1',
        'precio' => 349.0,
        'stock' => 8,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'AUD-14-1-V2',
        'precio' => 349.0,
        'stock' => 6,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Plateado',
        ),
      ),
    ),
  ),
  51 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-2',
    'nombre' => 'Bose QuietComfort Ultra Headphones',
    'marca' => 'Bose',
    'modelo' => '880066-0100',
    'precio' => 379.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Comodidad inigualable con Audio Espacial Inmersivo de Bose y modo Aware de transparencia natural.',
    'desc' => 'Audio espacial que hace que el sonido parezca venir del entorno frente a ti en lugar de estar atrapado en tu cabeza. Almohadillas de piel de proteína ultra suaves diseñadas para viajes transoceánicos.',
    'peso' => 0.25,
    'largo' => 19.5,
    'ancho' => 14.0,
    'alto' => 5.0,
    'specs' => 
    array (
      'Tecnología' => 'Bose Immersive Audio',
      'Cancelación' => 'CustomTune calibración personalizada',
      'Batería' => '24 Horas',
    ),
    'variantes' => NULL,
  ),
  52 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-3',
    'nombre' => 'Sennheiser Momentum 4 Wireless',
    'marca' => 'Sennheiser',
    'modelo' => '509266',
    'precio' => 299.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Firma de sonido audiófilo Sennheiser con una increíble batería de 60 horas por carga.',
    'desc' => 'Disfruta de sonido de alta fidelidad con transductores de 42mm desarrollados en Alemania. Su descomunal batería de 60 horas te permite viajar durante semanas sin buscar un cargador.',
    'peso' => 0.29,
    'largo' => 18.0,
    'ancho' => 19.5,
    'alto' => 4.6,
    'specs' => 
    array (
      'Batería' => '60 Horas récord',
      'Drivers' => '42 mm audiófilos',
      'Códec' => 'aptX Adaptive / AAC',
    ),
    'variantes' => NULL,
  ),
  53 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-4',
    'nombre' => 'Apple AirPods Max con Audio Espacial',
    'marca' => 'Apple',
    'modelo' => 'MGYH3AM/A',
    'precio' => 499.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Diadema de malla transpirable, copas de aluminio anodizado y audio computacional con chip H1.',
    'desc' => 'Unión perfecta entre audio de alta fidelidad y la magia de Apple. Audio espacial con seguimiento dinámico de la cabeza para sentirte en una sala de cine o concierto.',
    'peso' => 0.38,
    'largo' => 18.7,
    'ancho' => 16.8,
    'alto' => 8.3,
    'specs' => 
    array (
      'Chip' => 'Apple H1 en cada auricular',
      'Cancelación' => 'Cancelación Activa de Ruido Pro',
      'Material' => 'Aluminio y acero inoxidable',
    ),
    'variantes' => NULL,
  ),
  54 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-5',
    'nombre' => 'HyperX Cloud III Wireless Gaming',
    'marca' => 'HyperX',
    'modelo' => '77Z45AA',
    'precio' => 149.0,
    'precio_oferta' => 134.0,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Comodidad legendaria Cloud con hasta 120 horas de batería y micrófono ultra nítido de 10mm.',
    'desc' => 'Los audífonos gaming definitivos para largas jornadas de juego. Sus controladores en ángulo de 53mm sintonizan con precisión los pasos y disparos enemigos en shooters competitivos.',
    'peso' => 0.33,
    'largo' => 20.0,
    'ancho' => 15.5,
    'alto' => 9.0,
    'specs' => 
    array (
      'Batería' => '120 Horas inalámbricas',
      'Micrófono' => '10mm con filtro pop interno',
      'Audio' => 'DTS Headphone:X Spatial Audio',
    ),
    'variantes' => NULL,
  ),
  55 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-1',
    'nombre' => 'LG UltraGear 27GR95QE-B OLED 240Hz',
    'marca' => 'LG',
    'modelo' => '27GR95QE-B',
    'precio' => 799.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => true,
    'desc_corta' => 'Panel OLED QHD de 27 pulgadas con 240Hz y un tiempo de respuesta casi instantáneo de 0.03ms.',
    'desc' => 'La cúspide visual para juegos de competición. Negros perfectos gracias a sus píxeles autoemisivos con contraste 1.500.000:1 y compatibilidad certificada con NVIDIA G-SYNC y AMD FreeSync Premium.',
    'peso' => 7.35,
    'largo' => 60.45,
    'ancho' => 25.8,
    'alto' => 57.4,
    'specs' => 
    array (
      'Panel' => '27" QHD (2560x1440) OLED',
      'Frecuencia' => '240 Hz',
      'Tiempo Respuesta' => '0.03 ms GtG',
      'Color' => 'DCI-P3 98.5%',
    ),
    'variantes' => NULL,
  ),
  56 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-2',
    'nombre' => 'Samsung Odyssey G7 32" Curvo 240Hz',
    'marca' => 'Samsung',
    'modelo' => 'LC32G75TQSNXZA',
    'precio' => 599.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Curvatura profunda 1000R que coincide con el campo de visión humano, panel QLED WQHD y 240Hz.',
    'desc' => 'Inmersión periférica total que reduce la fatiga visual. Tecnología Quantum Dot para colores intensos con certificación VESA DisplayHDR 600 y retroiluminación trasera CoreSync.',
    'peso' => 8.2,
    'largo' => 71.01,
    'ancho' => 30.59,
    'alto' => 59.45,
    'specs' => 
    array (
      'Pantalla' => '32" Curva 1000R WQHD',
      'Frecuencia' => '240 Hz',
      'HDR' => 'DisplayHDR 600',
      'Panel' => 'VA QLED',
    ),
    'variantes' => NULL,
  ),
  57 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-3',
    'nombre' => 'Dell UltraSharp U2723QE 27" 4K IPS Black',
    'marca' => 'Dell',
    'modelo' => 'U2723QE',
    'precio' => 549.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Tecnología IPS Black pionera con contraste 2000:1, resolución 4K UHD y hub USB-C de 90W.',
    'desc' => 'El monitor favorito de diseñadores, fotógrafos y desarrolladores. Conecta tu laptop con un solo cable USB-C que transmite video 4K, datos de red Gigabit Ethernet y carga tu batería.',
    'peso' => 6.64,
    'largo' => 61.22,
    'ancho' => 18.5,
    'alto' => 53.55,
    'specs' => 
    array (
      'Resolución' => '3840 x 2160 (4K UHD)',
      'Tecnología' => 'IPS Black 2000:1',
      'Color' => '100% sRGB / 98% DCI-P3',
      'Hub' => 'USB-C 90W + RJ45',
    ),
    'variantes' => NULL,
  ),
  58 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-4',
    'nombre' => 'BenQ PD2705U 27" 4K Color Profesional',
    'marca' => 'BenQ',
    'modelo' => 'PD2705U',
    'precio' => 499.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Calibración de color de fábrica con reporte individual y certificaciones Calman y Pantone.',
    'desc' => 'Precisión cromática garantizada para creadores. Incluye controlador externo Hotkey Puck G2 para cambiar modos de color (sRGB, Rec.709, Darkroom, CAD/CAM) sin tocar los botones de la pantalla.',
    'peso' => 9.3,
    'largo' => 61.4,
    'ancho' => 23.83,
    'alto' => 55.27,
    'specs' => 
    array (
      'Pantalla' => '27" 4K UHD IPS',
      'Certificaciones' => 'Pantone / Calman Verified',
      'KVM' => 'Switch integrado para dos computadoras',
    ),
    'variantes' => NULL,
  ),
  59 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-5',
    'nombre' => 'ViewSonic ELITE XG270QG Gaming',
    'marca' => 'ViewSonic',
    'modelo' => 'XG270QG',
    'precio' => 429.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Panel Nano IPS de 1ms con tecnología NVIDIA G-SYNC nativa y viseras laterales antideslumbrantes.',
    'desc' => 'Colores de calidad Nano IPS combinados con la fluidez que exigen los torneos de eSports. Incluye soportes para audífonos y bungee retráctil para el cable del mouse integrados en la base.',
    'peso' => 7.7,
    'largo' => 61.4,
    'ancho' => 26.5,
    'alto' => 57.2,
    'specs' => 
    array (
      'Pantalla' => '27" QHD Nano IPS',
      'G-SYNC' => 'Módulo NVIDIA G-SYNC nativo',
      'Accesorios' => 'Viseras laterales incluidas',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'MON-15-5-V1',
        'precio' => 399.0,
        'stock' => 6,
        'opcion' => 
        array (
          0 => 'Frecuencia',
          1 => '144 Hz',
        ),
      ),
      1 => 
      array (
        'sku' => 'MON-15-5-V2',
        'precio' => 479.0,
        'stock' => 4,
        'opcion' => 
        array (
          0 => 'Frecuencia',
          1 => '240 Hz',
        ),
      ),
    ),
  ),
  60 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-1',
    'nombre' => 'Bose SoundLink Flex Bluetooth',
    'marca' => 'Bose',
    'modelo' => '865983-0100',
    'precio' => 149.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => true,
    'desc_corta' => 'Audio nítido y graves profundos con tecnología PositionIQ y resistencia al agua IP67 que flota.',
    'desc' => 'Diseñado para acompañarte a la playa o la piscina. La tecnología PositionIQ detecta automáticamente si el altavoz está de pie, acostado o colgado y calibra el sonido para ofrecer siempre el mejor timbre.',
    'peso' => 0.6,
    'largo' => 20.1,
    'ancho' => 9.0,
    'alto' => 5.2,
    'specs' => 
    array (
      'Resistencia' => 'IP67 (a prueba de agua, polvo y flota)',
      'Batería' => 'Hasta 12 horas',
      'Conexión' => 'Bluetooth 4.2 / USB-C',
    ),
    'variantes' => NULL,
  ),
  61 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-2',
    'nombre' => 'JBL Charge 5 Altavoz Portátil',
    'marca' => 'JBL',
    'modelo' => 'JBLCHARGE5BLKAM',
    'precio' => 179.0,
    'precio_oferta' => 159.0,
    'stock' => 25,
    'destacado' => true,
    'desc_corta' => 'Sonido JBL Original Pro con tweeter independiente, doble radiador pasivo y power bank integrado.',
    'desc' => 'El rey de las fiestas al aire libre. Sus radiadores pasivos de graves hacen vibrar la música con claridad. Su batería masiva de 7500mAh te permite cargar tu celular mientras escuchas tus canciones favoritas.',
    'peso' => 0.96,
    'largo' => 22.3,
    'ancho' => 9.65,
    'alto' => 9.4,
    'specs' => 
    array (
      'Potencia' => '40W RMS',
      'Batería' => 'Hasta 20 horas',
      'Función' => 'Power bank USB integrado / IP67',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'SND-16-2-V1',
        'precio' => 179.0,
        'stock' => 15,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'SND-16-2-V2',
        'precio' => 179.0,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Azul',
        ),
      ),
    ),
  ),
  62 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-3',
    'nombre' => 'Marshall Emberton II Portátil Vintage',
    'marca' => 'Marshall',
    'modelo' => '1006234',
    'precio' => 169.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Estilo clásico de amplificador de guitarra con sonido multidireccional True Stereophonic de 360°.',
    'desc' => 'Sonido estéreo espacial que llena toda la habitación. Construido con un 50% de plástico reciclado posconsumo y resistente al agua IP67 con más de 30 horas de reproducción.',
    'peso' => 0.7,
    'largo' => 16.0,
    'ancho' => 7.6,
    'alto' => 6.8,
    'specs' => 
    array (
      'Batería' => 'Más de 30 horas',
      'Sonido' => 'True Stereophonic 360°',
      'Modo' => 'Stack Mode para conectar varios',
    ),
    'variantes' => NULL,
  ),
  63 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-4',
    'nombre' => 'Edifier R1280DB Altavoces de Estudio Bluetooth',
    'marca' => 'Edifier',
    'modelo' => 'R1280DB-BK',
    'precio' => 139.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Monitores de estantería de madera MDF con entradas óptica, coaxial, RCA doble y Bluetooth.',
    'desc' => 'Sonido cálido y detallado para tu setup de computadora o tocadiscos de vinilo. Controles laterales para agudos, graves y volumen más control remoto inalámbrico.',
    'peso' => 4.9,
    'largo' => 24.1,
    'ancho' => 17.8,
    'alto' => 14.6,
    'specs' => 
    array (
      'Potencia' => '42W RMS',
      'Conexiones' => 'Bluetooth, Óptica, Coaxial, Dual RCA',
      'Gabinete' => 'Madera acústica MDF',
    ),
    'variantes' => NULL,
  ),
  64 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-5',
    'nombre' => 'Sony SRS-XB100 Altavoz Ultra Compacto',
    'marca' => 'Sony',
    'modelo' => 'SRSXB100/B',
    'precio' => 59.0,
    'precio_oferta' => NULL,
    'stock' => 20,
    'destacado' => false,
    'desc_corta' => 'Tamaño de bolsillo con correa versátil, procesador Sound Diffusion y EXTRA BASS potente.',
    'desc' => 'Sonido amplio y envolvente que desafía su tamaño miniatura. Su micrófono incorporado con cancelación de eco permite atender llamadas con manos libres de forma impecable.',
    'peso' => 0.27,
    'largo' => 7.6,
    'ancho' => 7.6,
    'alto' => 9.5,
    'specs' => 
    array (
      'Batería' => 'Hasta 16 horas',
      'Resistencia' => 'IP67 agua y polvo',
      'Micrófono' => 'Manos libres integrado',
    ),
    'variantes' => NULL,
  ),
  65 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-1',
    'nombre' => 'TP-Link Archer AX73 Wi-Fi 6 AX5400',
    'marca' => 'TP-Link',
    'modelo' => 'ARCHER-AX73',
    'precio' => 129.0,
    'precio_oferta' => 119.0,
    'stock' => 16,
    'destacado' => true,
    'desc_corta' => '6 antenas de alta ganancia con beamforming y velocidades Wi-Fi 6 de hasta 5400 Mbps.',
    'desc' => 'Maneja streaming en 8K sin cortes y descargas masivas simultáneas gracias a su estructura 4T4R y modulación 1024-QAM. Incluye seguridad TP-Link HomeShield con antivirus.',
    'peso' => 0.66,
    'largo' => 27.25,
    'ancho' => 14.72,
    'alto' => 4.92,
    'specs' => 
    array (
      'Velocidad' => '4804 Mbps (5 GHz) + 574 Mbps (2.4 GHz)',
      'Puertos' => 'Gigabit WAN + 4x LAN + USB 3.0',
      'Antenas' => '6x Antenas externas',
    ),
    'variantes' => NULL,
  ),
  66 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-2',
    'nombre' => 'ASUS RT-AX88U Pro Dual Band Wi-Fi 6',
    'marca' => 'ASUS',
    'modelo' => 'RT-AX88U-PRO',
    'precio' => 249.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Doble puerto de 2.5 Gbps, procesador quad-core de 2.0 GHz y aceleración de juegos por hardware.',
    'desc' => 'Potencia de red sin concesiones. Su doble puerto 2.5G permite aprovechar conexiones de fibra óptica multigigabit y transferencias ultra rápidas a servidores NAS locales.',
    'peso' => 1.01,
    'largo' => 30.0,
    'ancho' => 18.8,
    'alto' => 6.0,
    'specs' => 
    array (
      'Puertos' => '2x 2.5G Ethernet + 4x Gigabit LAN',
      'Seguridad' => 'AiProtection Pro con Trend Micro',
      'Wi-Fi' => 'AX6000',
    ),
    'variantes' => NULL,
  ),
  67 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-3',
    'nombre' => 'Netgear Nighthawk RAXE500 Wi-Fi 6E',
    'marca' => 'Netgear',
    'modelo' => 'RAXE500-100NAS',
    'precio' => 399.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Tecnología tri-banda Wi-Fi 6E con acceso a la nueva autopista libre de 6 GHz a 10.8 Gbps.',
    'desc' => 'Diseño futurista en forma de alas que oculta 8 antenas prealineadas para máxima cobertura. La banda de 6GHz elimina por completo la interferencia de los vecinos.',
    'peso' => 1.45,
    'largo' => 29.8,
    'ancho' => 21.1,
    'alto' => 8.4,
    'specs' => 
    array (
      'Bandas' => 'Tri-Band 2.4GHz / 5GHz / 6GHz',
      'Velocidad' => 'Hasta 10.8 Gbps',
      'Puerto' => 'Multi-Gig 2.5G',
    ),
    'variantes' => NULL,
  ),
  68 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-4',
    'nombre' => 'D-Link EAGLE PRO AI AX3200 Smart Router',
    'marca' => 'D-Link',
    'modelo' => 'R32-US',
    'precio' => 99.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Optimización automática del tráfico impulsada por algoritmos de Inteligencia Artificial.',
    'desc' => 'El asistente de IA monitorea de forma continua los canales disponibles y asigna el ancho de banda ideal a cada dispositivo de tu hogar evitando congestiones.',
    'peso' => 0.48,
    'largo' => 22.8,
    'ancho' => 15.9,
    'alto' => 4.5,
    'specs' => 
    array (
      'Wi-Fi' => 'Wi-Fi 6 AX3200',
      'IA' => 'AI Wi-Fi Optimizer / AI Mesh',
      'Control' => 'App EAGLE PRO AI',
    ),
    'variantes' => NULL,
  ),
  69 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-5',
    'nombre' => 'Ubiquiti UniFi Dream Router (UDR)',
    'marca' => 'Ubiquiti',
    'modelo' => 'UDR-US',
    'precio' => 199.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => true,
    'desc_corta' => 'Router corporativo todo en uno: switch PoE, punto de acceso Wi-Fi 6 y pantalla LCM de estado.',
    'desc' => 'La puerta de entrada al ecosistema profesional UniFi. Administra redes avanzadas, cámaras de seguridad UniFi Protect con su ranura MicroSD interna y alimenta dispositivos mediante puertos PoE.',
    'peso' => 1.15,
    'largo' => 11.0,
    'ancho' => 11.0,
    'alto' => 18.42,
    'specs' => 
    array (
      'Pantalla' => '0.96" LCM táctil',
      'Puertos' => '2x PoE Out 802.3af + 2x GbE LAN',
      'Wi-Fi' => 'Wi-Fi 6 4x4 MU-MIMO',
    ),
    'variantes' => NULL,
  ),
  70 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-1',
    'nombre' => 'TP-Link TL-SG108E Gigabit 8 Puertos Easy Smart',
    'marca' => 'TP-Link',
    'modelo' => 'TL-SG108E',
    'precio' => 29.0,
    'precio_oferta' => NULL,
    'stock' => 20,
    'destacado' => false,
    'desc_corta' => 'Chasis de acero robusto con administración web sencilla, soporte de VLAN y QoS para prioridad de red.',
    'desc' => 'Mejora tu red con monitoreo de tráfico, priorización de paquetes para streaming y telefonía IP, y prevención de bucles de red. Diseño fanless sin ventilador 100% silencioso.',
    'peso' => 0.54,
    'largo' => 15.8,
    'ancho' => 10.1,
    'alto' => 2.5,
    'specs' => 
    array (
      'Puertos' => '8x Gigabit RJ45 10/100/1000',
      'Gestión' => 'VLAN por puerto / 802.1Q',
      'Chasis' => 'Metal blindado',
    ),
    'variantes' => NULL,
  ),
  71 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-2',
    'nombre' => 'Netgear GS305E 5 Puertos Gigabit Plus',
    'marca' => 'Netgear',
    'modelo' => 'GS305E-100NAS',
    'precio' => 24.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Switch inteligente gestionable con diagnóstico de cables y montaje en pared o escritorio.',
    'desc' => 'Configura y soluciona problemas de red fácilmente. Detecta si un cable de red está roto o dañado mediante su utilidad de comprobación integrada.',
    'peso' => 0.25,
    'largo' => 9.4,
    'ancho' => 10.0,
    'alto' => 2.6,
    'specs' => 
    array (
      'Puertos' => '5x Gigabit',
      'Eficiencia' => 'Energy Efficient Ethernet IEEE 802.3az',
      'Garantía' => '3 Años',
    ),
    'variantes' => NULL,
  ),
  72 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-3',
    'nombre' => 'D-Link DGS-1008G 8 Puertos Gigabit Desktop',
    'marca' => 'D-Link',
    'modelo' => 'DGS-1008G',
    'precio' => 26.0,
    'precio_oferta' => NULL,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Plug and play inmediato con tecnología D-Link Green que reduce el consumo hasta un 80%.',
    'desc' => 'Detecta automáticamente la longitud del cable conectado y el estado de enlace de los dispositivos para apagar los puertos inactivos y ahorrar energía eléctrica.',
    'peso' => 0.33,
    'largo' => 13.0,
    'ancho' => 7.9,
    'alto' => 2.2,
    'specs' => 
    array (
      'Capacidad Switching' => '16 Gbps',
      'Instalación' => 'Plug and Play sin software',
      'Puertos' => '8x 10/100/1000 Mbps',
    ),
    'variantes' => NULL,
  ),
  73 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-4',
    'nombre' => 'Ubiquiti UniFi Switch Flex Mini 5 Puertos',
    'marca' => 'Ubiquiti',
    'modelo' => 'USW-Flex-Mini',
    'precio' => 35.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => true,
    'desc_corta' => 'Switch administrado de bolsillo que puede alimentarse por PoE 802.3af o por cable USB-C.',
    'desc' => 'Extiende la red UniFi a cualquier rincón de tu oficina o centro de entretenimiento. Gestionable desde el controlador central UniFi Network con estadísticas de tráfico por puerto.',
    'peso' => 0.15,
    'largo' => 10.72,
    'ancho' => 7.01,
    'alto' => 2.11,
    'specs' => 
    array (
      'Alimentación' => 'PoE In (Puerto 1) o USB-C 5V',
      'Puertos' => '5x GbE RJ45',
      'Consumo' => 'Apenas 2.5W',
    ),
    'variantes' => NULL,
  ),
  74 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-5',
    'nombre' => 'Anker PowerExpand USB-C a Gigabit Ethernet',
    'marca' => 'Anker',
    'modelo' => 'A83610A1',
    'precio' => 19.0,
    'precio_oferta' => NULL,
    'stock' => 26,
    'destacado' => false,
    'desc_corta' => 'Añade un puerto de red cableado Gigabit a laptops modernas sin puerto RJ45 nativo.',
    'desc' => 'Carcasa de aluminio disipador de calor y cable de nylon trenzado. Compatible con MacBooks, iPads, laptops Windows y consolas Nintendo Switch para una conexión sin lag.',
    'peso' => 0.03,
    'largo' => 5.2,
    'ancho' => 2.4,
    'alto' => 1.4,
    'specs' => 
    array (
      'Velocidad' => '1000 Mbps Gigabit',
      'Conexión' => 'USB-C macho a RJ45 hembra',
      'Plug & Play' => 'Sin drivers en Windows/Mac',
    ),
    'variantes' => NULL,
  ),
  75 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-1',
    'nombre' => 'Anker 765 USB-C a USB-C Cable 140W Nylon',
    'marca' => 'Anker',
    'modelo' => 'A8865011',
    'precio' => 19.0,
    'precio_oferta' => NULL,
    'stock' => 45,
    'destacado' => true,
    'desc_corta' => 'Carga ultrarrápida USB Power Delivery 3.1 de 140W con chip E-Marker y prueba de 35.000 flexiones.',
    'desc' => 'Capaz de cargar desde audífonos hasta una MacBook Pro de 16 pulgadas a máxima velocidad. Trenzado con fibras de nylon de grado aeroespacial para resistir tirones y dobladuras.',
    'peso' => 0.08,
    'largo' => 100.0,
    'ancho' => 1.2,
    'alto' => 0.6,
    'specs' => 
    array (
      'Potencia Soportada' => 'Hasta 140W (28V/5A)',
      'Velocidad Datos' => '480 Mbps USB 2.0',
      'Material' => 'Nylon trenzado doble',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CBL-20-1-V1',
        'precio' => 19.0,
        'stock' => 25,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '1 m',
        ),
      ),
      1 => 
      array (
        'sku' => 'CBL-20-1-V2',
        'precio' => 24.0,
        'stock' => 20,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '2 m',
        ),
      ),
    ),
  ),
  76 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-2',
    'nombre' => 'Belkin Ultra HD Alta Velocidad HDMI 2.1 4K/8K',
    'marca' => 'Belkin',
    'modelo' => 'AV10175bt2M-BLK',
    'precio' => 24.0,
    'precio_oferta' => NULL,
    'stock' => 35,
    'destacado' => false,
    'desc_corta' => 'Certificación oficial HDMI Forum: ancho de banda de 48 Gbps para 4K a 120Hz y 8K a 60Hz.',
    'desc' => 'Aprobado para PlayStation 5, Xbox Series X y televisores OLED. Soporta VRR (Tasa de Refresco Variable), ALLM (Modo de Baja Latencia Automático) y eARC para audio Dolby Atmos.',
    'peso' => 0.12,
    'largo' => 100.0,
    'ancho' => 1.8,
    'alto' => 0.9,
    'specs' => 
    array (
      'Ancho de Banda' => '48 Gbps Ultra High Speed',
      'HDR' => 'Dolby Vision / HDR10+',
      'Resoluciones' => '8K@60Hz / 4K@120Hz',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CBL-20-2-V1',
        'precio' => 24.0,
        'stock' => 20,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '1 m',
        ),
      ),
      1 => 
      array (
        'sku' => 'CBL-20-2-V2',
        'precio' => 29.0,
        'stock' => 15,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '2 m',
        ),
      ),
    ),
  ),
  77 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-3',
    'nombre' => 'Apple Cable Thunderbolt 4 Pro Trenzado',
    'marca' => 'Apple',
    'modelo' => 'MU883AM/A',
    'precio' => 69.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Transferencia de datos a 40 Gbps, salida de video DisplayPort y carga rápida de hasta 100W.',
    'desc' => 'Diseño trenzado negro de primera calidad que se enrolla sin enredos. Permite conectar monitores Studio Display, tarjetas gráficas externas (eGPU) y discos duros RAID profesionales.',
    'peso' => 0.09,
    'largo' => 180.0,
    'ancho' => 1.2,
    'alto' => 0.5,
    'specs' => 
    array (
      'Velocidad' => 'Hasta 40 Gb/s',
      'Video' => 'DisplayPort (HBR3)',
      'Carga' => '100W Power Delivery',
    ),
    'variantes' => NULL,
  ),
  78 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-4',
    'nombre' => 'TP-Link Cable Patch Cord Cat6 Gigabit RJ45',
    'marca' => 'TP-Link',
    'modelo' => 'TL-EC605',
    'precio' => 6.0,
    'precio_oferta' => NULL,
    'stock' => 50,
    'destacado' => false,
    'desc_corta' => 'Cable de red ensamblado en fábrica con conductores de cobre puro y contactos dorados.',
    'desc' => 'Garantiza el 100% de la velocidad contratada de tu plan de internet sin interferencias electromagnéticas gracias a su cruceta interna separadora de pares.',
    'peso' => 0.08,
    'largo' => 200.0,
    'ancho' => 1.1,
    'alto' => 0.6,
    'specs' => 
    array (
      'Categoría' => 'Cat6 UTP',
      'Frecuencia' => '250 MHz',
      'Conectores' => 'RJ45 con funda protectora antienganche',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CBL-20-4-V1',
        'precio' => 6.0,
        'stock' => 30,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '2 m',
        ),
      ),
      1 => 
      array (
        'sku' => 'CBL-20-4-V2',
        'precio' => 10.0,
        'stock' => 20,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '5 m',
        ),
      ),
    ),
  ),
  79 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-5',
    'nombre' => 'D-Link Cable de Red Cat6 UTP 3m Certificado',
    'marca' => 'D-Link',
    'modelo' => 'NCB-C6UBLR1-3',
    'precio' => 7.5,
    'precio_oferta' => NULL,
    'stock' => 30,
    'destacado' => false,
    'desc_corta' => 'Longitud intermedia ideal para conectar consolas, smart TVs o PCs al router o switch.',
    'desc' => 'Revestimiento de PVC resistente y flexible de color azul. Cumple con los estándares internacionales TIA/EIA-568-C.2.',
    'peso' => 0.1,
    'largo' => 300.0,
    'ancho' => 1.1,
    'alto' => 0.6,
    'specs' => 
    array (
      'Longitud' => '3 Metros',
      'Conductor' => '24 AWG Cobre Sólido',
      'Color' => 'Azul',
    ),
    'variantes' => NULL,
  ),
  80 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-1',
    'nombre' => 'SanDisk Extreme Portable SSD USB 3.2 Gen 2',
    'marca' => 'SanDisk',
    'modelo' => 'SDSSDE61-1T00-G25',
    'precio' => 109.0,
    'precio_oferta' => 99.0,
    'stock' => 28,
    'destacado' => true,
    'desc_corta' => 'Carcasa engomada resistente a caídas de hasta 2 metros con protección IP55 contra lluvia y polvo.',
    'desc' => 'El disco duro de bolsillo favorito de fotógrafos y viajeros. Su práctico mosquetón permite engancharlo a la mochila mientras caminas. Velocidades NVMe de hasta 1050 MB/s de lectura.',
    'peso' => 0.05,
    'largo' => 10.05,
    'ancho' => 5.24,
    'alto' => 0.89,
    'specs' => 
    array (
      'Lectura' => '1050 MB/s',
      'Escritura' => '1000 MB/s',
      'Protección' => 'IP55 resistente a agua/polvo',
      'Seguridad' => 'Cifrado AES 256-bit',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'EXT-21-1-V1',
        'precio' => 109.0,
        'stock' => 16,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '1 TB',
        ),
      ),
      1 => 
      array (
        'sku' => 'EXT-21-1-V2',
        'precio' => 169.0,
        'stock' => 12,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '2 TB',
        ),
      ),
    ),
  ),
  81 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-2',
    'nombre' => 'Samsung T7 Shield Portable SSD Resistente',
    'marca' => 'Samsung',
    'modelo' => 'MU-PE2T0S/AM',
    'precio' => 179.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Exterior de elastómero de alta tecnología resistente a golpes, agua y polvo con certificación IP65.',
    'desc' => 'Protección térmica avanzada Dynamic Thermal Guard para evitar caídas de velocidad por sobrecalentamiento al transferir archivos gigantes de video.',
    'peso' => 0.09,
    'largo' => 8.8,
    'ancho' => 5.9,
    'alto' => 1.3,
    'specs' => 
    array (
      'Capacidad' => '2 TB',
      'Velocidad' => '1050 MB/s',
      'Certificación' => 'IP65 y caída de 3 metros',
    ),
    'variantes' => NULL,
  ),
  82 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-3',
    'nombre' => 'Western Digital My Passport 2TB HDD USB 3.0',
    'marca' => 'Western Digital',
    'modelo' => 'WDBYVG0020BBK-WESN',
    'precio' => 69.0,
    'precio_oferta' => NULL,
    'stock' => 19,
    'destacado' => false,
    'desc_corta' => 'Disco duro externo confiable y estilizado con software de respaldo automático y contraseña.',
    'desc' => 'El clásico indiscutible para copias de seguridad de fotos familiares, películas y documentos importantes. Compatible de fábrica con Windows y Mac.',
    'peso' => 0.12,
    'largo' => 10.72,
    'ancho' => 7.5,
    'alto' => 1.11,
    'specs' => 
    array (
      'Capacidad' => '2 TB',
      'Interfaz' => 'USB 3.2 Gen 1 (USB 3.0)',
      'Seguridad' => 'Protección con contraseña',
    ),
    'variantes' => NULL,
  ),
  83 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-4',
    'nombre' => 'Seagate Expansion 4TB Disco Externo',
    'marca' => 'Seagate',
    'modelo' => 'STKM4000400',
    'precio' => 99.0,
    'precio_oferta' => 89.0,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Almacenamiento masivo de 4 Terabytes sin necesidad de adaptador de corriente adicional.',
    'desc' => 'Almacena miles de horas de series, juegos de consola o archivos de diseño. Simplemente conecta el cable USB y arrastra los archivos para guardarlos al instante.',
    'peso' => 0.26,
    'largo' => 11.53,
    'ancho' => 8.0,
    'alto' => 2.09,
    'specs' => 
    array (
      'Capacidad' => '4 TB (4000 GB)',
      'Alimentación' => 'Por USB directa',
      'Garantía' => '2 Años con Rescue Data Recovery',
    ),
    'variantes' => NULL,
  ),
  84 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-5',
    'nombre' => 'Crucial X9 Pro 1TB Portable SSD 1050MB/s',
    'marca' => 'Crucial',
    'modelo' => 'CT1000X9PROSSD9',
    'precio' => 89.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Tamaño ultracompacto menor al de una tarjeta de crédito con cuerpo de aluminio anodizado.',
    'desc' => 'Elegante y discreto en la palma de tu mano. Resistencia al agua y polvo IP55 con indicador LED de actividad discreto en la ranura de cuerda.',
    'peso' => 0.04,
    'largo' => 6.5,
    'ancho' => 5.0,
    'alto' => 1.0,
    'specs' => 
    array (
      'Capacidad' => '1 TB',
      'Lectura' => '1050 MB/s',
      'Dimensiones' => '65 x 50 mm ultra portátil',
    ),
    'variantes' => NULL,
  ),
  85 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-1',
    'nombre' => 'Anker 735 GaNPrime 65W Cargador 3 Puertos',
    'marca' => 'Anker',
    'modelo' => 'A2668111',
    'precio' => 49.0,
    'precio_oferta' => NULL,
    'stock' => 22,
    'destacado' => true,
    'desc_corta' => 'Tecnología GaNPrime más pequeña que un cargador de laptop tradicional para cargar 3 dispositivos a la vez.',
    'desc' => 'Equipado con 2 puertos USB-C y 1 puerto USB-A. Su tecnología PowerIQ 4.0 detecta dinámicamente las necesidades de energía de cada equipo para distribuir los 65W inteligentemente.',
    'peso' => 0.13,
    'largo' => 6.6,
    'ancho' => 3.8,
    'alto' => 2.9,
    'specs' => 
    array (
      'Potencia Total' => '65W Max',
      'Puertos' => '2x USB-C + 1x USB-A',
      'Tecnología' => 'GaNPrime / ActiveShield 2.0',
    ),
    'variantes' => NULL,
  ),
  86 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-2',
    'nombre' => 'Belkin BoostCharge Pro 3 en 1 MagSafe 15W',
    'marca' => 'Belkin',
    'modelo' => 'WIZ017ttBK',
    'precio' => 129.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Estación de carga inalámbrica rápida oficial de 15W para iPhone, Apple Watch y AirPods simultáneamente.',
    'desc' => 'Diseño arquitectónico con acabados cromados y base pesada que no se desliza en tu mesa de noche. Carga rápida oficial para Apple Watch Series 7 en adelante.',
    'peso' => 0.5,
    'largo' => 13.5,
    'ancho' => 13.5,
    'alto' => 13.0,
    'specs' => 
    array (
      'MagSafe' => '15W Oficial certificado por Apple',
      'Carga Simultánea' => '3 Dispositivos',
      'Compatibilidad' => 'iPhone 12 a 15, Watch, AirPods',
    ),
    'variantes' => NULL,
  ),
  87 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-3',
    'nombre' => 'Apple Adaptador de Corriente USB-C de 20W',
    'marca' => 'Apple',
    'modelo' => 'MHJA3AM/A',
    'precio' => 19.0,
    'precio_oferta' => NULL,
    'stock' => 35,
    'destacado' => false,
    'desc_corta' => 'Cargador de pared original de Apple para carga rápida del 50% de batería en 30 minutos.',
    'desc' => 'El cargador oficial recomendado para todos los modelos de iPhone y iPad. Circuito inteligente que protege la salud de tu batería a largo plazo.',
    'peso' => 0.08,
    'largo' => 4.5,
    'ancho' => 4.5,
    'alto' => 2.8,
    'specs' => 
    array (
      'Potencia' => '20 Watts USB-PD',
      'Conexión' => 'USB-C hembra',
      'Seguridad' => 'Protección contra sobretensiones Apple',
    ),
    'variantes' => NULL,
  ),
  88 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-4',
    'nombre' => 'Samsung 45W Super Fast Wall Charger USB-C',
    'marca' => 'Samsung',
    'modelo' => 'EP-T4510XBEGUS',
    'precio' => 34.0,
    'precio_oferta' => 29.0,
    'stock' => 20,
    'destacado' => false,
    'desc_corta' => 'Carga Super Rápida 2.0 oficial para Galaxy S24 Ultra, S23 Ultra y tablets Galaxy Tab.',
    'desc' => 'Incluye cable USB-C a USB-C de 5A de alta capacidad. Compatible con el estándar universal USB Power Delivery PPS para alimentar también laptops y otras marcas.',
    'peso' => 0.1,
    'largo' => 5.4,
    'ancho' => 4.3,
    'alto' => 2.8,
    'specs' => 
    array (
      'Potencia' => '45W PPS',
      'Cable' => 'USB-C a USB-C 5A de 1.8m incluido',
    ),
    'variantes' => NULL,
  ),
  89 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-5',
    'nombre' => 'Xiaomi 67W GaN Fast Charger Combo',
    'marca' => 'Xiaomi',
    'modelo' => 'BHR6127EU',
    'precio' => 29.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Cargador de nitruro de galio ultracompacto de 67W capaz de cargar laptops y smartphones.',
    'desc' => 'Alta potencia en tamaño reducido. Control térmico inteligente de temperatura constante y materiales resistentes al fuego de alta graduación.',
    'peso' => 0.09,
    'largo' => 5.5,
    'ancho' => 3.2,
    'alto' => 3.2,
    'specs' => 
    array (
      'Potencia' => '67W Max',
      'Material' => 'GaN (Nitruro de Galio)',
      'Cable' => 'Cable USB-A a USB-C 6A incluido',
    ),
    'variantes' => NULL,
  ),
  90 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-1',
    'nombre' => 'Anker 737 Power Bank 24000mAh 140W',
    'marca' => 'Anker',
    'modelo' => 'A1289011',
    'precio' => 119.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => true,
    'desc_corta' => 'Pantalla digital a color que muestra vatios de entrada/salida y tiempo restante de batería.',
    'desc' => 'El power bank más avanzado del mercado. Entrega hasta 140W de potencia para cargar laptops de alta gama mientras cargas dos celulares al mismo tiempo.',
    'peso' => 0.63,
    'largo' => 15.58,
    'ancho' => 5.46,
    'alto' => 4.96,
    'specs' => 
    array (
      'Capacidad' => '24.000 mAh (86.4 Wh)',
      'Salida Máxima' => '140W Power Delivery 3.1',
      'Pantalla' => 'Smart Digital Display a color',
    ),
    'variantes' => NULL,
  ),
  91 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-2',
    'nombre' => 'Xiaomi 50W Power Bank 20000mAh',
    'marca' => 'Xiaomi',
    'modelo' => 'BHR5121GL',
    'precio' => 49.0,
    'precio_oferta' => NULL,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Carga rápida de 50W para celulares y laptops con 3 puertos de salida y acabado cerámico brillante.',
    'desc' => 'Enorme capacidad de 20000mAh que permite recargar un smartphone hasta 4 veces completas. Compatible con carga de baja corriente para audífonos y pulseras.',
    'peso' => 0.44,
    'largo' => 15.35,
    'ancho' => 7.35,
    'alto' => 2.75,
    'specs' => 
    array (
      'Capacidad' => '20.000 mAh (74 Wh)',
      'Potencia' => '50W Max',
      'Puertos' => '1x USB-C + 2x USB-A',
    ),
    'variantes' => NULL,
  ),
  92 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-3',
    'nombre' => 'Anker Nano Power Bank 5000mAh USB-C',
    'marca' => 'Anker',
    'modelo' => 'A1653011',
    'precio' => 25.0,
    'precio_oferta' => NULL,
    'stock' => 32,
    'destacado' => true,
    'desc_corta' => 'Conector USB-C plegable integrado: se conecta directamente a la base de tu celular sin cables.',
    'desc' => 'Cabe en cualquier bolsillo de pantalón. Olvídate de llevar cables enredados. Simplemente despliega el conector USB-C, conéctalo a tu smartphone y sigue usándolo con normalidad.',
    'peso' => 0.1,
    'largo' => 7.7,
    'ancho' => 3.68,
    'alto' => 2.5,
    'specs' => 
    array (
      'Capacidad' => '5.000 mAh',
      'Potencia' => '22.5W Carga Rápida',
      'Conector' => 'USB-C plegable integrado',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'PWR-24-3-V1',
        'precio' => 25.0,
        'stock' => 18,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'PWR-24-3-V2',
        'precio' => 25.0,
        'stock' => 14,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
  ),
  93 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-4',
    'nombre' => 'Belkin BoostCharge Power Bank 10000mAh',
    'marca' => 'Belkin',
    'modelo' => 'BPB011btBK',
    'precio' => 29.0,
    'precio_oferta' => NULL,
    'stock' => 20,
    'destacado' => false,
    'desc_corta' => 'Carga hasta 3 dispositivos a la vez con protección certificada contra sobrecalentamiento y sobrecargas.',
    'desc' => 'Diseño delgado y estilizado que cabe perfectamente junto a tu teléfono en la mano. Indicador LED de 4 puntos para conocer la batería restante.',
    'peso' => 0.22,
    'largo' => 13.18,
    'ancho' => 6.83,
    'alto' => 1.57,
    'specs' => 
    array (
      'Capacidad' => '10.000 mAh',
      'Puertos' => '1x USB-C + 2x USB-A',
      'Potencia Total' => '15W',
    ),
    'variantes' => NULL,
  ),
  94 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-5',
    'nombre' => 'Samsung 25W Power Bank 10000mAh Batería',
    'marca' => 'Samsung',
    'modelo' => 'EB-P3400XUEGUS',
    'precio' => 39.0,
    'precio_oferta' => 34.0,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Carga super rápida de 25W en dos puertos USB-C fabricada con materiales reciclados ecológicos.',
    'desc' => 'Diseñada con líneas suaves y agarre cómodo en color beige natural. Carga dos dispositivos simultáneamente a velocidad equilibrada.',
    'peso' => 0.21,
    'largo' => 14.8,
    'ancho' => 7.0,
    'alto' => 1.56,
    'specs' => 
    array (
      'Capacidad' => '10.000 mAh',
      'Potencia' => '25W Super Fast Charging',
      'Puertos' => '2x USB-C',
    ),
    'variantes' => NULL,
  ),
  95 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-1',
    'nombre' => 'Apple Funda de Silicona con MagSafe iPhone 15',
    'marca' => 'Apple',
    'modelo' => 'MT113ZM/A',
    'precio' => 49.0,
    'precio_oferta' => NULL,
    'stock' => 25,
    'destacado' => true,
    'desc_corta' => 'Tacto exterior sedoso y forro interior de microfibra suave que abraza y protege el iPhone.',
    'desc' => 'Imanes perfectamente alineados que hacen que la carga inalámbrica MagSafe sea más rápida y cómoda. Tratamiento oleofóbico que repele huellas dactilares y suciedad.',
    'peso' => 0.05,
    'largo' => 15.0,
    'ancho' => 7.5,
    'alto' => 1.2,
    'specs' => 
    array (
      'Material' => 'Silicona líquida y microfibra',
      'MagSafe' => 'Integrado',
      'Compatibilidad' => 'iPhone 15',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CS-25-1-V1',
        'precio' => 49.0,
        'stock' => 15,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'CS-25-1-V2',
        'precio' => 49.0,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Azul',
        ),
      ),
    ),
  ),
  96 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-2',
    'nombre' => 'Samsung Funda Standing Grip Galaxy S24 Ultra',
    'marca' => 'Samsung',
    'modelo' => 'EF-GS928CBEGUS',
    'precio' => 39.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Correa de sujeción trasera que funciona también como soporte de pie para ver videos en horizontal.',
    'desc' => 'Sujeta tu smartphone con total seguridad con una sola mano pasando los dedos por la correa flexible integrada, o despliégala para apoyarlo en la mesa.',
    'peso' => 0.06,
    'largo' => 16.5,
    'ancho' => 8.2,
    'alto' => 1.3,
    'specs' => 
    array (
      'Compatibilidad' => 'Galaxy S24 Ultra',
      'Función' => 'Correa de agarre + Kickstand',
      'Material' => 'Policarbonato y silicona',
    ),
    'variantes' => NULL,
  ),
  97 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-3',
    'nombre' => 'Belkin Protector de Pantalla UltraGlass 2',
    'marca' => 'Belkin',
    'modelo' => 'OVA132zz',
    'precio' => 29.0,
    'precio_oferta' => NULL,
    'stock' => 30,
    'destacado' => false,
    'desc_corta' => 'Vidrio de silicato de litio hasta 2.7 veces más resistente que el vidrio templado convencional.',
    'desc' => 'Grosor ultrafino de apenas 0.29mm que conserva la sensibilidad táctil original de la pantalla al 100%. Incluye bandeja Easy Align para una instalación perfecta sin burbujas.',
    'peso' => 0.03,
    'largo' => 14.5,
    'ancho' => 7.0,
    'alto' => 0.03,
    'specs' => 
    array (
      'Dureza' => '9H de silicato de litio',
      'Grosor' => '0.29 mm',
      'Antimicrobiano' => 'Tratamiento antibacteriano',
    ),
    'variantes' => NULL,
  ),
  98 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-4',
    'nombre' => 'Anker Protector de Lente de Cámara Tempered Glass',
    'marca' => 'Anker',
    'modelo' => 'A7240011',
    'precio' => 14.0,
    'precio_oferta' => NULL,
    'stock' => 35,
    'destacado' => false,
    'desc_corta' => 'Anillos de aluminio y vidrio templado 9H individual para cada lente que no afectan las fotos nocturnas.',
    'desc' => 'Protege las cámaras de tu smartphone contra arañazos de llaves y golpes sin generar destellos con el flash ni alterar los colores de las fotos.',
    'peso' => 0.01,
    'largo' => 3.5,
    'ancho' => 3.5,
    'alto' => 0.2,
    'specs' => 
    array (
      'Dureza' => 'Vidrio 9H + Marco de aluminio',
      'Claridad' => '99.9% de transmisión de luz',
    ),
    'variantes' => NULL,
  ),
  99 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-5',
    'nombre' => 'Apple Funda Smart Folio para iPad Air',
    'marca' => 'Apple',
    'modelo' => 'MMW73ZM/A',
    'precio' => 79.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Funda magnética frontal y trasera que activa y suspende la pantalla al abrirla o cerrarla.',
    'desc' => 'Se pliega en diferentes posiciones para crear un soporte para leer, ver videos, escribir con el teclado o dibujar con el Apple Pencil con comodidad.',
    'peso' => 0.2,
    'largo' => 25.0,
    'ancho' => 18.0,
    'alto' => 1.1,
    'specs' => 
    array (
      'Compatibilidad' => 'iPad Air 11" (M2) y 5.ª Gen',
      'Material' => 'Poliuretano premium',
      'Sujeción' => 'Magnética',
    ),
    'variantes' => NULL,
  ),
  100 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-1',
    'nombre' => 'Apple iPhone 15 Pro Max Titanio',
    'marca' => 'Apple',
    'modelo' => 'MU773LL/A',
    'precio' => 1199.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => true,
    'desc_corta' => 'Estructura de titanio aeroespacial, chip A17 Pro, botón de Acción y teleobjetivo de 5 aumentos.',
    'desc' => 'El iPhone más ligero y potente de la historia. Integra el revolucionario chip A17 Pro con trazado de rayos por hardware para juegos de consola. Su cámara de 48MP ofrece 7 distancias focales profesionales y transferencia USB 3 a 10Gbps.',
    'peso' => 0.22,
    'largo' => 15.99,
    'ancho' => 7.67,
    'alto' => 0.83,
    'specs' => 
    array (
      'Pantalla' => '6.7" Super Retina XDR OLED 120Hz',
      'Procesador' => 'A17 Pro (3nm)',
      'Cámara' => '48 MP + 12 MP Ultra Wide + 12 MP Telefoto 5x',
      'Conector' => 'USB-C (USB 3)',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'PH-27-1-V1',
        'precio' => 1199.0,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '256 GB',
        ),
      ),
      1 => 
      array (
        'sku' => 'PH-27-1-V2',
        'precio' => 1399.0,
        'stock' => 6,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '512 GB',
        ),
      ),
    ),
  ),
  101 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-2',
    'nombre' => 'Samsung Galaxy S24 Ultra 5G con Galaxy AI',
    'marca' => 'Samsung',
    'modelo' => 'SM-S928UZKEXAA',
    'precio' => 1299.0,
    'precio_oferta' => 1199.0,
    'stock' => 12,
    'destacado' => true,
    'desc_corta' => 'Pantalla plana Dynamic AMOLED 2X de 2600 nits, marco de titanio, S Pen y Galaxy AI integrado.',
    'desc' => 'La era de la Inteligencia Artificial móvil. Traduce llamadas en vivo, rodea para buscar en Google y edita fotos mágicamente. Equipado con cámara de 200MP y cristal Corning Gorilla Armor antirreflejo.',
    'peso' => 0.23,
    'largo' => 16.23,
    'ancho' => 7.9,
    'alto' => 0.86,
    'specs' => 
    array (
      'Pantalla' => '6.8" QHD+ 120Hz 2600 nits',
      'Procesador' => 'Snapdragon 8 Gen 3 for Galaxy',
      'Cámara' => '200 MP + 50 MP + 12 MP + 10 MP',
      'Lápiz' => 'S Pen integrado',
    ),
    'variantes' => NULL,
  ),
  102 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-3',
    'nombre' => 'Google Pixel 8 Pro 128GB Obsidian 5G',
    'marca' => 'Google',
    'modelo' => 'GA04833-US',
    'precio' => 799.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Procesador Google Tensor G3, las mejores cámaras computacionales del mundo y 7 años de soporte.',
    'desc' => 'Experiencia Android pura con la mejor fotografía móvil. Funciones exclusivas como Mejor Toma (Best Take), Editor Mágico y Borrador Mágico para Audio.',
    'peso' => 0.21,
    'largo' => 16.26,
    'ancho' => 7.65,
    'alto' => 0.88,
    'specs' => 
    array (
      'Pantalla' => '6.7" LTPO OLED 120Hz',
      'Procesador' => 'Google Tensor G3',
      'Sensor' => 'Termómetro integrado',
    ),
    'variantes' => NULL,
  ),
  103 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-4',
    'nombre' => 'Xiaomi 14 Ultra 512GB Óptica Leica',
    'marca' => 'Xiaomi',
    'modelo' => 'MZB0G68EU',
    'precio' => 1099.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Sensor de 1 pulgada con apertura variable continua, óptica Leica Summilux y 16GB de RAM.',
    'desc' => 'Una cámara profesional que también es un smartphone. 4 sensores de 50MP desarrollados junto a Leica para una calidad cinematográfica superior en foto y video.',
    'peso' => 0.22,
    'largo' => 16.14,
    'ancho' => 7.53,
    'alto' => 0.92,
    'specs' => 
    array (
      'Cámara' => 'Sensor Sony LYT-900 1" 50MP',
      'Carga' => '90W con cable / 80W inalámbrica',
      'Batería' => '5000 mAh',
    ),
    'variantes' => NULL,
  ),
  104 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-5',
    'nombre' => 'OnePlus 12 512GB Snapdragon 8 Gen 3',
    'marca' => 'OnePlus',
    'modelo' => 'CPH2583',
    'precio' => 799.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Pantalla 2K ProXDR de 4500 nits, carga ultrarrápida SUPERVOOC de 80W y cámara Hasselblad.',
    'desc' => 'Velocidad que impresiona. Sistema de refrigeración Dual Cryo-velocity VC para que el teléfono se mantenga frío incluso en las sesiones de juego más exigentes.',
    'peso' => 0.22,
    'largo' => 16.43,
    'ancho' => 7.58,
    'alto' => 0.92,
    'specs' => 
    array (
      'Pantalla' => '6.82" 2K 120Hz 4500 nits',
      'Batería' => '5400 mAh dual-cell',
      'RAM' => '16 GB LPDDR5X',
    ),
    'variantes' => NULL,
  ),
  105 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-1',
    'nombre' => 'Apple iPad Pro 11" M4 Pantalla Ultra Retina',
    'marca' => 'Apple',
    'modelo' => 'MVV83LL/A',
    'precio' => 999.0,
    'precio_oferta' => NULL,
    'stock' => 13,
    'destacado' => true,
    'desc_corta' => 'El dispositivo Apple más delgado de la historia: chip M4 con pantalla OLED en tándem de 1000 nits.',
    'desc' => 'Potencia descomunal para ilustradores, editores y diseñadores. El chip M4 con motor neuronal de 38 billones de operaciones por segundo vuela en tareas de inteligencia artificial.',
    'peso' => 0.44,
    'largo' => 24.97,
    'ancho' => 17.75,
    'alto' => 0.53,
    'specs' => 
    array (
      'Pantalla' => '11" Ultra Retina XDR Tandem OLED',
      'Procesador' => 'Apple M4',
      'Grosor' => 'Apenas 5.3 mm',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'TAB-28-1-V1',
        'precio' => 999.0,
        'stock' => 8,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '256 GB',
        ),
      ),
      1 => 
      array (
        'sku' => 'TAB-28-1-V2',
        'precio' => 1199.0,
        'stock' => 5,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '512 GB',
        ),
      ),
    ),
  ),
  106 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-2',
    'nombre' => 'Samsung Galaxy Tab S9 Ultra 256GB 14.6"',
    'marca' => 'Samsung',
    'modelo' => 'SM-X910NZAAXAR',
    'precio' => 1049.0,
    'precio_oferta' => 999.0,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Pantalla gigante Dynamic AMOLED 2X de 14.6", resistencia al agua IP68 y S Pen incluido en caja.',
    'desc' => 'Reemplaza a tu laptop con Samsung DeX multiventana. Única tablet de su tamaño con certificación IP68 para resistir accidentes con líquidos en el escritorio.',
    'peso' => 0.73,
    'largo' => 32.64,
    'ancho' => 20.86,
    'alto' => 0.55,
    'specs' => 
    array (
      'Pantalla' => '14.6" WQXGA+ 120Hz',
      'Procesador' => 'Snapdragon 8 Gen 2 for Galaxy',
      'Batería' => '11.200 mAh',
    ),
    'variantes' => NULL,
  ),
  107 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-3',
    'nombre' => 'Xiaomi Pad 6 128GB 11" 144Hz WQHD+',
    'marca' => 'Xiaomi',
    'modelo' => 'VHU4379EU',
    'precio' => 349.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Increíble pantalla 2.8K fluida de 144Hz, cuerpo de aluminio unibody y 4 altavoces Dolby Atmos.',
    'desc' => 'La mejor tablet de gama media para estudiantes y entretenimiento multimedia. Batería de 8840mAh para ver series y trabajar durante todo el día sin recargar.',
    'peso' => 0.49,
    'largo' => 25.39,
    'ancho' => 16.51,
    'alto' => 0.65,
    'specs' => 
    array (
      'Pantalla' => '11" 2.8K 144Hz IPS',
      'Procesador' => 'Snapdragon 870',
      'Audio' => '4 Altavoces estéreo',
    ),
    'variantes' => NULL,
  ),
  108 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-4',
    'nombre' => 'Lenovo Tab P11 Pro Gen 2 128GB OLED',
    'marca' => 'Lenovo',
    'modelo' => 'ZAB50101US',
    'precio' => 329.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Panel cinematográfico OLED de 11.2" a 120Hz con sonido envolvente de cuatro altavoces JBL.',
    'desc' => 'Colores negros puros y contraste infinito para películas y juegos. Se puede utilizar como segunda pantalla inalámbrica para tu laptop Windows.',
    'peso' => 0.48,
    'largo' => 26.37,
    'ancho' => 16.67,
    'alto' => 0.68,
    'specs' => 
    array (
      'Pantalla' => '11.2" 2.5K OLED 120Hz',
      'Altavoces' => '4x JBL con Dolby Atmos',
      'Modo' => 'Segunda pantalla PC',
    ),
    'variantes' => NULL,
  ),
  109 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-5',
    'nombre' => 'Huawei MatePad 11.5" PaperMatte Edition',
    'marca' => 'Huawei',
    'modelo' => 'GOT-W29',
    'precio' => 299.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Pantalla antirreflejo con textura de papel que elimina el 97% de los brillos para lectura y dibujo.',
    'desc' => 'Escribe y dibuja como si fuera un cuaderno de papel real gracias a su grabado nanométrico que simula la fricción del grafito. Cuida la vista en largas lecturas.',
    'peso' => 0.5,
    'largo' => 26.09,
    'ancho' => 17.68,
    'alto' => 0.69,
    'specs' => 
    array (
      'Pantalla' => '11.5" 120Hz PaperMatte',
      'Relación' => '3:2 para productividad',
      'Software' => 'Huawei Notes integrado',
    ),
    'variantes' => NULL,
  ),
  110 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-1',
    'nombre' => 'Apple Watch Series 9 GPS 45mm',
    'marca' => 'Apple',
    'modelo' => 'MR9A3LL/A',
    'precio' => 399.0,
    'precio_oferta' => NULL,
    'stock' => 21,
    'destacado' => true,
    'desc_corta' => 'Gesto de Doble Toque, pantalla con el doble de brillo de 2000 nits y chip S9 SiP ultrarrápido.',
    'desc' => 'Controla tu reloj sin tocar la pantalla simplemente juntando el dedo índice y el pulgar dos veces. Sensor de temperatura, medición de oxígeno en sangre y electrocardiograma (ECG).',
    'peso' => 0.04,
    'largo' => 4.5,
    'ancho' => 3.8,
    'alto' => 1.07,
    'specs' => 
    array (
      'Pantalla' => 'OLED Retina Siempre Activa 2000 nits',
      'Chip' => 'Apple S9 SiP',
      'Sensores' => 'ECG, SpO2, Temperatura, Detección de Caídas',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'WAT-29-1-V1',
        'precio' => 399.0,
        'stock' => 12,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'WAT-29-1-V2',
        'precio' => 399.0,
        'stock' => 9,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Plateado',
        ),
      ),
    ),
  ),
  111 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-2',
    'nombre' => 'Samsung Galaxy Watch 6 Classic 47mm LTE',
    'marca' => 'Samsung',
    'modelo' => 'SM-R965UZKAXAA',
    'precio' => 369.0,
    'precio_oferta' => 339.0,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Bisel giratorio físico icónico, cristal de zafiro y análisis de composición corporal BIA.',
    'desc' => 'Navega por tus aplicaciones girando su bisel de acero inoxidable con respuesta táctil mecánica. Monitorea tu porcentaje de grasa corporal, masa muscular y calidad de sueño.',
    'peso' => 0.06,
    'largo' => 4.65,
    'ancho' => 4.65,
    'alto' => 1.09,
    'specs' => 
    array (
      'Bisel' => 'Giratorio físico de acero',
      'Cristal' => 'Cristal de zafiro',
      'Conectividad' => '4G LTE + GPS + NFC',
    ),
    'variantes' => NULL,
  ),
  112 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-3',
    'nombre' => 'Garmin Fenix 7 Pro Sapphire Solar GPS Titán',
    'marca' => 'Garmin',
    'modelo' => '010-02777-10',
    'precio' => 799.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => true,
    'desc_corta' => 'Lente de carga solar que extiende la batería hasta 22 días, linterna LED y mapas topográficos.',
    'desc' => 'El reloj de aventura multideporte definitivo. Caja de polímero reforzado con bisel y tapa trasera de titanio. Mide métricas de entrenamiento avanzadas, pendientes en subida y resistencia en carrera.',
    'peso' => 0.07,
    'largo' => 4.7,
    'ancho' => 4.7,
    'alto' => 1.45,
    'specs' => 
    array (
      'Batería' => 'Hasta 22 días con energía solar',
      'GPS' => 'Multibanda de alta precisión',
      'Linterna' => 'LED integrada',
    ),
    'variantes' => NULL,
  ),
  113 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-4',
    'nombre' => 'Amazfit GTR 4 Smartwatch GPS Dual Band',
    'marca' => 'Amazfit',
    'modelo' => 'W2166OV1N',
    'precio' => 189.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Batería épica de 14 días, más de 150 modos deportivos y llamadas telefónicas por Bluetooth.',
    'desc' => 'Antena GPS polarizada circularmente que rastrea tus rutas con un 99% de precisión incluso entre rascacielos. Reconocimiento inteligente de ejercicios de fuerza.',
    'peso' => 0.03,
    'largo' => 4.6,
    'ancho' => 4.6,
    'alto' => 1.06,
    'specs' => 
    array (
      'Pantalla' => '1.43" AMOLED HD',
      'Batería' => '14 Días de uso típico',
      'Deportes' => '150+ Modos deportivos',
    ),
    'variantes' => NULL,
  ),
  114 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-5',
    'nombre' => 'Xiaomi Watch 2 Pro 46mm Wear OS Snapdragon',
    'marca' => 'Xiaomi',
    'modelo' => 'BHR7584GL',
    'precio' => 229.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Sistema Google Wear OS con Google Wallet, Maps, Play Store y procesador Snapdragon W5+ Gen 1.',
    'desc' => 'Toda la tienda de Google en tu muñeca. Paga tus compras con Google Pay mediante NFC y responde mensajes de WhatsApp con teclado en pantalla o dictado de voz.',
    'peso' => 0.05,
    'largo' => 4.76,
    'ancho' => 4.59,
    'alto' => 1.18,
    'specs' => 
    array (
      'Sistema' => 'Wear OS by Google',
      'Procesador' => 'Snapdragon W5+ Gen 1 (4nm)',
      'Cuerpo' => 'Acero inoxidable',
    ),
    'variantes' => NULL,
  ),
  115 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-1',
    'nombre' => 'LG OLED evo C3 55" 4K Smart TV 120Hz',
    'marca' => 'LG',
    'modelo' => 'OLED55C3PUA',
    'precio' => 1299.0,
    'precio_oferta' => 1199.0,
    'stock' => 6,
    'destacado' => true,
    'desc_corta' => 'Procesador α9 AI 4K Gen6, brillo potenciado Brightness Booster y 4 puertos HDMI 2.1 a 120Hz.',
    'desc' => 'La televisión predilecta para cinéfilos y gamers. Píxeles autoemisivos que se apagan por completo para un contraste infinito con Dolby Vision, Dolby Atmos y FreeSync / G-SYNC.',
    'peso' => 16.0,
    'largo' => 122.2,
    'ancho' => 23.0,
    'alto' => 75.7,
    'specs' => 
    array (
      'Panel' => '55" OLED evo 4K 120Hz',
      'Puertos' => '4x HDMI 2.1 48Gbps',
      'Sistema' => 'webOS 23',
      'Gaming' => 'G-SYNC / FreeSync / 0.1ms',
    ),
    'variantes' => NULL,
  ),
  116 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-2',
    'nombre' => 'Samsung Neo QLED 65" QN90C 4K Mini-LED',
    'marca' => 'Samsung',
    'modelo' => 'QN65QN90CAFXZA',
    'precio' => 1499.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Miles de Quantum Mini LEDs con control de luz preciso Quantum Matrix y procesador Neural 4K.',
    'desc' => 'Brillo despampanante ideal para salas iluminadas con grandes ventanales. La tecnología antirreflejo elimina los destellos de lámparas y el sonido OTS+ sigue la acción en pantalla.',
    'peso' => 27.5,
    'largo' => 144.6,
    'ancho' => 27.0,
    'alto' => 89.1,
    'specs' => 
    array (
      'Panel' => '65" Neo QLED Mini-LED 120Hz',
      'Procesador' => 'Neural Quantum Processor 4K',
      'Audio' => 'Dolby Atmos 60W 4.2.2 canales',
    ),
    'variantes' => NULL,
  ),
  117 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-3',
    'nombre' => 'Sony Bravia XR 55" A80L OLED 4K Google TV',
    'marca' => 'Sony',
    'modelo' => 'XR55A80L',
    'precio' => 1399.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Procesador Cognitivo XR que replica cómo perciben los ojos humanos y sonido Acoustic Surface.',
    'desc' => 'La propia pantalla vibra con actuadores invisibles para emitir el sonido directamente desde el lugar donde ocurren los diálogos. Funciones exclusivas diseñadas para PlayStation 5.',
    'peso' => 17.9,
    'largo' => 122.7,
    'ancho' => 32.7,
    'alto' => 73.8,
    'specs' => 
    array (
      'Panel' => '55" OLED 4K HDR',
      'Procesador' => 'Cognitive Processor XR',
      'Audio' => 'Acoustic Surface Audio+',
      'Sistema' => 'Google TV',
    ),
    'variantes' => NULL,
  ),
  118 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-4',
    'nombre' => 'Hisense U8K 55" Mini-LED ULED 4K 144Hz',
    'marca' => 'Hisense',
    'modelo' => '55U8K',
    'precio' => 749.0,
    'precio_oferta' => 699.0,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Brillo pico colosal de 1500 nits, panel nativo de 144Hz con FreeSync Premium Pro y subwoofer.',
    'desc' => 'El mejor valor de la tecnología Mini-LED del mercado. Integra subwoofer trasero de 50W y compatibilidad total con todos los formatos HDR del mercado (Dolby Vision IQ, HDR10+, HLG).',
    'peso' => 19.8,
    'largo' => 123.2,
    'ancho' => 30.0,
    'alto' => 77.0,
    'specs' => 
    array (
      'Panel' => '55" Mini-LED ULED 144Hz',
      'Brillo' => '1500 nits pico',
      'Atenuación Local' => 'Más de 600 zonas',
    ),
    'variantes' => NULL,
  ),
  119 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-5',
    'nombre' => 'Philips 55" 4K UHD LED Ambilight Android TV',
    'marca' => 'Philips',
    'modelo' => '55PUT7906',
    'precio' => 529.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Luces LED inteligentes Ambilight en tres lados que proyectan los colores de la pantalla en tu pared.',
    'desc' => 'Una experiencia visual única: las luces traseras Ambilight se sincronizan en milisegundos con las escenas de tu película para hacer que la pantalla parezca mucho más grande.',
    'peso' => 14.5,
    'largo' => 123.1,
    'ancho' => 25.6,
    'alto' => 73.5,
    'specs' => 
    array (
      'Iluminación' => 'Ambilight de 3 lados integrado',
      'Resolución' => '4K UHD HDR',
      'Sistema' => 'Android TV con comandos de voz',
    ),
    'variantes' => NULL,
  ),
  120 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-1',
    'nombre' => 'Epson EcoTank L3250 Multifuncional Wi-Fi',
    'marca' => 'Epson',
    'modelo' => 'C11CJ67301',
    'precio' => 189.0,
    'precio_oferta' => 179.0,
    'stock' => 18,
    'destacado' => true,
    'desc_corta' => 'Imprime hasta 4500 páginas en negro y 7500 a color con la tinta incluida sin cartuchos costosos.',
    'desc' => 'La multifuncional más vendida para hogares y estudiantes. Sistema de tanque de tinta frontal EcoTank con botellas codificadas que no derraman. Conexión inalámbrica y app Epson Smart Panel.',
    'peso' => 3.9,
    'largo' => 37.5,
    'ancho' => 34.7,
    'alto' => 17.9,
    'specs' => 
    array (
      'Rendimiento' => '4.500 págs negro / 7.500 págs color',
      'Resolución' => '5760 x 1440 dpi',
      'Tecnología' => 'MicroPiezo Heat-Free sin calor',
    ),
    'variantes' => NULL,
  ),
  121 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-2',
    'nombre' => 'HP Smart Tank 580 Multifuncional Inalámbrica',
    'marca' => 'HP',
    'modelo' => '1F3Y2A',
    'precio' => 169.0,
    'precio_oferta' => NULL,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Conexión Wi-Fi autorreparable, escáner de alta resolución y hasta 2 años de tinta en la caja.',
    'desc' => 'Imprime, escanea y copia sin interrupciones. Los sensores automáticos de nivel de tinta te alertan antes de que se agote. Compatible con Apple AirPrint y Wi-Fi Direct.',
    'peso' => 5.03,
    'largo' => 43.46,
    'ancho' => 36.15,
    'alto' => 15.72,
    'specs' => 
    array (
      'Velocidad' => '12 ppm negro / 5 ppm color',
      'Conectividad' => 'Wi-Fi doble banda / Bluetooth LE / USB',
    ),
    'variantes' => NULL,
  ),
  122 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-3',
    'nombre' => 'Canon PIXMA G3110 Tanque Continuo Wi-Fi',
    'marca' => 'Canon',
    'modelo' => '2315C004AA',
    'precio' => 159.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Texto negro nítido con tinta de pigmento y fotos vibrantes sin bordes hasta tamaño carta.',
    'desc' => 'Sistema de tinta híbrido que combina tinta negra a base de pigmento para documentos de texto que no se corren con el resaltador y tintas a base de colorantes para fotos brillantes.',
    'peso' => 6.3,
    'largo' => 44.5,
    'ancho' => 33.0,
    'alto' => 16.3,
    'specs' => 
    array (
      'Fotos' => 'Impresión de fotos sin bordes 10x15cm en 60s',
      'Rendimiento' => '6.000 págs negro / 7.000 págs color',
    ),
    'variantes' => NULL,
  ),
  123 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-4',
    'nombre' => 'Brother HL-L2350DW Láser Monocromática Dúplex',
    'marca' => 'Brother',
    'modelo' => 'HLL2350DW',
    'precio' => 149.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Velocidad láser de hasta 32 páginas por minuto con impresión a doble cara automática (dúplex).',
    'desc' => 'El caballo de batalla para oficinas y despachos de abogados. Bandeja de papel cerrada con capacidad para 250 hojas que protege el papel del polvo y la humedad.',
    'peso' => 7.2,
    'largo' => 35.6,
    'ancho' => 36.0,
    'alto' => 18.3,
    'specs' => 
    array (
      'Tecnología' => 'Láser electrofotográfico',
      'Velocidad' => '32 ppm',
      'Bandeja' => '250 Hojas',
      'Dúplex' => 'Automático',
    ),
    'variantes' => NULL,
  ),
  124 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-5',
    'nombre' => 'Epson EcoTank L8180 Fotográfica 6 Colores A3+',
    'marca' => 'Epson',
    'modelo' => 'C11CJ21301',
    'precio' => 649.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => true,
    'desc_corta' => '6 tintas Claria ET con gris y negro fotográfico para impresiones artísticas de hasta tamaño A3+.',
    'desc' => 'La impresora soñada para fotógrafos de estudio, arquitectos y diseñadores gráficos. Permite imprimir sobre cartulinas gruesas de hasta 1.3mm y lienzos canvas sin atascos.',
    'peso' => 11.1,
    'largo' => 52.3,
    'ancho' => 37.9,
    'alto' => 16.9,
    'specs' => 
    array (
      'Formato Máximo' => 'A3+ (32.9 x 48.3 cm)',
      'Tintas' => '6 Colores Claria ET',
      'Pantalla' => '4.3" Táctil a color',
    ),
    'variantes' => NULL,
  ),
  125 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-1',
    'nombre' => 'Sony PlayStation 5 Slim Edición Disco 1TB',
    'marca' => 'Sony',
    'modelo' => 'CFI-2000A01',
    'precio' => 499.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => true,
    'desc_corta' => 'Chasis un 30% más compacto con 1TB de SSD ultra rápido, audio 3D Tempest y control DualSense.',
    'desc' => 'Disfruta de juegos en 4K hasta a 120 FPS con retroalimentación háptica y gatillos adaptativos que simulan la tensión de una cuerda de arco o el retroceso de un arma en tus manos.',
    'peso' => 3.2,
    'largo' => 35.8,
    'ancho' => 21.6,
    'alto' => 9.6,
    'specs' => 
    array (
      'Almacenamiento' => '1 TB SSD personalizado',
      'Unidad Óptica' => 'Ultra HD Blu-ray desmontable',
      'Resolución' => '4K / 120Hz / HDR',
    ),
    'variantes' => NULL,
  ),
  126 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-2',
    'nombre' => 'Microsoft Xbox Series X 1TB Consola',
    'marca' => 'Microsoft',
    'modelo' => 'RRT-00001',
    'precio' => 489.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => true,
    'desc_corta' => '12 teraflops de potencia gráfica pura: la consola más potente del mercado con Xbox Game Pass.',
    'desc' => 'Tiempos de carga casi inexistentes con Xbox Velocity Architecture y función Quick Resume para alternar entre múltiples juegos al instante exactamente donde los dejaste.',
    'peso' => 4.45,
    'largo' => 15.1,
    'ancho' => 15.1,
    'alto' => 30.1,
    'specs' => 
    array (
      'Potencia' => '12 TFLOPS AMD RDNA 2',
      'Almacenamiento' => '1 TB Custom NVMe',
      'Compatibilidad' => '4 Generaciones de Xbox',
    ),
    'variantes' => NULL,
  ),
  127 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-3',
    'nombre' => 'Nintendo Switch OLED 64GB',
    'marca' => 'Nintendo',
    'modelo' => 'HEG-S-KAAAA',
    'precio' => 339.0,
    'precio_oferta' => NULL,
    'stock' => 20,
    'destacado' => true,
    'desc_corta' => 'Pantalla OLED vibrante de 7 pulgadas, soporte ajustable amplio y base con puerto LAN por cable.',
    'desc' => 'Juega en casa en tu televisor o llévala contigo donde quieras. Su pantalla OLED llena tus aventuras de colores vivos con audio mejorado en modo portátil y 64GB de almacenamiento.',
    'peso' => 0.42,
    'largo' => 24.13,
    'ancho' => 10.16,
    'alto' => 1.39,
    'specs' => 
    array (
      'Pantalla' => '7.0" OLED Multi-touch',
      'Modos' => 'Televisor, Sobremesa, Portátil',
      'Base' => 'Con puerto Ethernet LAN',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CON-33-3-V1',
        'precio' => 339.0,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
      1 => 
      array (
        'sku' => 'CON-33-3-V2',
        'precio' => 339.0,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
  ),
  128 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-4',
    'nombre' => 'ASUS ROG Ally Z1 Extreme 512GB Consola PC',
    'marca' => 'ASUS',
    'modelo' => 'RC71L-ALLY.Z1X_512',
    'precio' => 599.0,
    'precio_oferta' => 549.0,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Consola portátil con Windows 11: juega tus títulos de Steam, Game Pass, Epic Games y Battle.net.',
    'desc' => 'Tu biblioteca completa de juegos de computadora en la palma de tu mano. Pantalla Full HD de 120Hz con FreeSync Premium y procesador AMD Ryzen Z1 Extreme con arquitectura RDNA 3.',
    'peso' => 0.61,
    'largo' => 28.0,
    'ancho' => 11.1,
    'alto' => 2.12,
    'specs' => 
    array (
      'Pantalla' => '7" FHD 120Hz 500 nits IPS',
      'Procesador' => 'AMD Ryzen Z1 Extreme (8C/16T)',
      'Sistema' => 'Windows 11 Home',
    ),
    'variantes' => NULL,
  ),
  129 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-5',
    'nombre' => 'Sony PlayStation Portal Remote Player',
    'marca' => 'Sony',
    'modelo' => 'CFI-Y1001',
    'precio' => 199.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Transmite tus juegos de PS5 a una pantalla LCD de 8 pulgadas y 60 FPS a través de tu Wi-Fi.',
    'desc' => 'Siente la inmersión total del control DualSense con gatillos adaptativos y respuesta háptica mientras otra persona usa el televisor de la casa.',
    'peso' => 0.54,
    'largo' => 30.5,
    'ancho' => 12.0,
    'alto' => 6.5,
    'specs' => 
    array (
      'Pantalla' => '8" LCD 1080p a 60 FPS',
      'Controles' => 'DualSense integrados completos',
      'Conectividad' => 'Wi-Fi 5 GHz',
    ),
    'variantes' => NULL,
  ),
  130 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-1',
    'nombre' => 'Corsair T3 Rush Silla Gamer Tela Transpirable',
    'marca' => 'Corsair',
    'modelo' => 'CF-9010029-WW',
    'precio' => 299.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => true,
    'desc_corta' => 'Tapizado en tela suave transpirable que no retiene calor, reposabrazos 4D y cojines de espuma viscoelástica.',
    'desc' => 'Inspirada en los asientos de autos de carreras deportivos de alto rendimiento. Mantiene tu cuerpo fresco durante el calor de Panamá con reclinación de hasta 160 grados.',
    'peso' => 22.5,
    'largo' => 88.5,
    'ancho' => 69.0,
    'alto' => 37.5,
    'specs' => 
    array (
      'Material' => 'Tela suave transpirable',
      'Pistón' => 'Gas Clase 4 con soporte hasta 120 kg',
      'Reposabrazos' => 'Ajustables en 4 direcciones (4D)',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CHR-34-1-V1',
        'precio' => 299.0,
        'stock' => 7,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Gris',
        ),
      ),
      1 => 
      array (
        'sku' => 'CHR-34-1-V2',
        'precio' => 299.0,
        'stock' => 5,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
    ),
  ),
  131 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-2',
    'nombre' => 'Razer Iskur Silla Ergonómica Soporte Lumbar',
    'marca' => 'Razer',
    'modelo' => 'RZ38-02770100-R3U1',
    'precio' => 399.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Sistema de curvatura lumbar ergonómico totalmente ajustable que acompaña la forma de tu columna.',
    'desc' => 'A diferencia de las sillas convencionales con cojines planos, la Razer Iskur cuenta con un respaldo lumbar mecánico curvo que empuja y soporta tu espalda baja para evitar dolores tras horas de trabajo o juego.',
    'peso' => 30.0,
    'largo' => 137.0,
    'ancho' => 66.5,
    'alto' => 74.5,
    'specs' => 
    array (
      'Soporte Lumbar' => 'Mecánico ajustable por palanca',
      'Cuero' => 'Sintético multicapa de alta resistencia',
      'Peso Soportado' => 'Hasta 136 kg',
    ),
    'variantes' => NULL,
  ),
  132 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-3',
    'nombre' => 'Cooler Master Caliber R2 Silla Ergonómica',
    'marca' => 'Cooler Master',
    'modelo' => 'CMI-GCR2-2019',
    'precio' => 249.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Cuero sintético transpirable perforado y base de estrella de acero reforzado para máxima estabilidad.',
    'desc' => 'Construcción con espuma moldeada en frío de densidad ultra alta que no se deforma con el paso de los años. Reclinación plana de 180° para descansar entre partidas.',
    'peso' => 21.5,
    'largo' => 86.0,
    'ancho' => 70.0,
    'alto' => 34.0,
    'specs' => 
    array (
      'Reclinación' => 'Hasta 180 grados',
      'Estructura' => 'Acero tubular soldado',
      'Cojines' => 'Cervical y lumbar incluidos',
    ),
    'variantes' => NULL,
  ),
  133 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-4',
    'nombre' => 'Thermaltake U Fit Black-Red Silla Gaming',
    'marca' => 'Thermaltake',
    'modelo' => 'GGC-UFT-BRMWDS-01',
    'precio' => 219.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Acolchado grueso con acabados de costura estilo fibra de carbono y ruedas de 60mm suaves.',
    'desc' => 'Ideal para jugadores que buscan confort garantizado a precio asequible. Base de 5 puntas de alta resistencia y pistón de elevación certificado por TÜV Rheinland.',
    'peso' => 24.0,
    'largo' => 89.0,
    'ancho' => 69.0,
    'alto' => 38.0,
    'specs' => 
    array (
      'Material' => 'PVC Premium antidesgaste',
      'Capacidad' => 'Hasta 140 kg',
      'Reposabrazos' => 'Ajustables en 2D',
    ),
    'variantes' => NULL,
  ),
  134 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-5',
    'nombre' => 'MSI MAG CH130 X Silla Gaming Confort Pro',
    'marca' => 'MSI',
    'modelo' => '306-7Y25B11-W57',
    'precio' => 279.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Asiento ensanchado de 57cm con profundidad extra y marco de acero inoxidable de alta rigidez.',
    'desc' => 'Diseñada con curvatura en los hombros para mejorar la postura natural del cuerpo y evitar la tensión muscular en el cuello durante extensas sesiones.',
    'peso' => 21.9,
    'largo' => 90.0,
    'ancho' => 68.0,
    'alto' => 38.0,
    'specs' => 
    array (
      'Asiento' => '57 cm de ancho ultra espacioso',
      'Pistón' => 'Clase 4',
      'Espuma' => 'Espuma de alta densidad (45-48 kg/m³)',
    ),
    'variantes' => NULL,
  ),
  135 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-1',
    'nombre' => 'TP-Link Tapo C210 Cámara de Seguridad Wi-Fi 2K Pan/Tilt',
    'marca' => 'TP-Link',
    'modelo' => 'TAPO-C210',
    'precio' => 34.99,
    'precio_oferta' => NULL,
    'stock' => 38,
    'destacado' => true,
    'desc_corta' => 'Rotación horizontal de 360°, visión nocturna nítida, audio bidireccional y detección de movimiento.',
    'desc' => 'Vigila tu casa o negocio desde tu smartphone en tiempo real. Graba en tarjeta MicroSD de hasta 512GB sin costos mensuales de suscripción y recibe alertas instantáneas cuando detecte personas.',
    'peso' => 0.19,
    'largo' => 8.66,
    'ancho' => 8.5,
    'alto' => 11.77,
    'specs' => 
    array (
      'Rotación' => '360° Horizontal / 114° Vertical',
      'Visión Nocturna' => 'Hasta 9 metros en oscuridad total',
      'Almacenamiento' => 'Ranura MicroSD hasta 512GB',
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CAM-35-1-V1',
        'precio' => 29.99,
        'stock' => 20,
        'opcion' => 
        array (
          0 => 'Resolución',
          1 => 'Full HD',
        ),
      ),
      1 => 
      array (
        'sku' => 'CAM-35-1-V2',
        'precio' => 34.99,
        'stock' => 18,
        'opcion' => 
        array (
          0 => 'Resolución',
          1 => '2K',
        ),
      ),
    ),
  ),
  136 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-2',
    'nombre' => 'Google Nest Cam Indoor/Outdoor con Batería',
    'marca' => 'Google',
    'modelo' => 'GA01317-US',
    'precio' => 159.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Cámara inteligente resistente a la intemperie sin cables: reconoce personas, animales y autos.',
    'desc' => 'Instalación magnética en segundos sin necesidad de enchufes cercanos. Su Inteligencia Artificial procesa el video en el propio dispositivo para avisarte solo de eventos relevantes.',
    'peso' => 0.4,
    'largo' => 8.3,
    'ancho' => 8.3,
    'alto' => 8.3,
    'specs' => 
    array (
      'Alimentación' => 'Batería recargable integrada',
      'Intemperie' => 'Resistente a lluvia y sol IP54',
      'Video' => '1080p HDR con visión nocturna',
    ),
    'variantes' => NULL,
  ),
  137 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-3',
    'nombre' => 'Xiaomi Smart Camera C400 2.5K 360°',
    'marca' => 'Xiaomi',
    'modelo' => 'BHR6619GL',
    'precio' => 44.0,
    'precio_oferta' => NULL,
    'stock' => 22,
    'destacado' => false,
    'desc_corta' => 'Sensor de 4 megapíxeles con resolución 2.5K ultra clara y seguimiento automático de humanos por IA.',
    'desc' => 'Conectividad Wi-Fi de doble banda (2.4GHz y 5GHz) para transferencias de video sin cortes. Micrófono con reducción de ruido ambiental para escuchar con claridad.',
    'peso' => 0.31,
    'largo' => 7.8,
    'ancho' => 7.8,
    'alto' => 11.9,
    'specs' => 
    array (
      'Resolución' => '2560 x 1440 (2.5K 4MP)',
      'Wi-Fi' => 'Dual Band 2.4 / 5 GHz',
      'Cifrado' => 'Chip de seguridad Xiaomi MJA1',
    ),
    'variantes' => NULL,
  ),
  138 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-4',
    'nombre' => 'Canon EOS R50 Cámara Mirrorless 4K Vlog',
    'marca' => 'Canon',
    'modelo' => '5811C002',
    'precio' => 649.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => true,
    'desc_corta' => 'Sensor APS-C de 24.2 MP, video 4K sin recorte a 30p y pantalla táctil de ángulo variable.',
    'desc' => 'La cámara definitiva para creadores de contenido de YouTube, TikTok e Instagram. Enfoque automático Dual Pixel CMOS AF II con detección de personas, perros, gatos y vehículos.',
    'peso' => 0.38,
    'largo' => 11.63,
    'ancho' => 8.55,
    'alto' => 6.88,
    'specs' => 
    array (
      'Sensor' => '24.2 MP CMOS APS-C',
      'Video' => '4K UHD 30p sobremuestreado de 6K',
      'Pantalla' => 'Táctil abatible de 3.0"',
    ),
    'variantes' => NULL,
  ),
  139 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-5',
    'nombre' => 'GoPro HERO12 Black Cámara de Acción 5.3K',
    'marca' => 'GoPro',
    'modelo' => 'CHDHX-121-CN',
    'precio' => 379.0,
    'precio_oferta' => 349.0,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Estabilización HyperSmooth 6.0 galardonada con un Emmy, video 5.3K60 y sumergible a 10m sin carcasa.',
    'desc' => 'Captura tus aventuras más extremas con calidad profesional. Soporte para audio inalámbrico por Bluetooth para AirPods y micrófonos externos, video HDR y batería Enduro para bajas temperaturas.',
    'peso' => 0.15,
    'largo' => 7.18,
    'ancho' => 5.08,
    'alto' => 3.36,
    'specs' => 
    array (
      'Video' => '5.3K a 60 fps / 4K a 120 fps',
      'Estabilización' => 'HyperSmooth 6.0 con Bloqueo de Horizonte 360°',
      'Sumergible' => 'Hasta 10 metros',
    ),
    'variantes' => NULL,
  ),
);
    }
}
