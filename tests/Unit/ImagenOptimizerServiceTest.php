<?php

namespace Tests\Unit;

use App\Services\ImagenOptimizerService;
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
        $url = $this->service->obtenerThumbnailUrl('storage/productos/175/LFPu0dmHY9U7ZBiWapQrLL8QLIQDiaXcKZF54vRP.webp', 128, 128);
        $this->assertStringContainsString('128x128.webp', $url);
        $this->assertStringContainsString('/storage/productos/175/thumbs/', $url);
    }
}
