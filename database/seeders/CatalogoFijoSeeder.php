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
        // Si ya existen productos, no sobrescribir para proteger fotos, pedidos e inventario
        if (Producto::count() > 0) {
            $this->command->info("El catálogo ya contiene productos. Se omite la siembra para proteger los datos existentes.");
            return;
        }

        $this->command->info("Iniciando siembra del catálogo...");

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
                'garantia_info' => $item['garantia'] ?? [
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
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 45.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Ensamblaje profesional de componentes de oficina con pruebas de encendido e instalación de BIOS.',
    'desc' => 'Servicio profesional de armado de computadoras de oficina. Incluye montaje de placa madre, procesador con disipador, memoria RAM, unidad de almacenamiento y fuente de poder en chasis estándar. Se realiza ordenamiento básico de cableado, actualización a la última versión estable de BIOS y prueba de encendido durante 30 minutos.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 computadora',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Montaje de componentes compatibles para oficina',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Instalación física, conexión de cables y prueba de encendido',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Equipo ensamblado y comprobación básica',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Componentes, licencias y instalación de software',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Componentes suministrados o cotizados por separado',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  1 => 
  array (
    'cat' => 'armado-de-pc',
    'sku' => 'SRV-ARM-2',
    'nombre' => 'Armado de PC Gamer Profesional',
    'marca' => 'Corsair',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 85.0,
    'precio_oferta' => 75,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Montaje de alto rendimiento gamer con gestión premium de cables y optimización de curvas de ventilación.',
    'desc' => 'Montaje exhaustivo para equipos gamer de alta gama. Incluye instalación de tarjetas gráficas pesadas con soporte anti-sag, configuración de ventiladores ARGB/PWM, optimización de curvas térmicas en BIOS, habilitación de perfiles XMP/EXPO y pruebas de estrés térmico con Cinebench y 3DMark durante 45 minutos.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 PC gamer',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Montaje de CPU, RAM, almacenamiento, GPU y fuente',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Organización de cables y comprobación de temperaturas',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Equipo montado y resultado de pruebas',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Componentes, licencias y overclock',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Compatibilidad de gabinete, fuente y refrigeración',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
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
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 160.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Montaje especializado de refrigeración líquida personalizada con doblado de tubos y prueba de fugas de 24h.',
    'desc' => 'Servicio de ensamblaje artesanal para sistemas con refrigeración líquida custom. Incluye curvado de tubos rígidos o flexibles, purgado del circuito con líquido dieléctrico refrigerante, prueba de presión de aire y prueba de hermeticidad continua de 24 horas para garantizar cero fugas.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 equipo',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Instalación de circuito de refrigeración líquida personalizado',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Montaje y prueba de estanqueidad antes del encendido',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Equipo con circuito verificado',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Bloques, tubos, bomba, radiadores y refrigerante',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Inspección y cotización previa',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  3 => 
  array (
    'cat' => 'armado-de-pc',
    'sku' => 'SRV-ARM-4',
    'nombre' => 'Migración de Componentes a Nuevo Chasis',
    'marca' => 'NZXT',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 60.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Desensamblaje cuidadoso de tu equipo actual y traspaso completo a un nuevo gabinete con limpieza previa.',
    'desc' => 'Traspaso integral de tus componentes existentes a un nuevo chasis o gabinete. Desmontamos pieza por pieza con protección antiestática, limpiamos el polvo acumulado y reconstruimos el sistema en el nuevo case asegurando un flujo de aire óptimo y cableado limpio.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 equipo',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Traslado de componentes a otro gabinete',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Desmontaje, montaje y conexión',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Encendido y comprobación funcional',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Gabinete, repuestos y reparación de componentes',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Gabinete compatible',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  4 => 
  array (
    'cat' => 'armado-de-pc',
    'sku' => 'SRV-ARM-5',
    'nombre' => 'Actualización General de Hardware',
    'marca' => 'MSI',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 40.0,
    'precio_oferta' => 35,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Instalación de nueva GPU, procesador, fuente o almacenamiento con actualización de drivers y firmware.',
    'desc' => 'Upgrade técnico de componentes para potenciar tu computadora. Instalamos la pieza seleccionada (tarjeta gráfica, fuente modular, kit de RAM, SSD M.2 o CPU), actualizamos la BIOS si la nueva generación lo requiere e instalamos los últimos controladores oficiales verificados.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 equipo',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Instalación de componentes de actualización',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Revisión de compatibilidad y comprobación de detección',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Equipo con hardware instalado',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Componentes, migración de datos y licencias',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Diagnóstico de compatibilidad previo',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  5 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-1',
    'nombre' => 'Mantenimiento Preventivo de Laptop',
    'marca' => 'Dell',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 35.0,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Limpieza de ventiladores, disipador y cambio de pasta térmica para bajar temperaturas y eliminar ruidos.',
    'desc' => 'Limpieza interna profunda para laptops de uso personal o empresarial. Desarmamos el módulo térmico, retiramos pelusas y polvo del extractor, aplicamos pasta térmica de alta conductividad y lubricamos los ventiladores para restaurar un funcionamiento silencioso.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 laptop',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Limpieza interna accesible y revisión de ventilación',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Inspección y prueba básica',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Registro de estado y recomendaciones',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Repuestos y cambio de pasta/pads salvo cotización',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Evaluar posibilidad de desmontaje',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  6 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-2',
    'nombre' => 'Limpieza Profunda de PC Gamer',
    'marca' => 'Corsair',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 55.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Desensamble de componentes, soplado con aire ionizado, cambio de pasta térmica y limpieza de radiadores.',
    'desc' => 'Mantenimiento exhaustivo diseñado para computadoras de alto consumo térmico. Retiramos tarjeta gráfica y radiadores, limpiamos filtros magnéticos, aplicamos solvente dieléctrico en ventiladores y renovamos los compuestos térmicos en CPU y GPU.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 PC gamer',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Limpieza de gabinete, filtros y ventiladores',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Inspección de cableado y temperatura',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Equipo limpio y comprobación funcional',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Reparación, repuestos y circuito líquido personalizado',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Inspección previa',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  7 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-3',
    'nombre' => 'Cambio de Pasta Térmica y Thermal Pads',
    'marca' => 'Cooler Master',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 30.0,
    'precio_oferta' => 25,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Sustitución de almohadillas térmicas resecas y pasta térmica vieja en tarjetas gráficas y procesadores.',
    'desc' => 'Reemplazo de pads térmicos de memoria VRAM y VRM con almohadillas de alta conductividad, además de aplicación fresca de pasta térmica en el procesador. Ideal para tarjetas gráficas y consolas con más de 2 años de uso intensivo.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 equipo o componente por cotizar',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Sustitución de pasta térmica y pads cuando corresponda',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Limpieza de superficies y comprobación de temperaturas',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Registro de material instalado',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Repuestos y pads especiales salvo acuerdo',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Espesor de pads y método adecuados para el equipo',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  8 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-4',
    'nombre' => 'Mantenimiento Preventivo de Servidor / Rack',
    'marca' => 'HP',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 130.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Inspección técnica, limpieza de fuentes redundantes, ventilación de chasis 1U/2U y revisión de arreglos RAID.',
    'desc' => 'Servicio para infraestructura empresarial. Limpieza técnica en seco de chasis de servidor tipo rack o torre, revisión de sensores térmicos, verificación del estado de salud de discos en RAID y limpieza de bahías hot-swap.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Presencial programado',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 servidor/rack por definir',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Limpieza y revisión preventiva de ventilación',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Inspección de cables y registro de hallazgos',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Informe de mantenimiento',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Repuestos, migración y recuperación de datos',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Ventana de mantenimiento y alcance por servidor',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  9 => 
  array (
    'cat' => 'mantenimiento-y-limpieza-de-equipos',
    'sku' => 'SRV-MNT-5',
    'nombre' => 'Limpieza y Desinfección de Periféricos y Setup',
    'marca' => 'Razer',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 25.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Retiro de keycaps de teclado, limpieza por ultrasonido, desinfección de mouse pads y pantallas.',
    'desc' => 'Tratamiento de estética y desinfección para tus herramientas diarias de trabajo o juego. Limpiamos a fondo el teclado mecánico desmontando teclas, lavamos mousepads con productos que preservan el deslizamiento y limpiamos monitores sin rayar el tratamiento antirreflejo.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 conjunto de periféricos definido en orden',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Limpieza superficial de teclado, mouse y superficies',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Métodos compatibles con materiales',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Periféricos limpios y comprobación básica',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Reparación ni garantía de esterilización',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Identificación de materiales y estado previo',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  10 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-1',
    'nombre' => 'Instalación y Configuración de Router Wi-Fi',
    'marca' => 'TP-Link',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 40.0,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Puesta en marcha de router de alta velocidad, separación de bandas 2.4G/5G y protección con WPA3.',
    'desc' => 'Configuración profesional de tu router doméstico o de oficina. Ajustamos los canales de radiofrecuencia menos congestionados de tu zona, activamos seguridad WPA3, configuramos red de invitados aislada y optimizamos las reglas de QoS para streaming y videojuegos.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Presencial o remoto según equipo',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 router',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Configuración WAN y red Wi-Fi',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'SSID, contraseña y pruebas de conectividad',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Configuración y credenciales al propietario',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Router, cableado y contratación de Internet',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Acceso autorizado al equipo y servicio activo',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  11 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-2',
    'nombre' => 'Cableado de Red Estructurado (Por punto)',
    'marca' => 'Ubiquiti',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 35.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Tirada de cable Cat6 100% cobre con conectorización RJ45 certificada y canalización estética.',
    'desc' => 'Instalación de punto de red ethernet cableado para oficinas o casas. Incluye cable de categoría 6 100% cobre certificado, jacks modulares hembra tipo keystone, faceplate de pared y certificación de continuidad.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Presencial',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 punto de red',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Tendido y terminación de cable Ethernet',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Prueba básica de continuidad y enlace',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Punto identificado',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Cable, canalización, obra civil y certificación con equipo especializado salvo cotización',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Definir metros, categoría y recorrido por punto',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
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
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 65.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Ubicación estratégica de nodos satélite para cobertura sin zonas muertas en casas de varios pisos.',
    'desc' => 'Despliegue de sistemas Wi-Fi en malla (Mesh). Realizamos mapa de calor previo para definir la ubicación de cada nodo, configuramos backhaul cableado o inalámbrico dedicado y probamos roaming transparente en todo el perímetro.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Presencial',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 sistema mesh, número de nodos por definir',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Instalación y ubicación de nodos',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Configuración de SSID y prueba de cobertura',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Red configurada',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Nodos, cableado y garantía de velocidad de Internet',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Plano o inspección y acceso al router principal',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  13 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-4',
    'nombre' => 'Configuración de Switch Administrable y VLANs',
    'marca' => 'D-Link',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 80.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Segmentación de tráfico por redes virtuales (VLAN), agregación de enlaces y prioridad de voz/cámaras.',
    'desc' => 'Servicio de ingeniería de red para empresas y locales comerciales. Configuramos switches L2/L3 con segmentación de VLAN para separar terminales de venta (POS), computadoras de oficina, cámaras IP de seguridad y Wi-Fi público de clientes.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Presencial o remota',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 switch',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Configuración de VLAN y puertos',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Acceso de gestión y comprobación de segmentación',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Respaldo de configuración y mapa de puertos',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Switch, licencias y cambios de red fuera del alcance',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Plan de VLAN y acceso administrativo autorizado',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  14 => 
  array (
    'cat' => 'instalacion-de-redes',
    'sku' => 'SRV-RED-5',
    'nombre' => 'Auditoría de Seguridad de Red Local y Firewall',
    'marca' => 'Zyxel',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 95.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Escaneo de puertos abiertos, detección de intrusos y endurecimiento de contraseñas de red.',
    'desc' => 'Evaluación de vulnerabilidades en la red corporativa. Escaneamos dispositivos conectados, verificamos puertos expuestos hacia Internet sin cifrar, cerramos accesos Telnet o HTTP y entregamos un informe con recomendaciones de blindaje de seguridad.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Evaluación autorizada',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 red local con alcance definido',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Revisión de configuración de red y firewall',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Inventario básico y recomendaciones',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Informe de hallazgos',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Pruebas intrusivas, certificación ni correcciones extensas salvo contrato',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Definir equipos, permisos y ventana de revisión',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  15 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-1',
    'nombre' => 'Recuperación de Disco Duro Dañado Nivel 1',
    'marca' => 'Seagate',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 90.0,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Extracción de archivos en discos mecánicos con sectores defectuosos o tablas de particiones corruptas.',
    'desc' => 'Rescate de documentos, fotos y carpetas en discos HDD con fallos lógicos o sectores dañados. Empleamos herramientas forenses de clonado bit a bit para no fatigar el disco y recomponer la estructura de archivos en un medio de almacenamiento seguro.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Diagnóstico en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 HDD',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Recuperación de nivel lógico o accesible sin apertura del disco',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Evaluación y copia de datos recuperables',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Listado de archivos recuperados',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Laboratorio especializado, apertura en sala limpia y medio destino',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'No seguir usando el disco y evaluación previa',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  16 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-2',
    'nombre' => 'Rescate de SSD NVMe con Fallo Lógico',
    'marca' => 'Western Digital',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 110.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Recuperación en unidades de estado sólido no reconocidas o formateadas accidentalmente.',
    'desc' => 'Diagnóstico y extracción de información en discos SSD M.2 y SATA con tabla de partición GPT perdida, formato involuntario o corrupción del sistema de archivos NTFS/APFS/EXT4.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 SSD NVMe',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Intento de recuperación ante fallo lógico',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Diagnóstico y extracción de datos recuperables',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Archivos recuperados al medio acordado',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Reparación electrónica, medio destino y recuperación de datos borrados por TRIM',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Acceso autorizado y diagnóstico del SSD',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  17 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-3',
    'nombre' => 'Recuperación de Memoria USB / Tarjeta SD',
    'marca' => 'SanDisk',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 45.0,
    'precio_oferta' => 39,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Rescate de fotos y videos de memorias que piden formato o tienen archivos ocultos por virus.',
    'desc' => 'Solución rápida para memorias flash extraíbles utilizadas en cámaras, celulares o llaves USB que muestran error de lectura o archivos con accesos directos maliciosos.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 memoria USB o tarjeta SD',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Diagnóstico e intento de recuperación lógica',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Copia de archivos recuperables',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Inventario de recuperación',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Reparación de chips, medio destino ni laboratorio',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Conservar dispositivo sin nuevas escrituras',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  18 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-4',
    'nombre' => 'Plan de Respaldo Preventivo de Información (500GB)',
    'marca' => 'Crucial',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 40.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Copia de seguridad estructurada de archivos vitales a disco externo o nube con verificación de hash.',
    'desc' => 'Copia de seguridad profesional sin perder carpetas personales, perfiles de correo Outlook, favoritos del navegador ni licencias instaladas. Verificamos la integridad de los datos mediante suma de comprobación.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Configuración de respaldo',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => 'Hasta 500 GB de datos',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Organización y copia preventiva',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Configuración de tarea y verificación de restauración de muestra',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Procedimiento de respaldo',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Disco, suscripción cloud y seguimiento continuo',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Medio destino y alcance de carpetas acordado',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  19 => 
  array (
    'cat' => 'recuperacion-de-datos',
    'sku' => 'SRV-REC-5',
    'nombre' => 'Clonación de Sistema Operativo a SSD',
    'marca' => 'Kingston',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 35.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Migra todo tu Windows o macOS con programas y claves a un nuevo SSD sin tener que reinstalar.',
    'desc' => 'Duplicación exacta de tu disco viejo a un nuevo SSD de mayor velocidad o capacidad. Mantiene intactos todos tus programas instalados, configuraciones, contraseñas y archivos sin necesidad de reinstalar desde cero.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 sistema a 1 SSD',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Clonación de disco con sistema operativo',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Prueba de arranque cuando el hardware lo permita',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'SSD con clonación verificada',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'SSD, licencia y recuperación de disco dañado',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Origen legible, capacidad suficiente y compatibilidad',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  20 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-1',
    'nombre' => 'Formateo e Instalación Limpia de Windows 11',
    'marca' => 'Microsoft',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 35.0,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Instalación oficial de Windows 11 con controladores certificados, optimización y utilitarios esenciales.',
    'desc' => 'Reinstalación desde cero de Windows 11 con medios oficiales de Microsoft. Incluye particionado seguro, instalación de controladores oficiales validados por el fabricante del equipo, utilitarios de lectura PDF, descompresores y optimización de arranque.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 equipo',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Instalación limpia de Windows 11',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Controladores y actualizaciones iniciales',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Sistema instalado y prueba básica',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Licencia, respaldo y recuperación',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Hardware compatible y autorización para borrar datos',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  21 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-2',
    'nombre' => 'Instalación Limpia y Configuración de macOS',
    'marca' => 'Apple',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 45.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Restauración oficial del sistema macOS compatible con tu Mac con vinculación segura de Apple ID.',
    'desc' => 'Restauración de fábrica para computadoras MacBook, iMac o Mac mini. Instalamos la última versión oficial compatible de macOS, desvinculamos cuentas anteriores de forma segura y dejamos el equipo listo para su nuevo usuario.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 Mac compatible',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Instalación limpia de macOS compatible',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Configuración inicial y actualizaciones',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Mac con sistema operativo operativo',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Respaldo, recuperación y eliminación de bloqueo de activación',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Acceso del propietario y autorización de borrado',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  22 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-3',
    'nombre' => 'Formateo con Respaldo de Datos (Hasta 100GB)',
    'marca' => 'Lenovo',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 50.0,
    'precio_oferta' => 45,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Salvamos tus fotos y documentos antes de formatear y los reubicamos en tu nuevo sistema limpio.',
    'desc' => 'Tranquilidad total: respaldamos tus carpetas de Documentos, Escritorio, Descargas y Fotos antes de borrar el disco. Reinstalamos el sistema operativo completamente limpio y regresamos tus archivos a sus ubicaciones originales.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Servicio en taller',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 equipo y hasta 100 GB',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Respaldo de carpetas acordadas e instalación limpia',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Restauración del respaldo y controladores',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Sistema instalado y archivos restaurados',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Licencias, recuperación de datos y más de 100 GB',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Inventario del respaldo y autorización de borrado',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  23 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-4',
    'nombre' => 'Instalación y Activación de Software de Oficina',
    'marca' => 'Microsoft',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 25.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Configuración de Word, Excel, PowerPoint y sincronización de correo empresarial en Outlook.',
    'desc' => 'Puesta a punto de programas de ofimática. Instalamos la suite oficial, sincronizamos tus cuentas corporativas de correo con protocolo IMAP o Exchange y configuramos firmas automáticas y plantillas.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Presencial o remoto',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 equipo',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Instalación y activación de suite de oficina',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Configuración inicial y prueba de apertura',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Software instalado con licencia del cliente',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Compra de licencia o cuenta',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Licencia válida y sistema compatible',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  24 => 
  array (
    'cat' => 'formateo-e-instalacion-de-software',
    'sku' => 'SRV-FMT-5',
    'nombre' => 'Desinfección Profunda de Virus y Spyware',
    'marca' => 'Kaspersky',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 40.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Eliminación de troyanos, mineros ocultos, secuestradores de navegador y limpieza de registro.',
    'desc' => 'Limpieza de amenazas informáticas sin formatear. Detectamos y eliminamos troyanos, cryptominers que consumen tu procesador al 100%, adware que abre publicidad no deseada y reparamos el registro de Windows afectado.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Presencial o remoto',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 equipo',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Análisis y eliminación de malware detectable',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Actualización y recomendaciones',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Informe de detecciones y resultado',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Recuperación de archivos cifrados, licencias ni reparación de hardware',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Acceso autorizado y evaluar reinstalación si es necesaria',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  25 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-1',
    'nombre' => 'Soporte Técnico Residencial (1 Hora)',
    'marca' => 'HP',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 45.0,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Visita de un técnico calificado a tu casa para resolver problemas de lentitud, impresoras o internet.',
    'desc' => 'Un especialista de PayMe Panamá visita tu residencia para atender fallas de conectividad, configuración de impresoras en red, lentitud en computadoras y asesoría técnica directa con atención personalizada.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'A domicilio',
          ),
          1 => 
          array (
            'clave' => 'Duración',
            'valor' => '1 hora',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Diagnóstico y asistencia informática residencial',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Soluciones básicas dentro del tiempo contratado',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Registro del trabajo y recomendaciones',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Repuestos y tiempo adicional',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Definir zona, traslado y disponibilidad',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  26 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-2',
    'nombre' => 'Visita Técnica Empresarial (Diagnóstico PyME)',
    'marca' => 'Dell',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 80.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Revisión en tu empresa de estaciones de trabajo, impresoras compartidas y puntos de venta.',
    'desc' => 'Diagnóstico profesional para oficinas y comercios de hasta 15 equipos. Evaluamos el estado operativo del parque informático, la velocidad de la red local, el estado de respaldos y elaboramos un plan de mejoras prioritarias.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Visita empresarial',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 visita de diagnóstico',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Inspección de equipos y necesidades de la PyME',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Diagnóstico inicial',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Informe y cotización de solución',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Repuestos, reparaciones y implementación',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Definir cantidad de equipos, duración y ubicación',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  27 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-3',
    'nombre' => 'Instalación y Conexión de Dispositivos Smart Home',
    'marca' => 'Google',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 50.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Vinculación de altavoces inteligentes, cerraduras, luces y cámaras a tu asistente de voz.',
    'desc' => 'Automatiza tu hogar sin complicaciones. Configuramos asistentes inteligentes, asociamos luces inteligentes, enchufes y programamos rutinas de encendido y apagado para tu comodidad.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'A domicilio',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => 'Dispositivos por definir',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Instalación y conexión de equipos de hogar inteligente',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Configuración en aplicaciones compatibles',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Prueba funcional y explicación de uso',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Dispositivos, instalación eléctrica y suscripciones',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Red Wi-Fi compatible y cuentas del propietario',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  28 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-4',
    'nombre' => 'Soporte Técnico Remoto Inmediato (45 Min)',
    'marca' => 'Lenovo',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 30.0,
    'precio_oferta' => 25,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Asistencia vía TeamViewer/AnyDesk en minutos para configurar software, correos o resolver fallas.',
    'desc' => 'Atención ágil sin esperas. Conexión cifrada directa a tu pantalla con tu autorización para corregir problemas de drivers, instalación de programas, errores de certificados o configuración de impresoras USB.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Remota',
          ),
          1 => 
          array (
            'clave' => 'Duración',
            'valor' => '45 minutos',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Diagnóstico y asistencia mediante sesión autorizada',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Configuraciones y soluciones dentro del tiempo contratado',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Registro de acciones',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Reparación física, licencias y tiempo adicional',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Internet estable y acceso remoto autorizado',
          ),
          7 => 
          array (
            'clave' => 'Disponibilidad',
            'valor' => 'Según agenda, no prometer atención inmediata sin horario definido',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  29 => 
  array (
    'cat' => 'soporte-tecnico-a-domicilio',
    'sku' => 'SRV-SPT-5',
    'nombre' => 'Asesoría Personalizada de Compra Tecnológica',
    'marca' => 'ASUS',
    'modelo' => 'Servicio propio de la PyME',
    'precio' => 35.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Te ayudamos a elegir la laptop, PC o componentes ideales para tu presupuesto sin malgastar dinero.',
    'desc' => 'Sesión de consultoría 1 a 1 de 45 minutos. Analizamos tus necesidades reales de trabajo, estudio o gaming, comparamos opciones de mercado y te entregamos una lista de compras con los mejores precios y compatibilidad garantizada.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Modalidad',
            'valor' => 'Asesoría presencial o remota',
          ),
          1 => 
          array (
            'clave' => 'Unidad',
            'valor' => '1 sesión, duración por definir',
          ),
          2 => 
          array (
            'clave' => 'Alcance propuesto',
            'valor' => 'Evaluación de necesidades, presupuesto y compatibilidad',
          ),
          3 => 
          array (
            'clave' => 'Incluye propuesto',
            'valor' => 'Comparación de opciones',
          ),
          4 => 
          array (
            'clave' => 'Entrega propuesta',
            'valor' => 'Recomendación o propuesta de compra',
          ),
          5 => 
          array (
            'clave' => 'No incluido',
            'valor' => 'Productos y descuentos de terceros',
          ),
          6 => 
          array (
            'clave' => 'Requisitos',
            'valor' => 'Definir uso y presupuesto',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del servicio',
      'duracion' => 'Definir y documentar por el negocio',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  30 => 
  array (
    'cat' => 'software-y-licencias',
    'sku' => 'SRV-SFT-1',
    'nombre' => 'Microsoft 365 Personal (Licencia Digital)',
    'marca' => 'Microsoft',
    'modelo' => 'Microsoft 365 Personal, suscripción de referencia de 1 año',
    'precio' => 69.99,
    'precio_oferta' => NULL,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Suscripción oficial con Word, Excel, PowerPoint y 1TB de almacenamiento en la nube de OneDrive.',
    'desc' => 'Licencia genuina de Microsoft 365 para un usuario. Utilizable en hasta 5 dispositivos simultáneamente (PC, Mac, iPhone, Android). Incluye las versiones premium de las apps de Office siempre actualizadas y 1.000 GB de almacenamiento seguro en OneDrive.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Suscripción digital',
          ),
          1 => 
          array (
            'clave' => 'Usuarios',
            'valor' => '1',
          ),
          2 => 
          array (
            'clave' => 'Dispositivos',
            'valor' => 'Inicio de sesión en hasta 5 simultáneamente',
          ),
          3 => 
          array (
            'clave' => 'Nube',
            'valor' => '1 TB OneDrive',
          ),
          4 => 
          array (
            'clave' => 'Aplicaciones',
            'valor' => 'Word, Excel, PowerPoint y otras según plataforma',
          ),
          5 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'PC, Mac, teléfonos y tabletas',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Productividad y seguridad según plan vigente',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de activación según contrato',
      'duracion' => 'Confirmar condiciones de la licencia y del proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
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
    'modelo' => 'Windows 11 Pro OEM, 1 equipo',
    'precio' => 149.0,
    'precio_oferta' => 129,
    'destacado' => true,
    'stock' => 999,
    'desc_corta' => 'Licencia vitalicia para 1 PC con BitLocker, Escritorio Remoto y funciones avanzadas de seguridad.',
    'desc' => 'Clave original de producto para Windows 11 Profesional. Se vincula permanentemente a la placa madre de tu equipo. Habilita cifrado de disco BitLocker, virtualización Hyper-V y conexión por Escritorio Remoto.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Licencia OEM, no Retail',
          ),
          1 => 
          array (
            'clave' => 'Edición',
            'valor' => 'Windows 11 Pro',
          ),
          2 => 
          array (
            'clave' => 'Equipos',
            'valor' => '1 equipo conforme a términos OEM',
          ),
          3 => 
          array (
            'clave' => 'Entrega',
            'valor' => 'Clave/licencia digital según canal autorizado',
          ),
          4 => 
          array (
            'clave' => 'Activación',
            'valor' => 'Conexión a Internet y hardware compatible',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Funciones Pro como BitLocker, unión a dominio y host de Escritorio remoto',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de activación según contrato',
      'duracion' => 'Confirmar condiciones de la licencia y del proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  32 => 
  array (
    'cat' => 'software-y-licencias',
    'sku' => 'SRV-SFT-3',
    'nombre' => 'Adobe Creative Cloud Todas las Aplicaciones',
    'marca' => 'Adobe',
    'modelo' => 'Creative Cloud, plan individual completo: contrato por confirmar',
    'precio' => 299.0,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Acceso completo a Photoshop, Illustrator, Premiere Pro, After Effects y 100GB en la nube.',
    'desc' => 'Plan completo con más de 20 herramientas de creación digital de Adobe. Incluye Photoshop, Illustrator, InDesign, Premiere Pro, After Effects, Lightroom, tipografías de Adobe Fonts y 100GB de almacenamiento en la nube.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Suscripción digital',
          ),
          1 => 
          array (
            'clave' => 'Usuario',
            'valor' => 'Individual de referencia',
          ),
          2 => 
          array (
            'clave' => 'Aplicaciones',
            'valor' => 'Suite Creative Cloud según plan',
          ),
          3 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'Windows/macOS, requisitos distintos por aplicación',
          ),
          4 => 
          array (
            'clave' => 'Activación',
            'valor' => 'Cuenta Adobe y conexión a Internet',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Herramientas de diseño, fotografía, video y documentos',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de activación según contrato',
      'duracion' => 'Confirmar condiciones de la licencia y del proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
    'variantes' => NULL,
  ),
  33 => 
  array (
    'cat' => 'software-y-licencias',
    'sku' => 'SRV-SFT-4',
    'nombre' => 'Kaspersky Total Security Antivirus',
    'marca' => 'Kaspersky',
    'modelo' => 'Kaspersky Total Security, licencia existente por confirmar',
    'precio' => 39.99,
    'precio_oferta' => 34.99,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Máxima protección contra ransomware, pagos bancarios seguros y VPN de navegación privada.',
    'desc' => 'Solución integral de ciberseguridad para tu equipo personal o laboral. Protege tus transacciones bancarias en un navegador aislado, bloquea páginas de phishing, administra tus contraseñas y frena ataques de ransomware en tiempo real.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Licencia de seguridad digital',
          ),
          1 => 
          array (
            'clave' => 'Producto',
            'valor' => 'Kaspersky Total Security',
          ),
          2 => 
          array (
            'clave' => 'Funciones de la edición',
            'valor' => 'Antimalware y herramientas de seguridad según versión',
          ),
          3 => 
          array (
            'clave' => 'Activación',
            'valor' => 'Cuenta y clave válida para la región',
          ),
          4 => 
          array (
            'clave' => 'Dispositivos',
            'valor' => 'No indicados en la lista',
          ),
          5 => 
          array (
            'clave' => 'Duración',
            'valor' => 'No indicada en la lista',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de activación según contrato',
      'duracion' => 'Confirmar condiciones de la licencia y del proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
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
    'modelo' => 'Norton 360 Deluxe, 5 dispositivos, referencia de 1 año',
    'precio' => 49.99,
    'precio_oferta' => NULL,
    'destacado' => false,
    'stock' => 999,
    'desc_corta' => 'Seguridad completa para hasta 5 dispositivos con VPN segura, 50GB en nube y control parental.',
    'desc' => 'Protección multicapa para toda tu familia. Defiende contra virus y malware en 5 equipos simultáneos, incluye VPN ilimitada para conectarte con seguridad en redes Wi-Fi públicas y monitoreo de la Dark Web para alertarte de filtraciones de datos.',
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Detalles del Servicio',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Suscripción digital',
          ),
          1 => 
          array (
            'clave' => 'Dispositivos',
            'valor' => 'Hasta 5',
          ),
          2 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'PC, Mac, teléfonos y tabletas compatibles',
          ),
          3 => 
          array (
            'clave' => 'Funciones',
            'valor' => 'Antimalware, VPN y herramientas de privacidad',
          ),
          4 => 
          array (
            'clave' => 'Respaldo',
            'valor' => '50 GB de copia de seguridad en la nube para PC en plan consultado',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Control parental y monitorización según región',
          ),
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía de activación según contrato',
      'duracion' => 'Confirmar condiciones de la licencia y del proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
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
    'modelo' => 'MacBook Air M2 (2022), GPU de 8 núcleos, 8 GB/256 GB',
    'precio' => 1099.0,
    'precio_oferta' => 999,
    'stock' => 13,
    'destacado' => true,
    'desc_corta' => 'Chip M2 ultraeficiente, pantalla Liquid Retina de 13.6 pulgadas y hasta 18 horas de batería.',
    'desc' => 'La laptop más delgada y portátil de Apple. Diseñada en aluminio unibody ultraligero con chip M2 de 8 núcleos de CPU y 8 núcleos de GPU. Su pantalla Liquid Retina ofrece 500 nits de brillo y compatibilidad con 1.000 millones de colores.',
    'peso' => 1.24,
    'largo' => 21.5,
    'ancho' => 30.41,
    'alto' => 1.13,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Apple M2, CPU de 8 núcleos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'GPU integrada de 8 núcleos',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '8 GB unificada',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD de 256 GB',
          ),
          4 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '13.6 pulgadas Liquid Retina IPS, 2560 × 1664',
          ),
          5 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6, Bluetooth 5.3',
          ),
          6 => 
          array (
            'clave' => 'Puertos',
            'valor' => '2 Thunderbolt/USB 4, MagSafe 3, audio de 3.5 mm',
          ),
          7 => 
          array (
            'clave' => 'Batería',
            'valor' => '52.6 Wh, hasta 18 horas de video',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Diseño sin ventilador, Touch ID y cámara FaceTime HD de 1080p',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'LAP-2-1-V1',
        'precio' => 1099,
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
        'precio' => 1399,
        'stock' => 5,
        'opcion' => 
        array (
          0 => 'Memoria RAM',
          1 => '32 GB',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  1 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-2',
    'nombre' => 'Dell XPS 13 Plus 9320',
    'marca' => 'Dell',
    'modelo' => 'XPS 13 Plus 9320, nxps139320_s3e, i7-1260P/16 GB/512 GB/OLED',
    'precio' => 1299.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => true,
    'desc_corta' => 'Diseño minimalista con barra táctil capacitiva, pantalla InfinityEdge FHD+ e Intel Core i7.',
    'desc' => 'Portátil de alta gama empresarial construido en aluminio mecanizado CNC. Integra touchpad háptico de vidrio sin bordes visibles, procesador Intel Core i7 de 13.ª generación y teclado de borde a borde.',
    'peso' => 1.26,
    'largo' => 19.904,
    'ancho' => 29.53,
    'alto' => 1.528,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i7-1260P, 12 núcleos y 16 hilos',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB LPDDR5-5200 integrada',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe de 512 GB',
          ),
          3 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel Iris Xe integrados',
          ),
          4 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '13.4 pulgadas OLED táctil 3456 × 2160, 60 Hz',
          ),
          5 => 
          array (
            'clave' => 'Puertos',
            'valor' => '2 Thunderbolt 4 USB-C',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '55 Wh',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Teclado de borde a borde y panel táctil háptico',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  2 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-3',
    'nombre' => 'Lenovo ThinkPad X1 Carbon Gen 11',
    'marca' => 'Lenovo',
    'modelo' => 'ThinkPad X1 Carbon Gen 11, 21HM00AKVA',
    'precio' => 1499.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Chasis de fibra de carbono probado bajo estándares militares con teclado legendario ThinkPad.',
    'desc' => 'El estándar de oro para ejecutivos y programadores. Certificado bajo 12 pruebas de grado militar MIL-STD-810H. Cuenta con lector de huellas biométrico, obturador de privacidad en cámara y procesador Intel Core i7.',
    'peso' => 1.12,
    'largo' => 22.25,
    'ancho' => 31.56,
    'alto' => 1.536,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i7-1355U, 10 núcleos y 12 hilos',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB LPDDR5/x-6400 soldada',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD de 512 GB PCIe 4.0 NVMe Opal 2.0',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '14 pulgadas IPS, 1920 × 1200, 400 nits, sin función táctil',
          ),
          4 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel Iris Xe',
          ),
          5 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6E AX211 y Bluetooth 5.3',
          ),
          6 => 
          array (
            'clave' => 'Puertos',
            'valor' => '2 Thunderbolt 4, 2 USB-A, HDMI 2.1 y audio',
          ),
          7 => 
          array (
            'clave' => 'Batería',
            'valor' => '57 Wh',
          ),
          8 => 
          array (
            'clave' => 'Sistema operativo',
            'valor' => 'Sin sistema operativo de fábrica',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  3 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-4',
    'nombre' => 'HP Spectre x360 14 2-in-1',
    'marca' => 'HP',
    'modelo' => 'Spectre x360 14-eu0013dx, 979L2UA',
    'precio' => 1199.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Convertible premium con bisagra de 360°, pantalla táctil OLED 3K2K y lápiz digital recargable.',
    'desc' => 'Laptop y tablet en un solo equipo de corte biselado de lujo. Su pantalla OLED ofrece negros perfectos con certificación VESA DisplayHDR True Black 400. Incluye cámara web inteligente de 5MP.',
    'peso' => 1.45,
    'largo' => 22.04,
    'ancho' => 31.37,
    'alto' => 1.7,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core Ultra 7 155H, 16 núcleos y 22 hilos',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB LPDDR5x-7467 soldada',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe PCIe 4.0 de 1 TB',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '14 pulgadas OLED táctil 3K',
          ),
          4 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel Arc integrados',
          ),
          5 => 
          array (
            'clave' => 'Puertos',
            'valor' => '2 Thunderbolt 4, USB-A de 10 Gb/s y audio',
          ),
          6 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6E AX211 y Bluetooth 5.3',
          ),
          7 => 
          array (
            'clave' => 'Batería',
            'valor' => '68 Wh',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Bisagra convertible, cámara IR de 9 MP, panel táctil háptico',
          ),
          9 => 
          array (
            'clave' => 'Sistema operativo',
            'valor' => 'Windows 11 Home',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  4 => 
  array (
    'cat' => 'laptops',
    'sku' => 'LAP-2-5',
    'nombre' => 'Acer Swift Go 14 OLED',
    'marca' => 'Acer',
    'modelo' => 'Swift Go 14 SFG14-71 OLED, NX.KMZEB.008',
    'precio' => 799.0,
    'precio_oferta' => 749,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Increíble pantalla OLED 2.8K de 90Hz, procesador Intel de 13.ª gen y peso pluma de 1.25 kg.',
    'desc' => 'Rendimiento y pantalla asombrosa al mejor precio. Cuenta con panel OLED calibrado al 100% DCI-P3, doble ventilador TwinAir para bajas temperaturas y conectividad Wi-Fi 6E.',
    'peso' => 1.25,
    'largo' => 21.79,
    'ancho' => 31.29,
    'alto' => 1.49,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i5-1335U, 10 núcleos, turbo hasta 4.6 GHz',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB LPDDR5',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe de 512 GB',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '14 pulgadas OLED, 2880 × 1800, relación 16:10',
          ),
          4 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel Iris Xe',
          ),
          5 => 
          array (
            'clave' => 'Lector de tarjetas',
            'valor' => 'microSD',
          ),
          6 => 
          array (
            'clave' => 'Sistema operativo',
            'valor' => 'Windows 11 Home',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Equipo ultraportátil con panel OLED no táctil',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  5 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-1',
    'nombre' => 'ASUS ROG Zephyrus G14 Gaming',
    'marca' => 'ASUS',
    'modelo' => 'ROG Zephyrus G14 (2024), GA403UV-G14.R94060',
    'precio' => 1599.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => true,
    'desc_corta' => 'Potencia pura en 14 pulgadas: AMD Ryzen 9, GeForce RTX 4080 y pantalla Nebula Display 165Hz.',
    'desc' => 'La laptop gaming compacta más premiada del mundo. Equipada con cámara de vapor, metal líquido sobre CPU y GPU, pantalla ROG Nebula QHD+ de 165Hz con tiempo de respuesta de 3ms y switch MUX.',
    'peso' => 1.5,
    'largo' => 22.0,
    'ancho' => 31.1,
    'alto' => 1.63,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'AMD Ryzen 9 8945HS, 8 núcleos y 16 hilos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'NVIDIA GeForce RTX 4060 Laptop, 8 GB GDDR6, hasta 90 W',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB LPDDR5X integrada',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe PCIe 4.0 de 1 TB',
          ),
          4 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '14 pulgadas OLED 2880 × 1800, 120 Hz',
          ),
          5 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6E y Bluetooth 5.3',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '73 Wh',
          ),
          7 => 
          array (
            'clave' => 'Sistema operativo',
            'valor' => 'Windows 11 Home',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Chasis de aluminio y pantalla ROG Nebula OLED',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'LAP-3-1-V1',
        'precio' => 1599,
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
        'precio' => 1629,
        'stock' => 4,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  6 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-2',
    'nombre' => 'Acer Predator Helios 16',
    'marca' => 'Acer',
    'modelo' => 'Predator Helios 16 PH16-71-74JC, NH.QJREX.004',
    'precio' => 1399.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Refrigeración con ventiladores metálicos AeroBlade 3D y tarjeta gráfica RTX 4070 a 140W.',
    'desc' => 'Máquina de combate para eSports. Su pantalla de 16 pulgadas WQXGA a 240Hz elimina el desenfoque en shooters competitivos. Cuenta con teclado retroiluminado RGB por tecla.',
    'peso' => 2.6,
    'largo' => 27.874,
    'ancho' => 35.778,
    'alto' => 2.69,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i7-13700HX, 16 núcleos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'GeForce RTX 4070 Laptop, 8 GB GDDR6, hasta 140 W',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '32 GB DDR5',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe de 1 TB',
          ),
          4 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '16 pulgadas IPS 2560 × 1600, 240 Hz',
          ),
          5 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6 y Ethernet Gigabit',
          ),
          6 => 
          array (
            'clave' => 'Sistema operativo',
            'valor' => 'EShell',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Teclado RGB y formato de pantalla 16:10',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  7 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-3',
    'nombre' => 'MSI Katana 15 B13V',
    'marca' => 'MSI',
    'modelo' => 'Katana 15 B13VFK-1057CL',
    'precio' => 1099.0,
    'precio_oferta' => 999,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Excelente relación precio-rendimiento con Intel Core i7 de 13.ª gen y gráficos RTX 4060.',
    'desc' => 'Inspirada en el filo de una espada samurái. Integra sistema Cooler Boost 5 con 2 ventiladores y 6 heatpipes de cobre compartidos para mantener temperaturas bajo control.',
    'peso' => 2.25,
    'largo' => 25.9,
    'ancho' => 35.9,
    'alto' => 2.49,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i7-13620H, 10 núcleos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'RTX 4060 Laptop de 8 GB GDDR6, hasta 105 W',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB (2 × 8 GB) DDR5',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe PCIe 4.0 de 512 GB',
          ),
          4 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '15.6 pulgadas IPS-Level FHD, 144 Hz',
          ),
          5 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6E AX211, Bluetooth 5.3 y Ethernet Gigabit',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '53.5 Wh',
          ),
          7 => 
          array (
            'clave' => 'Adaptador',
            'valor' => '200 W',
          ),
          8 => 
          array (
            'clave' => 'Sistema operativo',
            'valor' => 'Windows 11 Home',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'Teclado RGB de 4 zonas y dos ranuras M.2',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  8 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-4',
    'nombre' => 'Lenovo Legion Pro 5 Gen 8',
    'marca' => 'Lenovo',
    'modelo' => 'Legion Pro 5 16ARX8, 82WM005PRK',
    'precio' => 1449.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Potenciado por el chip de IA Lenovo LA1 y pantalla PureSight Gaming de 16 pulgadas 240Hz.',
    'desc' => 'Construida para rendir sin piedad. El chip de Inteligencia Artificial Legion Coldfront 5.0 ajusta los voltajes y ventilación en tiempo real para exprimir hasta el último FPS.',
    'peso' => 2.5,
    'largo' => 26.035,
    'ancho' => 36.34,
    'alto' => 2.675,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'AMD Ryzen 7 7745HX, 8 núcleos y 16 hilos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'RTX 4060 Laptop de 8 GB GDDR6',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '32 GB, 2 × 16 GB DDR5-5200',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe PCIe 4.0 de 1 TB',
          ),
          4 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '16 pulgadas IPS 2560 × 1600; frecuencia del panel por confirmar en esta variante',
          ),
          5 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6E, Bluetooth y Ethernet',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '80 Wh',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Refrigeración Legion ColdFront y teclado retroiluminado',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  9 => 
  array (
    'cat' => 'laptops-gamer',
    'sku' => 'LAP-3-5',
    'nombre' => 'HP Omen 16 Gaming Laptop',
    'marca' => 'HP',
    'modelo' => 'OMEN Gaming Laptop 16-wf0083dx, A7QR7UA',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Laptop gamer de la serie OMEN 16-wf0000',
          ),
          1 => 
          array (
            'clave' => 'Código del fabricante',
            'valor' => 'A7QR7UA',
          ),
          2 => 
          array (
            'clave' => 'Pantalla nominal',
            'valor' => '16.1 pulgadas',
          ),
          3 => 
          array (
            'clave' => 'Configuración',
            'valor' => 'Ficha de hardware exacta no recuperada de la fuente oficial',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'No publicar CPU, RAM, GPU o SSD hasta confirmar la ficha de A7QR7UA',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  10 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-1',
    'nombre' => 'Apple Mac mini M2',
    'marca' => 'Apple',
    'modelo' => 'Mac mini M2 (2023), 8 GB/256 GB',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Apple M2, CPU de 8 núcleos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'GPU de 10 núcleos',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '8 GB unificada',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD de 256 GB',
          ),
          4 => 
          array (
            'clave' => 'Puertos',
            'valor' => '2 Thunderbolt 4, 2 USB-A, HDMI, Ethernet Gigabit y audio de 3.5 mm',
          ),
          5 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6E y Bluetooth 5.3',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Equipo compacto',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'DESK-4-1-V1',
        'precio' => 599,
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
        'precio' => 799,
        'stock' => 7,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '512 GB',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  11 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-2',
    'nombre' => 'Dell Inspiron 3020 Desktop',
    'marca' => 'Dell',
    'modelo' => 'Inspiron 3020 Desktop, cd302014',
    'precio' => 649.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'PC de torre para oficina y hogar con procesador Intel Core i7 de 13.ª generación.',
    'desc' => 'Confiabilidad probada para productividad multitarea continua. Incluye teclado y mouse óptico Dell originales, unidad SSD PCIe rápida y múltiples puertos frontales.',
    'peso' => 7.3,
    'largo' => 29.28,
    'ancho' => 15.4,
    'alto' => 32.43,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i5-13400, 10 núcleos y 16 hilos',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB DDR4-3200',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe de 1 TB',
          ),
          3 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel UHD 730',
          ),
          4 => 
          array (
            'clave' => 'Sistema operativo',
            'valor' => 'Windows 11 Home',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Torre de escritorio para productividad',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  12 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-3',
    'nombre' => 'HP Envy Desktop TE02',
    'marca' => 'HP',
    'modelo' => 'ENVY TE02-0240nd, 670L7EA',
    'precio' => 899.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Chasis con acceso sin herramientas, refrigeración optimizada y expansión para tarjetas gráficas.',
    'desc' => 'Computadora de alto rendimiento para creadores de contenido, edición de fotos y video. Fácilmente ampliable sin tornillos en bahías de discos y slots de memoria RAM.',
    'peso' => 13.75,
    'largo' => 39.61,
    'ancho' => 16.5,
    'alto' => 44.8,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i9-12900, 16 núcleos y 24 hilos',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '32 GB DDR4-3200, 2 × 16 GB',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '2 SSD NVMe de 1 TB cada uno',
          ),
          3 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'GeForce RTX 3060 Ti de 8 GB',
          ),
          4 => 
          array (
            'clave' => 'Red',
            'valor' => 'Ethernet Gigabit, Wi-Fi 6 y Bluetooth 5.2',
          ),
          5 => 
          array (
            'clave' => 'Puertos',
            'valor' => 'Thunderbolt 4, USB-C, USB-A y RJ45',
          ),
          6 => 
          array (
            'clave' => 'Fuente de poder',
            'valor' => '500 W, 80 PLUS Bronze',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Torre con lector de tarjetas y almacenamiento dual',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  13 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-4',
    'nombre' => 'Lenovo IdeaCentre 3 07IAB7',
    'marca' => 'Lenovo',
    'modelo' => 'IdeaCentre 3 07IAB7, referencia Core i3-12100/8 GB/256 GB',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i3-12100, 4 núcleos y 8 hilos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel UHD 730',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '8 GB DDR4',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe de 256 GB',
          ),
          4 => 
          array (
            'clave' => 'Formato',
            'valor' => 'Small Form Factor, 7.4 litros',
          ),
          5 => 
          array (
            'clave' => 'Expansión',
            'valor' => 'Ranuras M.2 para SSD y WLAN',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Equipo compacto para oficina',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  14 => 
  array (
    'cat' => 'computadoras-de-escritorio',
    'sku' => 'DESK-4-5',
    'nombre' => 'ASUS ExpertCenter D5 SFF',
    'marca' => 'ASUS',
    'modelo' => 'ExpertCenter D5 SFF D500SD, referencia i5-12400/8 GB/256 GB',
    'precio' => 549.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Formato compacto Small Form Factor (SFF) con grado de durabilidad militar para empresas.',
    'desc' => 'Diseñado para durar en entornos de oficina y puntos de venta con uso continuo las 24 horas del día. Placa madre de grado industrial con condensadores sólidos japoneses al 100%.',
    'peso' => 5.0,
    'largo' => 29.27,
    'ancho' => 9.46,
    'alto' => 33.96,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Intel Core i5-12400, 6 núcleos y 12 hilos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel UHD 730',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '8 GB DDR4',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe de 256 GB',
          ),
          4 => 
          array (
            'clave' => 'Formato',
            'valor' => 'Small Form Factor',
          ),
          5 => 
          array (
            'clave' => 'Seguridad',
            'valor' => 'TPM 2.0 y ranura Kensington',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Escritorio compacto para entornos empresariales',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  15 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-1',
    'nombre' => 'Intel Core i9-14900K 24 Núcleos',
    'marca' => 'Intel',
    'modelo' => 'Core i9-14900K, BX8071514900K',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Socket',
            'valor' => 'LGA1700',
          ),
          1 => 
          array (
            'clave' => 'Arquitectura',
            'valor' => 'Intel Core de 14.ª generación',
          ),
          2 => 
          array (
            'clave' => 'Núcleos e hilos',
            'valor' => '24 núcleos (8 P + 16 E), 32 hilos',
          ),
          3 => 
          array (
            'clave' => 'Frecuencia turbo',
            'valor' => 'Hasta 6.0 GHz',
          ),
          4 => 
          array (
            'clave' => 'Caché L3',
            'valor' => '36 MB',
          ),
          5 => 
          array (
            'clave' => 'Potencia base/turbo',
            'valor' => '125 W/253 W',
          ),
          6 => 
          array (
            'clave' => 'Memoria compatible',
            'valor' => 'DDR5-5600 o DDR4-3200',
          ),
          7 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel UHD 770',
          ),
          8 => 
          array (
            'clave' => 'Disipador',
            'valor' => 'No incluido',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'Multiplicador desbloqueado',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  16 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-2',
    'nombre' => 'AMD Ryzen 9 7950X3D 16 Núcleos',
    'marca' => 'AMD',
    'modelo' => 'Ryzen 9 7950X3D, 100-100000908WOF',
    'precio' => 589.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => true,
    'desc_corta' => 'Tecnología 3D V-Cache masiva con 144MB de caché total: el procesador definitivo para videojuegos.',
    'desc' => 'Arquitectura Zen 4 en 5nm con tecnología AMD 3D V-Cache apilada en vertical. Ofrece un rendimiento imbatible en tasas de cuadros por segundo en títulos AAA y simulación pesada.',
    'peso' => 0.12,
    'largo' => 4,
    'ancho' => 4,
    'alto' => 0.6,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Socket',
            'valor' => 'AM5',
          ),
          1 => 
          array (
            'clave' => 'Arquitectura',
            'valor' => 'Zen 4',
          ),
          2 => 
          array (
            'clave' => 'Núcleos e hilos',
            'valor' => '16 núcleos y 32 hilos',
          ),
          3 => 
          array (
            'clave' => 'Frecuencia base/turbo',
            'valor' => '4.2 GHz/hasta 5.7 GHz',
          ),
          4 => 
          array (
            'clave' => 'Caché L3',
            'valor' => '128 MB',
          ),
          5 => 
          array (
            'clave' => 'TDP',
            'valor' => '120 W',
          ),
          6 => 
          array (
            'clave' => 'Memoria compatible',
            'valor' => 'DDR5',
          ),
          7 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'AMD Radeon integrados',
          ),
          8 => 
          array (
            'clave' => 'Disipador',
            'valor' => 'No incluido',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'AMD 3D V-Cache para cargas de juego y creación',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  17 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-3',
    'nombre' => 'Intel Core i7-14700K 20 Núcleos',
    'marca' => 'Intel',
    'modelo' => 'Core i7-14700K, BX8071514700K',
    'precio' => 399.0,
    'precio_oferta' => 379,
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Socket',
            'valor' => 'LGA1700',
          ),
          1 => 
          array (
            'clave' => 'Núcleos e hilos',
            'valor' => '20 núcleos (8 P + 12 E), 28 hilos',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia turbo',
            'valor' => 'Hasta 5.6 GHz',
          ),
          3 => 
          array (
            'clave' => 'Caché L3',
            'valor' => '33 MB',
          ),
          4 => 
          array (
            'clave' => 'Potencia base/turbo',
            'valor' => '125 W/253 W',
          ),
          5 => 
          array (
            'clave' => 'Memoria compatible',
            'valor' => 'DDR5-5600 o DDR4-3200',
          ),
          6 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel UHD 770',
          ),
          7 => 
          array (
            'clave' => 'Disipador',
            'valor' => 'No incluido',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Multiplicador desbloqueado',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  18 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-4',
    'nombre' => 'AMD Ryzen 7 7800X3D 8 Núcleos',
    'marca' => 'AMD',
    'modelo' => 'Ryzen 7 7800X3D, 100-100000910WOF',
    'precio' => 449.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'El rey indiscutible del gaming puro: 8 núcleos Zen 4 con 96MB de caché L3 y bajo consumo.',
    'desc' => 'La CPU más recomendada por analistas y jugadores de eSports. Su diseño de un solo chiplet CCD elimina latencias entre núcleos y exprime el potencial de cualquier tarjeta gráfica de alta gama.',
    'peso' => 0.11,
    'largo' => 4,
    'ancho' => 4,
    'alto' => 0.6,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Socket',
            'valor' => 'AM5',
          ),
          1 => 
          array (
            'clave' => 'Arquitectura',
            'valor' => 'Zen 4',
          ),
          2 => 
          array (
            'clave' => 'Núcleos e hilos',
            'valor' => '8 núcleos y 16 hilos',
          ),
          3 => 
          array (
            'clave' => 'Frecuencia base/turbo',
            'valor' => '4.2 GHz/hasta 5.0 GHz',
          ),
          4 => 
          array (
            'clave' => 'Caché L3',
            'valor' => '96 MB',
          ),
          5 => 
          array (
            'clave' => 'TDP',
            'valor' => '120 W',
          ),
          6 => 
          array (
            'clave' => 'Memoria compatible',
            'valor' => 'DDR5',
          ),
          7 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'AMD Radeon integrados',
          ),
          8 => 
          array (
            'clave' => 'Disipador',
            'valor' => 'No incluido',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'AMD 3D V-Cache',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  19 => 
  array (
    'cat' => 'procesadores',
    'sku' => 'CPU-6-5',
    'nombre' => 'Intel Core i5-14600K 14 Núcleos',
    'marca' => 'Intel',
    'modelo' => 'Core i5-14600K, BX8071514600K',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Socket',
            'valor' => 'LGA1700',
          ),
          1 => 
          array (
            'clave' => 'Núcleos e hilos',
            'valor' => '14 núcleos (6 P + 8 E), 20 hilos',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia turbo',
            'valor' => 'Hasta 5.3 GHz',
          ),
          3 => 
          array (
            'clave' => 'Caché L3',
            'valor' => '24 MB',
          ),
          4 => 
          array (
            'clave' => 'Potencia base/turbo',
            'valor' => '125 W/181 W',
          ),
          5 => 
          array (
            'clave' => 'Memoria compatible',
            'valor' => 'DDR5-5600 o DDR4-3200',
          ),
          6 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'Intel UHD 770',
          ),
          7 => 
          array (
            'clave' => 'Disipador',
            'valor' => 'No incluido',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Multiplicador desbloqueado',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  20 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-1',
    'nombre' => 'ASUS ROG Strix GeForce RTX 4090 24GB',
    'marca' => 'ASUS',
    'modelo' => 'ROG-STRIX-RTX4090-O24G-GAMING',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'GPU',
            'valor' => 'NVIDIA GeForce RTX 4090',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '24 GB GDDR6X, bus de 384 bits',
          ),
          2 => 
          array (
            'clave' => 'Núcleos CUDA',
            'valor' => '16384',
          ),
          3 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0',
          ),
          4 => 
          array (
            'clave' => 'Salidas',
            'valor' => '2 HDMI 2.1a y 3 DisplayPort 1.4a',
          ),
          5 => 
          array (
            'clave' => 'Alimentación',
            'valor' => '1 conector de 16 pines',
          ),
          6 => 
          array (
            'clave' => 'Fuente recomendada',
            'valor' => '1000 W',
          ),
          7 => 
          array (
            'clave' => 'Ocupación',
            'valor' => '3.5 ranuras',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Tres ventiladores Axial-tech y modo OC',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  21 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-2',
    'nombre' => 'Gigabyte GeForce RTX 4080 Super Gaming OC',
    'marca' => 'Gigabyte',
    'modelo' => 'GV-N408SGAMING OC-16GD',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'GPU',
            'valor' => 'GeForce RTX 4080 SUPER',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB GDDR6X, bus de 256 bits',
          ),
          2 => 
          array (
            'clave' => 'Núcleos CUDA',
            'valor' => '10240',
          ),
          3 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '2595 MHz',
          ),
          4 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0',
          ),
          5 => 
          array (
            'clave' => 'Salidas',
            'valor' => '3 DisplayPort 1.4a y 1 HDMI 2.1a',
          ),
          6 => 
          array (
            'clave' => 'Fuente recomendada',
            'valor' => '850 W',
          ),
          7 => 
          array (
            'clave' => 'Alimentación',
            'valor' => '1 conector de 16 pines',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Refrigeración WINDFORCE de tres ventiladores',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  22 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-3',
    'nombre' => 'MSI GeForce RTX 4070 Ti Super Ventus 3X',
    'marca' => 'MSI',
    'modelo' => 'GeForce RTX 4070 Ti SUPER 16G VENTUS 3X OC',
    'precio' => 829.0,
    'precio_oferta' => 799,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Actualizada a 16GB VRAM y bus de 256 bits para dominar resolución 1440p y 4K con soltura.',
    'desc' => 'Diseño sobrio en tonos plata y negro sin luces distractivas. Ventiladores TORX Fan 4.0 con aspas unidas por pares para concentrar la presión estática hacia el radiador.',
    'peso' => 1.107,
    'largo' => 30.8,
    'ancho' => 12.0,
    'alto' => 5.2,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'GPU',
            'valor' => 'GeForce RTX 4070 Ti SUPER',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB GDDR6X, bus de 256 bits',
          ),
          2 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0',
          ),
          3 => 
          array (
            'clave' => 'Salidas',
            'valor' => '3 DisplayPort 1.4a y 1 HDMI 2.1a',
          ),
          4 => 
          array (
            'clave' => 'Consumo',
            'valor' => '285 W',
          ),
          5 => 
          array (
            'clave' => 'Fuente recomendada',
            'valor' => '700 W',
          ),
          6 => 
          array (
            'clave' => 'Alimentación',
            'valor' => '1 conector de 16 pines',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Triple ventilador y refrigeración TORX Fan 4.0',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  23 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-4',
    'nombre' => 'AMD Radeon RX 7900 XTX 24GB',
    'marca' => 'AMD',
    'modelo' => 'Radeon RX 7900 XTX, diseño de referencia AMD',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Arquitectura',
            'valor' => 'RDNA 3',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '24 GB GDDR6, bus de 384 bits',
          ),
          2 => 
          array (
            'clave' => 'Procesadores de flujo',
            'valor' => '6144',
          ),
          3 => 
          array (
            'clave' => 'Frecuencia de juego/boost',
            'valor' => '2300 MHz/hasta 2500 MHz',
          ),
          4 => 
          array (
            'clave' => 'Potencia típica',
            'valor' => '355 W',
          ),
          5 => 
          array (
            'clave' => 'Fuente recomendada',
            'valor' => '800 W',
          ),
          6 => 
          array (
            'clave' => 'Alimentación',
            'valor' => '2 conectores PCIe de 8 pines',
          ),
          7 => 
          array (
            'clave' => 'Ocupación',
            'valor' => '2.5 ranuras',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'DisplayPort 2.1 y HDMI 2.1',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  24 => 
  array (
    'cat' => 'tarjetas-graficas',
    'sku' => 'GPU-7-5',
    'nombre' => 'ASUS TUF Gaming Radeon RX 7800 XT',
    'marca' => 'ASUS',
    'modelo' => 'TUF-RX7800XT-O16G-OG-GAMING',
    'precio' => 529.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Durabilidad legendaria TUF: condensadores de grado militar y 16GB de memoria para 1440p.',
    'desc' => 'Tarjeta gráfica construida para resistir años de uso ininterrumpido. Doble rodamiento de bolas en ventiladores que duplica su vida útil y switch Dual BIOS para alternar entre perfil silencioso y rendimiento.',
    'peso' => 1.35,
    'largo' => 32.0,
    'ancho' => 14.02,
    'alto' => 5.78,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'GPU',
            'valor' => 'Radeon RX 7800 XT',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB GDDR6, bus de 256 bits',
          ),
          2 => 
          array (
            'clave' => 'Procesadores de flujo',
            'valor' => '3840',
          ),
          3 => 
          array (
            'clave' => 'Frecuencia boost',
            'valor' => 'Hasta 2565 MHz en modo OC',
          ),
          4 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0',
          ),
          5 => 
          array (
            'clave' => 'Salidas',
            'valor' => '1 HDMI 2.1 y 3 DisplayPort 2.1',
          ),
          6 => 
          array (
            'clave' => 'Alimentación',
            'valor' => '2 conectores de 8 pines',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Versión OG OC con triple ventilador',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  25 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-1',
    'nombre' => 'Corsair Vengeance RGB 32GB (2x16GB) DDR5 6000MHz',
    'marca' => 'Corsair',
    'modelo' => 'Vengeance RGB CMH32GX5M2B6000C30, negro',
    'precio' => 119.0,
    'precio_oferta' => NULL,
    'stock' => 25,
    'destacado' => true,
    'desc_corta' => 'Latencia ultra baja CL30, iluminación dinámica de diez zonas RGB y perfiles XMP 3.0 / EXPO.',
    'desc' => 'Optimizado para las plataformas más recientes de Intel y AMD. Su disipador de aluminio sólido mantiene los módulos fríos mientras que el software iCUE permite crear efectos luminosos sincronizados con tus juegos.',
    'peso' => 0.15,
    'largo' => 13.5,
    'ancho' => 0.8,
    'alto' => 4.4,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '32 GB, kit de 2 × 16 GB',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'DDR5 UDIMM para escritorio',
          ),
          2 => 
          array (
            'clave' => 'Velocidad de perfil',
            'valor' => '6000 MT/s',
          ),
          3 => 
          array (
            'clave' => 'Latencia CAS',
            'valor' => 'CL30',
          ),
          4 => 
          array (
            'clave' => 'Perfil',
            'valor' => 'Intel XMP 3.0',
          ),
          5 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'RGB direccionable de 10 zonas',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Disipadores e integración con CORSAIR iCUE',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'RAM-8-1-V1',
        'precio' => 119,
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
        'precio' => 124,
        'stock' => 11,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  26 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-2',
    'nombre' => 'Kingston Fury Beast 32GB (2x16GB) DDR5 5600MHz',
    'marca' => 'Kingston',
    'modelo' => 'FURY Beast KF556C36BBEK2-32',
    'precio' => 99.0,
    'precio_oferta' => 89,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Disipador de calor de perfil bajo ideal para gabinetes compactos o disipadores de aire grandes.',
    'desc' => 'Overclocking automático Plug N Play a 4800MT/s o perfil XMP 3.0 a 5600MT/s con certificación Intel y AMD. Estabilidad probada al 100% en fábrica a velocidad nominal.',
    'peso' => 0.12,
    'largo' => 13.335,
    'ancho' => 0.662,
    'alto' => 3.49,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '32 GB, 2 × 16 GB',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'DDR5, DIMM de 288 pines',
          ),
          2 => 
          array (
            'clave' => 'Velocidad',
            'valor' => '5600 MT/s',
          ),
          3 => 
          array (
            'clave' => 'Latencia',
            'valor' => 'CL36',
          ),
          4 => 
          array (
            'clave' => 'Perfil',
            'valor' => 'AMD EXPO',
          ),
          5 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'Sin RGB',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Disipador negro de bajo perfil',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  27 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-3',
    'nombre' => 'Crucial Pro 32GB (2x16GB) DDR5 5600MHz',
    'marca' => 'Crucial',
    'modelo' => 'Crucial Pro CP2K16G56C46U5',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '32 GB, 2 × 16 GB',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'DDR5 UDIMM de escritorio',
          ),
          2 => 
          array (
            'clave' => 'Velocidad',
            'valor' => '5600 MT/s',
          ),
          3 => 
          array (
            'clave' => 'Latencia',
            'valor' => 'CL46',
          ),
          4 => 
          array (
            'clave' => 'Voltaje',
            'valor' => '1.1 V',
          ),
          5 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'Sin RGB',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Disipador de bajo perfil y soporte para perfiles de memoria',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  28 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-4',
    'nombre' => 'Adata XPG Lancer Blade RGB 32GB DDR5 6000MHz',
    'marca' => 'Adata',
    'modelo' => 'LANCER BLADE RGB, kit negro 2 × 16 GB DDR5-6000 CL30',
    'precio' => 109.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Diseño compacto de solo 33mm de altura con difusor de luz geométrico y ECC integrado.',
    'desc' => 'Perfecta para ensambles en chasis pequeños (SFF) donde el espacio sobre el socket es crítico. Incluye circuito integrado de administración de energía (PMIC) para voltajes estables.',
    'peso' => 0.13,
    'largo' => 13.335,
    'ancho' => 0.786,
    'alto' => 4.0,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '32 GB, 2 × 16 GB',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'DDR5 UDIMM',
          ),
          2 => 
          array (
            'clave' => 'Velocidad',
            'valor' => '6000 MT/s',
          ),
          3 => 
          array (
            'clave' => 'Latencia seleccionada',
            'valor' => 'CL30',
          ),
          4 => 
          array (
            'clave' => 'Perfiles',
            'valor' => 'Intel XMP 3.0 y AMD EXPO',
          ),
          5 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'RGB',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Disipador de 40 mm de altura',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  29 => 
  array (
    'cat' => 'memorias-ram',
    'sku' => 'RAM-8-5',
    'nombre' => 'Kingston Fury Impact 16GB DDR4 3200MHz SO-DIMM',
    'marca' => 'Kingston',
    'modelo' => 'FURY Impact KF432S20IB/16',
    'precio' => 45.0,
    'precio_oferta' => NULL,
    'stock' => 22,
    'destacado' => false,
    'desc_corta' => 'Actualiza tu laptop o mini PC a la máxima velocidad DDR4 con overclocking automático.',
    'desc' => 'Módulo de memoria SO-DIMM para portátiles de gaming y trabajo. Se ajusta automáticamente a la velocidad más alta permitida por la BIOS del sistema sin requerir configuración manual.',
    'peso' => 0.03,
    'largo' => 6.96,
    'ancho' => 3,
    'alto' => 0.38,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '16 GB, 1 módulo',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'DDR4 SO-DIMM de 260 pines para laptop',
          ),
          2 => 
          array (
            'clave' => 'Velocidad',
            'valor' => '3200 MT/s',
          ),
          3 => 
          array (
            'clave' => 'Latencia',
            'valor' => 'CL20',
          ),
          4 => 
          array (
            'clave' => 'Voltaje',
            'valor' => '1.2 V',
          ),
          5 => 
          array (
            'clave' => 'Organización',
            'valor' => '1Rx8',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Memoria compacta para equipos compatibles',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  30 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-1',
    'nombre' => 'Corsair RM850x 850W 80 Plus Gold Modular',
    'marca' => 'Corsair',
    'modelo' => 'RM850x (2021), CP-9020200-NA',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => '850 W continuos',
          ),
          1 => 
          array (
            'clave' => 'Certificación',
            'valor' => '80 PLUS Gold',
          ),
          2 => 
          array (
            'clave' => 'Formato',
            'valor' => 'ATX',
          ),
          3 => 
          array (
            'clave' => 'Cableado',
            'valor' => 'Totalmente modular',
          ),
          4 => 
          array (
            'clave' => 'Ventilador',
            'valor' => '135 mm',
          ),
          5 => 
          array (
            'clave' => 'Entrada',
            'valor' => '100–240 V CA',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Modo Zero RPM y conectores EPS para procesador',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  31 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-2',
    'nombre' => 'EVGA SuperNOVA 750 GT 80 Plus Gold',
    'marca' => 'EVGA',
    'modelo' => 'SuperNOVA 750 GT, 220-GT-0750-Y1',
    'precio' => 109.0,
    'precio_oferta' => 99,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Excelente relación calidad-precio con modo Auto ECO para operación completamente silenciosa.',
    'desc' => 'Construcción robusta con protecciones eléctricas completas (OVP, UVP, OCP, OPP, SCP, OTP). Cableado plano totalmente modular para un ensamblaje limpio y ordenado.',
    'peso' => 1.6,
    'largo' => 15,
    'ancho' => 15,
    'alto' => 8.5,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => '750 W',
          ),
          1 => 
          array (
            'clave' => 'Certificación',
            'valor' => '80 PLUS Gold',
          ),
          2 => 
          array (
            'clave' => 'Formato',
            'valor' => 'ATX',
          ),
          3 => 
          array (
            'clave' => 'Cableado',
            'valor' => 'Totalmente modular',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'Auto ECO y ventilador con rodamiento FDB',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  32 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-3',
    'nombre' => 'Cooler Master MWE Gold 850 V2 ATX 3.0',
    'marca' => 'Cooler Master',
    'modelo' => 'MWE Gold 850 V2 ATX 3.0 White Edition',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => '850 W',
          ),
          1 => 
          array (
            'clave' => 'Certificación',
            'valor' => '80 PLUS Gold',
          ),
          2 => 
          array (
            'clave' => 'Formato',
            'valor' => 'ATX',
          ),
          3 => 
          array (
            'clave' => 'Estándar',
            'valor' => 'ATX 3.0',
          ),
          4 => 
          array (
            'clave' => 'Cableado',
            'valor' => 'Totalmente modular',
          ),
          5 => 
          array (
            'clave' => 'Color',
            'valor' => 'Blanco',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Conector de alimentación para GPU de nueva generación',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  33 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-4',
    'nombre' => 'Thermaltake Toughpower GF1 750W Gold',
    'marca' => 'Thermaltake',
    'modelo' => 'Toughpower GF1 750W, PS-TPD-0750FNFAGU-1',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => '750 W',
          ),
          1 => 
          array (
            'clave' => 'Certificación',
            'valor' => '80 PLUS Gold',
          ),
          2 => 
          array (
            'clave' => 'Formato',
            'valor' => 'ATX',
          ),
          3 => 
          array (
            'clave' => 'Cableado',
            'valor' => 'Totalmente modular',
          ),
          4 => 
          array (
            'clave' => 'Ventilador',
            'valor' => '140 mm hidráulico',
          ),
          5 => 
          array (
            'clave' => 'Entrada',
            'valor' => '100–240 V CA',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Smart Zero Fan',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  34 => 
  array (
    'cat' => 'fuentes-de-poder',
    'sku' => 'PSU-9-5',
    'nombre' => 'Corsair SF750 750W Platinum Formato SFX',
    'marca' => 'Corsair',
    'modelo' => 'SF750 Platinum, CP-9020186-NA',
    'precio' => 169.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'La reina indiscutible del formato mini-ITX: 750W en tamaño ultracompacto con eficiencia Platinum.',
    'desc' => 'Diseñada específicamente para ensambles en gabinetes pequeños Small Form Factor. Cables individuales con mallado tipo paracord flexible y soporte adaptador SFX a ATX incluido.',
    'peso' => 0.9,
    'largo' => 10.0,
    'ancho' => 12.5,
    'alto' => 6.3,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => '750 W',
          ),
          1 => 
          array (
            'clave' => 'Certificación',
            'valor' => '80 PLUS Platinum',
          ),
          2 => 
          array (
            'clave' => 'Formato',
            'valor' => 'SFX',
          ),
          3 => 
          array (
            'clave' => 'Cableado',
            'valor' => 'Totalmente modular',
          ),
          4 => 
          array (
            'clave' => 'Ventilador',
            'valor' => '92 mm',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Modo Zero RPM y cables enfundados',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  35 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-1',
    'nombre' => 'Samsung 990 PRO NVMe M.2 SSD PCIe 4.0',
    'marca' => 'Samsung',
    'modelo' => '990 PRO 2 TB sin disipador, MZ-V9P2T0BW',
    'precio' => 179.0,
    'precio_oferta' => NULL,
    'stock' => 35,
    'destacado' => true,
    'desc_corta' => 'Velocidades de vértigo de hasta 7450 MB/s de lectura: el SSD PCIe 4.0 definitivo.',
    'desc' => 'Rendimiento supremo para gaming y cargas pesadas de datos. Equipado con controlador Pascal en 8nm con revestimiento de níquel para disipación térmica y software Samsung Magician.',
    'peso' => 0.009,
    'largo' => 8.0,
    'ancho' => 2.2,
    'alto' => 0.23,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '2 TB',
          ),
          1 => 
          array (
            'clave' => 'Formato',
            'valor' => 'M.2 2280',
          ),
          2 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0 ×4 NVMe 2.0',
          ),
          3 => 
          array (
            'clave' => 'Lectura secuencial',
            'valor' => 'Hasta 7450 MB/s',
          ),
          4 => 
          array (
            'clave' => 'Escritura secuencial',
            'valor' => 'Hasta 6900 MB/s',
          ),
          5 => 
          array (
            'clave' => 'Resistencia',
            'valor' => '1200 TBW',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Samsung Magician y cifrado AES de 256 bits',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'SSD-10-1-V1',
        'precio' => 109,
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
        'precio' => 179,
        'stock' => 15,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '2 TB',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  36 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-2',
    'nombre' => 'Western Digital WD_BLACK SN850X 1TB M.2',
    'marca' => 'Western Digital',
    'modelo' => 'WD_BLACK SN850X 1 TB sin disipador, WDS100T2X0E',
    'precio' => 94.0,
    'precio_oferta' => 84,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Velocidades de hasta 7300 MB/s con modo Game Mode 2.0 para carga instantánea de videojuegos.',
    'desc' => 'Totalmente compatible con PC y PlayStation 5. Minimiza las pantallas de carga y elimina los tirones en juegos de mundo abierto de nueva generación.',
    'peso' => 0.0075,
    'largo' => 8.0,
    'ancho' => 2.2,
    'alto' => 0.238,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '1 TB',
          ),
          1 => 
          array (
            'clave' => 'Formato',
            'valor' => 'M.2 2280',
          ),
          2 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0 ×4 NVMe',
          ),
          3 => 
          array (
            'clave' => 'Lectura secuencial',
            'valor' => 'Hasta 7300 MB/s',
          ),
          4 => 
          array (
            'clave' => 'Escritura secuencial',
            'valor' => 'Hasta 6300 MB/s',
          ),
          5 => 
          array (
            'clave' => 'Resistencia',
            'valor' => '600 TBW',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Game Mode 2.0',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  37 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-3',
    'nombre' => 'Crucial T500 2TB Gen4 NVMe SSD',
    'marca' => 'Crucial',
    'modelo' => 'T500 2 TB sin disipador, CT2000T500SSD8',
    'precio' => 159.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Tecnología Micron TLC de 232 capas con hasta 7400 MB/s y excelente eficiencia energética.',
    'desc' => 'Ideal para creadores de video en resoluciones 4K/8K y estaciones de renderizado. Acelera la edición en Premiere Pro y tiempos de arranque del sistema.',
    'peso' => 0.05,
    'largo' => 8,
    'ancho' => 2.2,
    'alto' => 0.23,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '2 TB',
          ),
          1 => 
          array (
            'clave' => 'Formato',
            'valor' => 'M.2 2280',
          ),
          2 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0 ×4 NVMe',
          ),
          3 => 
          array (
            'clave' => 'Lectura secuencial',
            'valor' => 'Hasta 7400 MB/s',
          ),
          4 => 
          array (
            'clave' => 'Escritura secuencial',
            'valor' => 'Hasta 7000 MB/s',
          ),
          5 => 
          array (
            'clave' => 'Resistencia',
            'valor' => '1200 TBW',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'NAND TLC de Micron',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  38 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-4',
    'nombre' => 'Kingston KC3000 PCIe 4.0 NVMe 1TB',
    'marca' => 'Kingston',
    'modelo' => 'KC3000 1 TB, SKC3000S/1024G',
    'precio' => 89.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Disipador térmico delgado de grafeno y aluminio para máxima estabilidad en laptops y placas base.',
    'desc' => 'Potenciado por el controlador Phison E18 de 8 canales. Gran resistencia a escrituras continuas y durabilidad excepcional comprobada.',
    'peso' => 0.007,
    'largo' => 8.0,
    'ancho' => 2.2,
    'alto' => 0.221,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '1024 GB',
          ),
          1 => 
          array (
            'clave' => 'Formato',
            'valor' => 'M.2 2280',
          ),
          2 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0 ×4 NVMe',
          ),
          3 => 
          array (
            'clave' => 'Lectura secuencial',
            'valor' => 'Hasta 7000 MB/s',
          ),
          4 => 
          array (
            'clave' => 'Escritura secuencial',
            'valor' => 'Hasta 6000 MB/s',
          ),
          5 => 
          array (
            'clave' => 'Resistencia',
            'valor' => '800 TBW',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'NAND 3D TLC y disipador de grafeno/aluminio',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  39 => 
  array (
    'cat' => 'almacenamiento-interno',
    'sku' => 'SSD-10-5',
    'nombre' => 'Seagate FireCuda 530 2TB NVMe SSD',
    'marca' => 'Seagate',
    'modelo' => 'FireCuda 530 2 TB sin disipador, ZP2000GM30013',
    'precio' => 169.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Resistencia legendaria de 2550 TBW con 3 años de servicio de recuperación de datos Rescue incluidos.',
    'desc' => 'Construido para soportar escritura intensa sin degradarse. Permite reescribir el 70% de la capacidad del disco todos los días durante 5 años continuos.',
    'peso' => 0.01,
    'largo' => 8.015,
    'ancho' => 2.215,
    'alto' => 0.358,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '2 TB',
          ),
          1 => 
          array (
            'clave' => 'Formato',
            'valor' => 'M.2 2280',
          ),
          2 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'PCIe 4.0 ×4 NVMe 1.4',
          ),
          3 => 
          array (
            'clave' => 'Lectura secuencial',
            'valor' => 'Hasta 7300 MB/s',
          ),
          4 => 
          array (
            'clave' => 'Escritura secuencial',
            'valor' => 'Hasta 6900 MB/s',
          ),
          5 => 
          array (
            'clave' => 'Resistencia',
            'valor' => '2550 TBW',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'NAND 3D TLC',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  40 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-1',
    'nombre' => 'Logitech G PRO X TKL Teclado Mecánico',
    'marca' => 'Logitech G',
    'modelo' => 'PRO X TKL LIGHTSPEED, GX Brown, negro inglés, 920-012127',
    'precio' => 179.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => true,
    'desc_corta' => 'Diseñado junto a profesionales de eSports: conectividad inalámbrica LIGHTSPEED y switches de precisión.',
    'desc' => 'Teclado para torneos sin teclado numérico (Tenkeyless). Cuenta con teclas PBT de doble inyección resistentes al desgaste, rueda de volumen metálica dedicada y estuche rígido de viaje incluido.',
    'peso' => 0.96,
    'largo' => 15.0,
    'ancho' => 35.2,
    'alto' => 3.8,
    'specs' => [
      [
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => [
          [
            'clave' => 'Formato',
            'valor' => 'Tenkeyless, sin bloque numérico',
          ],
          [
            'clave' => 'Tipo',
            'valor' => 'Mecánico',
          ],
          [
            'clave' => 'Conexión',
            'valor' => 'LIGHTSPEED, Bluetooth y USB',
          ],
          [
            'clave' => 'Interruptores',
            'valor' => 'GX Brown táctiles',
          ],
          [
            'clave' => 'Iluminación',
            'valor' => 'LIGHTSYNC RGB',
          ],
          [
            'clave' => 'Características',
            'valor' => 'Controles multimedia, rueda de volumen y funda de transporte',
          ],
          [
            'clave' => 'Autonomía',
            'valor' => 'Hasta 50 horas',
          ],
        ],
      ],
    ],
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'KB-12-1-V1',
        'precio' => 179,
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
        'precio' => 179,
        'stock' => 7,
        'opcion' => 
        array (
          0 => 'Tipo de switch',
          1 => 'Blue',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  41 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-2',
    'nombre' => 'Razer Huntsman V3 Pro TKL',
    'marca' => 'Razer',
    'modelo' => 'Huntsman V3 Pro Tenkeyless, RZ03-0498',
    'precio' => 219.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Switches analógicos ópticos Gen-2 con punto de actuación ajustable de 0.1mm a 4.0mm y Rapid Trigger.',
    'desc' => 'La máxima ventaja competitiva en Counter-Strike y Valorant. El modo Rapid Trigger permite pulsar y soltar teclas instantáneamente sin esperar el recorrido de reinicio.',
    'peso' => 0.7195,
    'largo' => 13.9,
    'ancho' => 36.3,
    'alto' => 3.9,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Formato',
            'valor' => 'TKL',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'USB con cable',
          ),
          2 => 
          array (
            'clave' => 'Interruptores',
            'valor' => 'Ópticos analógicos Razer de 2.ª generación',
          ),
          3 => 
          array (
            'clave' => 'Actuación',
            'valor' => 'Rango ajustable de 0.1 a 4.0 mm',
          ),
          4 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'Razer Chroma RGB',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Rapid Trigger, dial multifunción y reposamuñecas magnético',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  42 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-3',
    'nombre' => 'Corsair K70 RGB PRO Mecánico',
    'marca' => 'Corsair',
    'modelo' => 'K70 RGB PRO CHERRY MX Red, CH-9109410-NA',
    'precio' => 149.0,
    'precio_oferta' => 129,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Tecnología de hiperprocesamiento AXON a 8000Hz y switches CHERRY MX Red originales.',
    'desc' => 'Estructura icónica de aluminio anodizado negro con reposamuñecas magnético suave. Su interruptor trasero Tournament Switch desactiva macros y fija iluminación estática para juego limpio.',
    'peso' => 1.13,
    'largo' => 16.6,
    'ancho' => 44.4,
    'alto' => 4.0,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Formato',
            'valor' => 'Completo con bloque numérico',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'USB por cable desmontable',
          ),
          2 => 
          array (
            'clave' => 'Interruptores',
            'valor' => 'CHERRY MX Red',
          ),
          3 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'RGB por tecla',
          ),
          4 => 
          array (
            'clave' => 'Sondeo',
            'valor' => 'Hasta 8000 Hz mediante AXON',
          ),
          5 => 
          array (
            'clave' => 'Memoria',
            'valor' => '8 MB integrada',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Teclas PBT, rueda de volumen y modo torneo',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  43 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-4',
    'nombre' => 'SteelSeries Apex Pro TKL (2023)',
    'marca' => 'SteelSeries',
    'modelo' => 'Apex Pro TKL (2023), versión con cable',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Formato',
            'valor' => 'TKL',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'USB-C por cable desmontable',
          ),
          2 => 
          array (
            'clave' => 'Interruptores',
            'valor' => 'OmniPoint 2.0 ajustables',
          ),
          3 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'RGB por tecla',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'Rapid Trigger, pantalla OLED y reposamuñecas magnético',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  44 => 
  array (
    'cat' => 'teclados',
    'sku' => 'KB-12-5',
    'nombre' => 'Redragon K552 Kumara RGB Mecánico',
    'marca' => 'Redragon',
    'modelo' => 'KUMARA K552-RGB, 87 teclas',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Formato',
            'valor' => 'TKL de 87 teclas',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Mecánico',
          ),
          2 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'USB por cable',
          ),
          3 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'RGB',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'Teclas de doble inyección, anti-ghosting completo y bloqueo de tecla Windows',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  45 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-1',
    'nombre' => 'Logitech G Pro X Superlight 2',
    'marca' => 'Logitech G',
    'modelo' => 'PRO X SUPERLIGHT 2, negro',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Diseño',
            'valor' => 'Simétrico, botones laterales a la izquierda',
          ),
          1 => 
          array (
            'clave' => 'Orientación',
            'valor' => 'Uso con mano derecha',
          ),
          2 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'LIGHTSPEED de 2.4 GHz y USB por cable',
          ),
          3 => 
          array (
            'clave' => 'Sensor',
            'valor' => 'Óptico HERO 2',
          ),
          4 => 
          array (
            'clave' => 'Resolución',
            'valor' => 'Hasta 44000 dpi con firmware compatible',
          ),
          5 => 
          array (
            'clave' => 'Botones',
            'valor' => '5',
          ),
          6 => 
          array (
            'clave' => 'Sondeo',
            'valor' => 'Hasta 8000 Hz con firmware compatible',
          ),
          7 => 
          array (
            'clave' => 'Batería',
            'valor' => 'Hasta 95 horas',
          ),
          8 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'Sin RGB',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'Interruptores LIGHTFORCE y pies PTFE',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'MOU-13-1-V1',
        'precio' => 149,
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
        'precio' => 149,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  46 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-2',
    'nombre' => 'Razer DeathAdder V3 Pro Wireless',
    'marca' => 'Razer',
    'modelo' => 'DeathAdder V3 Pro negro, RZ01-04630100-R3U1',
    'precio' => 139.0,
    'precio_oferta' => 124,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Forma ergonómica legendaria refinada: 63g de peso y sensor óptico Focus Pro de 30.000 DPI.',
    'desc' => 'La forma ergonómica que ha ganado más campeonatos de esports en la historia, ahora en un chasis ultraligero sin perforaciones. Desliza suavemente sobre cualquier superficie con sus patas 100% de PTFE.',
    'peso' => 0.063,
    'largo' => 12.8,
    'ancho' => 6.8,
    'alto' => 4.4,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Diseño',
            'valor' => 'Ergonómico para mano derecha',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'HyperSpeed 2.4 GHz y cable USB-C',
          ),
          2 => 
          array (
            'clave' => 'Sensor',
            'valor' => 'Focus Pro 30K óptico',
          ),
          3 => 
          array (
            'clave' => 'Resolución',
            'valor' => 'Hasta 30000 dpi',
          ),
          4 => 
          array (
            'clave' => 'Controles',
            'valor' => '5 botones programables',
          ),
          5 => 
          array (
            'clave' => 'Sondeo',
            'valor' => '1000 Hz con receptor estándar',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => 'Hasta 90 horas a 1000 Hz',
          ),
          7 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'Sin RGB',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Interruptores ópticos Razer de 3.ª generación',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  47 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-3',
    'nombre' => 'SteelSeries Aerox 3 Wireless (2022)',
    'marca' => 'SteelSeries',
    'modelo' => 'Aerox 3 Wireless (2022), Onyx',
    'precio' => 79.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Diseño ultra liviano de 68 gramos con perforaciones de panal y protección IP54 AquaBarrier.',
    'desc' => 'Protegido contra salpicaduras de líquidos, polvo y grasa gracias a su recubrimiento interior certificado IP54. Doble conectividad inalámbrica Quantum 2.0 (2.4GHz y Bluetooth 5.0).',
    'peso' => 0.068,
    'largo' => 12.055,
    'ancho' => 6.703,
    'alto' => 3.798,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Diseño',
            'valor' => 'Simétrico con carcasa perforada',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => '2.4 GHz, Bluetooth y USB-C',
          ),
          2 => 
          array (
            'clave' => 'Sensor',
            'valor' => 'TrueMove Air óptico',
          ),
          3 => 
          array (
            'clave' => 'Resolución',
            'valor' => 'Hasta 18000 CPI',
          ),
          4 => 
          array (
            'clave' => 'Botones',
            'valor' => '6',
          ),
          5 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP54 AquaBarrier',
          ),
          6 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'RGB de 3 zonas',
          ),
          7 => 
          array (
            'clave' => 'Batería',
            'valor' => 'Hasta 200 horas, según modo',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Pies PTFE',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  48 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-4',
    'nombre' => 'Corsair Scimitar RGB Elite MMO',
    'marca' => 'Corsair',
    'modelo' => 'SCIMITAR RGB ELITE, CH-9304211-NA',
    'precio' => 74.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => '12 botones laterales ajustables mediante sistema Key Slider para dominar juegos MMO y MOBA.',
    'desc' => 'Personaliza tu botonera lateral deslizando el bloque numérico para que calce exactamente con el pulgar de tu mano. Sensor óptico nativo de 18000 DPI de PixArt.',
    'peso' => 0.122,
    'largo' => 11.94,
    'ancho' => 7.7,
    'alto' => 4.24,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Diseño',
            'valor' => 'Ergonómico para mano derecha',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'USB por cable',
          ),
          2 => 
          array (
            'clave' => 'Sensor',
            'valor' => 'Óptico PixArt PMW3391',
          ),
          3 => 
          array (
            'clave' => 'Resolución',
            'valor' => 'Hasta 18000 dpi',
          ),
          4 => 
          array (
            'clave' => 'Botones',
            'valor' => '17 programables',
          ),
          5 => 
          array (
            'clave' => 'Iluminación',
            'valor' => 'RGB de 4 zonas',
          ),
          6 => 
          array (
            'clave' => 'Cable',
            'valor' => 'Trenzado, 1.8 m',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Panel lateral de 12 botones ajustable y 3 perfiles integrados',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  49 => 
  array (
    'cat' => 'mouse',
    'sku' => 'MOU-13-5',
    'nombre' => 'HyperX Pulsefire Haste 2 Wireless',
    'marca' => 'HyperX',
    'modelo' => 'Pulsefire Haste 2 Wireless, negro',
    'precio' => 59.0,
    'precio_oferta' => NULL,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Estructura sólida de 61g sin orificios, sensor HyperX 26K y hasta 100 horas de juego continuo.',
    'desc' => 'Respuesta ultrarrápida sin necesidad de agujeros en la carcasa. Incluye cinta de agarre antideslizante (grip tape) troquelada a medida para ambos laterales y botones principales.',
    'peso' => 0.061,
    'largo' => 12.43,
    'ancho' => 6.68,
    'alto' => 3.82,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Diseño',
            'valor' => 'Simétrico con carcasa cerrada',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => '2.4 GHz, Bluetooth y USB',
          ),
          2 => 
          array (
            'clave' => 'Sensor',
            'valor' => 'HyperX 26K óptico',
          ),
          3 => 
          array (
            'clave' => 'Resolución',
            'valor' => 'Hasta 26000 dpi',
          ),
          4 => 
          array (
            'clave' => 'Botones',
            'valor' => '6',
          ),
          5 => 
          array (
            'clave' => 'Batería',
            'valor' => 'Hasta 100 horas',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Pies PTFE y personalización mediante NGENUITY',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  50 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-1',
    'nombre' => 'Sony WH-1000XM5 Cancelación de Ruido',
    'marca' => 'Sony',
    'modelo' => 'WH-1000XM5, negro',
    'precio' => 349.0,
    'precio_oferta' => 329,
    'stock' => 14,
    'destacado' => true,
    'desc_corta' => 'Los mejores audífonos con cancelación de ruido del mercado: procesador V1 y 8 micrófonos dedicados.',
    'desc' => 'Sumérgete en silencio absoluto. Su tecnología de cancelación de ruido líder en la industria adapta el filtrado acústico a la presión atmosférica y a tu entorno en tiempo real. Soporta códec LDAC de alta resolución y llamadas cristalinas.',
    'peso' => 0.25,
    'largo' => 22.5,
    'ancho' => 18,
    'alto' => 7.5,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Circumaural cerrado',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Bluetooth 5.2 y audio de 3.5 mm',
          ),
          2 => 
          array (
            'clave' => 'Cancelación',
            'valor' => 'ANC y modo sonido ambiente',
          ),
          3 => 
          array (
            'clave' => 'Transductores',
            'valor' => '30 mm',
          ),
          4 => 
          array (
            'clave' => 'Códecs',
            'valor' => 'SBC, AAC y LDAC',
          ),
          5 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 30 horas con ANC y hasta 40 sin ANC',
          ),
          6 => 
          array (
            'clave' => 'Carga',
            'valor' => 'USB-C',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Conexión multipunto y controles táctiles',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'AUD-14-1-V1',
        'precio' => 349,
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
        'precio' => 349,
        'stock' => 6,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Plateado',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  51 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-2',
    'nombre' => 'Bose QuietComfort Ultra Headphones',
    'marca' => 'Bose',
    'modelo' => 'QuietComfort Ultra Headphones, 1.ª generación (2023)',
    'precio' => 379.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Comodidad inigualable con Audio Espacial Inmersivo de Bose y modo Aware de transparencia natural.',
    'desc' => 'Audio espacial que hace que el sonido parezca venir del entorno frente a ti en lugar de estar atrapado en tu cabeza. Almohadillas de piel de proteína ultra suaves diseñadas para viajes transoceánicos.',
    'peso' => 0.254,
    'largo' => 5.08,
    'ancho' => 13.97,
    'alto' => 19.558,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Circumaural inalámbrico',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Bluetooth y entrada de audio',
          ),
          2 => 
          array (
            'clave' => 'Cancelación',
            'valor' => 'ANC con CustomTune',
          ),
          3 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Bose Immersive Audio',
          ),
          4 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 24 horas, hasta 18 con audio inmersivo',
          ),
          5 => 
          array (
            'clave' => 'Carga',
            'valor' => 'USB-C, carga completa aproximada de 3 horas',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Modo Quiet y Aware',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  52 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-3',
    'nombre' => 'Sennheiser Momentum 4 Wireless',
    'marca' => 'Sennheiser',
    'modelo' => 'MOMENTUM 4 Wireless, M4AEBT',
    'precio' => 299.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Firma de sonido audiófilo Sennheiser con una increíble batería de 60 horas por carga.',
    'desc' => 'Disfruta de sonido de alta fidelidad con transductores de 42mm desarrollados en Alemania. Su descomunal batería de 60 horas te permite viajar durante semanas sin buscar un cargador.',
    'peso' => 0.293,
    'largo' => 18,
    'ancho' => 19.5,
    'alto' => 4.6,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Circumaural cerrado',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Bluetooth 5.2, USB y audio analógico',
          ),
          2 => 
          array (
            'clave' => 'Transductores',
            'valor' => '42 mm',
          ),
          3 => 
          array (
            'clave' => 'Respuesta de frecuencia',
            'valor' => '6 Hz–22 kHz',
          ),
          4 => 
          array (
            'clave' => 'Códecs',
            'valor' => 'SBC, AAC, aptX y aptX Adaptive',
          ),
          5 => 
          array (
            'clave' => 'Cancelación',
            'valor' => 'ANC adaptativa y modo transparencia',
          ),
          6 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 60 horas',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Controles táctiles y multipunto',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  53 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-4',
    'nombre' => 'Apple AirPods Max con Audio Espacial',
    'marca' => 'Apple',
    'modelo' => 'AirPods Max (2024), USB-C',
    'precio' => 499.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Diadema de malla transpirable, copas de aluminio anodizado y audio computacional con chip H1.',
    'desc' => 'Unión perfecta entre audio de alta fidelidad y la magia de Apple. Audio espacial con seguimiento dinámico de la cabeza para sentirte en una sala de cine o concierto.',
    'peso' => 0.3862,
    'largo' => 8.34,
    'ancho' => 16.86,
    'alto' => 18.73,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Circumaural',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Bluetooth 5.0',
          ),
          2 => 
          array (
            'clave' => 'Cancelación',
            'valor' => 'ANC y modo ambiente',
          ),
          3 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Audio espacial personalizado con seguimiento de cabeza',
          ),
          4 => 
          array (
            'clave' => 'Chip',
            'valor' => 'Apple H1 en cada auricular',
          ),
          5 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 20 horas',
          ),
          6 => 
          array (
            'clave' => 'Carga',
            'valor' => 'USB-C',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Digital Crown y Smart Case',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  54 => 
  array (
    'cat' => 'audifonos',
    'sku' => 'AUD-14-5',
    'nombre' => 'HyperX Cloud III Wireless Gaming',
    'marca' => 'HyperX',
    'modelo' => 'Cloud III Wireless',
    'precio' => 149.0,
    'precio_oferta' => 134,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Comodidad legendaria Cloud con hasta 120 horas de batería y micrófono ultra nítido de 10mm.',
    'desc' => 'Los audífonos gaming definitivos para largas jornadas de juego. Sus controladores en ángulo de 53mm sintonizan con precisión los pasos y disparos enemigos en shooters competitivos.',
    'peso' => 0.342,
    'largo' => 20,
    'ancho' => 15.5,
    'alto' => 9,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Headset gamer circumaural',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Inalámbrica de 2.4 GHz',
          ),
          2 => 
          array (
            'clave' => 'Transductores',
            'valor' => '53 mm angulados',
          ),
          3 => 
          array (
            'clave' => 'Respuesta de frecuencia',
            'valor' => '10 Hz–21 kHz',
          ),
          4 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 120 horas',
          ),
          5 => 
          array (
            'clave' => 'Micrófono',
            'valor' => 'Desmontable de 10 mm con indicador LED',
          ),
          6 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'PC, PS5, PS4 y Nintendo Switch',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'DTS Headphone:X en plataformas compatibles',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  55 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-1',
    'nombre' => 'LG UltraGear 27GR95QE-B OLED 240Hz',
    'marca' => 'LG',
    'modelo' => 'UltraGear 27GR95QE-B',
    'precio' => 799.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => true,
    'desc_corta' => 'Panel OLED QHD de 27 pulgadas con 240Hz y un tiempo de respuesta casi instantáneo de 0.03ms.',
    'desc' => 'La cúspide visual para juegos de competición. Negros perfectos gracias a sus píxeles autoemisivos con contraste 1.500.000:1 y compatibilidad certificada con NVIDIA G-SYNC y AMD FreeSync Premium.',
    'peso' => 7.35,
    'largo' => 25.8,
    'ancho' => 60.44,
    'alto' => 57.44,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '26.5 pulgadas OLED, clase 27',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '2560 × 1440 QHD',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '240 Hz',
          ),
          3 => 
          array (
            'clave' => 'Respuesta',
            'valor' => '0.03 ms GtG',
          ),
          4 => 
          array (
            'clave' => 'HDR',
            'valor' => 'HDR10',
          ),
          5 => 
          array (
            'clave' => 'Sincronización',
            'valor' => 'FreeSync Premium y compatible con G-SYNC',
          ),
          6 => 
          array (
            'clave' => 'Conectores',
            'valor' => 'HDMI 2.1, DisplayPort 1.4 y hub USB',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Soporte con altura, giro, inclinación y pivote',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  56 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-2',
    'nombre' => 'Samsung Odyssey G7 32" Curvo 240Hz',
    'marca' => 'Samsung',
    'modelo' => 'Odyssey G7 32 pulgadas, LC32G75TQSPXEN',
    'precio' => 599.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Curvatura profunda 1000R que coincide con el campo de visión humano, panel QLED WQHD y 240Hz.',
    'desc' => 'Inmersión periférica total que reduce la fatiga visual. Tecnología Quantum Dot para colores intensos con certificación VESA DisplayHDR 600 y retroiluminación trasera CoreSync.',
    'peso' => 8.2,
    'largo' => 30.59,
    'ancho' => 71.01,
    'alto' => 59.45,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '32 pulgadas VA curva 1000R',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '2560 × 1440 QHD',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '240 Hz',
          ),
          3 => 
          array (
            'clave' => 'Respuesta',
            'valor' => '1 ms GtG',
          ),
          4 => 
          array (
            'clave' => 'HDR',
            'valor' => 'DisplayHDR 600',
          ),
          5 => 
          array (
            'clave' => 'Sincronización',
            'valor' => 'FreeSync Premium Pro y compatible con G-SYNC',
          ),
          6 => 
          array (
            'clave' => 'Conectores',
            'valor' => 'HDMI y DisplayPort',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Panel QLED y soporte regulable',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  57 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-3',
    'nombre' => 'Dell UltraSharp U2723QE 27" 4K IPS Black',
    'marca' => 'Dell',
    'modelo' => 'UltraSharp U2723QE',
    'precio' => 549.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Tecnología IPS Black pionera con contraste 2000:1, resolución 4K UHD y hub USB-C de 90W.',
    'desc' => 'El monitor favorito de diseñadores, fotógrafos y desarrolladores. Conecta tu laptop con un solo cable USB-C que transmite video 4K, datos de red Gigabit Ethernet y carga tu batería.',
    'peso' => 6.64,
    'largo' => 18.5,
    'ancho' => 61.14,
    'alto' => 38.518,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '27 pulgadas IPS Black',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '3840 × 2160 4K',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '60 Hz',
          ),
          3 => 
          array (
            'clave' => 'Contraste',
            'valor' => '2000:1',
          ),
          4 => 
          array (
            'clave' => 'Color',
            'valor' => '98% DCI-P3',
          ),
          5 => 
          array (
            'clave' => 'USB-C',
            'valor' => 'Video y alimentación hasta 90 W',
          ),
          6 => 
          array (
            'clave' => 'Conectores',
            'valor' => 'HDMI, DisplayPort, USB-C, hub USB y Ethernet RJ45',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'KVM y conexión de monitores en cadena',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
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
    'largo' => 25.68,
    'ancho' => 61.4,
    'alto' => 56.09,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '27 pulgadas IPS',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '3840 × 2160 4K',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '60 Hz',
          ),
          3 => 
          array (
            'clave' => 'Color',
            'valor' => '99% sRGB y 99% Rec.709',
          ),
          4 => 
          array (
            'clave' => 'USB-C',
            'valor' => 'Alimentación hasta 65 W',
          ),
          5 => 
          array (
            'clave' => 'Audio',
            'valor' => '2 altavoces de 2.5 W',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'KVM, modos CAD/CAM, Darkroom y M-Book',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  59 => 
  array (
    'cat' => 'monitores',
    'sku' => 'MON-15-5',
    'nombre' => 'ViewSonic ELITE XG270QG Gaming',
    'marca' => 'ViewSonic',
    'modelo' => 'ELITE XG270QG',
    'precio' => 429.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Panel Nano IPS de 1ms con tecnología NVIDIA G-SYNC nativa y viseras laterales antideslumbrantes.',
    'desc' => 'Colores de calidad Nano IPS combinados con la fluidez que exigen los torneos de eSports. Incluye soportes para audífonos y bungee retráctil para el cable del mouse integrados en la base.',
    'peso' => 7.7,
    'largo' => 26.52,
    'ancho' => 61.42,
    'alto' => 57.16,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '27 pulgadas Nano IPS',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '2560 × 1440',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '144 Hz nativos, hasta 165 Hz con overclock',
          ),
          3 => 
          array (
            'clave' => 'Respuesta',
            'valor' => '1 ms GtG',
          ),
          4 => 
          array (
            'clave' => 'Color',
            'valor' => '98% DCI-P3',
          ),
          5 => 
          array (
            'clave' => 'Sincronización',
            'valor' => 'NVIDIA G-SYNC',
          ),
          6 => 
          array (
            'clave' => 'Conectores',
            'valor' => 'DisplayPort, HDMI y hub USB',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Soporte ajustable e iluminación ELITE RGB',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'MON-15-5-V1',
        'precio' => 399,
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
        'precio' => 479,
        'stock' => 4,
        'opcion' => 
        array (
          0 => 'Frecuencia',
          1 => '240 Hz',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  60 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-1',
    'nombre' => 'Bose SoundLink Flex Bluetooth',
    'marca' => 'Bose',
    'modelo' => 'SoundLink Flex, 1.ª generación',
    'precio' => 149.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => true,
    'desc_corta' => 'Audio nítido y graves profundos con tecnología PositionIQ y resistencia al agua IP67 que flota.',
    'desc' => 'Diseñado para acompañarte a la playa o la piscina. La tecnología PositionIQ detecta automáticamente si el altavoz está de pie, acostado o colgado y calibra el sonido para ofrecer siempre el mejor timbre.',
    'peso' => 0.59,
    'largo' => 5.2324,
    'ancho' => 20.1422,
    'alto' => 9.0424,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Altavoz Bluetooth portátil',
          ),
          1 => 
          array (
            'clave' => 'Bluetooth',
            'valor' => '4.2',
          ),
          2 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP67',
          ),
          3 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 12 horas',
          ),
          4 => 
          array (
            'clave' => 'Carga',
            'valor' => 'USB-C',
          ),
          5 => 
          array (
            'clave' => 'Micrófono',
            'valor' => 'Integrado para llamadas',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'PositionIQ y Bose Connect',
          ),
          7 => 
          array (
            'clave' => 'Potencia RMS',
            'valor' => 'No publicada por el fabricante',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  61 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-2',
    'nombre' => 'JBL Charge 5 Altavoz Portátil',
    'marca' => 'JBL',
    'modelo' => 'Charge 5, versión Bluetooth',
    'precio' => 179.0,
    'precio_oferta' => 159,
    'stock' => 25,
    'destacado' => true,
    'desc_corta' => 'Sonido JBL Original Pro con tweeter independiente, doble radiador pasivo y power bank integrado.',
    'desc' => 'El rey de las fiestas al aire libre. Sus radiadores pasivos de graves hacen vibrar la música con claridad. Su batería masiva de 7500mAh te permite cargar tu celular mientras escuchas tus canciones favoritas.',
    'peso' => 0.96,
    'largo' => 9.4,
    'ancho' => 22.3,
    'alto' => 9.65,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => '40 W RMS: 30 W woofer y 10 W tweeter',
          ),
          1 => 
          array (
            'clave' => 'Bluetooth',
            'valor' => '5.1',
          ),
          2 => 
          array (
            'clave' => 'Respuesta de frecuencia',
            'valor' => '60 Hz–20 kHz',
          ),
          3 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP67',
          ),
          4 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 20 horas',
          ),
          5 => 
          array (
            'clave' => 'Batería',
            'valor' => '27 Wh',
          ),
          6 => 
          array (
            'clave' => 'Carga',
            'valor' => 'USB-C',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'JBL PartyBoost y salida USB para cargar dispositivos',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'SND-16-2-V1',
        'precio' => 179,
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
        'precio' => 179,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Azul',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  62 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-3',
    'nombre' => 'Marshall Emberton II Portátil Vintage',
    'marca' => 'Marshall',
    'modelo' => 'Emberton II, Black & Steel',
    'precio' => 169.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Estilo clásico de amplificador de guitarra con sonido multidireccional True Stereophonic de 360°.',
    'desc' => 'Sonido estéreo espacial que llena toda la habitación. Construido con un 50% de plástico reciclado posconsumo y resistente al agua IP67 con más de 30 horas de reproducción.',
    'peso' => 0.7,
    'largo' => 7.6,
    'ancho' => 16.0,
    'alto' => 6.8,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => '2 amplificadores clase D de 10 W',
          ),
          1 => 
          array (
            'clave' => 'Bluetooth',
            'valor' => '5.1',
          ),
          2 => 
          array (
            'clave' => 'Respuesta de frecuencia',
            'valor' => '60 Hz–20 kHz',
          ),
          3 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP67',
          ),
          4 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Más de 30 horas',
          ),
          5 => 
          array (
            'clave' => 'Carga',
            'valor' => 'USB-C',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Sonido estéreo multidireccional y Stack Mode',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  63 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-4',
    'nombre' => 'Edifier R1280DB Altavoces de Estudio Bluetooth',
    'marca' => 'Edifier',
    'modelo' => 'R1280DB, par de altavoces activos',
    'precio' => 139.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Monitores de estantería de madera MDF con entradas óptica, coaxial, RCA doble y Bluetooth.',
    'desc' => 'Sonido cálido y detallado para tu setup de computadora o tocadiscos de vinilo. Controles laterales para agudos, graves y volumen más control remoto inalámbrico.',
    'peso' => 4.9,
    'largo' => 19.6,
    'ancho' => 14.6,
    'alto' => 23.4,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Altavoces de estantería activos de 2 vías',
          ),
          1 => 
          array (
            'clave' => 'Potencia total',
            'valor' => '42 W RMS, 21 W por canal',
          ),
          2 => 
          array (
            'clave' => 'Entradas',
            'valor' => 'Bluetooth, 2 RCA, óptica y coaxial',
          ),
          3 => 
          array (
            'clave' => 'Woofer',
            'valor' => '4 pulgadas',
          ),
          4 => 
          array (
            'clave' => 'Tweeter',
            'valor' => '13 mm',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Control remoto y controles laterales de graves, agudos y volumen',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  64 => 
  array (
    'cat' => 'audio',
    'sku' => 'SND-16-5',
    'nombre' => 'Sony SRS-XB100 Altavoz Ultra Compacto',
    'marca' => 'Sony',
    'modelo' => 'SRS-XB100, negro',
    'precio' => 59.0,
    'precio_oferta' => NULL,
    'stock' => 20,
    'destacado' => false,
    'desc_corta' => 'Tamaño de bolsillo con correa versátil, procesador Sound Diffusion y EXTRA BASS potente.',
    'desc' => 'Sonido amplio y envolvente que desafía su tamaño miniatura. Su micrófono incorporado con cancelación de eco permite atender llamadas con manos libres de forma impecable.',
    'peso' => 0.274,
    'largo' => 7.5,
    'ancho' => 7.5,
    'alto' => 9.1,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Altavoz Bluetooth compacto',
          ),
          1 => 
          array (
            'clave' => 'Bluetooth',
            'valor' => '5.3',
          ),
          2 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP67',
          ),
          3 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 16 horas',
          ),
          4 => 
          array (
            'clave' => 'Carga',
            'valor' => 'USB-C',
          ),
          5 => 
          array (
            'clave' => 'Micrófono',
            'valor' => 'Integrado',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Sound Diffusion Processor y emparejamiento estéreo entre dos XB100',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  65 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-1',
    'nombre' => 'TP-Link Archer AX73 Wi-Fi 6 AX5400',
    'marca' => 'TP-Link',
    'modelo' => 'Archer AX73',
    'precio' => 129.0,
    'precio_oferta' => 119,
    'stock' => 16,
    'destacado' => true,
    'desc_corta' => '6 antenas de alta ganancia con beamforming y velocidades Wi-Fi 6 de hasta 5400 Mbps.',
    'desc' => 'Maneja streaming en 8K sin cortes y descargas masivas simultáneas gracias a su estructura 4T4R y modulación 1024-QAM. Incluye seguridad TP-Link HomeShield con antivirus.',
    'peso' => 0.66,
    'largo' => 14.72,
    'ancho' => 27.25,
    'alto' => 4.92,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Estándar',
            'valor' => 'Wi-Fi 6, doble banda',
          ),
          1 => 
          array (
            'clave' => 'Clase',
            'valor' => 'AX5400',
          ),
          2 => 
          array (
            'clave' => 'Velocidades de enlace',
            'valor' => 'Hasta 4804 Mb/s en 5 GHz y 574 Mb/s en 2.4 GHz',
          ),
          3 => 
          array (
            'clave' => 'Puertos',
            'valor' => '1 WAN Gigabit y 4 LAN Gigabit',
          ),
          4 => 
          array (
            'clave' => 'USB',
            'valor' => '1 USB 3.0',
          ),
          5 => 
          array (
            'clave' => 'Antenas',
            'valor' => '6 externas fijas',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'OFDMA, MU-MIMO y canal de 160 MHz',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  66 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-2',
    'nombre' => 'ASUS RT-AX88U Pro Dual Band Wi-Fi 6',
    'marca' => 'ASUS',
    'modelo' => 'RT-AX88U Pro',
    'precio' => 249.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Doble puerto de 2.5 Gbps, procesador quad-core de 2.0 GHz y aceleración de juegos por hardware.',
    'desc' => 'Potencia de red sin concesiones. Su doble puerto 2.5G permite aprovechar conexiones de fibra óptica multigigabit y transferencias ultra rápidas a servidores NAS locales.',
    'peso' => 1.01,
    'largo' => 18.8,
    'ancho' => 30.0,
    'alto' => 6.05,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Estándar',
            'valor' => 'Wi-Fi 6, doble banda',
          ),
          1 => 
          array (
            'clave' => 'Clase',
            'valor' => 'AX6000',
          ),
          2 => 
          array (
            'clave' => 'Velocidades',
            'valor' => 'Hasta 4804 Mb/s en 5 GHz y 1148 Mb/s en 2.4 GHz',
          ),
          3 => 
          array (
            'clave' => 'Puertos',
            'valor' => '1 WAN de 2.5 GbE, 1 LAN de 2.5 GbE y 4 LAN Gigabit',
          ),
          4 => 
          array (
            'clave' => 'USB',
            'valor' => '1 USB 3.2 Gen 1',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'AiMesh, AiProtection Pro y soporte de VPN',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  67 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-3',
    'nombre' => 'Netgear Nighthawk RAXE500 Wi-Fi 6E',
    'marca' => 'Netgear',
    'modelo' => 'Nighthawk RAXE500',
    'precio' => 399.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Tecnología tri-banda Wi-Fi 6E con acceso a la nueva autopista libre de 6 GHz a 10.8 Gbps.',
    'desc' => 'Diseño futurista en forma de alas que oculta 8 antenas prealineadas para máxima cobertura. La banda de 6GHz elimina por completo la interferencia de los vecinos.',
    'peso' => 1.45,
    'largo' => 22.12,
    'ancho' => 34.5,
    'alto' => 16.95,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Estándar',
            'valor' => 'Wi-Fi 6E tribanda',
          ),
          1 => 
          array (
            'clave' => 'Clase',
            'valor' => 'AXE11000',
          ),
          2 => 
          array (
            'clave' => 'Bandas',
            'valor' => '2.4 GHz, 5 GHz y 6 GHz',
          ),
          3 => 
          array (
            'clave' => 'Velocidades',
            'valor' => 'Hasta 1.2 + 4.8 + 4.8 Gb/s',
          ),
          4 => 
          array (
            'clave' => 'Puertos',
            'valor' => '1 WAN Gigabit, 4 LAN Gigabit y 1 puerto 2.5G',
          ),
          5 => 
          array (
            'clave' => 'USB',
            'valor' => '2 USB 3.0',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Canales de 160 MHz y antenas integradas',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  68 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-4',
    'nombre' => 'D-Link EAGLE PRO AI AX3200 Smart Router',
    'marca' => 'D-Link',
    'modelo' => 'EAGLE PRO AI R32',
    'precio' => 99.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Optimización automática del tráfico impulsada por algoritmos de Inteligencia Artificial.',
    'desc' => 'El asistente de IA monitorea de forma continua los canales disponibles y asigna el ancho de banda ideal a cada dispositivo de tu hogar evitando congestiones.',
    'peso' => 0.446,
    'largo' => 15.9,
    'ancho' => 22.83,
    'alto' => 6.19,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Estándar',
            'valor' => 'Wi-Fi 6, doble banda',
          ),
          1 => 
          array (
            'clave' => 'Clase',
            'valor' => 'AX3200',
          ),
          2 => 
          array (
            'clave' => 'Velocidades',
            'valor' => 'Hasta 2402 Mb/s en 5 GHz y 800 Mb/s en 2.4 GHz',
          ),
          3 => 
          array (
            'clave' => 'Puertos',
            'valor' => '1 WAN Gigabit y 4 LAN Gigabit',
          ),
          4 => 
          array (
            'clave' => 'Antenas',
            'valor' => '4 externas',
          ),
          5 => 
          array (
            'clave' => 'Seguridad',
            'valor' => 'WPA3',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'AI Wi-Fi Optimizer y control parental',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  69 => 
  array (
    'cat' => 'routers',
    'sku' => 'RTR-18-5',
    'nombre' => 'Ubiquiti UniFi Dream Router (UDR)',
    'marca' => 'Ubiquiti',
    'modelo' => 'UniFi Dream Router UDR original',
    'precio' => 199.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => true,
    'desc_corta' => 'Router corporativo todo en uno: switch PoE, punto de acceso Wi-Fi 6 y pantalla LCM de estado.',
    'desc' => 'La puerta de entrada al ecosistema profesional UniFi. Administra redes avanzadas, cámaras de seguridad UniFi Protect con su ranura MicroSD interna y alimenta dispositivos mediante puertos PoE.',
    'peso' => 1.2,
    'largo' => 11.0,
    'ancho' => 11.0,
    'alto' => 18.41,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Estándar',
            'valor' => 'Wi-Fi 6',
          ),
          1 => 
          array (
            'clave' => 'Puertos',
            'valor' => '1 WAN Gigabit y 4 LAN Gigabit',
          ),
          2 => 
          array (
            'clave' => 'PoE',
            'valor' => '2 puertos LAN con salida PoE',
          ),
          3 => 
          array (
            'clave' => 'Gestión',
            'valor' => 'UniFi Network y aplicaciones compatibles',
          ),
          4 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '128 GB integrado y ranura microSD',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Router, punto de acceso y controlador UniFi en un equipo',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  70 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-1',
    'nombre' => 'TP-Link TL-SG108E Gigabit 8 Puertos Easy Smart',
    'marca' => 'TP-Link',
    'modelo' => 'TL-SG108E, revisión V5',
    'precio' => 29.0,
    'precio_oferta' => NULL,
    'stock' => 20,
    'destacado' => false,
    'desc_corta' => 'Chasis de acero robusto con administración web sencilla, soporte de VLAN y QoS para prioridad de red.',
    'desc' => 'Mejora tu red con monitoreo de tráfico, priorización de paquetes para streaming y telefonía IP, y prevención de bucles de red. Diseño fanless sin ventilador 100% silencioso.',
    'peso' => 0.54,
    'largo' => 10.1,
    'ancho' => 15.8,
    'alto' => 2.5,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Switch Easy Smart',
          ),
          1 => 
          array (
            'clave' => 'Puertos',
            'valor' => '8 RJ45 10/100/1000 Mb/s',
          ),
          2 => 
          array (
            'clave' => 'Capacidad de conmutación',
            'valor' => '16 Gb/s',
          ),
          3 => 
          array (
            'clave' => 'Gestión',
            'valor' => 'VLAN 802.1Q, QoS, IGMP Snooping y port mirroring',
          ),
          4 => 
          array (
            'clave' => 'PoE',
            'valor' => 'No',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Carcasa metálica y diseño sin ventilador',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  71 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-2',
    'nombre' => 'Netgear GS305E 5 Puertos Gigabit Plus',
    'marca' => 'Netgear',
    'modelo' => 'GS305E',
    'precio' => 24.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Switch inteligente gestionable con diagnóstico de cables y montaje en pared o escritorio.',
    'desc' => 'Configura y soluciona problemas de red fácilmente. Detecta si un cable de red está roto o dañado mediante su utilidad de comprobación integrada.',
    'peso' => 0.25,
    'largo' => 10.0,
    'ancho' => 9.4,
    'alto' => 2.7,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Switch Gigabit Plus/Easy Smart',
          ),
          1 => 
          array (
            'clave' => 'Puertos',
            'valor' => '5 RJ45 10/100/1000 Mb/s',
          ),
          2 => 
          array (
            'clave' => 'Gestión',
            'valor' => 'VLAN, QoS e IGMP Snooping',
          ),
          3 => 
          array (
            'clave' => 'PoE',
            'valor' => 'No',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'Carcasa metálica y refrigeración pasiva',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  72 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-3',
    'nombre' => 'D-Link DGS-1008G 8 Puertos Gigabit Desktop',
    'marca' => 'D-Link',
    'modelo' => 'DGS-1008G, revisión D1',
    'precio' => 26.0,
    'precio_oferta' => NULL,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Plug and play inmediato con tecnología D-Link Green que reduce el consumo hasta un 80%.',
    'desc' => 'Detecta automáticamente la longitud del cable conectado y el estado de enlace de los dispositivos para apagar los puertos inactivos y ahorrar energía eléctrica.',
    'peso' => 0.13,
    'largo' => 8.5,
    'ancho' => 13.8,
    'alto' => 3.4,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Switch no administrable',
          ),
          1 => 
          array (
            'clave' => 'Puertos',
            'valor' => '8 Gigabit Ethernet RJ45',
          ),
          2 => 
          array (
            'clave' => 'Capacidad de conmutación',
            'valor' => '16 Gb/s',
          ),
          3 => 
          array (
            'clave' => 'PoE',
            'valor' => 'No',
          ),
          4 => 
          array (
            'clave' => 'Alimentación',
            'valor' => 'Adaptador de 5 V/1 A',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Auto MDI/MDIX y funcionamiento sin ventilador',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  73 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-4',
    'nombre' => 'Ubiquiti UniFi Switch Flex Mini 5 Puertos',
    'marca' => 'Ubiquiti',
    'modelo' => 'UniFi Switch Flex Mini, USW-Flex-Mini',
    'precio' => 35.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => true,
    'desc_corta' => 'Switch administrado de bolsillo que puede alimentarse por PoE 802.3af o por cable USB-C.',
    'desc' => 'Extiende la red UniFi a cualquier rincón de tu oficina o centro de entretenimiento. Gestionable desde el controlador central UniFi Network con estadísticas de tráfico por puerto.',
    'peso' => 0.15,
    'largo' => 7.015,
    'ancho' => 10.716,
    'alto' => 2.117,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Switch gestionado por UniFi Network',
          ),
          1 => 
          array (
            'clave' => 'Puertos',
            'valor' => '5 Gigabit RJ45',
          ),
          2 => 
          array (
            'clave' => 'Alimentación',
            'valor' => 'USB-C de 5 V o entrada PoE 802.3af/at',
          ),
          3 => 
          array (
            'clave' => 'Salida PoE',
            'valor' => 'No',
          ),
          4 => 
          array (
            'clave' => 'Conmutación',
            'valor' => '10 Gb/s',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Equipo compacto para redes UniFi',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  74 => 
  array (
    'cat' => 'switches-y-adaptadores',
    'sku' => 'SW-19-5',
    'nombre' => 'Anker PowerExpand USB-C a Gigabit Ethernet',
    'marca' => 'Anker',
    'modelo' => 'PowerExpand USB-C to Gigabit Ethernet Adapter, A8313',
    'precio' => 19.0,
    'precio_oferta' => NULL,
    'stock' => 26,
    'destacado' => false,
    'desc_corta' => 'Añade un puerto de red cableado Gigabit a laptops modernas sin puerto RJ45 nativo.',
    'desc' => 'Carcasa de aluminio disipador de calor y cable de nylon trenzado. Compatible con MacBooks, iPads, laptops Windows y consolas Nintendo Switch para una conexión sin lag.',
    'peso' => 0.023,
    'largo' => 5.2,
    'ancho' => 2.4,
    'alto' => 1.4,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Adaptador de red',
          ),
          1 => 
          array (
            'clave' => 'Conexión al equipo',
            'valor' => 'USB-C',
          ),
          2 => 
          array (
            'clave' => 'Puerto de red',
            'valor' => '1 RJ45',
          ),
          3 => 
          array (
            'clave' => 'Velocidad',
            'valor' => 'Hasta 1 Gb/s',
          ),
          4 => 
          array (
            'clave' => 'Alimentación',
            'valor' => 'Mediante USB',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Adaptador compacto para conexión Ethernet por cable',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  75 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-1',
    'nombre' => 'Anker 765 USB-C a USB-C Cable 140W Nylon',
    'marca' => 'Anker',
    'modelo' => '765 USB-C a USB-C, 140 W, versión de 3 pies',
    'precio' => 19.0,
    'precio_oferta' => NULL,
    'stock' => 45,
    'destacado' => true,
    'desc_corta' => 'Carga ultrarrápida USB Power Delivery 3.1 de 140W con chip E-Marker y prueba de 35.000 flexiones.',
    'desc' => 'Capaz de cargar desde audífonos hasta una MacBook Pro de 16 pulgadas a máxima velocidad. Trenzado con fibras de nylon de grado aeroespacial para resistir tirones y dobladuras.',
    'peso' => 0.08,
    'largo' => 100,
    'ancho' => 1.2,
    'alto' => 0.6,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Conectores',
            'valor' => 'USB-C macho en ambos extremos',
          ),
          1 => 
          array (
            'clave' => 'Potencia',
            'valor' => 'Hasta 140 W con equipos y cargador compatibles',
          ),
          2 => 
          array (
            'clave' => 'Longitud',
            'valor' => '0.9144 m (3 pies)',
          ),
          3 => 
          array (
            'clave' => 'Datos',
            'valor' => 'USB 2.0, hasta 480 Mb/s',
          ),
          4 => 
          array (
            'clave' => 'Recubrimiento',
            'valor' => 'Nylon trenzado',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Cable orientado a carga de alta potencia',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CBL-20-1-V1',
        'precio' => 19,
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
        'precio' => 24,
        'stock' => 20,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '2 m',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  76 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-2',
    'nombre' => 'Belkin Ultra HD Alta Velocidad HDMI 2.1 4K/8K',
    'marca' => 'Belkin',
    'modelo' => 'Ultra HD High Speed HDMI, AV10175, 2 m',
    'precio' => 24.0,
    'precio_oferta' => NULL,
    'stock' => 35,
    'destacado' => false,
    'desc_corta' => 'Certificación oficial HDMI Forum: ancho de banda de 48 Gbps para 4K a 120Hz y 8K a 60Hz.',
    'desc' => 'Aprobado para PlayStation 5, Xbox Series X y televisores OLED. Soporta VRR (Tasa de Refresco Variable), ALLM (Modo de Baja Latencia Automático) y eARC para audio Dolby Atmos.',
    'peso' => 0.12,
    'largo' => 100,
    'ancho' => 1.8,
    'alto' => 0.9,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Conectores',
            'valor' => 'HDMI macho a HDMI macho',
          ),
          1 => 
          array (
            'clave' => 'Longitud',
            'valor' => '2 m',
          ),
          2 => 
          array (
            'clave' => 'Ancho de banda',
            'valor' => 'Hasta 48 Gb/s',
          ),
          3 => 
          array (
            'clave' => 'Video',
            'valor' => 'Hasta 4K a 120 Hz u 8K a 60 Hz con equipos compatibles',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'HDR y canal de retorno de audio',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CBL-20-2-V1',
        'precio' => 24,
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
        'precio' => 29,
        'stock' => 15,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '2 m',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  77 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-3',
    'nombre' => 'Apple Cable Thunderbolt 4 Pro Trenzado',
    'marca' => 'Apple',
    'modelo' => 'Thunderbolt 4 Pro USB-C, versión de 3 m',
    'precio' => 69.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Transferencia de datos a 40 Gbps, salida de video DisplayPort y carga rápida de hasta 100W.',
    'desc' => 'Diseño trenzado negro de primera calidad que se enrolla sin enredos. Permite conectar monitores Studio Display, tarjetas gráficas externas (eGPU) y discos duros RAID profesionales.',
    'peso' => 0.09,
    'largo' => 180,
    'ancho' => 1.2,
    'alto' => 0.5,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Conectores',
            'valor' => 'USB-C en ambos extremos',
          ),
          1 => 
          array (
            'clave' => 'Longitud',
            'valor' => '3 m',
          ),
          2 => 
          array (
            'clave' => 'Datos',
            'valor' => 'Hasta 40 Gb/s por Thunderbolt 3/4 y USB4',
          ),
          3 => 
          array (
            'clave' => 'USB 3',
            'valor' => 'Hasta 10 Gb/s',
          ),
          4 => 
          array (
            'clave' => 'Carga',
            'valor' => 'Hasta 100 W',
          ),
          5 => 
          array (
            'clave' => 'Video',
            'valor' => 'DisplayPort HBR3',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Cable activo negro de diseño trenzado',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  78 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-4',
    'nombre' => 'TP-Link Cable Patch Cord Cat6 Gigabit RJ45',
    'marca' => 'TP-Link',
    'modelo' => 'Cable Cat6 atribuido a TP-Link, modelo no identificado',
    'precio' => 6.0,
    'precio_oferta' => NULL,
    'stock' => 50,
    'destacado' => false,
    'desc_corta' => 'Cable de red ensamblado en fábrica con conductores de cobre puro y contactos dorados.',
    'desc' => 'Garantiza el 100% de la velocidad contratada de tu plan de internet sin interferencias electromagnéticas gracias a su cruceta interna separadora de pares.',
    'peso' => 0.08,
    'largo' => 200,
    'ancho' => 1.1,
    'alto' => 0.6,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo declarado',
            'valor' => 'Patch cord Cat6 RJ45',
          ),
          1 => 
          array (
            'clave' => 'Código del fabricante',
            'valor' => 'No proporcionado',
          ),
          2 => 
          array (
            'clave' => 'Longitud',
            'valor' => 'No proporcionada',
          ),
          3 => 
          array (
            'clave' => 'Estado de verificación',
            'valor' => 'No se confirmó un modelo oficial que corresponda al nombre',
          ),
          4 => 
          array (
            'clave' => 'Acción',
            'valor' => 'Solicitar foto del empaque y código del producto antes de publicar',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CBL-20-4-V1',
        'precio' => 6,
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
        'precio' => 10,
        'stock' => 20,
        'opcion' => 
        array (
          0 => 'Longitud',
          1 => '5 m',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  79 => 
  array (
    'cat' => 'cables-y-conectores',
    'sku' => 'CBL-20-5',
    'nombre' => 'D-Link Cable de Red Cat6 UTP 3m Certificado',
    'marca' => 'D-Link',
    'modelo' => 'Patch cord D-Link Cat6 UTP, referencia de 3 m',
    'precio' => 7.5,
    'precio_oferta' => NULL,
    'stock' => 30,
    'destacado' => false,
    'desc_corta' => 'Longitud intermedia ideal para conectar consolas, smart TVs o PCs al router o switch.',
    'desc' => 'Revestimiento de PVC resistente y flexible de color azul. Cumple con los estándares internacionales TIA/EIA-568-C.2.',
    'peso' => 0.1,
    'largo' => 300,
    'ancho' => 1.1,
    'alto' => 0.6,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Categoría',
            'valor' => 'Cat6',
          ),
          1 => 
          array (
            'clave' => 'Conectores',
            'valor' => 'RJ45 macho a RJ45 macho',
          ),
          2 => 
          array (
            'clave' => 'Longitud',
            'valor' => '3 m',
          ),
          3 => 
          array (
            'clave' => 'Blindaje',
            'valor' => 'UTP, sin blindaje',
          ),
          4 => 
          array (
            'clave' => 'Uso',
            'valor' => 'Conexión Ethernet entre equipos y puertos de red',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Patch cord para redes Gigabit',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  80 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-1',
    'nombre' => 'SanDisk Extreme Portable SSD USB 3.2 Gen 2',
    'marca' => 'SanDisk',
    'modelo' => 'Extreme Portable SSD 1 TB, SDSSDE61-1T00-Z25B',
    'precio' => 109.0,
    'precio_oferta' => 99,
    'stock' => 28,
    'destacado' => true,
    'desc_corta' => 'Carcasa engomada resistente a caídas de hasta 2 metros con protección IP55 contra lluvia y polvo.',
    'desc' => 'El disco duro de bolsillo favorito de fotógrafos y viajeros. Su práctico mosquetón permite engancharlo a la mochila mientras caminas. Velocidades NVMe de hasta 1050 MB/s de lectura.',
    'peso' => 0.05,
    'largo' => 10.08,
    'ancho' => 5.255,
    'alto' => 0.96,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '1 TB',
          ),
          1 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'USB 3.2 Gen 2, 10 Gb/s',
          ),
          2 => 
          array (
            'clave' => 'Conector',
            'valor' => 'USB-C',
          ),
          3 => 
          array (
            'clave' => 'Lectura',
            'valor' => 'Hasta 1050 MB/s',
          ),
          4 => 
          array (
            'clave' => 'Escritura',
            'valor' => 'Hasta 1000 MB/s',
          ),
          5 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP65 en la revisión seleccionada',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Carcasa de silicona y protección contra caídas de hasta 3 m',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'EXT-21-1-V1',
        'precio' => 109,
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
        'precio' => 169,
        'stock' => 12,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '2 TB',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  81 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-2',
    'nombre' => 'Samsung T7 Shield Portable SSD Resistente',
    'marca' => 'Samsung',
    'modelo' => 'T7 Shield 2 TB, negro',
    'precio' => 179.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Exterior de elastómero de alta tecnología resistente a golpes, agua y polvo con certificación IP65.',
    'desc' => 'Protección térmica avanzada Dynamic Thermal Guard para evitar caídas de velocidad por sobrecalentamiento al transferir archivos gigantes de video.',
    'peso' => 0.098,
    'largo' => 8.8,
    'ancho' => 5.9,
    'alto' => 1.3,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '2 TB',
          ),
          1 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'USB 3.2 Gen 2, 10 Gb/s',
          ),
          2 => 
          array (
            'clave' => 'Conector',
            'valor' => 'USB-C',
          ),
          3 => 
          array (
            'clave' => 'Lectura',
            'valor' => 'Hasta 1050 MB/s',
          ),
          4 => 
          array (
            'clave' => 'Escritura',
            'valor' => 'Hasta 1000 MB/s',
          ),
          5 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP65',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Protección contra caídas de hasta 3 m y cifrado AES de 256 bits',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  82 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-3',
    'nombre' => 'Western Digital My Passport 2TB HDD USB 3.0',
    'marca' => 'Western Digital',
    'modelo' => 'My Passport HDD 2 TB, edición de 13.5 mm',
    'precio' => 69.0,
    'precio_oferta' => NULL,
    'stock' => 19,
    'destacado' => false,
    'desc_corta' => 'Disco duro externo confiable y estilizado con software de respaldo automático y contraseña.',
    'desc' => 'El clásico indiscutible para copias de seguridad de fotos familiares, películas y documentos importantes. Compatible de fábrica con Windows y Mac.',
    'peso' => 0.14,
    'largo' => 10.72,
    'ancho' => 7.5,
    'alto' => 1.35,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '2 TB',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Disco duro portátil',
          ),
          2 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'USB 3.2 Gen 1/USB 3.0',
          ),
          3 => 
          array (
            'clave' => 'Conector del cable al PC',
            'valor' => 'USB-A',
          ),
          4 => 
          array (
            'clave' => 'Alimentación',
            'valor' => 'Mediante USB',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Software de respaldo y protección mediante contraseña',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  83 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-4',
    'nombre' => 'Seagate Expansion 4TB Disco Externo',
    'marca' => 'Seagate',
    'modelo' => 'Expansion Portable 4 TB',
    'precio' => 99.0,
    'precio_oferta' => 89,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Almacenamiento masivo de 4 Terabytes sin necesidad de adaptador de corriente adicional.',
    'desc' => 'Almacena miles de horas de series, juegos de consola o archivos de diseño. Simplemente conecta el cable USB y arrastra los archivos para guardarlos al instante.',
    'peso' => 0.257,
    'largo' => 11.53,
    'ancho' => 8.0,
    'alto' => 2.09,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '4 TB',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'HDD portátil',
          ),
          2 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'USB 3.0',
          ),
          3 => 
          array (
            'clave' => 'Alimentación',
            'valor' => 'Mediante USB',
          ),
          4 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'Reformateo según sistema operativo y uso',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Almacenamiento externo portátil sin adaptador de CA',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  84 => 
  array (
    'cat' => 'almacenamiento',
    'sku' => 'EXT-21-5',
    'nombre' => 'Crucial X9 Pro 1TB Portable SSD 1050MB/s',
    'marca' => 'Crucial',
    'modelo' => 'X9 Pro 1 TB, CT1000X9PROSSD9',
    'precio' => 89.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Tamaño ultracompacto menor al de una tarjeta de crédito con cuerpo de aluminio anodizado.',
    'desc' => 'Elegante y discreto en la palma de tu mano. Resistencia al agua y polvo IP55 con indicador LED de actividad discreto en la ranura de cuerda.',
    'peso' => 0.038,
    'largo' => 6.5,
    'ancho' => 5.0,
    'alto' => 1.0,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '1 TB',
          ),
          1 => 
          array (
            'clave' => 'Interfaz',
            'valor' => 'USB 3.2 Gen 2',
          ),
          2 => 
          array (
            'clave' => 'Conector',
            'valor' => 'USB-C',
          ),
          3 => 
          array (
            'clave' => 'Lectura y escritura',
            'valor' => 'Hasta 1050 MB/s',
          ),
          4 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP55',
          ),
          5 => 
          array (
            'clave' => 'Cifrado',
            'valor' => 'AES de 256 bits',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Carcasa compacta con resistencia a caídas de hasta 2 m',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  85 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-1',
    'nombre' => 'Anker 735 GaNPrime 65W Cargador 3 Puertos',
    'marca' => 'Anker',
    'modelo' => '735 Charger GaNPrime 65W, A2668, enchufe US',
    'precio' => 49.0,
    'precio_oferta' => NULL,
    'stock' => 22,
    'destacado' => true,
    'desc_corta' => 'Tecnología GaNPrime más pequeña que un cargador de laptop tradicional para cargar 3 dispositivos a la vez.',
    'desc' => 'Equipado con 2 puertos USB-C y 1 puerto USB-A. Su tecnología PowerIQ 4.0 detecta dinámicamente las necesidades de energía de cada equipo para distribuir los 65W inteligentemente.',
    'peso' => 0.132,
    'largo' => 3.8,
    'ancho' => 2.9,
    'alto' => 6.6,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Puertos',
            'valor' => '2 USB-C y 1 USB-A',
          ),
          1 => 
          array (
            'clave' => 'Potencia total',
            'valor' => '65 W',
          ),
          2 => 
          array (
            'clave' => 'USB-C individual',
            'valor' => 'Hasta 65 W',
          ),
          3 => 
          array (
            'clave' => 'USB-A individual',
            'valor' => 'Hasta 22.5 W',
          ),
          4 => 
          array (
            'clave' => 'Tecnología',
            'valor' => 'GaNPrime, PowerIQ 4.0 y PPS',
          ),
          5 => 
          array (
            'clave' => 'Entrada',
            'valor' => '100–240 V CA',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Distribución inteligente de potencia',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  86 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-2',
    'nombre' => 'Belkin BoostCharge Pro 3 en 1 MagSafe 15W',
    'marca' => 'Belkin',
    'modelo' => 'BoostCharge Pro 3-in-1 MagSafe, WIZ017',
    'precio' => 129.0,
    'precio_oferta' => NULL,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Estación de carga inalámbrica rápida oficial de 15W para iPhone, Apple Watch y AirPods simultáneamente.',
    'desc' => 'Diseño arquitectónico con acabados cromados y base pesada que no se desliza en tu mesa de noche. Carga rápida oficial para Apple Watch Series 7 en adelante.',
    'peso' => 0.5,
    'largo' => 13.373,
    'ancho' => 13.373,
    'alto' => 14.364,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Base inalámbrica de 3 dispositivos',
          ),
          1 => 
          array (
            'clave' => 'Carga de iPhone',
            'valor' => 'MagSafe hasta 15 W',
          ),
          2 => 
          array (
            'clave' => 'Carga adicional',
            'valor' => 'Apple Watch y AirPods compatibles',
          ),
          3 => 
          array (
            'clave' => 'Alimentación',
            'valor' => 'Adaptador externo incluido según presentación',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'Soporte vertical y carga simultánea',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  87 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-3',
    'nombre' => 'Apple Adaptador de Corriente USB-C de 20W',
    'marca' => 'Apple',
    'modelo' => 'Adaptador de corriente USB-C de 20 W, enchufe US',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => '20 W',
          ),
          1 => 
          array (
            'clave' => 'Puerto',
            'valor' => '1 USB-C',
          ),
          2 => 
          array (
            'clave' => 'Tecnología',
            'valor' => 'USB Power Delivery',
          ),
          3 => 
          array (
            'clave' => 'Carga rápida',
            'valor' => 'Disponible en iPhone compatibles',
          ),
          4 => 
          array (
            'clave' => 'Contenido',
            'valor' => 'Adaptador, cable no incluido',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Fuente de alimentación compacta para dispositivos USB-C',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  88 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-4',
    'nombre' => 'Samsung 45W Super Fast Wall Charger USB-C',
    'marca' => 'Samsung',
    'modelo' => 'Cargador 45 W, EP-T4510XBEGCA',
    'precio' => 34.0,
    'precio_oferta' => 29,
    'stock' => 20,
    'destacado' => false,
    'desc_corta' => 'Carga Super Rápida 2.0 oficial para Galaxy S24 Ultra, S23 Ultra y tablets Galaxy Tab.',
    'desc' => 'Incluye cable USB-C a USB-C de 5A de alta capacidad. Compatible con el estándar universal USB Power Delivery PPS para alimentar también laptops y otras marcas.',
    'peso' => 0.0922,
    'largo' => 2.8,
    'ancho' => 4.35,
    'alto' => 6.37,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => 'Hasta 45 W',
          ),
          1 => 
          array (
            'clave' => 'Puerto',
            'valor' => 'USB-C',
          ),
          2 => 
          array (
            'clave' => 'Protocolos',
            'valor' => 'USB PD 3.0 PDO/PPS y Super Fast Charging 2.0',
          ),
          3 => 
          array (
            'clave' => 'Entrada',
            'valor' => '100–240 V',
          ),
          4 => 
          array (
            'clave' => 'Cable',
            'valor' => 'USB-C a USB-C de 5 A, 1.8 m incluido',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Carga rápida para equipos Samsung compatibles',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  89 => 
  array (
    'cat' => 'cargadores',
    'sku' => 'CHG-23-5',
    'nombre' => 'Xiaomi 67W GaN Fast Charger Combo',
    'marca' => 'Xiaomi',
    'modelo' => 'Xiaomi 67W HyperCharge Power Adapter Type-A, MDY-12-EH',
    'precio' => 29.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Cargador de nitruro de galio ultracompacto de 67W capaz de cargar laptops y smartphones.',
    'desc' => 'Alta potencia en tamaño reducido. Control térmico inteligente de temperatura constante y materiales resistentes al fuego de alta graduación.',
    'peso' => 0.09,
    'largo' => 7.3,
    'ancho' => 4.93,
    'alto' => 2.8,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Potencia',
            'valor' => 'Hasta 67 W',
          ),
          1 => 
          array (
            'clave' => 'Puerto',
            'valor' => 'USB-A',
          ),
          2 => 
          array (
            'clave' => 'Entrada',
            'valor' => '100–240 V CA',
          ),
          3 => 
          array (
            'clave' => 'Carga normal',
            'valor' => '5 V/3 A',
          ),
          4 => 
          array (
            'clave' => 'Salida rápida',
            'valor' => '5–20 V, hasta 67 W',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Identificación inteligente de dispositivo y compatibilidad de carga rápida',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  90 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-1',
    'nombre' => 'Anker 737 Power Bank 24000mAh 140W',
    'marca' => 'Anker',
    'modelo' => '737 Power Bank PowerCore 24K, A1289',
    'precio' => 119.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => true,
    'desc_corta' => 'Pantalla digital a color que muestra vatios de entrada/salida y tiempo restante de batería.',
    'desc' => 'El power bank más avanzado del mercado. Entrega hasta 140W de potencia para cargar laptops de alta gama mientras cargas dos celulares al mismo tiempo.',
    'peso' => 0.63,
    'largo' => 15.57,
    'ancho' => 5.46,
    'alto' => 4.95,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad nominal',
            'valor' => '24000 mAh',
          ),
          1 => 
          array (
            'clave' => 'Potencia',
            'valor' => 'Hasta 140 W',
          ),
          2 => 
          array (
            'clave' => 'Puertos',
            'valor' => '2 USB-C y 1 USB-A',
          ),
          3 => 
          array (
            'clave' => 'Tecnología',
            'valor' => 'USB PD 3.1',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'Carga bidireccional y pantalla digital',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  91 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-2',
    'nombre' => 'Xiaomi 50W Power Bank 20000mAh',
    'marca' => 'Xiaomi',
    'modelo' => 'Mi 50W Power Bank 20000, PB200SZM',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad de celdas',
            'valor' => '20000 mAh, 74 Wh',
          ),
          1 => 
          array (
            'clave' => 'Capacidad nominal a la salida',
            'valor' => '12000 mAh a 5 V/4 A',
          ),
          2 => 
          array (
            'clave' => 'Potencia de salida',
            'valor' => 'Hasta 50 W',
          ),
          3 => 
          array (
            'clave' => 'Entrada',
            'valor' => 'USB-C, hasta 45 W',
          ),
          4 => 
          array (
            'clave' => 'Puertos',
            'valor' => 'USB-C y USB-A',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Carga de múltiples dispositivos y modo de baja corriente',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  92 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-3',
    'nombre' => 'Anker Nano Power Bank 5000mAh USB-C',
    'marca' => 'Anker',
    'modelo' => 'Nano Power Bank 5000 mAh, A1653',
    'precio' => 25.0,
    'precio_oferta' => NULL,
    'stock' => 32,
    'destacado' => true,
    'desc_corta' => 'Conector USB-C plegable integrado: se conecta directamente a la base de tu celular sin cables.',
    'desc' => 'Cabe en cualquier bolsillo de pantalón. Olvídate de llevar cables enredados. Simplemente despliega el conector USB-C, conéctalo a tu smartphone y sigue usándolo con normalidad.',
    'peso' => 0.1,
    'largo' => 7.6962,
    'ancho' => 3.683,
    'alto' => 2.4892,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '5000 mAh',
          ),
          1 => 
          array (
            'clave' => 'Potencia',
            'valor' => 'Hasta 22.5 W',
          ),
          2 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Conector USB-C integrado plegable y puerto USB-C',
          ),
          3 => 
          array (
            'clave' => 'Características',
            'valor' => 'Formato compacto para carga de teléfonos compatibles',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'PWR-24-3-V1',
        'precio' => 25,
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
        'precio' => 25,
        'stock' => 14,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  93 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-4',
    'nombre' => 'Belkin BoostCharge Power Bank 10000mAh',
    'marca' => 'Belkin',
    'modelo' => 'BoostCharge Power Bank 10K, BPB011',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '10000 mAh',
          ),
          1 => 
          array (
            'clave' => 'Potencia total',
            'valor' => 'Hasta 15 W',
          ),
          2 => 
          array (
            'clave' => 'Puertos',
            'valor' => '1 USB-C y 2 USB-A',
          ),
          3 => 
          array (
            'clave' => 'Carga simultánea',
            'valor' => 'Hasta 3 dispositivos',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'Indicadores LED de batería',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  94 => 
  array (
    'cat' => 'power-banks',
    'sku' => 'PWR-24-5',
    'nombre' => 'Samsung 25W Power Bank 10000mAh Batería',
    'marca' => 'Samsung',
    'modelo' => 'Battery Pack 10000 mAh 25W, EB-P3400',
    'precio' => 39.0,
    'precio_oferta' => 34,
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Capacidad',
            'valor' => '10000 mAh',
          ),
          1 => 
          array (
            'clave' => 'Potencia',
            'valor' => 'Hasta 25 W',
          ),
          2 => 
          array (
            'clave' => 'Puertos',
            'valor' => '2 USB-C',
          ),
          3 => 
          array (
            'clave' => 'Protocolos',
            'valor' => 'USB PD 3.0 PDO/PPS y Samsung SFC',
          ),
          4 => 
          array (
            'clave' => 'Carga inalámbrica',
            'valor' => 'No',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Carga de dos dispositivos y diseño portátil',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  95 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-1',
    'nombre' => 'Apple Funda de Silicona con MagSafe iPhone 15',
    'marca' => 'Apple',
    'modelo' => 'Funda de silicona MagSafe para iPhone 15, negro',
    'precio' => 49.0,
    'precio_oferta' => NULL,
    'stock' => 25,
    'destacado' => true,
    'desc_corta' => 'Tacto exterior sedoso y forro interior de microfibra suave que abraza y protege el iPhone.',
    'desc' => 'Imanes perfectamente alineados que hacen que la carga inalámbrica MagSafe sea más rápida y cómoda. Tratamiento oleofóbico que repele huellas dactilares y suciedad.',
    'peso' => 0.05,
    'largo' => 15,
    'ancho' => 7.5,
    'alto' => 1.2,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'iPhone 15 de 6.1 pulgadas',
          ),
          1 => 
          array (
            'clave' => 'Material exterior',
            'valor' => 'Silicona',
          ),
          2 => 
          array (
            'clave' => 'Interior',
            'valor' => 'Microfibra',
          ),
          3 => 
          array (
            'clave' => 'MagSafe',
            'valor' => 'Imanes integrados',
          ),
          4 => 
          array (
            'clave' => 'Características',
            'valor' => 'Protección del cuerpo y compatibilidad con carga inalámbrica',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CS-25-1-V1',
        'precio' => 49,
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
        'precio' => 49,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Azul',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  96 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-2',
    'nombre' => 'Samsung Funda Standing Grip Galaxy S24 Ultra',
    'marca' => 'Samsung',
    'modelo' => 'Standing Grip Case Galaxy S24 Ultra, EF-GS928C',
    'precio' => 39.0,
    'precio_oferta' => NULL,
    'stock' => 18,
    'destacado' => false,
    'desc_corta' => 'Correa de sujeción trasera que funciona también como soporte de pie para ver videos en horizontal.',
    'desc' => 'Sujeta tu smartphone con total seguridad con una sola mano pasando los dedos por la correa flexible integrada, o despliégala para apoyarlo en la mesa.',
    'peso' => 0.056,
    'largo' => 1.28,
    'ancho' => 8.35,
    'alto' => 16.61,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'Galaxy S24 Ultra',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Funda con correa de agarre y soporte',
          ),
          2 => 
          array (
            'clave' => 'Material',
            'valor' => 'Acabado de silicona',
          ),
          3 => 
          array (
            'clave' => 'Características',
            'valor' => 'Soporte para visualización horizontal y agarre posterior',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  97 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-3',
    'nombre' => 'Belkin Protector de Pantalla UltraGlass 2',
    'marca' => 'Belkin',
    'modelo' => 'UltraGlass 2 para iPhone 15 Pro Max, OVA130dsAPL',
    'precio' => 29.0,
    'precio_oferta' => NULL,
    'stock' => 30,
    'destacado' => false,
    'desc_corta' => 'Vidrio de silicato de litio hasta 2.7 veces más resistente que el vidrio templado convencional.',
    'desc' => 'Grosor ultrafino de apenas 0.29mm que conserva la sensibilidad táctil original de la pantalla al 100%. Incluye bandeja Easy Align para una instalación perfecta sin burbujas.',
    'peso' => 0.03,
    'largo' => 14.5,
    'ancho' => 7,
    'alto' => 0.03,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'iPhone 15 Pro Max',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Protector de pantalla',
          ),
          2 => 
          array (
            'clave' => 'Material',
            'valor' => 'Vidrio UltraGlass 2',
          ),
          3 => 
          array (
            'clave' => 'Características',
            'valor' => 'Protección de pantalla y conservación de sensibilidad táctil',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  98 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-4',
    'nombre' => 'Anker Protector de Lente de Cámara Tempered Glass',
    'marca' => 'Anker',
    'modelo' => 'Protector de lente atribuido a Anker, modelo no confirmado',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo declarado',
            'valor' => 'Protector de lentes de cámara',
          ),
          1 => 
          array (
            'clave' => 'Marca declarada',
            'valor' => 'Anker',
          ),
          2 => 
          array (
            'clave' => 'Modelo',
            'valor' => 'No identificado',
          ),
          3 => 
          array (
            'clave' => 'Compatibilidad',
            'valor' => 'Teléfono no indicado',
          ),
          4 => 
          array (
            'clave' => 'Estado',
            'valor' => 'Sin ficha oficial verificable',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  99 => 
  array (
    'cat' => 'fundas-y-protectores',
    'sku' => 'CS-25-5',
    'nombre' => 'Apple Funda Smart Folio para iPad Air',
    'marca' => 'Apple',
    'modelo' => 'Smart Folio para iPad Air 11 pulgadas M2, gris carbón',
    'precio' => 79.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Funda magnética frontal y trasera que activa y suspende la pantalla al abrirla o cerrarla.',
    'desc' => 'Se pliega en diferentes posiciones para crear un soporte para leer, ver videos, escribir con el teclado o dibujar con el Apple Pencil con comodidad.',
    'peso' => 0.2,
    'largo' => 25,
    'ancho' => 18,
    'alto' => 1.1,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Compatibilidad seleccionada',
            'valor' => 'iPad Air de 11 pulgadas M2',
          ),
          1 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Funda magnética frontal y posterior',
          ),
          2 => 
          array (
            'clave' => 'Función',
            'valor' => 'Activación al abrir y reposo al cerrar',
          ),
          3 => 
          array (
            'clave' => 'Características',
            'valor' => 'Soporte plegable con varios ángulos',
          ),
          4 => 
          array (
            'clave' => 'Contenido',
            'valor' => 'Smart Folio, iPad no incluido',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  100 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-1',
    'nombre' => 'Apple iPhone 15 Pro Max Titanio',
    'marca' => 'Apple',
    'modelo' => 'iPhone 15 Pro Max, 256 GB, titanio natural',
    'precio' => 1199.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => true,
    'desc_corta' => 'Estructura de titanio aeroespacial, chip A17 Pro, botón de Acción y teleobjetivo de 5 aumentos.',
    'desc' => 'El iPhone más ligero y potente de la historia. Integra el revolucionario chip A17 Pro con trazado de rayos por hardware para juegos de consola. Su cámara de 48MP ofrece 7 distancias focales profesionales y transferencia USB 3 a 10Gbps.',
    'peso' => 0.221,
    'largo' => 15.99,
    'ancho' => 7.67,
    'alto' => 0.825,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Apple A17 Pro',
          ),
          1 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '256 GB',
          ),
          2 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '6.7 pulgadas OLED Super Retina XDR, 2796 × 1290, ProMotion hasta 120 Hz',
          ),
          3 => 
          array (
            'clave' => 'Cámara trasera',
            'valor' => 'Principal 48 MP, ultra gran angular 12 MP y teleobjetivo 12 MP de 5×',
          ),
          4 => 
          array (
            'clave' => 'Cámara frontal',
            'valor' => '12 MP TrueDepth',
          ),
          5 => 
          array (
            'clave' => 'Conexión',
            'valor' => '5G, Wi-Fi 6E, Bluetooth 5.3 y USB-C USB 3',
          ),
          6 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP68, condiciones de laboratorio',
          ),
          7 => 
          array (
            'clave' => 'Seguridad',
            'valor' => 'Face ID',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Botón de acción y estructura de titanio',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'PH-27-1-V1',
        'precio' => 1199,
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
        'precio' => 1399,
        'stock' => 6,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '512 GB',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  101 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-2',
    'nombre' => 'Samsung Galaxy S24 Ultra 5G con Galaxy AI',
    'marca' => 'Samsung',
    'modelo' => 'Galaxy S24 Ultra, SM-S928B, 12 GB/256 GB',
    'precio' => 1299.0,
    'precio_oferta' => 1199,
    'stock' => 12,
    'destacado' => true,
    'desc_corta' => 'Pantalla plana Dynamic AMOLED 2X de 2600 nits, marco de titanio, S Pen y Galaxy AI integrado.',
    'desc' => 'La era de la Inteligencia Artificial móvil. Traduce llamadas en vivo, rodea para buscar en Google y edita fotos mágicamente. Equipado con cámara de 200MP y cristal Corning Gorilla Armor antirreflejo.',
    'peso' => 0.232,
    'largo' => 16.23,
    'ancho' => 7.9,
    'alto' => 0.86,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Snapdragon 8 Gen 3 for Galaxy',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '12 GB',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '256 GB',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '6.8 pulgadas Dynamic AMOLED 2X, 3120 × 1440, hasta 120 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámaras posteriores',
            'valor' => '200 MP + 50 MP + 12 MP + 10 MP',
          ),
          5 => 
          array (
            'clave' => 'Cámara frontal',
            'valor' => '12 MP',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '5000 mAh típica',
          ),
          7 => 
          array (
            'clave' => 'Carga por cable',
            'valor' => 'Hasta 45 W',
          ),
          8 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP68',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'S Pen, Galaxy AI y Samsung DeX',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  102 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-3',
    'nombre' => 'Google Pixel 8 Pro 128GB Obsidian 5G',
    'marca' => 'Google',
    'modelo' => 'Pixel 8 Pro, 128 GB, Obsidian',
    'precio' => 799.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Procesador Google Tensor G3, las mejores cámaras computacionales del mundo y 7 años de soporte.',
    'desc' => 'Experiencia Android pura con la mejor fotografía móvil. Funciones exclusivas como Mejor Toma (Best Take), Editor Mágico y Borrador Mágico para Audio.',
    'peso' => 0.213,
    'largo' => 16.26,
    'ancho' => 7.65,
    'alto' => 0.88,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Google Tensor G3',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '12 GB LPDDR5X',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '128 GB UFS 3.1',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '6.7 pulgadas LTPO OLED, 2992 × 1344, 1–120 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámaras posteriores',
            'valor' => '50 MP + 48 MP ultra gran angular + 48 MP teleobjetivo',
          ),
          5 => 
          array (
            'clave' => 'Cámara frontal',
            'valor' => '10.5 MP',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '5050 mAh típica',
          ),
          7 => 
          array (
            'clave' => 'Conectividad',
            'valor' => '5G, Wi-Fi 7 y Bluetooth 5.3',
          ),
          8 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP68',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'Actualizaciones durante 7 años desde lanzamiento',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  103 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-4',
    'nombre' => 'Xiaomi 14 Ultra 512GB Óptica Leica',
    'marca' => 'Xiaomi',
    'modelo' => 'Xiaomi 14 Ultra global, 16 GB/512 GB, negro',
    'precio' => 1099.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Sensor de 1 pulgada con apertura variable continua, óptica Leica Summilux y 16GB de RAM.',
    'desc' => 'Una cámara profesional que también es un smartphone. 4 sensores de 50MP desarrollados junto a Leica para una calidad cinematográfica superior en foto y video.',
    'peso' => 0.2198,
    'largo' => 16.14,
    'ancho' => 7.53,
    'alto' => 0.92,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Snapdragon 8 Gen 3',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB LPDDR5X',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '512 GB UFS 4.0',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '6.73 pulgadas LTPO AMOLED 3200 × 1440, 1–120 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámaras',
            'valor' => 'Cuatro cámaras Leica de 50 MP',
          ),
          5 => 
          array (
            'clave' => 'Cámara principal',
            'valor' => 'Sensor LYT-900 de 1 pulgada, apertura variable f/1.63–f/4.0',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '5000 mAh típica, versión global',
          ),
          7 => 
          array (
            'clave' => 'Carga',
            'valor' => '90 W por cable y 80 W inalámbrica',
          ),
          8 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP68',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'Óptica Leica y teleobjetivos de 75 y 120 mm',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  104 => 
  array (
    'cat' => 'smartphones',
    'sku' => 'PH-27-5',
    'nombre' => 'OnePlus 12 512GB Snapdragon 8 Gen 3',
    'marca' => 'OnePlus',
    'modelo' => 'OnePlus 12 global, 16 GB/512 GB',
    'precio' => 799.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Pantalla 2K ProXDR de 4500 nits, carga ultrarrápida SUPERVOOC de 80W y cámara Hasselblad.',
    'desc' => 'Velocidad que impresiona. Sistema de refrigeración Dual Cryo-velocity VC para que el teléfono se mantenga frío incluso en las sesiones de juego más exigentes.',
    'peso' => 0.22,
    'largo' => 16.43,
    'ancho' => 7.58,
    'alto' => 0.915,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Snapdragon 8 Gen 3',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB LPDDR5X',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '512 GB UFS 4.0',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '6.82 pulgadas LTPO, 3168 × 1440, 1–120 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámaras posteriores',
            'valor' => '50 MP + 48 MP + 64 MP',
          ),
          5 => 
          array (
            'clave' => 'Cámara frontal',
            'valor' => '32 MP',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '5400 mAh',
          ),
          7 => 
          array (
            'clave' => 'Carga',
            'valor' => 'Hasta 100 W SUPERVOOC y 50 W AIRVOOC en referencia europea',
          ),
          8 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP65',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'Sistema de cámara Hasselblad',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  105 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-1',
    'nombre' => 'Apple iPad Pro 11" M4 Pantalla Ultra Retina',
    'marca' => 'Apple',
    'modelo' => 'iPad Pro 11 pulgadas M4 (2024), Wi-Fi, 256 GB',
    'precio' => 999.0,
    'precio_oferta' => NULL,
    'stock' => 13,
    'destacado' => true,
    'desc_corta' => 'El dispositivo Apple más delgado de la historia: chip M4 con pantalla OLED en tándem de 1000 nits.',
    'desc' => 'Potencia descomunal para ilustradores, editores y diseñadores. El chip M4 con motor neuronal de 38 billones de operaciones por segundo vuela en tareas de inteligencia artificial.',
    'peso' => 0.444,
    'largo' => 24.97,
    'ancho' => 17.75,
    'alto' => 0.53,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Apple M4, CPU de 9 núcleos y GPU de 10 núcleos en 256 GB',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '8 GB',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '256 GB',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'Ultra Retina XDR Tandem OLED, 2420 × 1668, ProMotion 10–120 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámara',
            'valor' => 'Trasera 12 MP y frontal ultra gran angular 12 MP',
          ),
          5 => 
          array (
            'clave' => 'Puerto',
            'valor' => 'Thunderbolt/USB 4 USB-C',
          ),
          6 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi 6E y Bluetooth 5.3',
          ),
          7 => 
          array (
            'clave' => 'Seguridad',
            'valor' => 'Face ID',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Compatible con Apple Pencil Pro y Apple Pencil USB-C',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'TAB-28-1-V1',
        'precio' => 999,
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
        'precio' => 1199,
        'stock' => 5,
        'opcion' => 
        array (
          0 => 'Capacidad de almacenamiento',
          1 => '512 GB',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  106 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-2',
    'nombre' => 'Samsung Galaxy Tab S9 Ultra 256GB 14.6"',
    'marca' => 'Samsung',
    'modelo' => 'Galaxy Tab S9 Ultra Wi-Fi 256 GB, SM-X910',
    'precio' => 1049.0,
    'precio_oferta' => 999,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Pantalla gigante Dynamic AMOLED 2X de 14.6", resistencia al agua IP68 y S Pen incluido en caja.',
    'desc' => 'Reemplaza a tu laptop con Samsung DeX multiventana. Única tablet de su tamaño con certificación IP68 para resistir accidentes con líquidos en el escritorio.',
    'peso' => 0.732,
    'largo' => 32.64,
    'ancho' => 20.86,
    'alto' => 0.55,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Snapdragon 8 Gen 2 for Galaxy',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '12 GB',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '256 GB y expansión microSD',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '14.6 pulgadas Dynamic AMOLED 2X, 2960 × 1848, 120 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámaras traseras',
            'valor' => '13 MP + 8 MP',
          ),
          5 => 
          array (
            'clave' => 'Cámaras frontales',
            'valor' => '12 MP + 12 MP',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '11200 mAh típica',
          ),
          7 => 
          array (
            'clave' => 'Carga',
            'valor' => 'Hasta 45 W',
          ),
          8 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP68',
          ),
          9 => 
          array (
            'clave' => 'Características',
            'valor' => 'S Pen incluido y Samsung DeX',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  107 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-3',
    'nombre' => 'Xiaomi Pad 6 128GB 11" 144Hz WQHD+',
    'marca' => 'Xiaomi',
    'modelo' => 'Xiaomi Pad 6, 6 GB/128 GB, gris grafito',
    'precio' => 349.0,
    'precio_oferta' => NULL,
    'stock' => 16,
    'destacado' => false,
    'desc_corta' => 'Increíble pantalla 2.8K fluida de 144Hz, cuerpo de aluminio unibody y 4 altavoces Dolby Atmos.',
    'desc' => 'La mejor tablet de gama media para estudiantes y entretenimiento multimedia. Batería de 8840mAh para ver series y trabajar durante todo el día sin recargar.',
    'peso' => 0.49,
    'largo' => 25.395,
    'ancho' => 16.518,
    'alto' => 0.651,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Snapdragon 870',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '6 GB LPDDR5',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '128 GB UFS 3.1',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '11 pulgadas LCD 2880 × 1800, hasta 144 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámara trasera/frontal',
            'valor' => '13 MP/8 MP',
          ),
          5 => 
          array (
            'clave' => 'Batería',
            'valor' => '8840 mAh típica',
          ),
          6 => 
          array (
            'clave' => 'Carga',
            'valor' => '33 W',
          ),
          7 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Cuatro altavoces Dolby Atmos',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Wi-Fi, sin conexión celular',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  108 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-4',
    'nombre' => 'Lenovo Tab P11 Pro Gen 2 128GB OLED',
    'marca' => 'Lenovo',
    'modelo' => 'Tab P11 Pro Gen 2, 6 GB/128 GB',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'MediaTek Kompanio 1300T',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '6 GB',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '128 GB con expansión microSD',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '11.2 pulgadas OLED, 2560 × 1536, 120 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámara trasera/frontal',
            'valor' => '13 MP/8 MP',
          ),
          5 => 
          array (
            'clave' => 'Batería',
            'valor' => '8200 mAh típica',
          ),
          6 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Cuatro altavoces JBL con Dolby Atmos',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Dolby Vision y HDR10+',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  109 => 
  array (
    'cat' => 'tablets',
    'sku' => 'TAB-28-5',
    'nombre' => 'Huawei MatePad 11.5" PaperMatte Edition',
    'marca' => 'Huawei',
    'modelo' => 'MatePad 11.5 PaperMatte Edition (2023), 8 GB/256 GB',
    'precio' => 299.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Pantalla antirreflejo con textura de papel que elimina el 97% de los brillos para lectura y dibujo.',
    'desc' => 'Escribe y dibuja como si fuera un cuaderno de papel real gracias a su grabado nanométrico que simula la fricción del grafito. Cuida la vista en largas lecturas.',
    'peso' => 0.499,
    'largo' => 26.088,
    'ancho' => 17.682,
    'alto' => 0.685,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Qualcomm Snapdragon 7 Gen 1',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '8 GB',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '256 GB',
          ),
          3 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '11.5 pulgadas PaperMatte 2200 × 1440, hasta 120 Hz',
          ),
          4 => 
          array (
            'clave' => 'Cámara trasera/frontal',
            'valor' => '13 MP/8 MP',
          ),
          5 => 
          array (
            'clave' => 'Batería',
            'valor' => '7700 mAh típica',
          ),
          6 => 
          array (
            'clave' => 'Carga',
            'valor' => '22.5 W',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Pantalla antirreflejos y HarmonyOS',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  110 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-1',
    'nombre' => 'Apple Watch Series 9 GPS 45mm',
    'marca' => 'Apple',
    'modelo' => 'Watch Series 9 GPS, aluminio, 45 mm',
    'precio' => 399.0,
    'precio_oferta' => NULL,
    'stock' => 21,
    'destacado' => true,
    'desc_corta' => 'Gesto de Doble Toque, pantalla con el doble de brillo de 2000 nits y chip S9 SiP ultrarrápido.',
    'desc' => 'Controla tu reloj sin tocar la pantalla simplemente juntando el dedo índice y el pulgar dos veces. Sensor de temperatura, medición de oxígeno en sangre y electrocardiograma (ECG).',
    'peso' => 0.0387,
    'largo' => 4.5,
    'ancho' => 3.8,
    'alto' => 1.07,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Chip',
            'valor' => 'Apple S9 SiP',
          ),
          1 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'Retina LTPO OLED siempre activa, hasta 2000 nits',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '64 GB',
          ),
          3 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'GPS, Wi-Fi y Bluetooth 5.3',
          ),
          4 => 
          array (
            'clave' => 'Resistencia',
            'valor' => 'Agua hasta 50 m e IP6X',
          ),
          5 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 18 horas, hasta 36 en bajo consumo',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Digital Crown y gesto de doble toque',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'WAT-29-1-V1',
        'precio' => 399,
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
        'precio' => 399,
        'stock' => 9,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Plateado',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  111 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-2',
    'nombre' => 'Samsung Galaxy Watch 6 Classic 47mm LTE',
    'marca' => 'Samsung',
    'modelo' => 'Galaxy Watch6 Classic LTE 47 mm, SM-R965F',
    'precio' => 369.0,
    'precio_oferta' => 339,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Bisel giratorio físico icónico, cristal de zafiro y análisis de composición corporal BIA.',
    'desc' => 'Navega por tus aplicaciones girando su bisel de acero inoxidable con respuesta táctil mecánica. Monitorea tu porcentaje de grasa corporal, masa muscular y calidad de sueño.',
    'peso' => 0.059,
    'largo' => 4.65,
    'ancho' => 4.65,
    'alto' => 1.09,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Exynos W930',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '2 GB RAM y 16 GB almacenamiento',
          ),
          2 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '1.5 pulgadas Super AMOLED, 480 × 480',
          ),
          3 => 
          array (
            'clave' => 'Batería',
            'valor' => '425 mAh típica',
          ),
          4 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'LTE/eSIM, Bluetooth 5.3, Wi-Fi y NFC',
          ),
          5 => 
          array (
            'clave' => 'Protección',
            'valor' => '5 ATM e IP68',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Bisel giratorio y Wear OS',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  112 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-3',
    'nombre' => 'Garmin Fenix 7 Pro Sapphire Solar GPS Titán',
    'marca' => 'Garmin',
    'modelo' => 'fēnix 7 Pro Sapphire Solar, 47 mm, titanio',
    'precio' => 799.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => true,
    'desc_corta' => 'Lente de carga solar que extiende la batería hasta 22 días, linterna LED y mapas topográficos.',
    'desc' => 'El reloj de aventura multideporte definitivo. Caja de polímero reforzado con bisel y tapa trasera de titanio. Mide métricas de entrenamiento avanzadas, pendientes en subida y resistencia en carrera.',
    'peso' => 0.073,
    'largo' => 4.7,
    'ancho' => 4.7,
    'alto' => 1.45,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '1.3 pulgadas MIP, 260 × 260',
          ),
          1 => 
          array (
            'clave' => 'Cristal',
            'valor' => 'Power Sapphire con carga solar',
          ),
          2 => 
          array (
            'clave' => 'Caja',
            'valor' => '47 mm, bisel de titanio',
          ),
          3 => 
          array (
            'clave' => 'Posicionamiento',
            'valor' => 'GNSS multibanda y SatIQ',
          ),
          4 => 
          array (
            'clave' => 'Resistencia',
            'valor' => '10 ATM',
          ),
          5 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 18 días en modo smartwatch, hasta 22 con condiciones solares oficiales',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Linterna LED, mapas y métricas deportivas',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  113 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-4',
    'nombre' => 'Amazfit GTR 4 Smartwatch GPS Dual Band',
    'marca' => 'Amazfit',
    'modelo' => 'GTR 4 original',
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
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '1.43 pulgadas AMOLED, 466 × 466',
          ),
          1 => 
          array (
            'clave' => 'GPS',
            'valor' => 'Doble banda',
          ),
          2 => 
          array (
            'clave' => 'Resistencia',
            'valor' => '5 ATM',
          ),
          3 => 
          array (
            'clave' => 'Batería',
            'valor' => '475 mAh',
          ),
          4 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 14 días con uso típico',
          ),
          5 => 
          array (
            'clave' => 'Sistema',
            'valor' => 'Zepp OS',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Llamadas Bluetooth y seguimiento de salud',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  114 => 
  array (
    'cat' => 'smartwatches',
    'sku' => 'WAT-29-5',
    'nombre' => 'Xiaomi Watch 2 Pro 46mm Wear OS Snapdragon',
    'marca' => 'Xiaomi',
    'modelo' => 'Watch 2 Pro, versión Bluetooth',
    'precio' => 229.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Sistema Google Wear OS con Google Wallet, Maps, Play Store y procesador Snapdragon W5+ Gen 1.',
    'desc' => 'Toda la tienda de Google en tu muñeca. Paga tus compras con Google Pay mediante NFC y responde mensajes de WhatsApp con teclado en pantalla o dictado de voz.',
    'peso' => 0.0545,
    'largo' => 4.76,
    'ancho' => 4.59,
    'alto' => 1.18,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Snapdragon W5+ Gen 1',
          ),
          1 => 
          array (
            'clave' => 'Memoria',
            'valor' => '2 GB RAM y 32 GB almacenamiento',
          ),
          2 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '1.43 pulgadas AMOLED, 466 × 466',
          ),
          3 => 
          array (
            'clave' => 'Batería',
            'valor' => '495 mAh',
          ),
          4 => 
          array (
            'clave' => 'Autonomía',
            'valor' => 'Hasta 65 horas para Bluetooth',
          ),
          5 => 
          array (
            'clave' => 'Sistema',
            'valor' => 'Wear OS',
          ),
          6 => 
          array (
            'clave' => 'Resistencia',
            'valor' => '5 ATM',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'GNSS y aplicaciones de Google',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  115 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-1',
    'nombre' => 'LG OLED evo C3 55" 4K Smart TV 120Hz',
    'marca' => 'LG',
    'modelo' => 'OLED evo C3 55 pulgadas, OLED55C3PSA',
    'precio' => 1299.0,
    'precio_oferta' => 1199,
    'stock' => 6,
    'destacado' => true,
    'desc_corta' => 'Procesador α9 AI 4K Gen6, brillo potenciado Brightness Booster y 4 puertos HDMI 2.1 a 120Hz.',
    'desc' => 'La televisión predilecta para cinéfilos y gamers. Píxeles autoemisivos que se apagan por completo para un contraste infinito con Dolby Vision, Dolby Atmos y FreeSync / G-SYNC.',
    'peso' => 16.0,
    'largo' => 23.0,
    'ancho' => 122.2,
    'alto' => 75.7,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'OLED evo de 55 pulgadas',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '3840 × 2160 4K',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '120 Hz',
          ),
          3 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'α9 AI 4K Gen6',
          ),
          4 => 
          array (
            'clave' => 'Sistema',
            'valor' => 'webOS 23',
          ),
          5 => 
          array (
            'clave' => 'HDR',
            'valor' => 'Dolby Vision, HDR10 y HLG',
          ),
          6 => 
          array (
            'clave' => 'HDMI',
            'valor' => '4 puertos con funciones HDMI 2.1',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'VRR, ALLM, G-SYNC Compatible y FreeSync',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  116 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-2',
    'nombre' => 'Samsung Neo QLED 65" QN90C 4K Mini-LED',
    'marca' => 'Samsung',
    'modelo' => 'Neo QLED QN90C 65 pulgadas, QN65QN90CAPXPA',
    'precio' => 1499.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Miles de Quantum Mini LEDs con control de luz preciso Quantum Matrix y procesador Neural 4K.',
    'desc' => 'Brillo despampanante ideal para salas iluminadas con grandes ventanales. La tecnología antirreflejo elimina los destellos de lámparas y el sonido OTS+ sigue la acción en pantalla.',
    'peso' => 27.5,
    'largo' => 27.06,
    'ancho' => 144.63,
    'alto' => 89.13,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'Neo QLED Mini LED de 65 pulgadas',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '3840 × 2160',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '120 Hz',
          ),
          3 => 
          array (
            'clave' => 'Sistema',
            'valor' => 'Tizen Smart TV',
          ),
          4 => 
          array (
            'clave' => 'HDR',
            'valor' => 'Neo Quantum HDR+',
          ),
          5 => 
          array (
            'clave' => 'HDMI',
            'valor' => '4 puertos',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Quantum Matrix, VRR, ALLM y eARC',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  117 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-3',
    'nombre' => 'Sony Bravia XR 55" A80L OLED 4K Google TV',
    'marca' => 'Sony',
    'modelo' => 'BRAVIA XR-55A80L',
    'precio' => 1399.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => false,
    'desc_corta' => 'Procesador Cognitivo XR que replica cómo perciben los ojos humanos y sonido Acoustic Surface.',
    'desc' => 'La propia pantalla vibra con actuadores invisibles para emitir el sonido directamente desde el lugar donde ocurren los diálogos. Funciones exclusivas diseñadas para PlayStation 5.',
    'peso' => 19.5,
    'largo' => 33.0,
    'ancho' => 122.7,
    'alto' => 73.8,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'OLED de 55 pulgadas',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '3840 × 2160',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia',
            'valor' => '120 Hz',
          ),
          3 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'Cognitive Processor XR',
          ),
          4 => 
          array (
            'clave' => 'Sistema',
            'valor' => 'Google TV',
          ),
          5 => 
          array (
            'clave' => 'HDR',
            'valor' => 'Dolby Vision, HDR10 y HLG',
          ),
          6 => 
          array (
            'clave' => 'HDMI',
            'valor' => '4 puertos, dos con funciones HDMI 2.1 avanzadas',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Acoustic Surface Audio+, VRR y ALLM',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  118 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-4',
    'nombre' => 'Hisense U8K 55" Mini-LED ULED 4K 144Hz',
    'marca' => 'Hisense',
    'modelo' => 'U8K 55 pulgadas, 55U8K, edición estadounidense',
    'precio' => 749.0,
    'precio_oferta' => 699,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Brillo pico colosal de 1500 nits, panel nativo de 144Hz con FreeSync Premium Pro y subwoofer.',
    'desc' => 'El mejor valor de la tecnología Mini-LED del mercado. Integra subwoofer trasero de 50W y compatibilidad total con todos los formatos HDR del mercado (Dolby Vision IQ, HDR10+, HLG).',
    'peso' => 19.006,
    'largo' => 32.004,
    'ancho' => 123.19,
    'alto' => 77.47,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'LCD Mini LED ULED de 55 pulgadas',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '3840 × 2160',
          ),
          2 => 
          array (
            'clave' => 'Frecuencia nativa',
            'valor' => '144 Hz',
          ),
          3 => 
          array (
            'clave' => 'Sistema',
            'valor' => 'Google TV',
          ),
          4 => 
          array (
            'clave' => 'HDR',
            'valor' => 'Dolby Vision y HDR10+',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Atenuación local y funciones de juego VRR',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  119 => 
  array (
    'cat' => 'televisores',
    'sku' => 'TV-30-5',
    'nombre' => 'Philips 55" 4K UHD LED Ambilight Android TV',
    'marca' => 'Philips',
    'modelo' => 'Philips 55PUT7374/56',
    'precio' => 529.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Luces LED inteligentes Ambilight en tres lados que proyectan los colores de la pantalla en tu pared.',
    'desc' => 'Una experiencia visual única: las luces traseras Ambilight se sincronizan en milisegundos con las escenas de tu película para hacer que la pantalla parezca mucho más grande.',
    'peso' => 16.4,
    'largo' => 23.54,
    'ancho' => 123.66,
    'alto' => 74.2,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'LED de 55 pulgadas',
          ),
          1 => 
          array (
            'clave' => 'Resolución',
            'valor' => '3840 × 2160 4K',
          ),
          2 => 
          array (
            'clave' => 'Sistema',
            'valor' => 'Android TV',
          ),
          3 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'P5 Perfect Picture Engine',
          ),
          4 => 
          array (
            'clave' => 'Ambilight',
            'valor' => '3 lados',
          ),
          5 => 
          array (
            'clave' => 'HDR',
            'valor' => 'HDR10+',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Televisor con iluminación ambiental Ambilight',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  120 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-1',
    'nombre' => 'Epson EcoTank L3250 Multifuncional Wi-Fi',
    'marca' => 'Epson',
    'modelo' => 'EcoTank L3250',
    'precio' => 189.0,
    'precio_oferta' => 179,
    'stock' => 18,
    'destacado' => true,
    'desc_corta' => 'Imprime hasta 4500 páginas en negro y 7500 a color con la tinta incluida sin cartuchos costosos.',
    'desc' => 'La multifuncional más vendida para hogares y estudiantes. Sistema de tanque de tinta frontal EcoTank con botellas codificadas que no derraman. Conexión inalámbrica y app Epson Smart Panel.',
    'peso' => 3.9,
    'largo' => 34.7,
    'ancho' => 37.5,
    'alto' => 17.9,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Funciones',
            'valor' => 'Impresión, copia y escaneo',
          ),
          1 => 
          array (
            'clave' => 'Tecnología',
            'valor' => 'Inyección de tinta con depósitos',
          ),
          2 => 
          array (
            'clave' => 'Color',
            'valor' => '4 colores',
          ),
          3 => 
          array (
            'clave' => 'Resolución de impresión',
            'valor' => 'Hasta 5760 × 1440 dpi',
          ),
          4 => 
          array (
            'clave' => 'Velocidad ISO',
            'valor' => 'Hasta 10 ppm en negro y 5 ppm en color',
          ),
          5 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'USB, Wi-Fi y Wi-Fi Direct',
          ),
          6 => 
          array (
            'clave' => 'Dúplex',
            'valor' => 'Manual',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Impresión desde dispositivos móviles',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  121 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-2',
    'nombre' => 'HP Smart Tank 580 Multifuncional Inalámbrica',
    'marca' => 'HP',
    'modelo' => 'Smart Tank 580, 1F3Y2A',
    'precio' => 169.0,
    'precio_oferta' => NULL,
    'stock' => 15,
    'destacado' => false,
    'desc_corta' => 'Conexión Wi-Fi autorreparable, escáner de alta resolución y hasta 2 años de tinta en la caja.',
    'desc' => 'Imprime, escanea y copia sin interrupciones. Los sensores automáticos de nivel de tinta te alertan antes de que se agote. Compatible con Apple AirPrint y Wi-Fi Direct.',
    'peso' => 5.03,
    'largo' => 36.153,
    'ancho' => 43.466,
    'alto' => 15.726,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Funciones',
            'valor' => 'Impresión, copia y escaneo',
          ),
          1 => 
          array (
            'clave' => 'Tecnología',
            'valor' => 'Inyección de tinta con depósitos',
          ),
          2 => 
          array (
            'clave' => 'Color',
            'valor' => '4 colores',
          ),
          3 => 
          array (
            'clave' => 'Velocidad ISO',
            'valor' => 'Hasta 12 ppm en negro y 5 ppm en color',
          ),
          4 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'USB, Wi-Fi y Wi-Fi Direct',
          ),
          5 => 
          array (
            'clave' => 'Dúplex',
            'valor' => 'Manual',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Impresión móvil mediante servicios compatibles',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  122 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-3',
    'nombre' => 'Canon PIXMA G3110 Tanque Continuo Wi-Fi',
    'marca' => 'Canon',
    'modelo' => 'PIXMA G3110',
    'precio' => 159.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Texto negro nítido con tinta de pigmento y fotos vibrantes sin bordes hasta tamaño carta.',
    'desc' => 'Sistema de tinta híbrido que combina tinta negra a base de pigmento para documentos de texto que no se corren con el resaltador y tintas a base de colorantes para fotos brillantes.',
    'peso' => 6.3,
    'largo' => 33.0,
    'ancho' => 44.5,
    'alto' => 16.3,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Funciones',
            'valor' => 'Impresión, copia y escaneo',
          ),
          1 => 
          array (
            'clave' => 'Tecnología',
            'valor' => 'Inyección de tinta de tanque integrado',
          ),
          2 => 
          array (
            'clave' => 'Resolución de impresión',
            'valor' => 'Hasta 4800 × 1200 dpi',
          ),
          3 => 
          array (
            'clave' => 'Velocidad',
            'valor' => 'Hasta 8.8 ipm negro y 5 ipm color',
          ),
          4 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'USB y Wi-Fi',
          ),
          5 => 
          array (
            'clave' => 'Dúplex',
            'valor' => 'Manual',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Canon PRINT y escaneo de documentos A4/carta',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  123 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-4',
    'nombre' => 'Brother HL-L2350DW Láser Monocromática Dúplex',
    'marca' => 'Brother',
    'modelo' => 'HL-L2350DW, referencia europea A4',
    'precio' => 149.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => false,
    'desc_corta' => 'Velocidad láser de hasta 32 páginas por minuto con impresión a doble cara automática (dúplex).',
    'desc' => 'El caballo de batalla para oficinas y despachos de abogados. Bandeja de papel cerrada con capacidad para 250 hojas que protege el papel del polvo y la humedad.',
    'peso' => 7.2,
    'largo' => 36.0,
    'ancho' => 35.6,
    'alto' => 18.3,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Impresora láser monocromática, sin escáner',
          ),
          1 => 
          array (
            'clave' => 'Velocidad',
            'valor' => 'Hasta 30 ppm A4',
          ),
          2 => 
          array (
            'clave' => 'Dúplex',
            'valor' => 'Automático',
          ),
          3 => 
          array (
            'clave' => 'Bandeja',
            'valor' => '250 hojas',
          ),
          4 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'USB, Wi-Fi y Wi-Fi Direct',
          ),
          5 => 
          array (
            'clave' => 'Memoria',
            'valor' => '64 MB',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Impresión móvil y pantalla LCD de una línea',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  124 => 
  array (
    'cat' => 'impresoras-y-escaneres',
    'sku' => 'PRN-31-5',
    'nombre' => 'Epson EcoTank L8180 Fotográfica 6 Colores A3+',
    'marca' => 'Epson',
    'modelo' => 'EcoTank L8180',
    'precio' => 649.0,
    'precio_oferta' => NULL,
    'stock' => 5,
    'destacado' => true,
    'desc_corta' => '6 tintas Claria ET con gris y negro fotográfico para impresiones artísticas de hasta tamaño A3+.',
    'desc' => 'La impresora soñada para fotógrafos de estudio, arquitectos y diseñadores gráficos. Permite imprimir sobre cartulinas gruesas de hasta 1.3mm y lienzos canvas sin atascos.',
    'peso' => 11.1,
    'largo' => 37.9,
    'ancho' => 52.3,
    'alto' => 16.9,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Funciones',
            'valor' => 'Impresión, copia y escaneo',
          ),
          1 => 
          array (
            'clave' => 'Tecnología',
            'valor' => 'Tanques de tinta de 6 colores',
          ),
          2 => 
          array (
            'clave' => 'Impresión máxima',
            'valor' => 'A3+',
          ),
          3 => 
          array (
            'clave' => 'Resolución de impresión',
            'valor' => 'Hasta 5760 × 1440 dpi',
          ),
          4 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'USB, Ethernet, Wi-Fi y Wi-Fi Direct',
          ),
          5 => 
          array (
            'clave' => 'Características',
            'valor' => 'Impresión fotográfica y dúplex en tamaños compatibles',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  125 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-1',
    'nombre' => 'Sony PlayStation 5 Slim Edición Disco 1TB',
    'marca' => 'Sony',
    'modelo' => 'PlayStation 5 Slim Disc, familia CFI-2000, 1 TB',
    'precio' => 499.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => true,
    'desc_corta' => 'Chasis un 30% más compacto con 1TB de SSD ultra rápido, audio 3D Tempest y control DualSense.',
    'desc' => 'Disfruta de juegos en 4K hasta a 120 FPS con retroalimentación háptica y gatillos adaptativos que simulan la tensión de una cuerda de arco o el retroceso de un arma en tus manos.',
    'peso' => 3.2,
    'largo' => 21.6,
    'ancho' => 35.8,
    'alto' => 9.6,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'AMD Zen 2 de 8 núcleos y 16 hilos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'AMD RDNA 2 personalizada',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB GDDR6',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD de 1 TB',
          ),
          4 => 
          array (
            'clave' => 'Unidad óptica',
            'valor' => 'Ultra HD Blu-ray',
          ),
          5 => 
          array (
            'clave' => 'Video',
            'valor' => 'HDMI con salida hasta 4K/120 Hz en juegos compatibles',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Mando DualSense y lector de discos desmontable',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  126 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-2',
    'nombre' => 'Microsoft Xbox Series X 1TB Consola',
    'marca' => 'Microsoft',
    'modelo' => 'Xbox Series X 1 TB, negro, lector de discos',
    'precio' => 489.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => true,
    'desc_corta' => '12 teraflops de potencia gráfica pura: la consola más potente del mercado con Xbox Game Pass.',
    'desc' => 'Tiempos de carga casi inexistentes con Xbox Velocity Architecture y función Quick Resume para alternar entre múltiples juegos al instante exactamente donde los dejaste.',
    'peso' => 4.4,
    'largo' => 15.1,
    'ancho' => 15.1,
    'alto' => 30.1,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'AMD Zen 2 de 8 núcleos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'AMD RDNA 2, 12 TFLOPS',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB GDDR6',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe de 1 TB',
          ),
          4 => 
          array (
            'clave' => 'Unidad óptica',
            'valor' => '4K UHD Blu-ray',
          ),
          5 => 
          array (
            'clave' => 'Video',
            'valor' => '4K y hasta 120 fps en contenido compatible',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Quick Resume y ranura de expansión de almacenamiento',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  127 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-3',
    'nombre' => 'Nintendo Switch OLED 64GB',
    'marca' => 'Nintendo',
    'modelo' => 'Switch OLED, HEG-001',
    'precio' => 339.0,
    'precio_oferta' => NULL,
    'stock' => 20,
    'destacado' => true,
    'desc_corta' => 'Pantalla OLED vibrante de 7 pulgadas, soporte ajustable amplio y base con puerto LAN por cable.',
    'desc' => 'Juega en casa en tu televisor o llévala contigo donde quieras. Su pantalla OLED llena tus aventuras de colores vivos con audio mejorado en modo portátil y 64GB de almacenamiento.',
    'peso' => 0.42,
    'largo' => 2.84,
    'ancho' => 24.2,
    'alto' => 10.2,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'OLED táctil de 7 pulgadas, 1280 × 720',
          ),
          1 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => '64 GB',
          ),
          2 => 
          array (
            'clave' => 'Expansión',
            'valor' => 'microSD',
          ),
          3 => 
          array (
            'clave' => 'Salida en TV',
            'valor' => 'Hasta 1920 × 1080 a 60 fps',
          ),
          4 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi y Bluetooth 4.1',
          ),
          5 => 
          array (
            'clave' => 'Autonomía',
            'valor' => '4.5–9 horas',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Joy-Con, base con LAN y soporte ajustable',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CON-33-3-V1',
        'precio' => 339,
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
        'precio' => 339,
        'stock' => 10,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Blanco',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  128 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-4',
    'nombre' => 'ASUS ROG Ally Z1 Extreme 512GB Consola PC',
    'marca' => 'ASUS',
    'modelo' => 'ROG Ally (2023), RC71L, Z1 Extreme 512 GB',
    'precio' => 599.0,
    'precio_oferta' => 549,
    'stock' => 8,
    'destacado' => false,
    'desc_corta' => 'Consola portátil con Windows 11: juega tus títulos de Steam, Game Pass, Epic Games y Battle.net.',
    'desc' => 'Tu biblioteca completa de juegos de computadora en la palma de tu mano. Pantalla Full HD de 120Hz con FreeSync Premium y procesador AMD Ryzen Z1 Extreme con arquitectura RDNA 3.',
    'peso' => 0.608,
    'largo' => 11.1,
    'ancho' => 28.0,
    'alto' => 3.24,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'AMD Ryzen Z1 Extreme, 8 núcleos y 16 hilos',
          ),
          1 => 
          array (
            'clave' => 'Gráficos',
            'valor' => 'AMD Radeon RDNA 3 de 12 CU',
          ),
          2 => 
          array (
            'clave' => 'Memoria',
            'valor' => '16 GB LPDDR5',
          ),
          3 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'SSD NVMe de 512 GB, M.2 2230',
          ),
          4 => 
          array (
            'clave' => 'Pantalla',
            'valor' => '7 pulgadas IPS 1920 × 1080, 120 Hz',
          ),
          5 => 
          array (
            'clave' => 'Sistema',
            'valor' => 'Windows 11 Home',
          ),
          6 => 
          array (
            'clave' => 'Batería',
            'valor' => '40 Wh',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Controles integrados y lector microSD',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  129 => 
  array (
    'cat' => 'consolas',
    'sku' => 'CON-33-5',
    'nombre' => 'Sony PlayStation Portal Remote Player',
    'marca' => 'Sony',
    'modelo' => 'PlayStation Portal Remote Player, CFI-Y1001',
    'precio' => 199.0,
    'precio_oferta' => NULL,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Transmite tus juegos de PS5 a una pantalla LCD de 8 pulgadas y 60 FPS a través de tu Wi-Fi.',
    'desc' => 'Siente la inmersión total del control DualSense con gatillos adaptativos y respuesta háptica mientras otra persona usa el televisor de la casa.',
    'peso' => 0.54,
    'largo' => 30.5,
    'ancho' => 12,
    'alto' => 6.5,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Dispositivo de juego remoto',
          ),
          1 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'LCD táctil de 8 pulgadas, 1920 × 1080',
          ),
          2 => 
          array (
            'clave' => 'Video',
            'valor' => 'Hasta 60 fps',
          ),
          3 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi y PlayStation Link',
          ),
          4 => 
          array (
            'clave' => 'Controles',
            'valor' => 'Gatillos adaptativos y respuesta háptica',
          ),
          5 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Altavoces y conector de 3.5 mm',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Remote Play desde PS5 y funciones en línea según disponibilidad',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  130 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-1',
    'nombre' => 'Corsair T3 Rush Silla Gamer Tela Transpirable',
    'marca' => 'Corsair',
    'modelo' => 'T3 RUSH Fabric (2023), CF-9010056-WW',
    'precio' => 299.0,
    'precio_oferta' => NULL,
    'stock' => 12,
    'destacado' => true,
    'desc_corta' => 'Tapizado en tela suave transpirable que no retiene calor, reposabrazos 4D y cojines de espuma viscoelástica.',
    'desc' => 'Inspirada en los asientos de autos de carreras deportivos de alto rendimiento. Mantiene tu cuerpo fresco durante el calor de Panamá con reclinación de hasta 160 grados.',
    'peso' => 21.3,
    'largo' => 88.5,
    'ancho' => 69,
    'alto' => 37.5,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tapizado',
            'valor' => 'Tejido transpirable',
          ),
          1 => 
          array (
            'clave' => 'Estructura',
            'valor' => 'Acero',
          ),
          2 => 
          array (
            'clave' => 'Carga máxima',
            'valor' => '120 kg',
          ),
          3 => 
          array (
            'clave' => 'Reposabrazos',
            'valor' => '4D',
          ),
          4 => 
          array (
            'clave' => 'Asiento',
            'valor' => '49 cm de profundidad × 54 cm de ancho',
          ),
          5 => 
          array (
            'clave' => 'Respaldo',
            'valor' => '85 cm de alto',
          ),
          6 => 
          array (
            'clave' => 'Altura del asiento',
            'valor' => 'Desde 48 cm',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Cojines cervical y lumbar',
          ),
        ),
      ),
    ),
    'variantes' => 
    array (
      0 => 
      array (
        'sku' => 'CHR-34-1-V1',
        'precio' => 299,
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
        'precio' => 299,
        'stock' => 5,
        'opcion' => 
        array (
          0 => 'Color',
          1 => 'Negro',
        ),
      ),
    ),
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  131 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-2',
    'nombre' => 'Razer Iskur Silla Ergonómica Soporte Lumbar',
    'marca' => 'Razer',
    'modelo' => 'Iskur original, RZ38-0277',
    'precio' => 399.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => false,
    'desc_corta' => 'Sistema de curvatura lumbar ergonómico totalmente ajustable que acompaña la forma de tu columna.',
    'desc' => 'A diferencia de las sillas convencionales con cojines planos, la Razer Iskur cuenta con un respaldo lumbar mecánico curvo que empuja y soporta tu espalda baja para evitar dolores tras horas de trabajo o juego.',
    'peso' => 30.3,
    'largo' => 74.5,
    'ancho' => 66.5,
    'alto' => 137.0,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Silla gamer ergonómica',
          ),
          1 => 
          array (
            'clave' => 'Tapizado',
            'valor' => 'Cuero sintético multicapa',
          ),
          2 => 
          array (
            'clave' => 'Soporte lumbar',
            'valor' => 'Sistema ajustable integrado',
          ),
          3 => 
          array (
            'clave' => 'Reposabrazos',
            'valor' => '4D',
          ),
          4 => 
          array (
            'clave' => 'Elevación',
            'valor' => 'Pistón de gas clase 4',
          ),
          5 => 
          array (
            'clave' => 'Carga recomendada',
            'valor' => 'Menos de 130 kg, según ficha de soporte',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Cojín de espuma viscoelástica y soporte lumbar mecánico',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  132 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-3',
    'nombre' => 'Cooler Master Caliber R2 Silla Ergonómica',
    'marca' => 'Cooler Master',
    'modelo' => 'Caliber R2 negro',
    'precio' => 249.0,
    'precio_oferta' => NULL,
    'stock' => 9,
    'destacado' => false,
    'desc_corta' => 'Cuero sintético transpirable perforado y base de estrella de acero reforzado para máxima estabilidad.',
    'desc' => 'Construcción con espuma moldeada en frío de densidad ultra alta que no se deforma con el paso de los años. Reclinación plana de 180° para descansar entre partidas.',
    'peso' => 21.5,
    'largo' => 86,
    'ancho' => 70,
    'alto' => 34,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tapizado',
            'valor' => 'Cuero PU',
          ),
          1 => 
          array (
            'clave' => 'Estructura',
            'valor' => 'Acero',
          ),
          2 => 
          array (
            'clave' => 'Carga máxima',
            'valor' => '150 kg',
          ),
          3 => 
          array (
            'clave' => 'Reposabrazos',
            'valor' => '2D',
          ),
          4 => 
          array (
            'clave' => 'Reclinación',
            'valor' => '90°–180°',
          ),
          5 => 
          array (
            'clave' => 'Altura total',
            'valor' => '125.5–133.5 cm',
          ),
          6 => 
          array (
            'clave' => 'Altura del asiento',
            'valor' => '48–56 cm',
          ),
          7 => 
          array (
            'clave' => 'Asiento',
            'valor' => '54 cm profundidad, 58 cm ancho con laterales',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Cojines cervical y lumbar y pistón clase 4',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  133 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-4',
    'nombre' => 'Thermaltake U Fit Black-Red Silla Gaming',
    'marca' => 'Thermaltake',
    'modelo' => 'U Fit Black-Red, GGC-UFT-BRMWDS-01',
    'precio' => 219.0,
    'precio_oferta' => NULL,
    'stock' => 10,
    'destacado' => false,
    'desc_corta' => 'Acolchado grueso con acabados de costura estilo fibra de carbono y ruedas de 60mm suaves.',
    'desc' => 'Ideal para jugadores que buscan confort garantizado a precio asequible. Base de 5 puntas de alta resistencia y pistón de elevación certificado por TÜV Rheinland.',
    'peso' => 24,
    'largo' => 89,
    'ancho' => 69,
    'alto' => 38,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Silla gamer',
          ),
          1 => 
          array (
            'clave' => 'Tapizado',
            'valor' => 'PVC',
          ),
          2 => 
          array (
            'clave' => 'Reposabrazos',
            'valor' => '2D',
          ),
          3 => 
          array (
            'clave' => 'Altura del asiento',
            'valor' => '45–53 cm',
          ),
          4 => 
          array (
            'clave' => 'Profundidad del asiento',
            'valor' => '55 cm total',
          ),
          5 => 
          array (
            'clave' => 'Altura del respaldo',
            'valor' => '84 cm',
          ),
          6 => 
          array (
            'clave' => 'Elevación',
            'valor' => 'Pistón clase 4',
          ),
          7 => 
          array (
            'clave' => 'Características',
            'valor' => 'Cojines cervical y lumbar y ajuste de inclinación',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  134 => 
  array (
    'cat' => 'sillas-y-muebles-gamer',
    'sku' => 'CHR-34-5',
    'nombre' => 'MSI MAG CH130 X Silla Gaming Confort Pro',
    'marca' => 'MSI',
    'modelo' => 'MAG CH130 X',
    'precio' => 279.0,
    'precio_oferta' => NULL,
    'stock' => 7,
    'destacado' => false,
    'desc_corta' => 'Asiento ensanchado de 57cm con profundidad extra y marco de acero inoxidable de alta rigidez.',
    'desc' => 'Diseñada con curvatura en los hombros para mejorar la postura natural del cuerpo y evitar la tensión muscular en el cuello durante extensas sesiones.',
    'peso' => 21.9,
    'largo' => 70.0,
    'ancho' => 69.0,
    'alto' => 140.0,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Silla gamer ergonómica',
          ),
          1 => 
          array (
            'clave' => 'Estructura',
            'valor' => 'Acero',
          ),
          2 => 
          array (
            'clave' => 'Tapizado',
            'valor' => 'Cuero sintético PVC',
          ),
          3 => 
          array (
            'clave' => 'Carga máxima',
            'valor' => '150 kg',
          ),
          4 => 
          array (
            'clave' => 'Elevación',
            'valor' => 'Pistón clase 4',
          ),
          5 => 
          array (
            'clave' => 'Reclinación',
            'valor' => 'Hasta 150°',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Cojines de cabeza y lumbar y reposabrazos ajustables',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  135 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-1',
    'nombre' => 'TP-Link Tapo C210 Cámara de Seguridad Wi-Fi 2K Pan/Tilt',
    'marca' => 'TP-Link',
    'modelo' => 'Tapo C210, referencia V2.20',
    'precio' => 34.99,
    'precio_oferta' => NULL,
    'stock' => 38,
    'destacado' => true,
    'desc_corta' => 'Rotación horizontal de 360°, visión nocturna nítida, audio bidireccional y detección de movimiento.',
    'desc' => 'Vigila tu casa o negocio desde tu smartphone en tiempo real. Graba en tarjeta MicroSD de hasta 512GB sin costos mensuales de suscripción y recibe alertas instantáneas cuando detecte personas.',
    'peso' => 0.19,
    'largo' => 8.68,
    'ancho' => 8.54,
    'alto' => 11.77,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Resolución',
            'valor' => '3 MP, 2304 × 1296',
          ),
          1 => 
          array (
            'clave' => 'Video',
            'valor' => 'H.264, hasta 15 fps en referencia consultada',
          ),
          2 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Wi-Fi de 2.4 GHz',
          ),
          3 => 
          array (
            'clave' => 'Movimiento',
            'valor' => 'Cobertura horizontal de 360°',
          ),
          4 => 
          array (
            'clave' => 'Visión nocturna',
            'valor' => 'Infrarroja, hasta unos 9 m',
          ),
          5 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Bidireccional',
          ),
          6 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'microSD y nube opcional',
          ),
          7 => 
          array (
            'clave' => 'Uso',
            'valor' => 'Interiores',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Detección de movimiento y control desde Tapo',
          ),
        ),
      ),
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
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  136 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-2',
    'nombre' => 'Google Nest Cam Indoor/Outdoor con Batería',
    'marca' => 'Google',
    'modelo' => 'Nest Cam de interior/exterior con batería',
    'precio' => 159.0,
    'precio_oferta' => NULL,
    'stock' => 11,
    'destacado' => false,
    'desc_corta' => 'Cámara inteligente resistente a la intemperie sin cables: reconoce personas, animales y autos.',
    'desc' => 'Instalación magnética en segundos sin necesidad de enchufes cercanos. Su Inteligencia Artificial procesa el video en el propio dispositivo para avisarte solo de eventos relevantes.',
    'peso' => 0.398,
    'largo' => 8.3,
    'ancho' => 8.3,
    'alto' => 8.3,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Video',
            'valor' => '1080p HDR, hasta 30 fps',
          ),
          1 => 
          array (
            'clave' => 'Campo de visión',
            'valor' => '130° diagonal',
          ),
          2 => 
          array (
            'clave' => 'Conectividad',
            'valor' => 'Wi-Fi',
          ),
          3 => 
          array (
            'clave' => 'Alimentación',
            'valor' => 'Batería recargable',
          ),
          4 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Bidireccional',
          ),
          5 => 
          array (
            'clave' => 'Protección',
            'valor' => 'IP54',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Detección de personas, animales y vehículos y visión nocturna',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  137 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-3',
    'nombre' => 'Xiaomi Smart Camera C400 2.5K 360°',
    'marca' => 'Xiaomi',
    'modelo' => 'Smart Camera C400, MJSXJ23CM',
    'precio' => 44.0,
    'precio_oferta' => NULL,
    'stock' => 22,
    'destacado' => false,
    'desc_corta' => 'Sensor de 4 megapíxeles con resolución 2.5K ultra clara y seguimiento automático de humanos por IA.',
    'desc' => 'Conectividad Wi-Fi de doble banda (2.4GHz y 5GHz) para transferencias de video sin cortes. Micrófono con reducción de ruido ambiental para escuchar con claridad.',
    'peso' => 0.318,
    'largo' => 7.8,
    'ancho' => 7.8,
    'alto' => 11.9,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Resolución',
            'valor' => '2560 × 1440, 4 MP',
          ),
          1 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Wi-Fi 2.4/5 GHz',
          ),
          2 => 
          array (
            'clave' => 'Almacenamiento',
            'valor' => 'microSD de hasta 256 GB',
          ),
          3 => 
          array (
            'clave' => 'Entrada',
            'valor' => '5 V/2 A',
          ),
          4 => 
          array (
            'clave' => 'Movimiento',
            'valor' => 'Visión panorámica motorizada',
          ),
          5 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Bidireccional',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Visión nocturna y control desde Xiaomi Home',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  138 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-4',
    'nombre' => 'Canon EOS R50 Cámara Mirrorless 4K Vlog',
    'marca' => 'Canon',
    'modelo' => 'EOS R50, cuerpo negro sin lente',
    'precio' => 649.0,
    'precio_oferta' => NULL,
    'stock' => 6,
    'destacado' => true,
    'desc_corta' => 'Sensor APS-C de 24.2 MP, video 4K sin recorte a 30p y pantalla táctil de ángulo variable.',
    'desc' => 'La cámara definitiva para creadores de contenido de YouTube, TikTok e Instagram. Enfoque automático Dual Pixel CMOS AF II con detección de personas, perros, gatos y vehículos.',
    'peso' => 0.375,
    'largo' => 6.88,
    'ancho' => 11.63,
    'alto' => 8.55,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Tipo',
            'valor' => 'Cámara mirrorless',
          ),
          1 => 
          array (
            'clave' => 'Sensor',
            'valor' => 'APS-C CMOS de 24.2 MP efectivos',
          ),
          2 => 
          array (
            'clave' => 'Montura',
            'valor' => 'Canon RF',
          ),
          3 => 
          array (
            'clave' => 'Procesador',
            'valor' => 'DIGIC X',
          ),
          4 => 
          array (
            'clave' => 'Video',
            'valor' => '4K hasta 30p sobremuestreado de 6K y Full HD hasta 120p',
          ),
          5 => 
          array (
            'clave' => 'Enfoque',
            'valor' => 'Dual Pixel CMOS AF II',
          ),
          6 => 
          array (
            'clave' => 'Pantalla',
            'valor' => 'LCD táctil de ángulo variable',
          ),
          7 => 
          array (
            'clave' => 'Conexión',
            'valor' => 'Wi-Fi y Bluetooth',
          ),
          8 => 
          array (
            'clave' => 'Características',
            'valor' => 'Grabación para creación de contenido y visor electrónico',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
  139 => 
  array (
    'cat' => 'camaras-y-seguridad',
    'sku' => 'CAM-35-5',
    'nombre' => 'GoPro HERO12 Black Cámara de Acción 5.3K',
    'marca' => 'GoPro',
    'modelo' => 'HERO12 Black, CHDHX-121',
    'precio' => 379.0,
    'precio_oferta' => 349,
    'stock' => 14,
    'destacado' => false,
    'desc_corta' => 'Estabilización HyperSmooth 6.0 galardonada con un Emmy, video 5.3K60 y sumergible a 10m sin carcasa.',
    'desc' => 'Captura tus aventuras más extremas con calidad profesional. Soporte para audio inalámbrico por Bluetooth para AirPods y micrófonos externos, video HDR y batería Enduro para bajas temperaturas.',
    'peso' => 0.154,
    'largo' => 3.36,
    'ancho' => 7.18,
    'alto' => 5.08,
    'specs' => 
    array (
      0 => 
      array (
        'grupo' => 'Especificaciones Técnicas',
        'atributos' => 
        array (
          0 => 
          array (
            'clave' => 'Video',
            'valor' => 'Hasta 5.3K60, 4K120 y 2.7K240',
          ),
          1 => 
          array (
            'clave' => 'Fotografía',
            'valor' => '27 MP',
          ),
          2 => 
          array (
            'clave' => 'Estabilización',
            'valor' => 'HyperSmooth 6.0',
          ),
          3 => 
          array (
            'clave' => 'Batería',
            'valor' => 'Enduro de 1720 mAh',
          ),
          4 => 
          array (
            'clave' => 'Resistencia al agua',
            'valor' => 'Hasta 10 m sin carcasa, según condiciones del fabricante',
          ),
          5 => 
          array (
            'clave' => 'Audio',
            'valor' => 'Compatibilidad Bluetooth para accesorios',
          ),
          6 => 
          array (
            'clave' => 'Características',
            'valor' => 'Video HDR, formato 8:7 y montaje integrado',
          ),
        ),
      ),
    ),
    'variantes' => NULL,
    'garantia' => 
    array (
      'nombre' => 'Garantía del producto según proveedor',
      'duracion' => 'Confirmar plazo aplicable con proveedor',
      'contacto' => 'Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.',
    ),
  ),
);
    }
}
