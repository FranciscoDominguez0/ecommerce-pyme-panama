<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Renderiza la plantilla base para invitados (autenticación).
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
