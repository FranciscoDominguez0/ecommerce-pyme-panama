<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BrandService
{
    // Genera un slug único para la marca
    public function generarSlug(string $nombre, ?string $slugBase = null, ?int $ignorarId = null): string
    {
        $slug = !empty($slugBase) ? Str::slug($slugBase) : Str::slug($nombre);
        $slugOriginal = $slug;
        $contador = 1;

        $consulta = Brand::where('slug', $slug);
        if ($ignorarId) {
            $consulta->where('id', '!=', $ignorarId);
        }

        while ($consulta->exists()) {
            $slug = "{$slugOriginal}-{$contador}";
            $contador++;
            $consulta = Brand::where('slug', $slug);
            if ($ignorarId) {
                $consulta->where('id', '!=', $ignorarId);
            }
        }

        return $slug;
    }

    // Procesa y almacena el archivo del logo, preparando datos para disco y columna bytea de PostgreSQL
    public function procesarLogo(?UploadedFile $archivo, string $nombreMarca): array
    {
        if (!$archivo || !$archivo->isValid()) {
            return [
                'resource' => null,
                'mime' => null,
                'ruta' => null,
            ];
        }

        $extension = $archivo->getClientOriginalExtension() ?: 'webp';
        $mime = $archivo->getMimeType();
        $nombreArchivo = Str::slug($nombreMarca) . '.' . $extension;
        $directorioDestino = public_path('images/Marcas');

        if (!File::isDirectory($directorioDestino)) {
            File::makeDirectory($directorioDestino, 0755, true, true);
        }

        $archivo->move($directorioDestino, $nombreArchivo);
        $rutaCompleta = 'images/Marcas/' . $nombreArchivo;

        $bytes = File::get($directorioDestino . DIRECTORY_SEPARATOR . $nombreArchivo);
        $recursoMemoria = fopen('php://memory', 'r+');
        fwrite($recursoMemoria, $bytes);
        rewind($recursoMemoria);

        return [
            'resource' => $recursoMemoria,
            'mime' => $mime,
            'ruta' => $rutaCompleta,
        ];
    }

    // Registra una nueva marca y vincula productos existentes que coincidan con su nombre
    public function crear(array $datos, ?UploadedFile $archivoLogo = null, bool $verificada = true): Brand
    {
        $nombre = trim($datos['name']);
        $slug = $this->generarSlug($nombre, $datos['slug'] ?? null);
        $logo = $this->procesarLogo($archivoLogo, $nombre);

        $brand = Brand::create([
            'name' => $nombre,
            'slug' => $slug,
            'image' => $logo['resource'],
            'image_mime' => $logo['mime'],
            'image_path' => $logo['ruta'],
            'verified' => $verificada,
        ]);

        if (is_resource($logo['resource'])) {
            fclose($logo['resource']);
        }

        // Vincular productos que tengan el nombre o slug de esta marca registrado en texto plano
        Producto::whereNull('brand_id')
            ->where(function ($q) use ($nombre, $slug) {
                $q->where('marca', 'ILIKE', $nombre)
                  ->orWhere('marca', 'ILIKE', $slug);
            })
            ->update(['brand_id' => $brand->id, 'marca' => $nombre]);

        AuditoriaService::registrar(
            'Marcas',
            'crear',
            "Marca '{$brand->name}' creada con éxito.",
            null,
            $brand->only(['id', 'name', 'slug', 'verified'])
        );

        return $brand;
    }

    // Actualiza los datos de una marca y sincroniza los productos asociados
    public function actualizar(Brand $brand, array $datos, ?UploadedFile $archivoLogo = null, bool $verificada = false): Brand
    {
        $nombre = trim($datos['name']);
        $slug = $this->generarSlug($nombre, $datos['slug'] ?? null, $brand->id);

        $datosActualizar = [
            'name' => $nombre,
            'slug' => $slug,
            'verified' => $verificada,
        ];

        $recursoCerrar = null;
        if ($archivoLogo && $archivoLogo->isValid()) {
            $logo = $this->procesarLogo($archivoLogo, $nombre);
            $datosActualizar['image'] = $logo['resource'];
            $datosActualizar['image_mime'] = $logo['mime'];
            $datosActualizar['image_path'] = $logo['ruta'];
            $recursoCerrar = $logo['resource'];
        }

        $valorAnterior = $brand->only(['name', 'slug', 'verified']);
        $brand->update($datosActualizar);

        if (is_resource($recursoCerrar)) {
            fclose($recursoCerrar);
        }

        // Sincronizar el nombre en los productos asociados
        Producto::where('brand_id', $brand->id)->update(['marca' => $nombre]);

        AuditoriaService::registrar(
            'Marcas',
            'actualizar',
            "Marca '{$brand->name}' actualizada.",
            $valorAnterior,
            $brand->only(['name', 'slug', 'verified'])
        );

        return $brand;
    }

    // Desvincula productos asociados y elimina la marca
    public function eliminar(Brand $brand): void
    {
        $nombre = $brand->name;
        $datosAnteriores = $brand->only(['id', 'name', 'slug']);

        // Desvincular productos antes de eliminar
        Producto::where('brand_id', $brand->id)->update(['brand_id' => null]);

        $brand->delete();

        AuditoriaService::registrar(
            'Marcas',
            'eliminar',
            "Marca '{$nombre}' eliminada.",
            $datosAnteriores,
            null
        );
    }
}
