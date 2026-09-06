<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'zona_envio_id',
        'alias',
        'nombre_receptor',
        'provincia',
        'distrito',
        'corregimiento',
        'direccion_exacta',
        'referencia',
        'es_predeterminada',
        'eliminado_en',
    ];

    protected $casts = [
        'zona_envio_id' => 'integer',
        'es_predeterminada' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'eliminado_en' => 'datetime',
    ];

    public function scopeSinEliminar($query)
    {
        return $query->whereNull('eliminado_en');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function zonaEnvio(): BelongsTo
    {
        return $this->belongsTo(ZonaEnvio::class, 'zona_envio_id');
    }

    /**
     * Resuelve la zona de envío vinculada o la deduce automáticamente desde la provincia.
     */
    public function getZonaEnvioCalculadaAttribute(): ?ZonaEnvio
    {
        if ($this->zonaEnvio) {
            return $this->zonaEnvio;
        }

        if (!empty($this->provincia)) {
            return app(\App\Services\EnvioService::class)->obtenerZonaPorProvincia($this->provincia);
        }

        return null;
    }
}
