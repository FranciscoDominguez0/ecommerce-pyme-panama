<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoDelMes extends Model
{
    use HasFactory;

    protected $table = 'producto_del_mes';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'producto_id',
        'descripcion_mes',
        'imagen_banner_ruta',
        'descuento_especial',
        'inicio_en',
        'fin_en',
        'activo',
        'creado_en',
    ];

    protected $casts = [
        'descuento_especial' => 'float',
        'activo' => 'boolean',
        'inicio_en' => 'datetime',
        'fin_en' => 'datetime',
        'creado_en' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
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

    public function precioPromocional(): float
    {
        if (!$this->producto) {
            return 0.0;
        }

        $precioOriginal = (float) ($this->producto->precio ?? 0);
        if ($precioOriginal <= 0) {
            return 0.0;
        }

        $descuentoPct = (float) $this->descuento_especial;
        if ($descuentoPct <= 0) {
            return $precioOriginal;
        }

        $descuentoMonto = ($precioOriginal * $descuentoPct) / 100;
        return max(0.0, round($precioOriginal - $descuentoMonto, 2));
    }
}
