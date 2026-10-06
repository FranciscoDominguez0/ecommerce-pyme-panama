<?php

namespace Tests\Unit;

use App\Services\TurnstileService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileServiceTest extends TestCase
{
    protected TurnstileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.turnstile.secret', 'test-secret-key');
        Config::set('services.turnstile.key', 'test-site-key');
        $this->service = new TurnstileService();
    }

    public function test_retorna_falso_si_el_token_esta_vacio(): void
    {
        $this->assertFalse($this->service->verificar(''));
        $this->assertFalse($this->service->verificar(null));
    }

    public function test_retorna_falso_si_la_llave_secreta_no_esta_configurada(): void
    {
        Config::set('services.turnstile.secret', '');
        $serviceSinSecret = new TurnstileService();

        $this->assertFalse($serviceSinSecret->verificar('un-token-cualquiera'));
    }

    public function test_retorna_verdadero_cuando_cloudflare_valida_el_token_con_exito(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'challenge_ts' => now()->toIso8601String(),
                'hostname' => '127.0.0.1',
                'error-codes' => [],
            ], 200),
        ]);

        // Aseguramos que se evalúa la llamada a la API
        $resultado = $this->service->verificar('token-valido', '127.0.0.1');

        $this->assertTrue($resultado);
    }

    public function test_retorna_falso_cuando_cloudflare_rechaza_el_token(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $resultado = $this->service->verificar('token-invalido', '127.0.0.1');

        $this->assertFalse($resultado);
    }

    public function test_retorna_falso_si_ocurre_un_error_en_el_servidor_de_cloudflare(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([], 500),
        ]);

        $resultado = $this->service->verificar('token-de-prueba');

        $this->assertFalse($resultado);
    }
}
