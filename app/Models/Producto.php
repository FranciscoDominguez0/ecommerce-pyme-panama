<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'categoria_id',
        'brand_id',
        'nombre',
        'slug',
        'descripcion',
        'descripcion_corta',
        'sku',
        'marca',
        'marca_logo',
        'modelo',
        'precio',
        'precio_oferta',
        'oferta_activa',
        'oferta_inicio_en',
        'oferta_fin_en',
        'stock',
        'stock_minimo',
        'destacado',
        'activo',
        'aplica_itbms',
        'eliminado_en',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_oferta' => 'decimal:2',
        'oferta_activa' => 'boolean',
        'oferta_inicio_en' => 'datetime',
        'oferta_fin_en' => 'datetime',
        'destacado' => 'boolean',
        'activo' => 'boolean',
        'aplica_itbms' => 'boolean',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'categoria_id' => 'integer',
        'brand_id' => 'integer',
        'eliminado_en' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Categoría a la que pertenece el producto.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Marca oficial del producto.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Galería de imágenes del producto.
     */
    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id')
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Variantes del producto (ej. Color, Talla, Memoria).
     */
    public function variantes(): HasMany
    {
        return $this->hasMany(VarianteProducto::class, 'producto_id');
    }

    /**
     * Items de pedidos asociados a este producto.
     */
    public function itemsPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class, 'producto_id');
    }

    /**
     * Obtiene la imagen principal del producto.
     */
    public function imagenPrincipal()
    {
        return $this->imagenes->firstWhere('es_principal', true) ?? $this->imagenes->first();
    }

    /**
     * URL completa y validada de la imagen principal del producto.
     */
    public function getImagenUrlAttribute(): string
    {
        $img = $this->imagenPrincipal();
        if (!$img || empty($img->ruta)) {
            return asset('images/placeholder-product.png');
        }

        $ruta = $img->ruta;
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
     * Scope para excluir eliminados suaves.
     */
    public function scopeSinEliminar(Builder $query): Builder
    {
        return $query->whereNull('eliminado_en');
    }

    /**
     * Scope para productos activos en tienda.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true)->whereNull('eliminado_en');
    }

    /**
     * Scope para productos destacados en portada.
     */
    public function scopeDestacados(Builder $query): Builder
    {
        return $query->where('destacado', true)->where('activo', true)->whereNull('eliminado_en');
    }

    /**
     * Retorna si el producto cuenta con oferta activa vigente.
     */
    public function tieneOfertaValida(): bool
    {
        if (!$this->oferta_activa || is_null($this->precio_oferta) || $this->precio_oferta <= 0) {
            return false;
        }

        $ahora = now();
        if ($this->oferta_inicio_en && $ahora->lt($this->oferta_inicio_en)) {
            return false;
        }
        if ($this->oferta_fin_en && $ahora->gt($this->oferta_fin_en)) {
            return false;
        }
        return true;
    }

    /**
     * Solicitudes de notificación cuando vuelva a haber stock.
     */
    public function notificacionesStock(): HasMany
    {
        return $this->hasMany(NotificacionStock::class, 'producto_id');
    }

    /**
     * Relación con promociones de Producto del Mes.
     */
    public function promocionesProductoDelMes(): HasMany
    {
        return $this->hasMany(ProductoDelMes::class, 'producto_id');
    }

    /**
     * Retorna la promoción de Producto del Mes activa y vigente si existe.
     */
    public function promocionDelMesActiva(): ?ProductoDelMes
    {
        /** @var ProductoDelMes|null $promocion */
        $promocion = ProductoDelMes::where('producto_id', $this->id)
            ->where('activo', true)
            ->first();

        if ($promocion && $promocion->esVigente()) {
            return $promocion;
        }

        return null;
    }

    /**
     * Comprueba si el producto tiene oferta regular o Promoción del Mes activa.
     */
    public function tienePromocionOPrecioOferta(): bool
    {
        if ($this->promocionDelMesActiva()) {
            return true;
        }

        return $this->tieneOfertaValida();
    }

    /**
     * Retorna el precio promocional final aplicable.
     */
    public function precioFinalPromocional(): float
    {
        $promoMes = $this->promocionDelMesActiva();
        if ($promoMes) {
            return $promoMes->precioPromocional();
        }

        if ($this->tieneOfertaValida()) {
            return (float) $this->precio_oferta;
        }

        return (float) $this->precio;
    }

    /**
     * Retorna el porcentaje de descuento especial (Producto del Mes u Oferta).
     */
    public function porcentajeDescuentoPromocional(): float
    {
        $promoMes = $this->promocionDelMesActiva();
        if ($promoMes) {
            return (float) $promoMes->descuento_especial;
        }

        if ($this->tieneOfertaValida() && $this->precio > 0) {
            $desc = $this->precio - $this->precio_oferta;
            return round(($desc / $this->precio) * 100);
        }

        return 0;
    }

    /**
     * Retorna el HTML del logotipo oficial de la marca.
     */
    public function getMarcaLogoHtmlAttribute(): string
    {
        if ($this->brand) {
            return $this->brand->logo_html;
        }

        return \App\Helpers\BrandHelper::getLogoHtml($this->marca, $this->marca_logo);
    }
}
