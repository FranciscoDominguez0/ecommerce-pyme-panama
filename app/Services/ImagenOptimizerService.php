<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio de optimización y generación de miniaturas responsivas para imágenes.
 * Reduce el peso de descarga de imágenes en el catálogo y detalle de productos (LCP / Performance).
 */
class ImagenOptimizerService
{
    /**
     * Obtiene o genera una miniatura WebP de dimensiones reducidas para una imagen de producto.
     *
     * @param string|null $rutaOriginal Ruta de la imagen (ej: "storage/productos/175/abc.webp")
     * @param int $ancho Ancho objetivo en píxeles (default: 128)
     * @param int $alto Alto objetivo en píxeles (default: 128)
     * @param int $calidad Calidad de compresión WebP de 1 a 100 (default: 80)
     * @return string URL pública de la miniatura o de la imagen original como fallback
     */
    public function obtenerThumbnailUrl(?string $rutaOriginal, int $ancho = 128, int $alto = 128, int $calidad = 80): string
    {
        if (empty($rutaOriginal)) {
            return asset('images/placeholder-product.png');
        }

        // Si es un SVG, Data URI o icono, no procesar con GD
        if (str_contains($rutaOriginal, '<svg') || str_starts_with($rutaOriginal, 'data:image')) {
            return $rutaOriginal;
        }

        // Normalizar la ruta relativa dentro de storage
        $rutaLimpia = preg_replace('#^https?://[^/]+/#', '', $rutaOriginal);
        $rutaLimpia = ltrim($rutaLimpia, '/');

        if (!str_starts_with($rutaLimpia, 'storage/')) {
            return asset($rutaLimpia);
        }

        $relativoStorage = substr($rutaLimpia, strlen('storage/'));
        
        // Ubicar el archivo físico en el disco público de Laravel (compatible con Storage::fake)
        $rutaFisicaOriginal = null;
        try {
            $diskPath = Storage::disk('public')->path($relativoStorage);
            if (file_exists($diskPath) && !is_dir($diskPath)) {
                $rutaFisicaOriginal = $diskPath;
            }
        } catch (\Throwable $e) {}

        if (!$rutaFisicaOriginal) {
            $storagePath = storage_path('app/public/' . $relativoStorage);
            if (file_exists($storagePath) && !is_dir($storagePath)) {
                $rutaFisicaOriginal = $storagePath;
            } elseif (file_exists(public_path('storage/' . $relativoStorage))) {
                $rutaFisicaOriginal = public_path('storage/' . $relativoStorage);
            }
        }

        if (!$rutaFisicaOriginal || !file_exists($rutaFisicaOriginal) || is_dir($rutaFisicaOriginal)) {
            return asset($rutaLimpia);
        }

        $pathInfo = pathinfo($relativoStorage);
        $dirName = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? '';

        // Definir la ruta del thumbnail en caché
        $thumbRelativo = ($dirName && $dirName !== '.' ? $dirName . '/' : '') . 'thumbs/' . $filename . "_{$ancho}x{$alto}.webp";
        
        $thumbFisico = null;
        try {
            $thumbFisico = Storage::disk('public')->path($thumbRelativo);
        } catch (\Throwable $e) {}

        if (!$thumbFisico) {
            $thumbFisico = storage_path('app/public/' . $thumbRelativo);
        }

        // Si ya existe en caché, retornarlo inmediatamente
        if (file_exists($thumbFisico) && filesize($thumbFisico) > 0) {
            return asset('storage/' . $thumbRelativo);
        }

        // Si GD no está disponible, retornar el original
        if (!extension_loaded('gd')) {
            return asset($rutaLimpia);
        }

        try {
            $thumbDir = dirname($thumbFisico);
            if (!File::isDirectory($thumbDir)) {
                File::makeDirectory($thumbDir, 0755, true, true);
            }

            $img = $this->cargarImagenGd($rutaFisicaOriginal);
            if (!$img) {
                return asset($rutaLimpia);
            }

            $origW = imagesx($img);
            $origH = imagesy($img);

            if ($origW <= 0 || $origH <= 0) {
                imagedestroy($img);
                return asset($rutaLimpia);
            }

            // Calcular escala manteniendo la proporción
            $ratio = min($ancho / $origW, $alto / $origH);
            $nuevoAncho = max(1, (int) round($origW * $ratio));
            $nuevoAlto = max(1, (int) round($origH * $ratio));

            $canvas = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

            // Manejo de transparencia para PNG/WebP
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparente = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefilledrectangle($canvas, 0, 0, $nuevoAncho, $nuevoAlto, $transparente);

            imagecopyresampled($canvas, $img, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $origW, $origH);

            imagewebp($canvas, $thumbFisico, $calidad);

            imagedestroy($canvas);
            imagedestroy($img);

            // Sincronizar con public/storage si existe el enlace simbólico
            $publicThumbFisico = public_path('storage/' . $thumbRelativo);
            if (!file_exists($publicThumbFisico) && file_exists($thumbFisico)) {
                @File::ensureDirectoryExists(dirname($publicThumbFisico));
                @copy($thumbFisico, $publicThumbFisico);
            }

            return asset('storage/' . $thumbRelativo);
        } catch (\Throwable $e) {
            Log::warning("ImagenOptimizerService: Error al generar miniatura para {$rutaOriginal}: " . $e->getMessage());
            return asset($rutaLimpia);
        }
    }

    /**
     * Carga un recurso de imagen GD según el tipo de archivo.
     */
    private function cargarImagenGd(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) {
            return false;
        }

        return match ($info[2]) {
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            default        => false,
        };
    }
}
