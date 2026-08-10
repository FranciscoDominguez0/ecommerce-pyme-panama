<?php

namespace Tests\Feature;

use App\Models\Categoria;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_storefront_pages_render_and_use_vite_build(): void
    {
        $rutas = ['/', '/catalogo', '/login', '/register', '/carrito'];

        foreach ($rutas as $ruta) {
            $respuesta = $this->get($ruta);
            $this->assertSame(200, $respuesta->getStatusCode(), $ruta);
            $this->assertStringContainsString('/build/assets/app-', $respuesta->getContent(), $ruta . ' build');
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $respuesta->getContent(), $ruta . ' cdn');
        }
    }

    public function test_wire_navigate_present_on_storefront(): void
    {
        foreach (['/', '/catalogo', '/carrito'] as $ruta) {
            $respuesta = $this->get($ruta);
            $this->assertGreaterThan(0, substr_count($respuesta->getContent(), 'wire:navigate'), $ruta);
        }
    }

    public function test_catalogo_por_categoria_render(): void
    {
        $categoria = Categoria::activas()->first();

        if (!$categoria) {
            $this->markTestSkipped('No hay categorías activas.');
        }

        $respuesta = $this->get('/catalogo/' . $categoria->slug);
        $this->assertSame(200, $respuesta->getStatusCode());
    }
}