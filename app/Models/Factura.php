<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'itbms_tasa' => 'decimal:2',
        'itbms_monto' => 'decimal:2',
        'total' => 'decimal:2',
        'emitida_en' => 'datetime',
    ];

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'numero',
        'metodo_pago',
        'referencia_pago_externo',
        'subtotal',
        'descuento',
        'costo_envio',
        'itbms_tasa',
        'itbms_monto',
        'total',
        'estado',
        'pdf_ruta',
        'emitida_en',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function reenvios()
    {
        return $this->hasMany(ReenvioFactura::class, 'factura_id');
    }

    /**
     * Retorna la configuración visual y etiqueta según el estado de la factura.
     */
    public function getConfigEstadoAttribute(): array
    {
        $config = match ($this->estado) {
            'emitida' => [
                'bar'       => 'bg-secondary/80',
                'chip_bg'   => 'bg-secondary/10',
                'chip_text' => 'text-secondary',
                'icon'      => 'check_circle',
            ],
            'anulada' => [
                'bar'       => 'bg-primary',
                'chip_bg'   => 'bg-primary/10',
                'chip_text' => 'text-primary',
                'icon'      => 'cancel',
            ],
            default => [
                'bar'       => 'bg-tertiary-container',
                'chip_bg'   => 'bg-tertiary-container/20',
                'chip_text' => 'text-tertiary',
                'icon'      => 'schedule',
            ],
        };

        $config['label'] = ucfirst(str_replace('_', ' ', $this->estado ?? 'pendiente'));

        return $config;
    }
}
