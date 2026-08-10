<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Las operaciones de alta/edición/eliminación/predeterminada de direcciones
 * ahora viven en el componente reutilizable App\Livewire\GestionDirecciones.
 * Este controlador únicamente renderiza la vista del módulo mi-cuenta/direcciones.
 */
class DireccionController extends Controller
{
    public function index(): View
    {
        return view('cliente.perfil.direcciones');
    }
}
