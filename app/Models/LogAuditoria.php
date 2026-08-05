<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAuditoria extends Model
{
    protected $table = 'logs_auditoria';

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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
