<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Genera dinámicamente el archivo sitemap.xml según el protocolo oficial de sitemaps.org
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap_xml', 3600, function () {
            return $this->generarXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Construye la estructura XML con todas las URLs públicas e indexables de la tienda.
     */
    protected function generarXml(): string
    {
        $items = [];

        // 1. Página de Inicio
        $items[] = [
            'loc' => url('/'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        // 2. Catálogo principal
        $items[] = [
            'loc' => route('cliente.catalogo'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        // 3. Categorías activas
        $categorias = Categoria::sinEliminar()
            ->whereNotNull('slug')
            ->orderBy('nombre')
            ->get();

        foreach ($categorias as $categoria) {
            $items[] = [
                'loc' => route('cliente.catalogo', ['categoria' => $categoria->slug]),
                'lastmod' => ($categoria->actualizado_en ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // 4. Productos activos
        $productos = Producto::sinEliminar()
            ->where('activo', true)
            ->whereNotNull('slug')
            ->orderByDesc('actualizado_en')
            ->get(['id', 'slug', 'actualizado_en']);

        foreach ($productos as $producto) {
            $items[] = [
                'loc' => route('cliente.producto.detalle', $producto->slug),
                'lastmod' => ($producto->actualizado_en ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        // 5. Páginas institucionales e informativas
        $items[] = [
            'loc' => route('terminos'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.3',
        ];

        $items[] = [
            'loc' => route('privacidad'),
            'lastmod' => now()->startOfMonth()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.3',
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($items as $item) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>' . htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= '        <lastmod>' . $item['lastmod'] . "</lastmod>\n";
            $xml .= '        <changefreq>' . $item['changefreq'] . "</changefreq>\n";
            $xml .= '        <priority>' . $item['priority'] . "</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}

