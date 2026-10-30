<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityAndSitemapTest extends TestCase
{
    #[Test]
    public function las_respuestas_web_incluyen_content_security_policy(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertTrue($response->headers->has('Content-Security-Policy'));

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src", $csp);
        $this->assertStringContainsString("style-src", $csp);
        $this->assertStringContainsString("https://www.googletagmanager.com", $csp);
        $this->assertStringContainsString("https://js.stripe.com", $csp);
    }

    #[Test]
    public function las_respuestas_web_incluyen_permissions_policy(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertTrue($response->headers->has('Permissions-Policy'));

        $policy = $response->headers->get('Permissions-Policy');
        $this->assertStringContainsString('camera=(self)', $policy);
        $this->assertStringContainsString('microphone=()', $policy);
        $this->assertStringContainsString('geolocation=()', $policy);
    }

    #[Test]
    public function las_respuestas_web_incluyen_hsts(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertTrue($response->headers->has('Strict-Transport-Security'));
        $this->assertStringContainsString('max-age=31536000', $response->headers->get('Strict-Transport-Security'));
        $this->assertStringContainsString('includeSubDomains', $response->headers->get('Strict-Transport-Security'));
    }

    #[Test]
    public function el_archivo_security_txt_es_accesible(): void
    {
        $response = $this->get('/.well-known/security.txt');

        $response->assertOk();
        $this->assertStringContainsString('Contact: mailto:seguridad@rsfsecure.site', $response->getContent());
        $this->assertStringContainsString('Expires:', $response->getContent());
    }

    #[Test]
    public function las_rutas_de_login_y_registro_tienen_rate_limiting(): void
    {
        $responseLogin = $this->post('/login', []);
        $this->assertTrue($responseLogin->headers->has('X-RateLimit-Limit'));
        $this->assertSame(5, (int) $responseLogin->headers->get('X-RateLimit-Limit'));

        $responseRegister = $this->post('/register', []);
        $this->assertTrue($responseRegister->headers->has('X-RateLimit-Limit'));
        $this->assertSame(3, (int) $responseRegister->headers->get('X-RateLimit-Limit'));
    }

    #[Test]
    public function el_menu_movil_no_expone_ruta_dashboard_a_visitantes(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $content = $response->getContent();
        // El enlace Inicio en el menú móvil debe ir a / y no a /dashboard
        $this->assertStringNotContainsString('href="http://localhost/dashboard"', $content);
        $this->assertStringNotContainsString('href="https://localhost/dashboard"', $content);
    }

    #[Test]
    public function el_endpoint_up_esta_deshabilitado_por_defecto(): void
    {
        $response = $this->get('/up');

        // Al estar deshabilitado en bootstrap/app.php, debe responder 404
        $response->assertNotFound();
    }

    #[Test]
    public function el_sitemap_xml_responde_correctamente_y_contiene_rutas_clave(): void
    {
        $categoria = Categoria::factory()->create([
            'nombre' => 'Laptops Gamers',
            'slug' => 'laptops-gamers',
        ]);

        $producto = Producto::factory()->create([
            'nombre' => 'Laptop Gamer RTX 4080',
            'slug' => 'laptop-gamer-rtx-4080',
            'categoria_id' => $categoria->id,
            'activo' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString(url('/'), $content);
        $this->assertStringContainsString(route('cliente.catalogo'), $content);
        $this->assertStringContainsString('laptop-gamer-rtx-4080', $content);
        $this->assertStringContainsString('laptops-gamers', $content);
    }
}

