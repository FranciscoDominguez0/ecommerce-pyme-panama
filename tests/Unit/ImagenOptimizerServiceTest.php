<?php

namespace Tests\Unit;

use App\Services\ImagenOptimizerService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagenOptimizerServiceTest extends TestCase
{
    private ImagenOptimizerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ImagenOptimizerService();
    }

    public function test_retorna_fallback_si_ruta_es_vacia(): void
    {
        $url = $this->service->obtenerThumbnailUrl('');
        $this->assertStringContainsString('placeholder-product.png', $url);

        $urlNull = $this->service->obtenerThumbnailUrl(null);
        $this->assertStringContainsString('placeholder-product.png', $urlNull);
    }

    public function test_retorna_misma_ruta_para_svg_o_data_uri(): void
    {
        $svg = '<svg viewBox="0 0 10 10"></svg>';
        $this->assertEquals($svg, $this->service->obtenerThumbnailUrl($svg));

        $dataUri = 'data:image/png;base64,iVBORw0KGgo=';
        $this->assertEquals($dataUri, $this->service->obtenerThumbnailUrl($dataUri));
    }

    public function test_genera_o_resuelve_thumbnail_para_imagen_en_storage(): void
    {
        Storage::fake('public');

        // Generar una imagen PNG válida de prueba en memoria
        $canvas = imagecreatetruecolor(200, 200);
        $fondo = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, 200, 200, $fondo);

        ob_start();
        imagepng($canvas);
        $contenidoPng = ob_get_clean();
        imagedestroy($canvas);

        Storage::disk('public')->put('productos/test/sample.png', $contenidoPng);

        $url = $this->service->obtenerThumbnailUrl('storage/productos/test/sample.png', 100, 100);

        $this->assertStringContainsString('100x100.webp', $url);
        $this->assertStringContainsString('productos/test/thumbs/', $url);
        $this->assertTrue(Storage::disk('public')->exists('productos/test/thumbs/sample_100x100.webp'));
    }
}
