<?php

namespace App\Helpers;

use App\Models\Brand;
use Illuminate\Support\Str;

class BrandHelper
{
    /**
     * Retorna el listado completo de marcas registradas en la base de datos.
     */
    public static function getAvailableBrands(): array
    {
        try {
            return Brand::orderBy('is_suggested', 'desc')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function (Brand $brand) {
                    return [
                        'id' => $brand->id,
                        'nombre' => $brand->name,
                        'slug' => $brand->slug,
                        'path' => $brand->image_path,
                        'url' => $brand->logo_url,
                        'is_suggested' => (bool) $brand->is_suggested,
                        'verified' => (bool) $brand->verified,
                    ];
                })
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Busca la ruta de imagen registrada en la base de datos para la marca dada.
     */
    public static function getBrandImagePath(?string $marca): ?string
    {
        if (empty($marca)) {
            return null;
        }

        try {
            $brand = Brand::where('name', 'ILIKE', trim($marca))
                ->orWhere('slug', 'ILIKE', Str::slug($marca))
                ->first();

            return $brand?->image_path;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Retorna el HTML del logotipo oficial de la marca desde la base de datos o fallback.
     */
    public static function getLogoHtml(?string $marca, ?string $customLogoUrl = null): string
    {
        if (!empty($customLogoUrl)) {
            return '<img src="' . htmlspecialchars($customLogoUrl) . '" alt="' . htmlspecialchars($marca ?? 'Marca') . '" class="h-6 max-h-7 max-w-[85px] object-contain select-none">';
        }

        if (empty($marca)) {
            return '<span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-black tracking-wider uppercase">Oficial</span>';
        }

        try {
            $brand = Brand::where('name', 'ILIKE', trim($marca))
                ->orWhere('slug', 'ILIKE', Str::slug($marca))
                ->first();

            if ($brand) {
                return $brand->logo_html;
            }
        } catch (\Throwable $e) {
            // Silencioso si la BD no está disponible
        }

        return '<span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-black tracking-wider uppercase">' . htmlspecialchars($marca) . '</span>';
    }
}
