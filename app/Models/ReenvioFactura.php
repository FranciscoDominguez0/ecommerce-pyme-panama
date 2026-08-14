<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReenvioFactura extends Model
{
    use HasFactory;

    protected $table = 'reenvios_factura';

    public $timestamps = false; // Solo usa enviado_en, u otros.

    protected $fillable = [
        'factura_id',
        'usuario_id',
        'email_destino',
        'mensaje_personalizado',
        'enviado_en',
    ];

    protected $casts = [
        'enviado_en' => 'datetime',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
