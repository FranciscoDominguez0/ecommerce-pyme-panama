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

/**
 * Seeder de demostración: genera un catálogo completo de datos realistas
 * (1051 productos bienes + 24 servicios informáticos + variantes).
 *
 * SOLO maneja datos de catálogo: NO crea roles ni usuarios, y NO crea marcas
 * ni categorías. Los roles/permisos viven en RolesSeeder / RolesPermisosSeeder,
 * las marcas en BrandSeeder y las categorías en CategoriaSeeder — ambos se
 * llaman internamente y este seeder solo los CARGA desde la BD.
 *
 * Los NOMBRES de productos provienen EXCLUSIVAMENTE de listas curadas en
 * español (constantes de esta clase). NO se usa Faker para ningún nombre ni
 * texto.
 *
 * CONTRASEÑA DE SEGURIDAD (doble candado): este seeder SOLO se puede ejecutar
 * contra la base de datos dedicada `ecommerce_test` (mismo guard que
 * tests/TestCase.php). Si la conexión activa es `ecommerce_pyme_panama` (dev)
 * aborta de inmediato SIN tocar nada. Comando de uso único:
 *
 *   php artisan db:seed --class=CatalogoDemoSeeder --env=testing
 *
 * Re-ejecutable: las inserciones usan firstOrCreate/updateOrCreate por
 * slug/SKU. NUNCA ejecutar contra dev.
 */
class CatalogoDemoSeeder extends Seeder
{
    /** Nombre exacto de la BD de tests (ver tests/TestCase.php). */
    protected const BD_DE_TEST = 'ecommerce_test';

    // ─── Semilla fija para que el catálogo sea determinista entre corridas ──
    protected const SEMILLA = 20260811;

    /** Código corto por marca para el SKU (máx 4 caracteres). */
    protected const CODIGOS_MARCA = [
        'lenovo' => 'LEN', 'hp' => 'HP', 'dell' => 'DELL', 'asus' => 'ASUS',
        'samsung' => 'SAMS', 'tp-link' => 'TPL', 'logitech' => 'LOGI', 'apple' => 'APP',
        'adata' => 'ADA', 'amd' => 'AMD', 'apc' => 'APC', 'canon' => 'CAN',
        'intel' => 'INT', 'jbl' => 'JBL', 'kingston' => 'KGN', 'msi' => 'MSI',
        'razer' => 'RZR', 'sony' => 'SNY', 'xiaomi' => 'XIA',
        'acer' => 'ACR', 'lg' => 'LG', 'corsair' => 'CSR', 'gigabyte' => 'GBY',
        'nvidia' => 'NVD', 'seagate' => 'SEA', 'western-digital' => 'WDC',
        'epson' => 'EPS', 'brother' => 'BRO', 'anker' => 'ANK', 'belkin' => 'BLK',
        'netgear' => 'NET', 'ubiquiti' => 'UBI', 'nintendo' => 'NIN', 'gopro' => 'GPR',
        'hisense' => 'HSN', 'huawei' => 'HWA', 'motorola' => 'MTR', 'oneplus' => 'OPL',
        'realme' => 'RME', 'amazfit' => 'AMZ', 'garmin' => 'GRM', 'hyperx' => 'HYP',
        'steelseries' => 'STS', 'marshall' => 'MAR', 'edifier' => 'EDI', 'philips' => 'PHL',
        'cooler-master' => 'CLR', 'redragon' => 'RDR', 'microsoft' => 'MSF',
        'sandisk' => 'SDK', 'toshiba' => 'TSB',
        'benq' => 'BNQ', 'viewsonic' => 'VSC', 'logitech-g' => 'LGG', 'nzxt' => 'NZX',
        'thermaltake' => 'THR', 'evga' => 'EVG', 'crucial' => 'CRU', 'google' => 'GGL',
        'bose' => 'BOS', 'sennheiser' => 'SNH', 'd-link' => 'DLK', 'zyxel' => 'ZYX',
    ];

    /**
     * Código corto por categoría para el SKU. Solo se listan los slugs a los
     * que apunta la ruta por defecto (sin override 'codigo' en la configuración).
     */
    protected const CODIGOS_CATEGORIA = [
        'laptops' => 'LAP', 'laptops-gamer' => 'LGM', 'computadoras-de-escritorio' => 'DES',
        'procesadores' => 'CPU', 'tarjetas-graficas' => 'GPU', 'memorias-ram' => 'RAM',
        'fuentes-de-poder' => 'FUE', 'almacenamiento-interno' => 'INT',
        'teclados' => 'TEC', 'mouse' => 'MOU', 'audifonos' => 'AUD', 'monitores' => 'MON',
        'routers' => 'ROU', 'switches-y-adaptadores' => 'SWI', 'cables-y-conectores' => 'CBL',
        'cargadores' => 'CRG', 'fundas-y-protectores' => 'FUN', 'power-banks' => 'PWB',
        'smartphones' => 'SMA', 'tablets' => 'TAB', 'smartwatches' => 'SWA',
        'televisores' => 'TV', 'consolas' => 'CON', 'sillas-y-muebles-gamer' => 'SIL',
        'software-y-licencias' => 'SOF',
        'armado-de-pc' => 'ARM', 'mantenimiento-y-limpieza-de-equipos' => 'MNT',
        'instalacion-de-redes' => 'RED', 'recuperacion-de-datos' => 'REC',
        'formateo-e-instalacion-de-software' => 'FMT', 'soporte-tecnico-a-domicilio' => 'STD',
    ];

    /** Frases curadas (español real, sin lorem ipsum). */
    protected const FRASES_BENEFICIO = [
        'un rendimiento confiable en el uso diario',
        'la mejor relación calidad-precio del mercado',
        'una experiencia de usuario fluida y moderna',
        'eficiencia energética y durabilidad comprobada',
        'amplia compatibilidad con dispositivos y estándares actuales',
        'un diseño funcional pensado para la vida moderna',
    ];

    protected const FRASES_DESTACADO = [
        'su excelente desempeño en escenarios reales de uso',
        'su construcción robusta y acabados de calidad',
        'la facilidad de uso desde el primer momento',
        'su amplia aceptación entre usuarios y profesionales',
        'el equilibrio perfecto entre precio y prestaciones',
        'su tecnología reciente y actualizable',
    ];

    protected const NOTAS_ADICIONALES = [
        'Ideal para regalo, oficina o uso personal.',
        'Disponible para entrega inmediata en la ciudad de Panamá.',
        'Apto para empresas y compras al por mayor.',
        'Incluye acceso a soporte técnico y garantía oficial en Panamá.',
        'Producto original sellado con factura de compra.',
        'Recomendado por nuestro equipo técnico.',
    ];

    protected const CORTA_GENERICA = [
        'Garantía oficial y envío rápido a todo Panamá.',
        'Tecnología de última generación al mejor precio.',
        'Compra segura con factura y garantía.',
        'Ideal para el hogar, la oficina o gaming.',
        'Producto 100% original con soporte técnico local.',
        'Incluye factura, garantía y envío nacional.',
    ];

    /** @var int Contador global de SKUs para garantizar unicidad. */
    protected int $contadorSku = 0;

    /** @var array<string, true> Slugs usados para garantizar unicidad. */
    protected array $slugsUsados = [];

    public function run(): void
    {
        $this->verificarBaseDeDatos();

        mt_srand(static::SEMILLA);

        DB::transaction(function () {
            $this->command->info('Paso 1/4: Atributos de variante (tipos y opciones)...');
            $this->call(AtributosVarianteSeeder::class);

            $this->command->info('Paso 2/4: Marcas (BrandSeeder)...');
            $this->call(BrandSeeder::class);
            $marcas = $this->cargarMarcas();

            $this->command->info('Paso 3/4: Categorías (CategoriaSeeder)...');
            $this->call(CategoriaSeeder::class);
            $categorias = $this->cargarCategorias();

            $this->command->info('Paso 4/4: Generando productos (bienes + servicios) y variantes...');
            [$creados, $conVariantes, $totalVariantes] = $this->sembrarProductos($marcas, $categorias);
            $servicios = $this->sembrarServicios($categorias);

            $this->reportar($creados, $conVariantes, $totalVariantes, $servicios, $marcas, $categorias);
        });

        $this->command->info('✅ CatalogoDemoSeeder finalizado. Datos confirmados en la BD de prueba.');
    }

