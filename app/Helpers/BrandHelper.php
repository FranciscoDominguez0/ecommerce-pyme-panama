<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BrandHelper
{
    /**
     * Nombres oficiales formateados de las marcas reconocidas.
     */
    protected static array $nombresFormateados = [
        'adata' => 'Adata',
        'amd' => 'AMD',
        'apc' => 'APC',
        'apple' => 'Apple',
        'asus' => 'ASUS',
        'cannon' => 'Canon',
        'canon' => 'Canon',
        'dell' => 'Dell',
        'epson' => 'Epson',
        'hp' => 'HP',
        'intel' => 'Intel',
        'jbl' => 'JBL',
        'kingston' => 'Kingston',
        'lenovo' => 'Lenovo',
        'logitech' => 'Logitech',
        'msi' => 'MSI',
        'razer' => 'Razer',
        'samsung' => 'Samsung',
        'sony' => 'Sony',
        'tp-link' => 'TP-Link',
        'tplink' => 'TP-Link',
        'xiaomi' => 'Xiaomi',
    ];

    /**
     * Retorna el listado completo de marcas disponibles escaneando la carpeta public/images/Marcas
     */
    public static function getAvailableBrands(): array
    {
        $dirPath = public_path('images/Marcas');
        $brands = [];

        if (File::isDirectory($dirPath)) {
            $files = File::files($dirPath);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $baseName = pathinfo($filename, PATHINFO_FILENAME);
                $key = strtolower(trim($baseName));
                
                $nombre = self::$nombresFormateados[$key] ?? ucfirst($baseName);

                $brands[$key] = [
                    'nombre' => $nombre,
                    'slug' => Str::slug($nombre),
                    'filename' => $filename,
                    'path' => 'images/Marcas/' . $filename,
                    'url' => asset('images/Marcas/' . $filename),
                ];
            }
        }

        // Agregar Apple si no está en la carpeta de imágenes
        if (!isset($brands['apple'])) {
            $brands['apple'] = [
                'nombre' => 'Apple',
                'slug' => 'apple',
                'filename' => null,
                'path' => null,
                'url' => null,
            ];
        }

        // Ordenar alfabéticamente por nombre
        uasort($brands, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));

        return array_values($brands);
    }

    /**
     * Busca la imagen correspondiente a una marca en public/images/Marcas
     */
    public static function getBrandImagePath(?string $marca): ?string
    {
        if (empty($marca)) {
            return null;
        }

        $marcaNormalizada = strtolower(trim($marca));
        $dirPath = public_path('images/Marcas');

        if (!File::isDirectory($dirPath)) {
            return null;
        }

        // 1. Coincidencias especiales conocidas
        $mapeos = [
            'canon' => 'cannon.webp',
            'cannon' => 'cannon.webp',
            'tplink' => 'tp-link.webp',
            'tp-link' => 'tp-link.webp',
        ];

        if (isset($mapeos[$marcaNormalizada])) {
            $file = $mapeos[$marcaNormalizada];
            if (File::exists($dirPath . DIRECTORY_SEPARATOR . $file)) {
                return 'images/Marcas/' . $file;
            }
        }

        // 2. Búsqueda directa por nombre o mayúsculas/minúsculas
        $archivos = File::files($dirPath);
        foreach ($archivos as $archivo) {
            $nombreSinExt = strtolower(pathinfo($archivo->getFilename(), PATHINFO_FILENAME));
            if ($nombreSinExt === $marcaNormalizada) {
                return 'images/Marcas/' . $archivo->getFilename();
            }
        }

        return null;
    }

    /**
     * Retorna el HTML del logotipo oficial de la marca dada.
     */
    public static function getLogoHtml(?string $marca, ?string $customLogoUrl = null): string
    {
        if (!empty($customLogoUrl)) {
            return '<img src="' . htmlspecialchars($customLogoUrl) . '" alt="' . htmlspecialchars($marca ?? 'Marca') . '" class="h-6 max-h-7 max-w-[85px] object-contain select-none">';
        }

        if (empty($marca)) {
            return '<span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-black tracking-wider uppercase">Oficial</span>';
        }

        // 1. Buscar en la base de datos si existe el registro Brand
        try {
            $brand = \App\Models\Brand::where('name', 'ILIKE', $marca)
                ->orWhere('slug', 'ILIKE', Str::slug($marca))
                ->first();

            if ($brand && $brand->logo_url) {
                return '<img src="' . htmlspecialchars($brand->logo_url) . '" alt="' . htmlspecialchars($brand->name) . '" class="h-6 max-h-7 max-w-[85px] object-contain select-none">';
            }
        } catch (\Throwable $e) {
            // Fallback silencioso si la BD aún no ha corrido la migración
        }

        // 2. Buscar en la carpeta física public/images/Marcas
        $imagePath = self::getBrandImagePath($marca);
        if ($imagePath) {
            return '<img src="' . asset($imagePath) . '" alt="' . htmlspecialchars($marca ?? 'Marca') . '" class="h-6 max-h-7 max-w-[85px] object-contain select-none">';
        }

        $marcaNormalizada = strtolower(trim($marca ?? ''));

        switch ($marcaNormalizada) {
            case 'apple':
                return '<svg class="h-6 w-6 text-black fill-current" viewBox="0 0 170 170">
                            <path d="M150.37 130.25c-2.45 5.66-5.35 10.87-8.71 15.66-4.58 6.53-8.33 11.05-11.22 13.56-4.48 4.12-9.28 6.23-14.42 6.35-3.69 0-8.14-1.05-13.32-3.18-5.19-2.12-9.97-3.17-14.34-3.17-4.58 0-9.49 1.05-14.75 3.17-5.26 2.13-9.5 3.24-12.74 3.35-4.35.13-9.16-1.9-14.42-6.08-3.7-3.04-7.7-7.88-12-14.52-6.55-10.13-11.49-21.36-14.81-33.7-3.32-12.33-4.99-23.77-4.99-34.3 0-14.61 3.73-26.68 11.2-36.21 7.46-9.53 16.73-14.39 27.79-14.57 4.89 0 10.36 1.34 16.4 4.02 6.04 2.68 9.77 4.07 11.19 4.17 1.12 0 5.09-1.52 11.91-4.57 6.83-3.04 12.63-4.37 17.41-3.99 13.25 1.13 23.36 5.86 30.34 14.18-11.83 7.15-17.61 16.89-17.33 29.2.29 9.69 4.15 17.65 11.58 23.87 7.43 6.22 16.27 9.78 26.51 10.68-2.6 7.82-5.74 15.65-9.41 23.49zm-38.64-106.8c0-7.39 2.65-14.18 7.95-20.36 5.3-6.19 11.75-9.83 19.34-10.93.9 4.02 1.35 7.82 1.35 11.4 0 7.39-2.78 14.35-8.33 20.88-5.55 6.53-12.31 10.23-20.31 11.1-.38-3.9-.38-7.93 0-12.09z"/>
                        </svg>';

            default:
                return '<span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-black tracking-wider uppercase">' . htmlspecialchars($marca ?: 'Oficial') . '</span>';
        }
    }
}
