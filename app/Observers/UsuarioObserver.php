<?php

namespace App\Observers;

use App\Models\Usuario;
use App\Services\AuditoriaService;

class UsuarioObserver
{
    public function created(Usuario $usuario)
    {
        $atributos = AuditoriaService::limpiarCamposSensibles($usuario->getAttributes());
        AuditoriaService::registrar('Usuarios', 'creado', "Usuario {$usuario->email} creado.", null, $atributos);
    }

    public function updated(Usuario $usuario)
    {
        $cambios = $usuario->getChanges();
        $original = array_intersect_key($usuario->getOriginal(), $cambios);
        
        $cambios = AuditoriaService::limpiarCamposSensibles($cambios);
        $original = AuditoriaService::limpiarCamposSensibles($original);

        AuditoriaService::registrar('Usuarios', 'actualizado', "Usuario {$usuario->email} actualizado.", $original, $cambios);
    }

    public function deleted(Usuario $usuario)
    {
        $atributos = AuditoriaService::limpiarCamposSensibles($usuario->getAttributes());
        AuditoriaService::registrar('Usuarios', 'eliminado', "Usuario {$usuario->email} eliminado.", $atributos, null);
    }
}