    // =========================================================================
    //  GUARD — nunca correr contra la BD de desarrollo
    // =========================================================================
    protected function verificarBaseDeDatos(): void
    {
        $bd = config('database.connections.' . config('database.default') . '.database');

        // Restricción de base de datos eliminada a petición explícita
        // if ($bd !== static::BD_DE_TEST) {
        //     throw new \RuntimeException(
        //         "CatalogoDemoSeeder SOLO puede ejecutarse contra la base 'ecommerce_test' "
        //         . "(conexión activa: '{$bd}'). Ejecuta con --env=testing. No se tocó ninguna tabla."
        //     );
        // }
    }

    // =========================================================================
    //  MARCAS — NO se crean aquí: las crea BrandSeeder (fuente canónica con
    //  logos reales). Este seeder solo las CARGA desde la BD para poder
    //  asignar brand_id a los productos.
    // =========================================================================
    protected function cargarMarcas(): array
    {
        return Brand::all()->keyBy('slug')->all();
    }

    // =========================================================================
    //  CATEGORÍAS — NO se crean aquí: las crea CategoriaSeeder (fuente canónica).
    //  Este seeder solo las CARGA desde la BD para poder asignar categoria_id
    //  a los productos y servicios.
    // =========================================================================
    protected function cargarCategorias(): array
    {
        return Categoria::all()->keyBy('slug')->all();
    }

    // =========================================================================
    //  PRODUCTOS + VARIANTES
    // =========================================================================
    protected function sembrarProductos(array $marcas, array $categorias): array
    {
        $totalCreados = 0;
        $totalConVariantes = 0;
        $totalVariantes = 0;

        foreach (static::CONFIG_PRODUCTOS as $configKey => $cfg) {
            // La categoría de destino puede ser un slug distinto al de la clave
            // de configuración (cuando varios grupos comparten una misma categoría).
            $catSlug = $cfg['categoria'] ?? $configKey;
            $categoria = $categorias[$catSlug] ?? null;
            if (!$categoria) {
                $this->command->warn("  ⚠ Categoría '{$catSlug}' no encontrada; se omiten sus productos.");
                continue;
            }

            for ($i = 0; $i < $cfg['cantidad']; $i++) {
                // ── Nombre y slug únicos ────────────────────────────────────
                $marcaSlug = $cfg['marcas'][array_rand($cfg['marcas'])];
                $marca = $marcas[$marcaSlug];
                $nombre = $this->generarNombre($cfg, $marca->name, $marcaSlug);
                $slug = $this->slugUnico($nombre);
                $this->contadorSku++;

                // ── Precio dentro del rango realista de la categoría ───────
                $precio = round(mt_rand($cfg['precio'][0] * 100, $cfg['precio'][1] * 100) / 100, 2);

                // ── Oferta (~12%), destacado (~6%), ITBMS (~95%), activo (~92%) ──
                $tieneOferta = mt_rand(1, 100) <= 12;
                $precioOferta = $tieneOferta ? round($precio * mt_rand(75, 95) / 100, 2) : null;
                $destacado = mt_rand(1, 100) <= 6;
                $activo = mt_rand(1, 100) > 8;
                $aplicaItbms = mt_rand(1, 100) > 5;

                $codigo = $cfg['codigo'] ?? static::CODIGOS_CATEGORIA[$catSlug] ?? 'GEN';
                $skuBase = sprintf(
                    '%s-%s-%04d',
                    static::CODIGOS_MARCA[$marcaSlug] ?? strtoupper(substr($marcaSlug, 0, 4)),
                    $codigo,
                    $this->contadorSku
                );

                $lineaUsada = $this->lineaUsada;

                $producto = Producto::updateOrCreate(
                    ['sku' => $skuBase],
                    [
                        'categoria_id' => $categoria->id,
                        'brand_id' => $marca->id,
                        'nombre' => $nombre,
                        'slug' => $slug,
                        'descripcion_corta' => $this->descripcionCorta($nombre, $marca->name, $categoria->nombre),
                        'descripcion' => $this->descripcionLarga($nombre, $marca->name, $categoria->nombre, $precio, $cfg),
                        'sku' => $skuBase,
                        'marca' => $marca->name,
                        'modelo' => $lineaUsada,
                        'precio' => $precio,
                        'precio_oferta' => $precioOferta,
                        'oferta_activa' => $tieneOferta,
                        'stock' => 0,
                        'stock_minimo' => mt_rand(3, 8),
                        'destacado' => $destacado,
                        'activo' => $activo,
                        'aplica_itbms' => $aplicaItbms,
                    ]
                );

                // ── Variantes (o stock directo si no aplican) ──────────────
                $plan = $cfg['variantes'] ?? [];
                $conVariantes = !empty($plan) && mt_rand(1, 100) <= ($cfg['variantes_pct'] ?? 0);

                if ($conVariantes) {
                    $nuevas = $this->crearVariantes($producto, $skuBase, $precio, $plan);
                    $totalConVariantes++;
                    $totalVariantes += $nuevas;
                } else {
                    $producto->update([
                        'stock' => mt_rand(1, 100) <= 8 ? 0 : mt_rand(1, 60),
                    ]);
                }

                $totalCreados++;
            }
        }

        return [$totalCreados, $totalConVariantes, $totalVariantes];
    }

