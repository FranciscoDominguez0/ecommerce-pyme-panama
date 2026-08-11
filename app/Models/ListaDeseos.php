<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListaDeseos extends Model
{
    use HasFactory;
    protected $table = 'lista_deseos';

    public $incrementing = false;
    protected $primaryKey = ['usuario_id', 'producto_id'];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'producto_id',
        'creado_en',
    ];

    protected $casts = [
        'usuario_id' => 'integer',
        'producto_id' => 'integer',
        'creado_en' => 'datetime',
    ];

    /**
     * Usuario que guardó el producto.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Producto guardado en la lista de deseos.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
