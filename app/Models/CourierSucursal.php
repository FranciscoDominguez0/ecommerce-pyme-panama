<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourierSucursal extends Model
{
    use HasFactory;

    protected $table = 'courier_sucursales';

    protected $fillable = [
        'zona',
        'courier',
        'sucursal',
        'direccion',
        'tarifa_uno_hasta_7lb',
        'verificacion',
        'observaciones',
        'activo',
        'telefono',
        'fuente_url',
        'tipo_punto',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'tarifa_uno_hasta_7lb' => 'decimal:2',
    ];
}
