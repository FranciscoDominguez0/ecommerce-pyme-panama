<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'image_mime',
        'image_path',
        'verified',
        'is_suggested',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'is_suggested' => 'boolean',
    ];

    /**
     * Relación con los productos pertenecientes a esta marca.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'brand_id');
    }

    /**
     * Accessor para obtener la imagen como Data URI base64.
     */
    public function getImageBase64Attribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        $data = null;
        if (is_resource($this->image)) {
            @rewind($this->image);
            $data = stream_get_contents($this->image);
        } else {
            $data = $this->image;
        }

        if (empty($data)) {
            return null;
        }

        // Tratamiento para PostgreSQL bytea (hex string o raw binary)
        if (is_string($data) && str_starts_with($data, '\\x')) {
            $hex = substr($data, 2);
            $binary = @hex2bin($hex);
            if ($binary !== false) {
                $data = $binary;
            }
        }

        $mime = $this->image_mime ?: 'image/webp';
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    /**
     * Accessor para obtener la URL final del logo (sea ruta en base de datos o base64).
     */
    public function getLogoUrlAttribute(): ?string
    {
        // 1. Si tiene ruta registrada en base de datos
        if (!empty($this->image_path)) {
            if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
                return $this->image_path;
            }
            return asset($this->image_path);
        }

        // 2. Si tiene imagen en blob (base64)
        $base64 = $this->image_base64;
        if (!empty($base64)) {
            return $base64;
        }

        return null;
    }

    /**
     * Retorna el HTML del logotipo oficial (imagen o badge de texto).
     */
    public function getLogoHtmlAttribute(): string
    {
        $url = $this->logo_url;
        if ($url) {
            return '<img src="' . $url . '" alt="' . htmlspecialchars($this->name) . '" class="h-6 max-h-7 max-w-[85px] object-contain select-none">';
        }

        return '<span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-black tracking-wider uppercase">' . htmlspecialchars($this->name ?: 'Oficial') . '</span>';
    }



    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term))
            return $query;
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ILIKE', "%{$term}%")
                ->orWhere('slug', 'ILIKE', "%{$term}%");
        });
    }
}
