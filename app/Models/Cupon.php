<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cupon extends Model
{
    use HasFactory;

    protected $table = 'cupones';

    public $timestamps = false; // Manejado por creado_en y actualizado_en

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'codigo',
        'tipo',
        'valor',
        'monto_minimo',
        'maximo_usos_total',
        'usos_por_cliente',
        'usos_actuales',
        'activo',
        'inicio_en',
        'fin_en',
        'aplica_a',
        'categoria_id',
        'producto_id',
        'creado_en',
        'actualizado_en',
    ];

    protected $casts = [
        'valor' => 'float',
        'monto_minimo' => 'float',
        'maximo_usos_total' => 'integer',
        'usos_por_cliente' => 'integer',
        'usos_actuales' => 'integer',
        'activo' => 'boolean',
        'inicio_en' => 'datetime',
        'fin_en' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Mutador para asegurar que el código siempre esté en mayúsculas y sin espacios.
     */
    public function setCodigoAttribute($value): void
    {
        $this->attributes['codigo'] = strtoupper(trim($value));
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usos(): HasMany
    {
        return $this->hasMany(UsoCupon::class, 'cupon_id');
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

    public function estaVencido(): bool
    {
        if (!$this->fin_en) {
            return false;
        }
        return Carbon::now()->gt($this->fin_en);
    }

    public function alcanzoLimiteTotal(): bool
    {
        if ($this->maximo_usos_total === null || $this->maximo_usos_total <= 0) {
            return false; // Sin límite
        }
        return $this->usos_actuales >= $this->maximo_usos_total;
    }

    public function calcularDescuento(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        if ($this->monto_minimo && $subtotal < $this->monto_minimo) {
            return 0.0;
        }

        if ($this->tipo === 'porcentaje') {
            $descuento = ($subtotal * $this->valor) / 100;
            return min($descuento, $subtotal);
        }

        if ($this->tipo === 'monto_fijo') {
            return min($this->valor, $subtotal);
        }

        if ($this->tipo === 'envio_gratis') {
            return 0.0; // El envío gratis se maneja a nivel de costo de envío
        }

        return 0.0;
    }

    public function obtenerEstadoTexto(): string
    {
        if (!$this->activo) {
            return 'Inactivo';
        }
        if ($this->estaVencido()) {
            return 'Vencido';
        }
        if ($this->alcanzoLimiteTotal()) {
            return 'Agotado';
        }
        return 'Activo';
    }
}
