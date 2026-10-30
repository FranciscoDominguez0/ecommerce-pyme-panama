<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para aplicar cabeceras HTTP de seguridad a todas las respuestas web.
 *
 * Configura:
 *  - Content-Security-Policy (CSP): Mitiga inyecciones XSS y restringe orígenes de scripts, estilos, medios y frames.
 *  - Permissions-Policy: Restringe el acceso a APIs de hardware (cámara, micrófono, geolocalización).
 *  - X-Content-Type-Options: Previene MIME-sniffing.
 *  - X-Frame-Options: Previene ataques de Clickjacking.
 *  - Referrer-Policy: Controla la fuga de información de navegación entre dominios.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Devolver 404 (en vez de 403) para peticiones a archivos sensibles para evitar reconocimiento pasivo
        $uri = $request->getRequestUri();
        if (preg_match('#(^|/)(\.env|\.git|\.svn|.*\.log|composer\.(json|lock)|package\.json|phpunit\.xml)#i', $uri)) {
            abort(404);
        }

        $response = $next($request);

        // Previene la interpretación errónea de tipos MIME (MIME-sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Evita que el sitio sea embebido en iframes externos (Clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Controla la información enviada en la cabecera Referer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HSTS (HTTP Strict Transport Security): Fuerza conexiones HTTPS por 1 año e incluye subdominios
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Elimina posibles cabeceras con el hostname interno o proxy
        $response->headers->remove('X-Served-By');
        $response->headers->remove('x-served-by');
        if (function_exists('header_remove')) {
            @header_remove('X-Served-By');
            @header_remove('x-served-by');
        }

        // Permissions-Policy: Restringe APIs sensibles del navegador
        // Permite la cámara en el mismo origen (usada por el lector de códigos de barra del admin)
        // Bloquea micrófono, geolocalización y usb; autoriza pasarela Stripe para pagos
        $permissions = [
            'camera=(self)',
            'microphone=()',
            'geolocation=()',
            'payment=(self "https://js.stripe.com")',
            'usb=()',
        ];
        $response->headers->set('Permissions-Policy', implode(', ', $permissions));

        // Content-Security-Policy (CSP)
        // Soporta Livewire 4, Alpine.js ('unsafe-inline'), Google Tag Manager,
        // Cloudflare Insights, Stripe, Cloudflare Turnstile, Reverb WebSockets (ws/wss) y Cloudinary
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://challenges.cloudflare.com https://static.cloudflareinsights.com https://js.stripe.com https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "media-src 'self' https://res.cloudinary.com data: blob:",
            "connect-src 'self' https://www.google-analytics.com https://analytics.google.com https://www.googletagmanager.com https://static.cloudflareinsights.com https://api.stripe.com https://challenges.cloudflare.com ws: wss:",
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://challenges.cloudflare.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.stripe.com",
        ];
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
}

