<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null; // Tabla append-only, sin actualizado_en

    protected $fillable = [
        'producto_id',
        'variante_producto_id',
        'usuario_id',
        'pedido_id',
        'tipo',
        'cantidad',
        'stock_antes',
        'stock_despues',
        'motivo',
        'proveedor',
        'factura_proveedor',
        'notas',
    ];

    protected $casts = [
        'producto_id'          => 'integer',
        'variante_producto_id' => 'integer',
        'usuario_id'           => 'integer',
        'pedido_id'            => 'integer',
        'cantidad'             => 'integer',
        'stock_antes'          => 'integer',
        'stock_despues'        => 'integer',
        'creado_en'            => 'datetime',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────────

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeDeEntrada(Builder $query): Builder
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeDeSalida(Builder $query): Builder
    {
        return $query->where('tipo', 'salida');
    }

    public function scopeDeAjuste(Builder $query): Builder
    {
        return $query->where('tipo', 'ajuste');
    }

    public function scopeDeProducto(Builder $query, int $productoId): Builder
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopeEnRango(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        if ($desde) {
            $query->whereDate('creado_en', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('creado_en', '<=', $hasta);
        }
        return $query;
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Diferencia neta entre stock_despues y stock_antes.
     * Positivo = entrada, Negativo = salida, 0 = neutro.
     */
    public function getDiferenciaAttribute(): int
    {
        return $this->stock_despues - $this->stock_antes;
    }

    /**
     * Símbolo de diferencia para presentación (+5, -3, ±0).
     */
    public function getDiferenciaFormateadaAttribute(): string
    {
        $diff = $this->diferencia;
        if ($diff > 0) return '+' . $diff;
        if ($diff < 0) return (string) $diff;
        return '±0';
    }
}
