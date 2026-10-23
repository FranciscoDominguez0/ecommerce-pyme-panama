<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InicioController extends Controller
{
    // Muestra la página principal de la tienda con productos destacados y marcas
    public function inicio(): View
    {
        $destacados = Producto::with([
            'categoria',
            'imagenes',
            'variantes.opciones.tipo',
            'promocionesProductoDelMes'
        ])
        ->destacados()
        ->take(8)
        ->get();

        $marcasDistribuidores = Brand::verificada()->get();

        return view('welcome', compact('destacados', 'marcasDistribuidores'));
    }

    // Redirige al panel correspondiente según el rol del usuario autenticado
    public function redirigirSegunRol(): RedirectResponse
    {
        $usuario = Auth::user();

        if ($usuario) {
            $esCliente = $usuario->hasRole('cliente') || $usuario->roles->isEmpty();
            if (!$esCliente) {
                return redirect()->route('admin.dashboard');
            }
        }

        return redirect()->route('dashboard');
    }

    // Muestra el panel de inicio del cliente autenticado
    public function dashboard(): View
    {
        $productos = Producto::with([
            'categoria',
            'imagenes',
            'variantes.opciones.tipo'
        ])
        ->sinEliminar()
        ->where('activo', true)
        ->where('es_digital', false)
        ->whereHas('categoria', function ($q) {
            $q->where('exento_envio', false);
        })
        ->inRandomOrder()
        ->take(8)
        ->get();

        $slugsOrdenados = [
            'laptops-y-computadoras',
            'componentes-de-pc',
            'perifericos',
            'monitores',
            'audio',
            'redes-y-conectividad',
            'almacenamiento',
            'accesorios'
        ];

        $categoriasPrincipales = Categoria::whereIn('slug', $slugsOrdenados)
            ->where('activo', true)
            ->get()
            ->sortBy(function ($cat) use ($slugsOrdenados) {
                return array_search($cat->slug, $slugsOrdenados, true);
            });

        $marcas = Brand::verificada()->get();

        return view('dashboard', compact('productos', 'categoriasPrincipales', 'marcas'));
    }
}
