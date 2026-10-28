<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluacionUsabilidad extends Model
{
    use HasFactory;

    protected $table = 'evaluaciones_usabilidad';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'puntuacion_facilidad',
        'puntuacion_general',
        'tiempo_checkout_segundos',
        'comentario',
        'dispositivo',
        'navegador',
    ];

    protected $casts = [
        'puntuacion_facilidad' => 'integer',
        'puntuacion_general' => 'integer',
        'tiempo_checkout_segundos' => 'integer',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Pedido evaluado.
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Cliente que emitió la opinión.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Texto descriptivo según la escala CES (Customer Effort Score).
     */
    public function getEtiquetaFacilidadAttribute(): string
    {
        return match ($this->puntuacion_facilidad) {
            5 => 'Muy fácil',
            4 => 'Fácil',
            3 => 'Aceptable',
            2 => 'Difícil',
            1 => 'Muy difícil',
            default => 'Sin calificar',
        };
    }

    /**
     * Nombre en español del dispositivo utilizado (Móvil o Escritorio).
     */
    public function getNombreDispositivoAttribute(): string
    {
        $dispositivo = strtolower((string) $this->dispositivo);
        if (in_array($dispositivo, ['mobile', 'móvil', 'movil'])) {
            return 'Móvil';
        }
        if (in_array($dispositivo, ['desktop', 'escritorio'])) {
            return 'Escritorio';
        }
        return ucfirst((string) $this->dispositivo) ?: 'No especificado';
    }
}
