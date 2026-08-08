<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromocionEnvioGratis extends Model
{
    use HasFactory;

    protected $table = 'promociones_envio_gratis';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'zona_envio_id',
        'monto_minimo',
        'inicio_en',
        'fin_en',
        'activo',
        'creado_en',
    ];

    protected $casts = [
        'monto_minimo' => 'float',
        'activo' => 'boolean',
        'inicio_en' => 'datetime',
        'fin_en' => 'datetime',
        'creado_en' => 'datetime',
    ];

    public function zonaEnvio(): BelongsTo
    {
        return $this->belongsTo(ZonaEnvio::class, 'zona_envio_id');
    }

    public function esVigente(): bool
    {
        if (!$this->activo) {
            return false;
        }

        $ahora = Carbon::now();

        if ($this->inicio_en && $ahora->lt($this->inicio_en)) {
            return false;
        }

        if ($this->fin_en && $ahora->gt($this->fin_en)) {
            return false;
        }

        return true;
    }

    public function aplicaParaMonto(float $montoSubtotal): bool
    {
        if (!$this->esVigente()) {
            return false;
        }

        if ($this->monto_minimo && $montoSubtotal < $this->monto_minimo) {
            return false;
        }

        return true;
    }
}
