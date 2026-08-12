<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder de categorías y subcategorías (fuente canónica del catálogo).
 *
 * Misma lógica que BrandSeeder: lista curada en español, slug derivado SIEMPRE
 * del nombre vía Str::slug($nombre) (nombre y slug jamás divergen), activo = true,
 * orden_visualizacion por orden de aparición e imagen_ruta = null (las imágenes
 * se agregan manualmente más adelante).
 *
 * Primero crea las categorías raíz (padre_id = null) y luego las subcategorías
 * referenciando el padre_id correcto (FK auto-referenciada de la tabla categorias).
 *
 * Es idempotente: usa firstOrCreate por slug, por lo que se puede ejecutar las
 * veces que sea necesario sin duplicar ni romper nada.
 */
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categoriasIniciales = [
            [
                'nombre' => 'Laptops y Computadoras',
                'subcategorias' => ['Laptops', 'Laptops Gamer', 'Computadoras de Escritorio'],
            ],
            [
                'nombre' => 'Componentes de PC',
                'subcategorias' => ['Procesadores', 'Tarjetas Gráficas', 'Memorias RAM', 'Fuentes de Poder', 'Almacenamiento Interno'],
            ],
            [
                'nombre' => 'Periféricos',
                'subcategorias' => ['Teclados', 'Mouse', 'Audífonos'],
            ],
            [
                'nombre' => 'Monitores',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Audio',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Redes y Conectividad',
                'subcategorias' => ['Routers', 'Switches y Adaptadores', 'Cables y Conectores'],
            ],
            [
                'nombre' => 'Almacenamiento',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Accesorios',
                'subcategorias' => ['Cargadores', 'Power Banks', 'Fundas y Protectores'],
            ],
            [
                'nombre' => 'Smartphones y Tablets',
                'subcategorias' => ['Smartphones', 'Tablets', 'Smartwatches'],
            ],
            [
                'nombre' => 'Televisores',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Impresoras y Escáneres',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Gaming',
                'subcategorias' => ['Consolas', 'Sillas y Muebles Gamer'],
            ],
            [
                'nombre' => 'Cámaras y Seguridad',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Software y Licencias',
                'subcategorias' => [],
            ],
            [
                'nombre' => 'Servicios Informáticos',
                'subcategorias' => [
                    'Armado de PC',
                    'Mantenimiento y Limpieza de Equipos',
                    'Instalación de Redes',
                    'Recuperación de Datos',
                    'Formateo e Instalación de Software',
                    'Soporte Técnico a Domicilio',
                ],
            ],
        ];

        $orden = 0;
        $total = 0;

        foreach ($categoriasIniciales as $padre) {
            // ── Categoría raíz (padre_id = null) ───────────────────────────
            $slugPadre = Str::slug($padre['nombre']);
            $orden++;
            $categoriaPadre = Categoria::firstOrCreate(
                ['slug' => $slugPadre],
                [
                    'padre_id' => null,
                    'nombre' => $padre['nombre'],
                    'descripcion' => "Categoría de {$padre['nombre']} para el catálogo de tecnología.",
                    'imagen_ruta' => null,
                    'activo' => true,
                    'orden_visualizacion' => $orden,
                ]
            );
            $total++;

            // ── Subcategorías (referencian el padre_id) ────────────────────
            foreach ($padre['subcategorias'] as $nombreHija) {
                $slugHija = Str::slug($nombreHija);
                $orden++;
                Categoria::firstOrCreate(
                    ['slug' => $slugHija],
                    [
                        'padre_id' => $categoriaPadre->id,
                        'nombre' => $nombreHija,
                        'descripcion' => "Subcategoría {$nombreHija} dentro de {$padre['nombre']}.",
                        'imagen_ruta' => null,
                        'activo' => true,
                        'orden_visualizacion' => $orden,
                    ]
                );
                $total++;
            }
        }

        $this->verificarSlugs();
        $this->command->info("Seeder de Categorías ejecutado exitosamente con {$total} categorías (raíz + subcategorías).");
    }

    /**
     * Verifica que el slug de TODAS las categorías coincida con Str::slug(nombre).
     */
    protected function verificarSlugs(): void
    {
        $fallos = 0;
        foreach (Categoria::all() as $cat) {
            $esperado = Str::slug($cat->nombre);
            if ($cat->slug !== $esperado) {
                $fallos++;
                $this->command->warn("  ⚠ Mismatch categoría '{$cat->nombre}': slug '{$cat->slug}' != '{$esperado}'");
            }
        }
        if ($fallos === 0) {
            $this->command->info('  ✅ Todas las categorías: nombre real en español y slug = Str::slug(nombre).');
        }
    }
}
