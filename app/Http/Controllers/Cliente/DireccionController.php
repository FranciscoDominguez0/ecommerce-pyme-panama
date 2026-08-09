<?php

namespace App\Http\Controllers\Cliente;

use App\Helpers\GeolocalizacionPanama;
use App\Http\Controllers\Controller;
use App\Models\Direccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DireccionController extends Controller
{
    public function index(): View
    {
        $direcciones = Direccion::where('usuario_id', Auth::id())
            ->sinEliminar()
            ->orderByDesc('es_predeterminada')
            ->orderByDesc('creado_en')
            ->get();

        $provincias = GeolocalizacionPanama::provincias();

        return view('cliente.perfil.direcciones', compact('direcciones', 'provincias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alias' => 'required|string|max:100',
            'nombre_receptor' => 'required|string|max:255',
            'provincia' => 'required|string|max:100',
            'distrito' => 'required|string|max:100',
            'corregimiento' => 'required|string|max:100',
            'direccion_exacta' => 'required|string',
            'referencia' => 'nullable|string|max:255',
            'es_predeterminada' => 'boolean',
        ]);

        $esPredeterminada = $request->boolean('es_predeterminada');

        DB::transaction(function () use ($validated, $esPredeterminada) {
            if ($esPredeterminada) {
                Direccion::where('usuario_id', Auth::id())
                    ->sinEliminar()
                    ->update(['es_predeterminada' => false]);
            }

            Direccion::create([
                'usuario_id' => Auth::id(),
                'alias' => $validated['alias'],
                'nombre_receptor' => $validated['nombre_receptor'],
                'provincia' => $validated['provincia'],
                'distrito' => $validated['distrito'],
                'corregimiento' => $validated['corregimiento'],
                'direccion_exacta' => $validated['direccion_exacta'],
                'referencia' => $validated['referencia'] ?? null,
                'es_predeterminada' => $esPredeterminada,
            ]);
        });

        return redirect()->route('cliente.perfil.direcciones')->with('toast_success', 'Dirección agregada correctamente.');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $direccion = Direccion::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->sinEliminar()
            ->firstOrFail();

        $validated = $request->validate([
            'alias' => 'required|string|max:100',
            'nombre_receptor' => 'required|string|max:255',
            'provincia' => 'required|string|max:100',
            'distrito' => 'required|string|max:100',
            'corregimiento' => 'required|string|max:100',
            'direccion_exacta' => 'required|string',
            'referencia' => 'nullable|string|max:255',
            'es_predeterminada' => 'boolean',
        ]);

        $esPredeterminada = $request->boolean('es_predeterminada');

        DB::transaction(function () use ($direccion, $validated, $esPredeterminada) {
            if ($esPredeterminada) {
                Direccion::where('usuario_id', Auth::id())
                    ->sinEliminar()
                    ->where('id', '!=', $direccion->id)
                    ->update(['es_predeterminada' => false]);
            }

            $direccion->fill([
                'alias' => $validated['alias'],
                'nombre_receptor' => $validated['nombre_receptor'],
                'provincia' => $validated['provincia'],
                'distrito' => $validated['distrito'],
                'corregimiento' => $validated['corregimiento'],
                'direccion_exacta' => $validated['direccion_exacta'],
                'referencia' => $validated['referencia'] ?? null,
                'es_predeterminada' => $esPredeterminada,
            ])->save();
        });

        return redirect()->route('cliente.perfil.direcciones')->with('toast_success', 'Dirección actualizada correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $direccion = Direccion::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->sinEliminar()
            ->firstOrFail();

        $direccion->update(['eliminado_en' => now()]);

        return redirect()->route('cliente.perfil.direcciones')->with('toast_success', 'Dirección eliminada correctamente.');
    }

    public function setDefault($id): RedirectResponse
    {
        $direccion = Direccion::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->sinEliminar()
            ->firstOrFail();

        DB::transaction(function () use ($direccion) {
            Direccion::where('usuario_id', Auth::id())
                ->sinEliminar()
                ->update(['es_predeterminada' => false]);

            $direccion->update(['es_predeterminada' => true]);
        });

        return redirect()->route('cliente.perfil.direcciones')->with('toast_success', 'Dirección establecida como predeterminada.');
    }

    public function apiDistritos(Request $request): JsonResponse
    {
        $provincia = $request->query('provincia', '');
        $distritos = GeolocalizacionPanama::distritosPorProvincia($provincia);

        return response()->json($distritos);
    }

    public function apiCorregimientos(Request $request): JsonResponse
    {
        $distrito = $request->query('distrito', '');
        $corregimientos = GeolocalizacionPanama::corregimientosPorDistrito($distrito);

        return response()->json($corregimientos);
    }
}
