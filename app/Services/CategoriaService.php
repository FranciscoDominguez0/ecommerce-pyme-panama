<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategoriaService
{
    // Obtiene los datos principales para el listado de categorías del panel administrativo
    public function obtenerCategoriasListado(?string $busqueda = null, ?string $filtroEstado = 'all', int $porPagina = 15)
    {
        $query = Categoria::sinEliminar()
            ->with(['padre', 'hijas'])
            ->withCount(['productos' => function ($q) {
                $q->whereNull('eliminado_en');
            }]);

        if (!empty($busqueda)) {
            $busquedaLimpia = trim($busqueda);
            $query->where(function ($q) use ($busquedaLimpia) {
                $q->whereRaw('unaccent(nombre) ILIKE unaccent(?)', ["%{$busquedaLimpia}%"])
                  ->orWhereRaw('unaccent(slug) ILIKE unaccent(?)', ["%{$busquedaLimpia}%"])
                  ->orWhereRaw('unaccent(descripcion) ILIKE unaccent(?)', ["%{$busquedaLimpia}%"]);
            });
        }

        if ($filtroEstado === 'active') {
            $query->where('activo', true);
        } elseif ($filtroEstado === 'inactive') {
            $query->where('activo', false);
        }

        if (empty($busqueda)) {
            $query->orderByRaw('COALESCE(padre_id, id), padre_id IS NOT NULL, orden_visualizacion ASC, nombre ASC');
        } else {
            $query->orderBy('orden_visualizacion', 'asc')->orderBy('nombre', 'asc');
        }

        return $query->paginate($porPagina)->withQueryString();
    }

    // Calcula los contadores clave para las tarjetas superiores
    public function obtenerMetricas(): array
    {
        return [
            'totalCategorias' => Categoria::sinEliminar()->count(),
            'categoriasPrincipalesCount' => Categoria::principales()->count(),
            'subcategoriasCount' => Categoria::sinEliminar()->whereNotNull('padre_id')->count(),
            'totalProductosAsignados' => Producto::whereNotNull('categoria_id')->whereNull('eliminado_en')->count(),
        ];
    }

    // Formatea la lista jerárquica con niveles y nombres de ancestros
    public function formatearPadresJerarquicos(Collection $padresCollection): Collection
    {
        $mapaCategorias = Categoria::sinEliminar()->get()->keyBy('id');
        $porPadre = $padresCollection->groupBy(function ($cat) {
            return $cat->padre_id ?? 'root';
        });

        $porPadre->transform(function ($grupo) {
            return $grupo->sortBy(function ($item) {
                return strtolower($item->nombre);
            });
        });

        $ordenadoJerarquico = collect();
        $agregarConHijos = function ($padreId) use (&$agregarConHijos, $porPadre, &$ordenadoJerarquico) {
            $key = $padreId ?? 'root';
            if ($porPadre->has($key)) {
                foreach ($porPadre->get($key) as $categoria) {
                    $ordenadoJerarquico->push($categoria);
                    $agregarConHijos($categoria->id);
                }
            }
        };

        $agregarConHijos(null);
        $incluidosIds = $ordenadoJerarquico->pluck('id')->all();

        $huerfanas = $padresCollection->reject(function ($cat) use ($incluidosIds) {
            return in_array($cat->id, $incluidosIds);
        })->sortBy(function ($item) {
            return strtolower($item->nombre);
        });

        $coleccionFinal = $ordenadoJerarquico->concat($huerfanas);

        return $coleccionFinal->values()->map(function ($cat) use ($mapaCategorias) {
            $ancestros = [];
            $actualId = $cat->padre_id;

            while ($actualId && isset($mapaCategorias[$actualId])) {
                $padreObj = $mapaCategorias[$actualId];
                array_unshift($ancestros, $padreObj->nombre);
                $actualId = $padreObj->padre_id;
            }

            $rutaPadres = !empty($ancestros) ? implode(' > ', $ancestros) : null;
            $nivel = count($ancestros);

            return [
                'id' => (int) $cat->id,
                'nombre' => $cat->nombre,
                'padre_nombre' => $cat->padre ? $cat->padre->nombre : null,
                'ruta_jerarquica' => $rutaPadres ? "{$rutaPadres} > {$cat->nombre}" : $cat->nombre,
                'ruta_padres' => $rutaPadres,
                'nivel' => $nivel,
                'activo' => (bool) $cat->activo,
            ];
        });
    }

    // Obtiene de forma recursiva los identificadores de todas las subcategorías descendientes
    public function obtenerIdsDescendientes(Categoria $categoria): array
    {
        $ids = [];
        $hijas = Categoria::sinEliminar()->where('padre_id', $categoria->id)->get();

        foreach ($hijas as $hija) {
            $ids[] = $hija->id;
            $ids = array_merge($ids, $this->obtenerIdsDescendientes($hija));
        }

        return $ids;
    }

    // Genera un slug único para la categoría
    public function generarSlug(string $nombre, ?string $slugBase = null, ?int $ignorarId = null): string
    {
        $slug = !empty($slugBase) ? Str::slug($slugBase) : Str::slug($nombre);
        $slugOriginal = $slug;
        $contador = 1;

        $consulta = Categoria::where('slug', $slug);
        if ($ignorarId) {
            $consulta->where('id', '!=', $ignorarId);
        }

        while ($consulta->exists()) {
            $slug = "{$slugOriginal}-{$contador}";
            $contador++;
            $consulta = Categoria::where('slug', $slug);
            if ($ignorarId) {
                $consulta->where('id', '!=', $ignorarId);
            }
        }

        return $slug;
    }

    // Guarda o reemplaza la imagen de una categoría
    public function guardarImagen(?UploadedFile $archivo, ?string $rutaAnterior = null): ?string
    {
        if ($archivo && $archivo->isValid()) {
            $this->eliminarArchivoImagen($rutaAnterior);

            $nombreArchivo = 'cat_' . time() . '_' . Str::random(8) . '.' . $archivo->getClientOriginalExtension();
            $carpetaDestino = public_path('uploads/categorias');

            if (!File::isDirectory($carpetaDestino)) {
                File::makeDirectory($carpetaDestino, 0755, true, true);
            }

            $archivo->move($carpetaDestino, $nombreArchivo);
            return 'uploads/categorias/' . $nombreArchivo;
        }

        return $rutaAnterior;
    }

    // Elimina el archivo de imagen del disco
    public function eliminarArchivoImagen(?string $ruta): void
    {
        if ($ruta && File::exists(public_path($ruta))) {
            File::delete(public_path($ruta));
        }
    }

    // Crea una nueva categoría con sus validaciones de negocio y auditoría
    public function crear(array $datos, ?UploadedFile $imagen = null): Categoria
    {
        $slug = $this->generarSlug($datos['nombre'], $datos['slug'] ?? null);
        $imagenRuta = $this->guardarImagen($imagen);

        $categoria = Categoria::create([
            'nombre' => $datos['nombre'],
            'slug' => $slug,
            'padre_id' => !empty($datos['padre_id']) ? (int) $datos['padre_id'] : null,
            'descripcion' => $datos['descripcion'] ?? null,
            'imagen_ruta' => $imagenRuta,
            'activo' => isset($datos['activo']) ? (bool) $datos['activo'] : true,
            'exento_envio' => (bool) ($datos['exento_envio'] ?? false),
            'orden_visualizacion' => isset($datos['orden_visualizacion']) ? (int) $datos['orden_visualizacion'] : 0,
        ]);

        AuditoriaService::registrar(
            'Categorías',
            'crear',
            "Categoría '{$categoria->nombre}' creada con éxito.",
            null,
            $categoria->toArray()
        );

        return $categoria;
    }

    // Actualiza los datos de la categoría y sincroniza productos dependientes
    public function actualizar(Categoria $categoria, array $datos, ?UploadedFile $imagen = null, bool $eliminarImagen = false): Categoria
    {
        $padreId = !empty($datos['padre_id']) ? (int) $datos['padre_id'] : null;
        $slug = $this->generarSlug($datos['nombre'], $datos['slug'] ?? null, $categoria->id);
        $imagenRuta = $categoria->imagen_ruta;

        if ($eliminarImagen) {
            $this->eliminarArchivoImagen($imagenRuta);
            $imagenRuta = null;
        }

        if ($imagen) {
            $imagenRuta = $this->guardarImagen($imagen, $imagenRuta);
        }

        $valorAnterior = $categoria->toArray();

        $categoria->update([
            'nombre' => $datos['nombre'],
            'slug' => $slug,
            'padre_id' => $padreId,
            'descripcion' => $datos['descripcion'] ?? null,
            'imagen_ruta' => $imagenRuta,
            'activo' => isset($datos['activo']) ? (bool) $datos['activo'] : false,
            'exento_envio' => (bool) ($datos['exento_envio'] ?? false),
            'orden_visualizacion' => isset($datos['orden_visualizacion']) ? (int) $datos['orden_visualizacion'] : 0,
        ]);

        $this->sincronizarProductosDigitales($categoria);

        AuditoriaService::registrar(
            'Categorías',
            'actualizar',
            "Categoría '{$categoria->nombre}' actualizada.",
            $valorAnterior,
            $categoria->toArray()
        );

        return $categoria;
    }

    // Marca como eliminada una categoría si no tiene relaciones dependientes
    public function eliminar(Categoria $categoria): void
    {
        $valorAnterior = $categoria->toArray();

        $categoria->update([
            'eliminado_en' => Carbon::now(),
            'activo' => false,
        ]);

        AuditoriaService::registrar(
            'Categorías',
            'eliminar',
            "Categoría '{$categoria->nombre}' (ID #{$categoria->id}) eliminada.",
            $valorAnterior,
            null
        );
    }

    // Alterna el estado activo o inactivo
    public function alternarEstado(Categoria $categoria): bool
    {
        $estadoAnterior = $categoria->activo;
        $categoria->activo = !$categoria->activo;
        $categoria->save();

        AuditoriaService::registrar(
            'Categorías',
            'actualizar_estado',
            "Estado de categoría '{$categoria->nombre}' cambiado a: " . ($categoria->activo ? 'Activo' : 'Inactivo'),
            ['activo' => $estadoAnterior],
            ['activo' => $categoria->activo]
        );

        return $categoria->activo;
    }

    // Alterna la exención de cobro de envío y sincroniza productos
    public function alternarExentoEnvio(Categoria $categoria): bool
    {
        $nuevoEstado = !$categoria->exento_envio_directo;
        $categoria->exento_envio = $nuevoEstado;
        $categoria->save();

        $this->sincronizarProductosDigitales($categoria);

        $descripcion = $nuevoEstado
            ? ($categoria->esPrincipal()
                ? "Categoría '{$categoria->nombre}' marcada como sin envío (aplicado a sus subcategorías)."
                : "Subcategoría '{$categoria->nombre}' marcada como sin envío.")
            : ($categoria->esPrincipal()
                ? "Categoría '{$categoria->nombre}' configurada con cobro de envío."
                : "Subcategoría '{$categoria->nombre}' configurada con cobro de envío.");

        AuditoriaService::registrar(
            'Categorías',
            'actualizar_exento_envio',
            $descripcion,
            ['exento_envio' => !$nuevoEstado],
            ['exento_envio' => $nuevoEstado]
        );

        return $nuevoEstado;
    }

    // Sincroniza la propiedad es_digital en productos asociados a la categoría y sus hijas
    protected function sincronizarProductosDigitales(Categoria $categoria): void
    {
        $categoriaIds = $categoria->hijas()->pluck('id')->push($categoria->id);
        Producto::whereIn('categoria_id', $categoriaIds)->update(['es_digital' => (bool) $categoria->exento_envio]);
    }
}
