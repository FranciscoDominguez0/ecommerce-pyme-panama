<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCarrito extends Model
{
    use HasFactory;
    protected $table = 'items_carrito';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'carrito_id',
        'producto_id',
        'variante_producto_id',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'carrito_id' => 'integer',
        'producto_id' => 'integer',
        'variante_producto_id' => 'integer',
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Carrito al que pertenece este item.
     */
    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    /**
     * Producto base asociado.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Variante específica del producto (si aplica).
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_producto_id');
    }

    /**
     * Subtotal calculado de la línea (cantidad * precio_unitario).
     */
    public function getSubtotalAttribute(): float
    {
        return round($this->cantidad * (float) $this->precio_unitario, 2);
    }

    /**
     * Stock disponible actual del producto o de su variante.
     */
    public function getStockDisponibleAttribute(): int
    {
        if ($this->variante) {
            return (int) $this->variante->stock;
        }

        return $this->producto ? (int) $this->producto->stock : 0;
    }

    /**
     * Ruta de imagen priorizando la variante y luego la principal del producto.
     */
    public function getImagenRutaAttribute(): ?string
    {
        if ($this->variante && !empty($this->variante->imagen_ruta)) {
            return $this->variante->imagen_ruta;
        }

        if ($this->producto) {
            $primeraImagen = $this->producto->imagenes->first();
            if ($primeraImagen) {
                return $primeraImagen->ruta;
            }
        }

        return null;
    }

    /**
     * Resuelve la URL completa de la imagen del item.
     */
    public function getImagenUrlAttribute(): string
    {
        $ruta = $this->imagen_ruta;
        if (empty($ruta)) {
            return asset('images/placeholder-product.png');
        }
        
        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://') || str_starts_with($ruta, 'data:image')) {
            return $ruta;
        }

        if (str_starts_with($ruta, 'storage/')) {
            return asset($ruta);
        }

        if (str_starts_with($ruta, '/storage/')) {
            return asset(ltrim($ruta, '/'));
        }

        return asset('storage/' . $ruta);
    }

    /**
     * Descripción de la variante en texto para mostrar en la interfaz (ej. "Negro / M").
     */
    public function getVarianteTextoAttribute(): ?string
    {
        if (!$this->variante) {
            return null;
        }

        if ($this->variante->relationLoaded('opciones') || $this->variante->opciones()->exists()) {
            $opciones = $this->variante->opciones->pluck('nombre')->join(', ');
            if (!empty($opciones)) {
                return $opciones;
            }
        }

        if (!empty($this->variante->sku)) {
            return 'SKU: ' . $this->variante->sku;
        }

        return null;
    }
}
