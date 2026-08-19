<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAuditoria extends Model
{
    protected $table = 'logs_auditoria';
    
    // El log es inmutable
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'modulo',
        'accion',
        'descripcion',
        'valor_anterior',
        'valor_nuevo',
        'ip',
        'agente_usuario',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'valor_anterior' => 'json',
        'valor_nuevo' => 'json',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Scopes de filtrado
    public function scopePorUsuario(Builder $query, $usuarioId)
    {
        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }
    }

    public function scopePorModulo(Builder $query, $modulo)
    {
        if ($modulo) {
            $query->where('modulo', $modulo);
        }
    }

    public function scopePorAccion(Builder $query, $accion)
    {
        if ($accion) {
            $query->where('accion', $accion);
        }
    }

    public function scopePorRangoFechas(Builder $query, $fechaInicio, $fechaFin)
    {
        if ($fechaInicio) {
            $query->whereDate('creado_en', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('creado_en', '<=', $fechaFin);
        }
    }
}
