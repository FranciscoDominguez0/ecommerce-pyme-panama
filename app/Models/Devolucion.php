<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Devolucion extends Model
{
    use HasFactory;

    protected $table = 'devoluciones';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'motivo',
        'descripcion',
        'foto_evidencia_ruta',
        'estado',
        'comentario_admin',
        'aprobado_en',
    ];

    protected $casts = [
        'pedido_id'   => 'integer',
        'usuario_id'  => 'integer',
        'aprobado_en' => 'datetime',
        'creado_en'   => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────────

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function estaAprobada(): bool
    {
        return $this->estado === 'aprobada';
    }
}
