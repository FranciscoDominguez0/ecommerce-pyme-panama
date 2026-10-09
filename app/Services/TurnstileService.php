<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    /**
     * Endpoint de verificación de Cloudflare Turnstile.
     */
    protected string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Clave secreta configurada.
     */
    protected ?string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.turnstile.secret');
    }

    /**
     * Valida el token de Cloudflare Turnstile contra la API de Cloudflare.
     *
     * @param string|null $token El token devuelto por el widget (cf-turnstile-response).
     * @param string|null $ip Dirección IP remota del cliente (opcional).
     * @return bool
     */
    public function verificar(?string $token, ?string $ip = null): bool
    {
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        if (empty($this->secretKey)) {
            Log::warning('Cloudflare Turnstile: La clave secreta no está configurada en services.turnstile.secret.');
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post($this->verifyUrl, array_filter([
                    'secret' => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ]));

            if ($response->successful()) {
                $datos = $response->json();
                $esValido = (bool) ($datos['success'] ?? false);

                if (!$esValido) {
                    Log::info('Cloudflare Turnstile: Verificación fallida.', [
                        'error-codes' => $datos['error-codes'] ?? [],
                        'ip' => $ip,
                    ]);
                }

                return $esValido;
            }

            Log::error('Cloudflare Turnstile: Respuesta no exitosa de la API.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Cloudflare Turnstile: Error al conectar con la API: ' . $e->getMessage());
            return false;
        }
    }
}
