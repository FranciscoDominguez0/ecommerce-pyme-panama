<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $table = 'usuarios';
    protected $guard_name = 'web';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password_hash',
        'telefono',
        'foto_perfil_ruta',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Obtiene el nombre del atributo de la contraseña para autenticación.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * Obtiene el hash de la contraseña para autenticación.
     */
    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }

    public function getFotoPerfilUrlAttribute(): ?string
    {
        return $this->foto_perfil_ruta ? asset($this->foto_perfil_ruta) : null;
    }

    public function getInicialesAttribute(): string
    {
        return strtoupper(substr($this->nombre ?: '', 0, 1) . substr($this->apellido ?: '', 0, 1));
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'usuario_id');
    }

    /**
     * Obtiene el nombre completo del usuario.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }
}