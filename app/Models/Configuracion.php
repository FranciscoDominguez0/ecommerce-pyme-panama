<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    protected $table = 'configuracion';
    public $timestamps = false;

    protected $fillable = [
        'clave',
        'valor',
        'grupo',
        'descripcion',
        'actualizado_en'
    ];

    /**
     * Obtiene el valor de una configuración por su clave.
     * Ejemplo para facturación: Configuracion::obtener('empresa.nombre')
     *
     * @param string $clave
     * @param mixed $valorPorDefecto
     * @return string|null
     */
    public static function obtener(string $clave, $valorPorDefecto = null)
    {
        return Cache::rememberForever('config_' . $clave, function () use ($clave, $valorPorDefecto) {
            $config = self::where('clave', $clave)->first();
            return $config ? $config->valor : $valorPorDefecto;
        });
    }

    /**
     * Obtiene el valor casteado a boolean.
     * Ejemplo para checkout: Configuracion::obtenerBool('pagos.yappy.activo')
     *
     * @param string $clave
     * @param bool $porDefecto
     * @return bool
     */
    public static function obtenerBool(string $clave, bool $porDefecto = false): bool
    {
        $valor = self::obtener($clave);
        if ($valor === null) {
            return $porDefecto;
        }
        return in_array(strtolower($valor), ['1', 'true', 'on', 'yes', 'activo']);
    }

    /**
     * Obtiene el valor casteado a float.
     * Ejemplo para facturación: Configuracion::obtenerFloat('impuestos.itbms.tasa', 7.00)
     *
     * @param string $clave
     * @param float $porDefecto
     * @return float
     */
    public static function obtenerFloat(string $clave, float $porDefecto = 0): float
    {
        $valor = self::obtener($clave);
        return $valor !== null ? (float) $valor : $porDefecto;
    }

    /**
     * Guarda o actualiza una configuración.
     *
     * @param string $clave
     * @param mixed $valor
     * @param string $grupo
     * @param string|null $descripcion
     * @return Configuracion
     */
    public static function guardar(string $clave, $valor, string $grupo, ?string $descripcion = null)
    {
        $config = self::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => (string) $valor,
                'grupo' => $grupo,
                'descripcion' => $descripcion,
                'actualizado_en' => now(),
            ]
        );

        Cache::forget('config_' . $clave);

        return $config;
    }

    /**
     * Scope para filtrar configuraciones por grupo.
     */
    public function scopePorGrupo($query, $grupo)
    {
        return $query->where('grupo', $grupo);
    }
}
