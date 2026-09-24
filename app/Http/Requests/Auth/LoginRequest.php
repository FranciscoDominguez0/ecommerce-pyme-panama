<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Usuario;

class LoginRequest extends FormRequest
{
    /**
     * Determina si el usuario tiene permiso para ejecutar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para las credenciales de acceso.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Intenta autenticar las credenciales del formulario.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $user = Usuario::where('email', $this->string('email'))->first();

        if ($user && $user->bloqueado) {
            throw ValidationException::withMessages([
                'email' => 'Su cuenta ha sido inhabilitada por seguridad tras múltiples intentos fallidos. Por favor, comuníquese con soporte.',
            ]);
        }

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->incrementFailedAttempts($user);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $this->clearLoginAttempts();
    }

    /**
     * Verifica que no se haya excedido el límite de intentos de inicio de sesión.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $lockKey = $this->throttleKey() . ':lockout';

        if (Cache::has($lockKey)) {
            event(new Lockout($this));

            $expiresAt = Cache::get($lockKey);
            $seconds = max(0, $expiresAt - now()->timestamp);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    protected function incrementFailedAttempts(?Usuario $user): void
    {
        $attemptsKey = $this->throttleKey() . ':fail_count';
        $attempts = (int) Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addHours(24));

        $lockKey = $this->throttleKey() . ':lockout';

        if ($attempts >= 9) {
            if ($user) {
                $user->update([
                    'bloqueado' => true,
                    'motivo_bloqueo' => 'Demasiados intentos fallidos de inicio de sesión (9).',
                    'bloqueado_en' => now()
                ]);
                throw ValidationException::withMessages([
                    'email' => 'Su cuenta ha sido inhabilitada por seguridad tras múltiples intentos fallidos. Por favor, comuníquese con soporte.',
                ]);
            } else {
                Cache::put($lockKey, now()->addYears(1)->timestamp, now()->addYears(1));
            }
        } elseif ($attempts == 6) {
            Cache::put($lockKey, now()->addMinutes(5)->timestamp, now()->addMinutes(5));
        } elseif ($attempts == 3) {
            Cache::put($lockKey, now()->addMinutes(1)->timestamp, now()->addMinutes(1));
        }
    }

    protected function clearLoginAttempts(): void
    {
        Cache::forget($this->throttleKey() . ':fail_count');
        Cache::forget($this->throttleKey() . ':lockout');
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Genera la clave única para el control de tasa de intentos por usuario e IP.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