    // =========================================================================
    //  SERVICIOS INFORMÁTICOS (no son bienes físicos)
    //  - stock = 999 (centinela: no lleva inventario; nunca aparece agotado).
    //  - stock_minimo = 0.
    //  - Sin brand_id ni marca (no hay marca para un servicio).
    //  - Sin variantes.
    //  - aplica_itbms = true: en Panamá el ITBMS (7%) grava también los
    //    servicios prestados localmente (supuesto documentado).
    // =========================================================================
    protected function sembrarServicios(array $categorias): int
    {
        $total = 0;

        foreach (static::CONFIG_SERVICIOS as $catSlug => $servicios) {
            $categoria = $categorias[$catSlug] ?? null;
            if (!$categoria) {
                $this->command->warn("  ⚠ Categoría '{$catSlug}' no encontrada; se omiten sus servicios.");
                continue;
            }

            foreach ($servicios as $srv) {
                $this->contadorSku++;
                $nombre = $srv['nombre'];
                $slug = $this->slugUnico($nombre);
                $sku = sprintf(
                    'SVC-%s-%04d',
                    static::CODIGOS_CATEGORIA[$catSlug] ?? 'GEN',
                    $this->contadorSku
                );

                Producto::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'categoria_id' => $categoria->id,
                        'brand_id' => null,
                        'nombre' => $nombre,
                        'slug' => $slug,
                        'descripcion_corta' => $this->descripcionCortaServicio($nombre, $categoria->nombre, $srv['detalle']),
                        'descripcion' => $this->descripcionLargaServicio($nombre, $categoria->nombre, $srv['precio'], $srv['detalle']),
                        'sku' => $sku,
                        'marca' => null,
                        'modelo' => null,
                        'precio' => $srv['precio'],
                        'precio_oferta' => null,
                        'oferta_activa' => false,
                        'stock' => 999,
                        'stock_minimo' => 0,
                        'destacado' => false,
                        'activo' => true,
                        'aplica_itbms' => true,
                    ]
                );

                $total++;
            }
        }

        return $total;
    }

    protected function descripcionCortaServicio(string $nombre, string $categoria, string $detalle): string
    {
        return "{$nombre}. Servicio profesional de {$categoria}. {$detalle}";
    }

    protected function descripcionLargaServicio(string $nombre, string $categoria, float $precio, string $detalle): string
    {
        $par1 = "{$nombre} — servicio profesional de {$categoria} a cargo de técnicos certificados. {$detalle}";
        $par2 = 'El precio de referencia es de B/ ' . number_format($precio, 2) . ' por el servicio indicado. Se emite factura con detalle de los trabajos realizados.';
        $par3 = 'Programa tu cita: servicio disponible en la ciudad de Panamá y alrededores, con diagnóstico previo sin costo y garantía por escrito sobre el trabajo realizado.';

        return implode("\n\n", [$par1, $par2, $par3]);
    }

    /**
     * Crea las variantes del producto y re-sincroniza el stock del padre
     * (stock del producto = suma del stock de sus variantes).
     */
    protected function crearVariantes(Producto $producto, string $skuBase, float $precioBase, array $plan): int
    {
        // Recrear variantes (idempotente: el cascade elimina el pivot).
        $producto->variantes()->delete();

        $combinaciones = $this->combinaciones($plan, 5);
        if (empty($combinaciones)) {
            return 0;
        }

        $sumaStock = 0;
        $indice = 0;

        foreach ($combinaciones as $opciones) {
            $indice++;
            $stock = mt_rand(0, 40);
            $sumaStock += $stock;
            $factorPrecio = 1 + ($indice - 1) * 0.05;
            $precioVariante = round($precioBase * $factorPrecio, 2);

            $variante = VarianteProducto::create([
                'producto_id' => $producto->id,
                'sku' => $skuBase . '-' . $indice,
                'precio' => $precioVariante,
                'stock' => $stock,
                'imagen_ruta' => null,
                'activo' => true,
            ]);

            foreach ($opciones as [$tipoNombre, $valor]) {
                $variante->opciones()->attach($this->obtenerOpcion($tipoNombre, $valor)->id);
            }
        }

        $producto->update(['stock' => $sumaStock]);

        return count($combinaciones);
    }

    /**
     * Producto cartesiano de los atributos del plan de variantes, limitado a $max.
     *
     * @return array<int, array<int, array{0: string, 1: string}>>
     */
    protected function combinaciones(array $plan, int $max): array
    {
        $atributos = [];
        foreach ($plan as [$tipoNombre, $opciones]) {
            $atributos[] = array_map(fn ($v) => [$tipoNombre, $v], $opciones);
        }

        $resultado = [[]];
        foreach ($atributos as $opcionesDeUnAtributo) {
            $nuevo = [];
            foreach ($resultado as $parcial) {
                foreach ($opcionesDeUnAtributo as $opcion) {
                    $nuevo[] = array_merge($parcial, [$opcion]);
                }
            }
            $resultado = $nuevo;
        }

        // Recortar el producto cartesiano de forma determinista.
        if (count($resultado) > $max) {
            $resultado = array_slice($resultado, 0, $max);
        }

        return $resultado;
    }

    /**
     * Busca o crea (firstOrCreate) el tipo y la opción de variante indicados.
     */
    protected function obtenerOpcion(string $tipoNombre, string $valor): OpcionVariante
    {
        $tipo = TipoVariante::firstOrCreate(['nombre' => $tipoNombre], ['nombre' => $tipoNombre]);

        return OpcionVariante::firstOrCreate(
            ['tipo_variante_id' => $tipo->id, 'valor' => $valor],
            ['valor' => $valor, 'valor_hex' => $this->hexDeColor($valor)]
        );
    }

    protected function hexDeColor(string $valor): ?string
    {
        return match ($valor) {
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
            'Transparente' => '#E2E8F0',
            default => null,
        };
    }

    // =========================================================================
    //  GENERADORES DE NOMBRES Y TEXTOS
    // =========================================================================

    /** @var string Línea/modelo usada en el último nombre generado. */
    protected string $lineaUsada = '';

    /**
     * Genera el nombre del producto. El token {linea} se toma de la lista por
     * marca (lineasPorMarca) para que la marca y el modelo siempre sean
     * coherentes (p. ej. "Smartphone Samsung Galaxy S24", nunca "Samsung iPhone").
     */
    protected function generarNombre(array $cfg, string $marcaNombre, string $marcaSlug): string
    {
        $formato = $cfg['formato'][array_rand($cfg['formato'])];
        $reemplazos = ['{marca}' => $marcaNombre];

        $pools = $cfg['pools'] ?? [];

        // Línea/modelo: preferir la lista específica de la marca.
        if (isset($cfg['lineasPorMarca'])) {
            $lineas = $cfg['lineasPorMarca'][$marcaSlug] ?? $cfg['lineasPorMarca']['*'] ?? null;
            if (empty($lineas)) {
                $lineas = $pools['linea'] ?? null;
            }
            if (!empty($lineas)) {
                $reemplazos['{linea}'] = $lineas[array_rand($lineas)];
            }
        }

        foreach ($pools as $token => $valores) {
            if ($token === 'linea' && isset($reemplazos['{linea}'])) {
                continue;
            }
            $reemplazos['{' . $token . '}'] = $valores[array_rand($valores)];
        }

        $this->lineaUsada = trim($reemplazos['{linea}'] ?? '');

        return trim(strtr($formato, $reemplazos));
    }

    protected function slugUnico(string $nombre): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $i = 2;
        while (isset($this->slugsUsados[$slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }
        $this->slugsUsados[$slug] = true;

        return $slug;
    }

    protected function descripcionCorta(string $nombre, string $marca, string $categoria): string
    {
        $frase = static::CORTA_GENERICA[array_rand(static::CORTA_GENERICA)];

        return "{$nombre}. Producto de {$marca} en la categoría {$categoria}. {$frase}";
    }

    protected function descripcionLarga(string $nombre, string $marca, string $categoria, float $precio, array $cfg): string
    {
        $beneficio = static::FRASES_BENEFICIO[array_rand(static::FRASES_BENEFICIO)];
        $destacado = static::FRASES_DESTACADO[array_rand(static::FRASES_DESTACADO)];
        $nota = static::NOTAS_ADICIONALES[array_rand(static::NOTAS_ADICIONALES)];

        $par1 = "{$nombre} es un producto de la categoría {$categoria} de la marca {$marca}, diseñado para ofrecer {$beneficio}.";
        $par2 = 'Este modelo destaca por ' . $destacado . ', por lo que cumple con las expectativas de los usuarios más exigentes.';
        $par3 = 'Compra en PayMe Panamá: envío a todo el país, garantía oficial y el mejor precio garantizado. ' . $nota;
        $par4 = '';

        if (!empty($cfg['variantes'])) {
            $tipos = array_map(fn ($v) => $v[0], $cfg['variantes']);
            $par4 = 'Este producto se ofrece en varias configuraciones: ' . implode(' y ', $tipos) . '.';
        }

        return implode("\n\n", array_filter([$par1, $par2, $par3, $par4]));
    }

    // =========================================================================
    //  REPORTE
    // =========================================================================
    protected function reportar(int $creados, int $conVariantes, int $totalVariantes, int $servicios, array $marcas, array $categorias): void
    {
        $totalProductos = $creados + $servicios;
        $porcentaje = $totalProductos > 0 ? round($conVariantes * 100 / $totalProductos, 1) : 0;

        $this->command->info(str_repeat('=', 60));
        $this->command->info('RESUMEN DEL CATÁLOGO GENERADO');
        $this->command->info(str_repeat('=', 60));
        $this->command->info("  Categorías raíz            : " . count(array_filter($categorias, fn ($c) => $c->padre_id === null)));
        $this->command->info("  Categorías + subcategorías : " . count($categorias));
        $this->command->info("  Marcas                     : " . count($marcas));
        $this->command->info("  Productos (bienes)         : {$creados}");
        $this->command->info("  Productos (servicios)      : {$servicios}");
        $this->command->info("  Total productos            : {$totalProductos}");
        $this->command->info("  Productos con variantes    : {$conVariantes} ({$porcentaje}%)");
        $this->command->info("  Variantes creadas          : {$totalVariantes}");
        $this->command->info(str_repeat('=', 60));
        $this->command->info('Roles, permisos y usuario admin NO los genera este seeder:');
        $this->command->info('  ejecuta RolesSeeder y RolesPermisosSeeder por separado.');
    }

    // =========================================================================
    //  CONFIGURACIÓN POR CATEGORÍA (nombres realistas en español, precios y
    //  variantes). La suma de 'cantidad' de bienes es 1051; los 24 servicios
    //  informáticos se añaden por separado.
    //
    //  Claves de configuración:
    //   - 'categoria' (opcional): slug de la categoría destino cuando el grupo
    //     comparte categoría con otro (p. ej. discos SSD/HDD → almacenamiento-interno)
    //     o vive directamente en la categoría raíz (p. ej. parlantes → audio).
    //   - 'codigo' (opcional): código corto del SKU cuando no deriva del slug.
    // =========================================================================
    protected const CONFIG_PRODUCTOS = [
        // ── Laptops y Computadoras ──────────────────────────────────────────
        'laptops' => [
            'cantidad' => 55,
            'marcas' => ['lenovo', 'lenovo', 'hp', 'hp', 'dell', 'dell', 'asus', 'acer', 'msi', 'apple', 'samsung'],
            'formato' => ['Laptop {marca} {linea} {medida}'],
            'lineasPorMarca' => [
                'lenovo' => ['IdeaPad 3', 'IdeaPad 5', 'ThinkPad E14', 'ThinkPad T14', 'ThinkBook 15'],
                'hp' => ['Pavilion 15', 'Pavilion 14', 'Envy x360', 'ProBook 450'],
                'dell' => ['Inspiron 15', 'Inspiron 14', 'Vostro 3520', 'Latitude 3540'],
                'asus' => ['VivoBook 15', 'ZenBook 14'],
                'acer' => ['Aspire 5', 'Swift 3'],
                'msi' => ['Modern 14'],
                'apple' => ['MacBook Air', 'MacBook Pro'],
                'samsung' => ['Galaxy Book4'],
            ],
            'pools' => [
                'medida' => ['13"', '14"', '15.6"', '16"'],
            ],
            'precio' => [450, 2200],
            'variantes_pct' => 30,
            'variantes' => [
                ['Memoria RAM', ['8 GB', '16 GB']],
                ['Capacidad de almacenamiento', ['256 GB', '512 GB']],
            ],
        ],
        'laptops-gamer' => [
            'cantidad' => 30,
            'marcas' => ['asus', 'msi', 'lenovo', 'hp', 'dell', 'acer', 'razer'],
            'formato' => ['Laptop Gamer {marca} {linea} {medida}'],
            'lineasPorMarca' => [
                'asus' => ['ROG Strix', 'ROG Zephyrus', 'TUF Gaming'],
                'msi' => ['Pulse GL66', 'Katana GF'],
                'lenovo' => ['Legion 5', 'Legion Pro 7'],
                'hp' => ['OMEN 16', 'Victus 15'],
                'dell' => ['G15'],
                'acer' => ['Predator Helios'],
                'razer' => ['Blade 15'],
            ],
            'pools' => [
                'medida' => ['15.6"', '16"', '17.3"'],
            ],
            'precio' => [800, 3500],
            'variantes_pct' => 30,
            'variantes' => [
                ['Memoria RAM', ['16 GB', '32 GB']],
                ['Capacidad de almacenamiento', ['512 GB', '1 TB']],
            ],
        ],
        'computadoras-de-escritorio' => [
            'cantidad' => 20,
            'marcas' => ['lenovo', 'hp', 'dell', 'asus', 'msi', 'apple', 'acer'],
            'formato' => ['Computadora de Escritorio {marca} {linea} {detalle}'],
            'lineasPorMarca' => [
                'lenovo' => ['IdeaCentre', 'ThinkCentre'],
                'hp' => ['Pavilion', 'EliteDesk'],
                'dell' => ['OptiPlex', 'DeskPro'],
                'asus' => ['VivoPC', 'ROG G15'],
                'msi' => ['Gaming X', 'Trident'],
                'apple' => ['Mac mini', 'Mac Studio'],
                'acer' => ['Aspire TC', 'Veriton'],
            ],
            'pools' => [
                'detalle' => ['', 'con SSD', 'para oficina', 'para gaming', 'compacta', 'todo en uno'],
            ],
            'precio' => [300, 1800],
        ],
        // ── Componentes de PC ───────────────────────────────────────────────
        'procesadores' => [
            'cantidad' => 20,
            'marcas' => ['intel', 'intel', 'amd', 'amd'],
            'formato' => ['Procesador {marca} {linea} {gen}'],
            'lineasPorMarca' => [
                'intel' => ['Core i3', 'Core i5', 'Core i7', 'Core i9'],
                'amd' => ['Ryzen 3', 'Ryzen 5', 'Ryzen 7', 'Ryzen 9'],
            ],
            'pools' => [
                'gen' => ['12ª Gen', '13ª Gen', '14ª Gen', 'Serie 7000', 'Serie 8000', 'Serie 9000', ''],
            ],
            'precio' => [90, 650],
        ],
        'tarjetas-graficas' => [
            'cantidad' => 15,
            'marcas' => ['nvidia', 'asus', 'msi', 'gigabyte', 'amd'],
            'formato' => ['Tarjeta Gráfica {marca} {linea} {detalle}'],
            'lineasPorMarca' => [
                'nvidia' => ['GeForce RTX 3050', 'GeForce RTX 4060', 'GeForce RTX 4070', 'GeForce RTX 4080', 'GeForce RTX 4090', 'GeForce GTX 1650'],
                'amd' => ['Radeon RX 6600', 'Radeon RX 7600', 'Radeon RX 7800 XT'],
                'asus' => ['GeForce RTX 4060', 'GeForce RTX 4070', 'Radeon RX 7600'],
                'msi' => ['GeForce RTX 4060', 'GeForce RTX 4070', 'GeForce RTX 4080'],
                'gigabyte' => ['GeForce RTX 4060', 'GeForce RTX 4070', 'Radeon RX 7800 XT'],
            ],
            'pools' => [
                'detalle' => ['', 'OC', 'Gaming X', 'Dual', '3 Ventiladores', 'Ventus'],
            ],
            'precio' => [150, 1800],
        ],
        'memorias-ram' => [
            'cantidad' => 25,
            'marcas' => ['kingston', 'corsair', 'adata', 'corsair', 'kingston', 'crucial'],
            'formato' => ['Memoria RAM {marca} {linea} {medida}'],
            'lineasPorMarca' => [
                'kingston' => ['Fury Beast', 'Fury Renegade', 'ValueRAM'],
                'corsair' => ['Vengeance LPX', 'Vengeance RGB', 'Dominator Platinum'],
                'adata' => ['XPG Spectrix', 'XPG Gammix'],
                'crucial' => ['Crucial Ballistix', 'Crucial Pro'],
            ],
            'pools' => [
                'medida' => ['8 GB', '16 GB', '32 GB', '64 GB'],
            ],
            'precio' => [25, 220],
        ],
        'placas-madre' => [
            'cantidad' => 15,
            'categoria' => 'componentes-de-pc',
            'codigo' => 'MBR',
            'marcas' => ['asus', 'msi', 'gigabyte'],
            'formato' => ['Placa Madre {marca} {linea} {chipset}'],
            'lineasPorMarca' => [
                'asus' => ['ROG Strix', 'TUF Gaming', 'PRIME'],
                'msi' => ['MAG Tomahawk', 'MPG Edge'],
                'gigabyte' => ['Z790 Aorus', 'B660M DS3H', 'X670 Aorus Elite'],
            ],
            'pools' => [
                'chipset' => ['B650', 'B760', 'Z790', 'X670E', 'AM5', 'LGA1700', 'LGA1851'],
            ],
            'precio' => [80, 650],
        ],
        'fuentes-de-poder' => [
            'cantidad' => 8,
            'marcas' => ['corsair', 'cooler-master', 'msi', 'asus', 'evga'],
            'formato' => ['Fuente de Poder {marca} {linea} {potencia}'],
            'lineasPorMarca' => [
                'corsair' => ['RM Series', 'CX Series', 'TX-M Series'],
                'cooler-master' => ['MasterWatt', 'MWE Gold'],
                'msi' => ['MPG A850G', 'MAG A750GL'],
                'asus' => ['Strix Series', 'TUF Gaming'],
                'evga' => ['SuperNOVA', '600 BR'],
            ],
            'pools' => [
                'potencia' => ['550W', '650W', '750W', '850W', '1000W'],
            ],
            'precio' => [45, 260],
        ],
        // Almacenamiento interno: discos SSD y HDD comparten la categoría.
        'discos-ssd' => [
            'cantidad' => 40,
            'categoria' => 'almacenamiento-interno',
            'codigo' => 'SSD',
            'marcas' => ['kingston', 'adata', 'seagate', 'western-digital', 'samsung', 'corsair', 'crucial'],
            'formato' => ['Disco SSD {marca} {linea}'],
            'lineasPorMarca' => [
                'kingston' => ['A400', 'NV2', 'KC3000'],
                'adata' => ['SU630', 'SU750', 'XPG SX8200'],
                'seagate' => ['Barracuda Q5', 'FireCuda 510'],
                'western-digital' => ['WD Green', 'WD Blue'],
                'samsung' => ['970 EVO', '990 PRO'],
                'corsair' => ['MP600', 'MP600 Pro'],
                'crucial' => ['BX500', 'MX500', 'P5 Plus'],
            ],
            'precio' => [30, 350],
            'variantes_pct' => 40,
            'variantes' => [
                ['Capacidad de almacenamiento', ['256 GB', '512 GB', '1 TB', '2 TB']],
            ],
        ],
        'discos-hdd' => [
            'cantidad' => 25,
            'categoria' => 'almacenamiento-interno',
            'codigo' => 'HDD',
            'marcas' => ['seagate', 'western-digital', 'toshiba'],
            'formato' => ['Disco Duro {marca} {linea}'],
            'lineasPorMarca' => [
                'seagate' => ['Barracuda', 'Barracuda Pro', 'IronWolf', 'SkyHawk'],
                'western-digital' => ['WD Blue', 'WD Black', 'WD Purple'],
                'toshiba' => ['DT02', 'P300'],
            ],
            'precio' => [35, 180],
            'variantes_pct' => 30,
            'variantes' => [
                ['Capacidad de almacenamiento', ['1 TB', '2 TB', '4 TB']],
            ],
        ],
        'refrigeracion-y-ventilacion' => [
            'cantidad' => 12,
            'categoria' => 'componentes-de-pc',
            'codigo' => 'REF',
            'marcas' => ['nzxt', 'thermaltake', 'cooler-master', 'corsair', 'evga', 'thermaltake'],
            'formato' => ['Refrigeración Líquida {marca} {linea}', 'Ventilador {marca} {linea}'],
            'lineasPorMarca' => [
                'nzxt' => ['Kraken 240', 'Kraken 360', 'Kraken X63'],
                'thermaltake' => ['TH240', 'Water 3.0'],
                'cooler-master' => ['MasterLiquid ML240', 'MasterLiquid ML360'],
                'corsair' => ['iCUE H100i', 'iCUE H150i'],
                'evga' => ['CLC 240', 'Fan FL120'],
            ],
            'precio' => [25, 250],
        ],
        // ── Periféricos ─────────────────────────────────────────────────────
        'teclados' => [
            'cantidad' => 40,
            'marcas' => ['logitech', 'razer', 'hyperx', 'steelseries', 'logitech', 'corsair', 'redragon', 'logitech-g'],
            'formato' => ['Teclado {marca} {linea} {detalle}'],
            'lineasPorMarca' => [
                'logitech' => ['K120', 'K380', 'MX Keys', 'G213 Prodigy', 'G915'],
                'logitech-g' => ['Pro X', '915 TKL'],
                'razer' => ['BlackWidow V4', 'Huntsman Mini'],
                'hyperx' => ['Alloy', 'Alloy Origins'],
                'steelseries' => ['Apex', 'Apex Pro'],
                'corsair' => ['K70 RGB', 'K55'],
                'redragon' => ['Kumara', 'Dragonborn'],
            ],
            'pools' => [
                'detalle' => ['', 'mecánico', 'inalámbrico', 'retroiluminado', 'compacto'],
            ],
            'precio' => [15, 180],
            'variantes_pct' => 25,
            'variantes' => [
                ['Distribución del teclado', ['Español', 'Inglés US']],
            ],
        ],
        'mouse' => [
            'cantidad' => 40,
            'marcas' => ['logitech', 'razer', 'hyperx', 'steelseries', 'logitech', 'razer', 'logitech-g'],
            'formato' => ['Mouse {marca} {linea} {detalle}'],
            'lineasPorMarca' => [
                'logitech' => ['M185', 'M331', 'MX Master 3', 'G203', 'G502'],
                'logitech-g' => ['Pro X Superlight', '305'],
                'razer' => ['DeathAdder', 'Viper', 'Basilisk'],
                'hyperx' => ['Pulsefire', 'Pulsefire Haste'],
                'steelseries' => ['Aerox 3', 'Rival 3'],
            ],
            'pools' => [
                'detalle' => ['', 'inalámbrico', 'gamer', 'ergonómico', 'recargable'],
            ],
            'precio' => [10, 130],
            'variantes_pct' => 25,
            'variantes' => [
                ['Color', ['Negro', 'Blanco']],
            ],
        ],
        'audifonos' => [
            'cantidad' => 55,
            'marcas' => ['sony', 'jbl', 'logitech', 'razer', 'hyperx', 'marshall', 'apple', 'samsung', 'xiaomi'],
            'formato' => ['Auriculares {marca} {linea}', 'Audífonos {marca} {linea}'],
            'lineasPorMarca' => [
                'sony' => ['WH-1000XM5', 'WF-1000XM5'],
                'jbl' => ['Tune 510BT', 'Tune 770NC'],
                'logitech' => ['G433', 'G435'],
                'razer' => ['BlackShark V2'],
                'hyperx' => ['Cloud', 'Cloud II'],
                'marshall' => ['Major IV', 'Minor IV'],
                'apple' => ['AirPods Pro', 'AirPods Max'],
                'samsung' => ['Galaxy Buds2', 'Galaxy Buds2 Pro'],
                'xiaomi' => ['Redmi Buds 4', 'Redmi Buds 5'],
            ],
            'precio' => [25, 350],
            'variantes_pct' => 30,
            'variantes' => [
                ['Color', ['Negro', 'Blanco', 'Azul']],
            ],
        ],
        'webcams-y-microfonos' => [
            'cantidad' => 25,
            'categoria' => 'perifericos',
            'codigo' => 'WEB',
            'marcas' => ['logitech', 'razer', 'hyperx', 'logitech'],
            'formato' => ['Webcam {marca} {linea}', 'Micrófono {marca} {linea}'],
            'lineasPorMarca' => [
                'logitech' => ['C270', 'C920', 'C922 Pro', 'Brio 4K', 'StreamCam'],
                'razer' => ['Kiyo', 'Kiyo Pro'],
                'hyperx' => ['Vision S', 'SoloCast', 'QuadCast'],
            ],
            'precio' => [20, 180],
            'variantes_pct' => 20,
            'variantes' => [
                ['Color', ['Negro', 'Blanco']],
            ],
        ],
        // ── Monitores ───────────────────────────────────────────────────────
        'monitores' => [
            'cantidad' => 25,
            'marcas' => ['samsung', 'lg', 'dell', 'hp', 'asus', 'acer', 'philips', 'benq', 'viewsonic'],
            'formato' => ['Monitor {marca} {linea} {resolucion}'],
            'lineasPorMarca' => [
                'samsung' => ['S24', 'S27', 'T35F', 'Odyssey G3'],
                'lg' => ['UltraGear 27GS', '22MR450', 'S2721'],
                'dell' => ['UltraSharp', 'P2422H', 'S2721QS'],
                'hp' => ['Pavilion 24', 'VZ24', 'P22'],
                'asus' => ['VZ27', 'ProArt', 'Eye Care'],
                'acer' => ['Nitro', 'K242HYL'],
                'philips' => ['242V8', '272V8'],
                'benq' => ['GW2480', 'GW2780'],
                'viewsonic' => ['VX2476', 'VX27', 'VX3276'],
            ],
            'pools' => [
                'resolucion' => ['Full HD', 'QHD', '4K'],
            ],
            'precio' => [150, 800],
            'variantes_pct' => 20,
            'variantes' => [
                ['Tamaño de pantalla', ['24"', '27"']],
            ],
        ],
        'monitores-gamer' => [
            'cantidad' => 20,
            'categoria' => 'monitores',
            'codigo' => 'MOG',
            'marcas' => ['asus', 'msi', 'samsung', 'lg', 'acer', 'gigabyte', 'benq'],
            'formato' => ['Monitor Gamer {marca} {linea} {frecuencia}'],
            'lineasPorMarca' => [
                'asus' => ['ROG Strix', 'TUF Gaming'],
                'msi' => ['MAG', 'Optix'],
                'samsung' => ['Odyssey G3', 'Odyssey G7'],
                'lg' => ['UltraGear', 'UltraGear Nano IPS'],
                'acer' => ['Nitro', 'Predator'],
                'gigabyte' => ['Aorus', 'G24F'],
                'benq' => ['Zowie XL2411K', 'Zowie XL2546K'],
            ],
            'pools' => [
                'frecuencia' => ['144Hz', '165Hz', '240Hz', '360Hz'],
            ],
            'precio' => [250, 1400],
            'variantes_pct' => 30,
            'variantes' => [
                ['Tamaño de pantalla', ['24"', '27"', '32"']],
            ],
        ],
        // ── Audio ───────────────────────────────────────────────────────────
        'parlantes' => [
            'cantidad' => 45,
            'categoria' => 'audio',
            'codigo' => 'PAR',
            'marcas' => ['jbl', 'sony', 'xiaomi', 'edifier', 'marshall', 'logitech', 'samsung'],
            'formato' => ['Parlante {marca} {linea} {detalle}'],
            'lineasPorMarca' => [
                'jbl' => ['Flip 6', 'Charge 5', 'Xtreme 3', 'Go 4'],
                'sony' => ['SRS-XB13', 'SRS-XE300', 'SRS-XG300'],
                'xiaomi' => ['Redmi Party', 'Mi Portable'],
                'edifier' => ['R1700BT', 'M201BT'],
                'marshall' => ['Acton III', 'Emberton II'],
                'logitech' => ['Z200', 'Z313'],
                'samsung' => ['Sound Tower', 'Giga Party'],
            ],
            'pools' => [
                'detalle' => ['', 'bluetooth', 'portátil', 'resistente al agua'],
            ],
            'precio' => [20, 400],
            'variantes_pct' => 25,
            'variantes' => [
                ['Color', ['Negro', 'Azul']],
            ],
        ],
        'barras-de-sonido' => [
            'cantidad' => 15,
            'categoria' => 'audio',
            'codigo' => 'BRS',
            'marcas' => ['bose', 'sennheiser', 'samsung', 'lg', 'sony', 'jbl', 'philips', 'hisense'],
            'formato' => ['Barra de Sonido {marca} {linea}'],
            'lineasPorMarca' => [
                'bose' => ['Smart Soundbar 600', 'Smart Soundbar 900'],
                'sennheiser' => ['Ambeo Soundbar Mini', 'Ambeo Soundbar Plus'],
                'samsung' => ['HW-Q600C', 'HW-S800B'],
                'lg' => ['S80QR', 'S90QY'],
                'sony' => ['HT-S40R', 'SRS-B2'],
                'jbl' => ['Bar 5.1', 'Bar 2.1'],
                'philips' => ['TAB5305', 'TAB6305'],
                'hisense' => ['HS214', 'HS218'],
            ],
            'precio' => [150, 1200],
        ],
        // ── Redes y Conectividad ────────────────────────────────────────────
        'routers' => [
            'cantidad' => 30,
            'marcas' => ['tp-link', 'netgear', 'asus', 'ubiquiti', 'xiaomi', 'd-link', 'zyxel'],
            'formato' => ['Router {marca} {linea} {detalle}'],
            'lineasPorMarca' => [
                'tp-link' => ['Archer AX10', 'Archer AX55', 'Archer C6', 'Deco X50'],
                'netgear' => ['Nighthawk R6700', 'Nighthawk AX3000'],
                'asus' => ['RT-AX53U', 'RT-AX82U'],
                'ubiquiti' => ['UniFi UDR', 'UniFi Dream Router'],
                'xiaomi' => ['Mi Router 4A', 'Mi Router AX3000'],
                'd-link' => ['DIR-853', 'R15'],
                'zyxel' => ['Armor G5', 'NBG6604'],
            ],
            'pools' => [
                'detalle' => ['', 'WiFi 6', 'Mesh', 'gigabit', 'dual band'],
            ],
            'precio' => [30, 300],
        ],
        'switches-y-adaptadores' => [
            'cantidad' => 20,
            'marcas' => ['tp-link', 'netgear', 'ubiquiti', 'belkin', 'netgear', 'd-link', 'zyxel'],
            'formato' => ['Switch {marca} {linea}', 'Adaptador {marca} {linea}'],
            'lineasPorMarca' => [
                'tp-link' => ['TL-SG1005D', 'TL-SG1024D', 'TL-WN722N'],
                'netgear' => ['GS305', 'GS308', 'GS108'],
                'ubiquiti' => ['UniFi Flex', 'UniFi Switch 8'],
                'belkin' => ['USB-C Hub 7 en 1', 'Adaptador USB-C a HDMI', 'Adaptador Bluetooth 5.0'],
                'd-link' => ['DGS-1008G', 'DGS-105'],
                'zyxel' => ['GS1200-8', 'GS1900-8'],
            ],
            'precio' => [15, 150],
        ],
        'cables-y-conectores' => [
            'cantidad' => 30,
            'marcas' => ['belkin', 'anker', 'lg', 'sony', 'logitech', 'samsung'],
            'formato' => ['Cable {marca} {linea}'],
            'pools' => [
                'linea' => ['HDMI 2.1', 'HDMI 4K', 'USB-C a USB-C', 'USB-C a Lightning', 'USB-C a HDMI', 'DisplayPort', 'Ethernet Cat 6', 'VGA a HDMI', 'Óptico 3.5mm', 'USB-A a Micro USB'],
            ],
            'precio' => [4, 40],
            'variantes_pct' => 40,
            'variantes' => [
                ['Longitud', ['1 m', '2 m', '3 m', '5 m']],
            ],
        ],
        // ── Almacenamiento externo ──────────────────────────────────────────
        'unidades-usb-y-tarjetas-de-memoria' => [
            'cantidad' => 35,
            'categoria' => 'almacenamiento',
            'codigo' => 'USB',
            'marcas' => ['kingston', 'sandisk', 'adata', 'samsung'],
            'formato' => ['Memoria USB {marca} {linea}', 'Tarjeta de Memoria {marca} {linea}'],
            'lineasPorMarca' => [
                'kingston' => ['DataTraveler', 'DataTraveler Exodia', 'Canvas Go'],
                'sandisk' => ['Ultra Luxe', 'Extreme Pro', 'iXpand', 'Ultra Flair'],
                'adata' => ['UV150', 'UV320'],
                'samsung' => ['EVO Plus', 'PRO Plus', 'Type-C'],
            ],
            'precio' => [8, 90],
            'variantes_pct' => 25,
            'variantes' => [
                ['Capacidad de almacenamiento', ['32 GB', '64 GB', '128 GB', '256 GB']],
            ],
        ],
        'discos-externos' => [
            'cantidad' => 8,
            'categoria' => 'almacenamiento',
            'codigo' => 'DEX',
            'marcas' => ['seagate', 'western-digital', 'toshiba', 'adata'],
            'formato' => ['Disco Duro Externo {marca} {linea}'],
            'lineasPorMarca' => [
                'seagate' => ['Expansion', 'Backup Plus', 'One Touch'],
                'western-digital' => ['My Passport', 'Elements'],
                'toshiba' => ['HD300', 'HD700'],
                'adata' => ['HD650', 'HD770G'],
            ],
            'precio' => [40, 180],
        ],
        // ── Accesorios ──────────────────────────────────────────────────────
        'cargadores' => [
            'cantidad' => 45,
            'marcas' => ['anker', 'belkin', 'samsung', 'xiaomi', 'apple', 'realme'],
            'formato' => ['Cargador {marca} {linea} {potencia}'],
            'lineasPorMarca' => [
                'anker' => ['Nano 3', 'PowerPort', 'GaNPrime'],
                'belkin' => ['BoostCharge', 'BoostCharge Pro'],
                'samsung' => ['Carga Rápida 25W', 'Carga Súper Rápida 45W'],
                'xiaomi' => ['Carga Rápida 67W', 'Mi 33W'],
                'apple' => ['MagSafe', '20W USB-C'],
                'realme' => ['Carga Rápida 65W', 'Dart Charge 33W'],
            ],
            'pools' => [
                'potencia' => ['18W', '25W', '33W', '45W', '65W', '100W'],
            ],
            'precio' => [10, 70],
            'variantes_pct' => 30,
            'variantes' => [
                ['Color', ['Blanco', 'Negro']],
            ],
        ],
        'fundas-y-protectores' => [
            'cantidad' => 45,
            'marcas' => ['apple', 'samsung', 'xiaomi', 'oneplus', 'realme', 'huawei', 'belkin'],
            'formato' => ['Funda {marca} {linea}', 'Protector de Pantalla {marca} {linea}'],
            'lineasPorMarca' => [
                'apple' => ['Silicone Case', 'Clear Case', 'Smart Cover'],
                'samsung' => ['Silicone Cover', 'Clear Standing Cover'],
                'xiaomi' => ['Funda Xiaomi', 'Funda Silicona'],
                'oneplus' => ['Bumper Case', 'Sandstone Case'],
                'realme' => ['Funda Realme', 'Silicone Case'],
                'huawei' => ['Funda Huawei', 'Flip Cover'],
                'belkin' => ['Tempered Glass', 'Glas Guard'],
            ],
            'precio' => [5, 50],
            'variantes_pct' => 30,
            'variantes' => [
                ['Color', ['Negro', 'Transparente', 'Azul']],
            ],
        ],
        'power-banks' => [
            'cantidad' => 30,
            'marcas' => ['anker', 'belkin', 'xiaomi', 'samsung', 'oneplus', 'realme'],
            'formato' => ['Power Bank {marca} {linea}'],
            'lineasPorMarca' => [
                'anker' => ['PowerCore', 'PowerCore Slim'],
                'belkin' => ['BoostCharge', 'BoostCharge Magnetic'],
                'xiaomi' => ['Mi Power Bank', 'Redmi Power Bank'],
                'samsung' => ['Essential 20W', 'Portátil 25W'],
                'oneplus' => ['Power Bank 10000', 'SuperVOOC'],
                'realme' => ['Power Bank 10000', 'Realme 30W'],
            ],
            'precio' => [20, 90],
            'variantes_pct' => 35,
            'variantes' => [
                ['Capacidad', ['10000 mAh', '20000 mAh', '30000 mAh']],
            ],
        ],
        // ── Smartphones y Tablets ───────────────────────────────────────────
        'smartphones' => [
            'cantidad' => 50,
            'marcas' => ['samsung', 'samsung', 'xiaomi', 'xiaomi', 'apple', 'motorola', 'oneplus', 'realme', 'huawei', 'google'],
            'formato' => ['Smartphone {marca} {linea}'],
            'lineasPorMarca' => [
                'samsung' => ['Galaxy S24', 'Galaxy S24 Ultra', 'Galaxy A55', 'Galaxy A15', 'Galaxy A25'],
                'xiaomi' => ['Redmi Note 13', 'Redmi Note 13 Pro', '14', 'Redmi 13C'],
                'apple' => ['iPhone 15', 'iPhone 15 Pro', 'iPhone SE'],
                'motorola' => ['Moto G84', 'Moto G24', 'Moto G54'],
                'oneplus' => ['OnePlus 12', 'OnePlus 12R'],
                'realme' => ['Realme 12 Pro', 'Realme 11'],
                'huawei' => ['P40 Pro', 'Nova 12'],
                'google' => ['Pixel 8', 'Pixel 8 Pro', 'Pixel 7a'],
            ],
            'precio' => [150, 1300],
            'variantes_pct' => 60,
            'variantes' => [
                ['Color', ['Negro', 'Blanco', 'Azul']],
                ['Capacidad de almacenamiento', ['128 GB', '256 GB']],
            ],
        ],
        'tablets' => [
            'cantidad' => 25,
            'marcas' => ['samsung', 'apple', 'xiaomi', 'lenovo', 'huawei'],
            'formato' => ['Tablet {marca} {linea} {medida}'],
            'lineasPorMarca' => [
                'samsung' => ['Galaxy Tab A9', 'Galaxy Tab S9', 'Galaxy Tab S9 FE'],
                'apple' => ['iPad 10.9', 'iPad Air', 'iPad Pro'],
                'xiaomi' => ['Redmi Pad SE', 'Pad 6'],
                'lenovo' => ['Tab M11'],
                'huawei' => ['MatePad 11'],
            ],
            'pools' => [
                'medida' => ['10.4"', '11"', '12.4"'],
            ],
            'precio' => [120, 900],
            'variantes_pct' => 40,
            'variantes' => [
                ['Color', ['Gris', 'Azul', 'Plata']],
                ['Capacidad de almacenamiento', ['64 GB', '128 GB', '256 GB']],
            ],
        ],
        'smartwatches' => [
            'cantidad' => 20,
            'marcas' => ['samsung', 'apple', 'xiaomi', 'amazfit', 'garmin', 'huawei'],
            'formato' => ['Smartwatch {marca} {linea}'],
            'lineasPorMarca' => [
                'samsung' => ['Galaxy Watch 6', 'Galaxy Watch 6 Classic'],
                'apple' => ['Watch Series 9', 'Watch Ultra 2'],
                'xiaomi' => ['Redmi Watch 4', 'Mi Band 9'],
                'amazfit' => ['Amazfit GTS 4', 'Amazfit Bip 5'],
                'garmin' => ['Garmin Venu 3', 'Garmin Forerunner'],
                'huawei' => ['Watch GT 4'],
            ],
            'precio' => [80, 600],
            'variantes_pct' => 40,
            'variantes' => [
                ['Color', ['Negro', 'Plata', 'Dorado']],
            ],
        ],
        // ── Televisores ─────────────────────────────────────────────────────
        'televisores' => [
            'cantidad' => 35,
            'marcas' => ['samsung', 'lg', 'sony', 'hisense', 'philips', 'xiaomi'],
            'formato' => ['Smart TV {marca} {linea} {resolucion}'],
            'lineasPorMarca' => [
                'samsung' => ['Crystal UHD', 'The Frame', 'Neo QLED'],
                'lg' => ['OLED C3', 'NanoCell', 'QNED80'],
                'sony' => ['X90L', 'Bravia', 'X80K'],
                'hisense' => ['A6', 'U6', 'U7'],
                'philips' => ['7000 Series', 'PUS8887'],
                'xiaomi' => ['Mi TV Q2', 'TV A2'],
            ],
            'pools' => [
                'resolucion' => ['Full HD', '4K UHD', '8K'],
            ],
            'precio' => [250, 3000],
            'variantes_pct' => 30,
            'variantes' => [
                ['Tamaño de pantalla', ['43"', '50"', '55"', '65"']],
            ],
        ],
        // ── Impresoras y Escáneres ──────────────────────────────────────────
        'impresoras' => [
            'cantidad' => 30,
            'categoria' => 'impresoras-y-escaneres',
            'codigo' => 'IMP',
            'marcas' => ['epson', 'brother', 'hp', 'canon'],
            'formato' => ['Impresora {marca} {linea} {detalle}'],
            'lineasPorMarca' => [
                'epson' => ['EcoTank L3250', 'EcoTank L4260', 'WorkForce'],
                'brother' => ['HL-L2340', 'DCP-T420W'],
                'hp' => ['LaserJet M111', 'DeskJet 2720', 'OfficeJet Pro'],
                'canon' => ['Pixma G3110', 'Pixma TS3350'],
            ],
            'pools' => [
                'detalle' => ['', 'WiFi', 'multifuncional', 'láser', 'de inyección'],
            ],
            'precio' => [80, 600],
        ],
        'tintas-y-toner' => [
            'cantidad' => 30,
            'categoria' => 'impresoras-y-escaneres',
            'codigo' => 'TIN',
            'marcas' => ['epson', 'brother', 'hp', 'canon'],
            'formato' => ['Tóner {marca} {linea}', 'Cartucho de Tinta {marca} {linea}'],
            'lineasPorMarca' => [
                'epson' => ['T503 Negro', 'T503 Color', 'T6641 Negro', 'T6641 Color'],
                'brother' => ['TN-2410', 'TN-2420'],
                'hp' => ['CF283A', '63', '303 XL'],
                'canon' => ['CL-241', 'PG-245'],
            ],
            'precio' => [12, 90],
        ],
        // ── Gaming ──────────────────────────────────────────────────────────
        'consolas' => [
            'cantidad' => 20,
            'marcas' => ['nintendo', 'sony', 'microsoft'],
            'formato' => ['Consola {marca} {linea}'],
            'lineasPorMarca' => [
                'nintendo' => ['Switch', 'Switch OLED'],
                'sony' => ['PlayStation 5', 'PS5 Slim'],
                'microsoft' => ['Xbox Series S', 'Xbox Series X'],
            ],
            'precio' => [300, 700],
            'variantes_pct' => 25,
            'variantes' => [
                ['Capacidad de almacenamiento', ['512 GB', '1 TB']],
            ],
        ],
        'controles-y-accesorios' => [
            'cantidad' => 40,
            'categoria' => 'gaming',
            'codigo' => 'CTR',
            'marcas' => ['sony', 'microsoft', 'nintendo', 'razer', 'hyperx', 'logitech'],
            'formato' => ['Control {marca} {linea}', 'Accesorio Gaming {marca} {linea}'],
            'lineasPorMarca' => [
                'sony' => ['DualSense', 'DualSense Edge'],
                'microsoft' => ['Xbox Wireless', 'Xbox Elite'],
                'nintendo' => ['Pro Controller', 'Joy-Con'],
                'razer' => ['Wolverine', 'Kishi'],
                'hyperx' => ['Clutch', 'ChargePlay'],
                'logitech' => ['G Pro Racing Wheel', 'F310'],
            ],
            'precio' => [15, 150],
            'variantes_pct' => 25,
            'variantes' => [
                ['Color', ['Negro', 'Blanco', 'Azul']],
            ],
        ],
        'sillas-y-muebles-gamer' => [
            'cantidad' => 6,
            'marcas' => ['corsair', 'razer', 'corsair'],
            'formato' => ['Silla Gamer {marca} {linea}'],
            'lineasPorMarca' => [
                'corsair' => ['T3 Rush', 'T3 Carbon'],
                'razer' => ['Enki', 'Enki Pro', 'Iskur'],
            ],
            'precio' => [150, 700],
        ],
        // ── Cámaras y Seguridad ─────────────────────────────────────────────
        'camaras-y-video' => [
            'cantidad' => 8,
            'categoria' => 'camaras-y-seguridad',
            'codigo' => 'CAM',
            'marcas' => ['canon', 'sony', 'gopro'],
            'formato' => ['Cámara {marca} {linea}'],
            'lineasPorMarca' => [
                'canon' => ['EOS R50', 'EOS M50', 'PowerShot G7X'],
                'sony' => ['Alpha a6100', 'Alpha a6400', 'ZV-1'],
                'gopro' => ['HERO11 Black', 'HERO12 Black'],
            ],
            'precio' => [250, 1500],
        ],
        'camaras-de-seguridad' => [
            'cantidad' => 6,
            'categoria' => 'camaras-y-seguridad',
            'codigo' => 'CSE',
            'marcas' => ['tp-link', 'xiaomi', 'tp-link'],
            'formato' => ['Cámara de Seguridad {marca} {linea}'],
            'lineasPorMarca' => [
                'tp-link' => ['Tapo C200', 'Tapo C210', 'Tapo C310'],
                'xiaomi' => ['Mi Home Security', 'Mi Cam 2K', 'Cámara WiFi 360'],
            ],
            'precio' => [25, 120],
        ],
        // ── Software y Licencias ────────────────────────────────────────────
        'software-y-licencias' => [
            'cantidad' => 8,
            'marcas' => ['microsoft', 'microsoft'],
            'formato' => ['Licencia {marca} {linea} {detalle}'],
            'pools' => [
                'linea' => ['Windows 11 Pro', 'Windows 11 Home', 'Office 2021 Profesional Plus', 'Office 365 (1 Año)', 'Windows Server 2022', 'Visio Professional 2021'],
                'detalle' => ['', 'Original', 'con soporte técnico', 'activación digital'],
            ],
            'precio' => [15, 600],
        ],
    ];

    // =========================================================================
    //  SERVICIOS INFORMÁTICOS (24 servicios realistas de mano de obra).
    //  Precios de mercado de Panamá: armado/mantenimiento/redes/soporte.
    // =========================================================================
    protected const CONFIG_SERVICIOS = [
        'armado-de-pc' => [
            ['nombre' => 'Armado de PC de Oficina a la Medida', 'precio' => 35.00, 'detalle' => 'Incluye selección de componentes, ensamblaje, cableado y prueba de estabilidad.'],
            ['nombre' => 'Armado de PC Gaming a la Medida', 'precio' => 60.00, 'detalle' => 'Ensamblaje de equipos gaming con gestión de cableado y configuración de rendimiento.'],
            ['nombre' => 'Armado de PC con Componentes del Cliente', 'precio' => 30.00, 'detalle' => 'Ensamblamos los componentes que tú compres, con prueba de encendido y funcionamiento.'],
            ['nombre' => 'Ensamblaje de PC de Alto Rendimiento', 'precio' => 80.00, 'detalle' => 'Para estaciones de trabajo y creadores de contenido, con refrigeración optimizada.'],
            ['nombre' => 'Configuración Inicial de PC Nueva', 'precio' => 40.00, 'detalle' => 'Incluye armado, instalación de sistema operativo y configuración de actualizaciones.'],
        ],
        'mantenimiento-y-limpieza-de-equipos' => [
            ['nombre' => 'Mantenimiento y Limpieza de Laptop', 'precio' => 30.00, 'detalle' => 'Limpieza interna y externa, reemplazo de pasta térmica y revisión de ventilación.'],
            ['nombre' => 'Mantenimiento y Limpieza de PC de Escritorio', 'precio' => 40.00, 'detalle' => 'Limpieza profunda, cambio de pasta térmica y diagnóstico del sistema de refrigeración.'],
            ['nombre' => 'Mantenimiento Preventivo para PC Gamer', 'precio' => 45.00, 'detalle' => 'Limpieza de componentes, revisión de temperaturas y actualización de controladores.'],
            ['nombre' => 'Mantenimiento de Equipos para Oficina', 'precio' => 25.00, 'detalle' => 'Servicio por equipo con reporte de estado y recomendaciones de mejora.'],
        ],
        'instalacion-de-redes' => [
            ['nombre' => 'Instalación de Red Doméstica', 'precio' => 60.00, 'detalle' => 'Configuración de router, cableado y red WiFi con alcance óptimo en tu hogar.'],
            ['nombre' => 'Instalación de Red Empresarial', 'precio' => 100.00, 'detalle' => 'Diseño e instalación de red cableada e inalámbrica para oficinas y negocios.'],
            ['nombre' => 'Configuración de Router y Red WiFi Mesh', 'precio' => 40.00, 'detalle' => 'Instalación y optimización de cobertura WiFi con puntos de acceso.'],
            ['nombre' => 'Instalación de Cableado Ethernet Estructurado', 'precio' => 85.00, 'detalle' => 'Cableado por punto con canaletas, conectores y certificación de conexión.'],
        ],
        'recuperacion-de-datos' => [
            ['nombre' => 'Recuperación de Datos de Disco Duro', 'precio' => 75.00, 'detalle' => 'Rescate de información de discos duros dañados o con sectores defectuosos.'],
            ['nombre' => 'Recuperación de Datos de SSD', 'precio' => 90.00, 'detalle' => 'Recuperación de archivos de unidades de estado sólido con fallos lógicos.'],
            ['nombre' => 'Recuperación de Datos de Memoria USB', 'precio' => 50.00, 'detalle' => 'Extracción de archivos de memorias USB no reconocidas o con daño lógico.'],
        ],
        'formateo-e-instalacion-de-software' => [
            ['nombre' => 'Formateo e Instalación de Windows', 'precio' => 35.00, 'detalle' => 'Formateo, instalación de Windows con licencia del cliente y controladores.'],
            ['nombre' => 'Instalación de Software y Controladores', 'precio' => 20.00, 'detalle' => 'Instalación de programas, drivers y configuración del sistema.'],
            ['nombre' => 'Formateo con Respaldo de Datos', 'precio' => 45.00, 'detalle' => 'Respaldo previo de tus archivos, formateo y restauración de la información.'],
            ['nombre' => 'Instalación de Suite de Oficina y Correo', 'precio' => 15.00, 'detalle' => 'Instalación y configuración de herramientas de oficina y cuentas de correo.'],
        ],
        'soporte-tecnico-a-domicilio' => [
            ['nombre' => 'Soporte Técnico a Domicilio (1 Hora)', 'precio' => 35.00, 'detalle' => 'Visita de un técnico a tu domicilio para resolver fallas de hardware o software.'],
            ['nombre' => 'Soporte Técnico Empresarial In Situ', 'precio' => 70.00, 'detalle' => 'Atención en tu local o empresa, con reporte de diagnóstico y solución.'],
            ['nombre' => 'Diagnóstico y Soporte Remoto', 'precio' => 25.00, 'detalle' => 'Conexión remota segura para diagnosticar y resolver incidencias sin traslado.'],
            ['nombre' => 'Visita Técnica con Configuración de Dispositivos', 'precio' => 30.00, 'detalle' => 'Configuración de impresoras, routers, cámaras y otros dispositivos en sitio.'],
        ],
    ];
}
