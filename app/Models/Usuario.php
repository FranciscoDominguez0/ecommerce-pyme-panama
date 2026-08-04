<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasRoles;

    protected $table = 'usuarios';
    protected $guard_name = 'web';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre', 'apellido', 'email', 'password_hash', 'telefono',
    ];

    protected $hidden = ['password_hash', 'remember_token'];
}