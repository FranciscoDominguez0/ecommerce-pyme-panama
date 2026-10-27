<?php

namespace App\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Servicio centralizado para el despacho de correos y notificaciones en segundo plano.
 * Aplica el patrón Service Layer + Delegator (Proxy asíncrono) para desacoplar el
 * envío de mensajes del ciclo de respuesta HTTP, garantizando que el usuario
 * reciba respuesta inmediata sin bloqueos por latencia de red o SMTP (Gmail).
 */
class CorreoService
{
    /**
     * Envía un correo electrónico en segundo plano (deferred) sin bloquear la petición.
     *
     * @param string|object $destinatario Correo electrónico o modelo con atributo email
     * @param Mailable $mailable Instancia del correo a despachar
     * @param string|null $descripcion Contexto para identificar el envío en caso de logs
     */
    public static function enviarEnSegundoPlano(mixed $destinatario, Mailable $mailable, ?string $descripcion = null): void
    {
        $email = is_object($destinatario) ? ($destinatario->email ?? null) : $destinatario;

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info("CorreoService: Se omitió el envío ({$descripcion}) porque el destinatario no tiene un correo válido.");
            return;
        }

        try {
            // Si la cola está configurada (database/redis) o en pruebas unitarias, se despacha a la cola
            if (config('queue.default') !== 'sync' || app()->runningUnitTests()) {
                Mail::to($email)->queue($mailable);
                self::despertarWorkerSiEsLocal();
                return;
            }

            // Si es sync, usamos defer para ejecutar tras el término de la petición
            defer(function () use ($email, $mailable, $descripcion) {
                try {
                    Mail::to($email)->send($mailable);
                } catch (\Throwable $e) {
                    $contexto = $descripcion ? " [{$descripcion}]" : "";
                    Log::warning("CorreoService: Error al enviar correo{$contexto} a {$email}: " . $e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            $contexto = $descripcion ? " [{$descripcion}]" : "";
            Log::warning("CorreoService: Error al despachar correo{$contexto} a {$email}: " . $e->getMessage());
        }
    }

    /**
     * Envía una notificación a uno o varios usuarios en segundo plano.
     *
     * @param mixed $notificables Colección, array o usuario individual
     * @param Notification $notificacion Instancia de la notificación
     * @param string|null $descripcion Contexto descriptivo para logs
     */
    public static function notificarEnSegundoPlano(mixed $notificables, Notification $notificacion, ?string $descripcion = null): void
    {
        if (empty($notificables)) {
            return;
        }

        try {
            if (config('queue.default') !== 'sync') {
                NotificationFacade::send($notificables, $notificacion);
                self::despertarWorkerSiEsLocal();
                return;
            }

            defer(function () use ($notificables, $notificacion, $descripcion) {
                try {
                    NotificationFacade::send($notificables, $notificacion);
                } catch (\Throwable $e) {
                    $contexto = $descripcion ? " [{$descripcion}]" : "";
                    Log::warning("CorreoService: Error al enviar notificación{$contexto}: " . $e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            $contexto = $descripcion ? " [{$descripcion}]" : "";
            Log::warning("CorreoService: Error al despachar notificación{$contexto}: " . $e->getMessage());
        }
    }

    /**
     * Envía una notificación por correo a una dirección anónima/externa en segundo plano.
     *
     * @param string $correo Correo de destino
     * @param Notification $notificacion Instancia de la notificación
     * @param string|null $descripcion Contexto descriptivo para logs
     */
    public static function notificarRutaMailEnSegundoPlano(string $correo, Notification $notificacion, ?string $descripcion = null): void
    {
        $correo = trim($correo);
        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            if (config('queue.default') !== 'sync') {
                NotificationFacade::route('mail', $correo)->notify($notificacion);
                self::despertarWorkerSiEsLocal();
                return;
            }

            defer(function () use ($correo, $notificacion, $descripcion) {
                try {
                    NotificationFacade::route('mail', $correo)->notify($notificacion);
                } catch (\Throwable $e) {
                    $contexto = $descripcion ? " [{$descripcion}]" : "";
                    Log::warning("CorreoService: Error enviando notificación ruta mail{$contexto} a {$correo}: " . $e->getMessage());
                }
            });
        } catch (\Throwable $e) {
            $contexto = $descripcion ? " [{$descripcion}]" : "";
            Log::warning("CorreoService: Error al despachar notificación ruta mail{$contexto} a {$correo}: " . $e->getMessage());
        }
    }

    /**
     * Ejecuta cualquier tarea o cierre de mensajería/correo en segundo plano (defer)
     * con captura defensiva de errores y registro en logs.
     *
     * @param callable $callback
     * @param string|null $descripcion
     */
    public static function despacharEnSegundoPlano(callable $callback, ?string $descripcion = null): void
    {
        defer(function () use ($callback, $descripcion) {
            try {
                $callback();
            } catch (\Throwable $e) {
                $contexto = $descripcion ? " [{$descripcion}]" : "";
                Log::warning("CorreoService: Error en despacho diferido{$contexto}: " . $e->getMessage());
            }
        });
    }

    /**
     * En entorno local sobre Windows, si la cola es database, levanta un worker
     * silencioso en background para procesar los trabajos pendientes sin bloquear la petición.
     */
    public static function despertarWorkerSiEsLocal(): void
    {
        if (app()->isLocal() && PHP_OS_FAMILY === 'Windows') {
            @pclose(@popen('start /B php artisan queue:work --stop-when-empty > NUL 2>&1', 'r'));
        }
    }
}
