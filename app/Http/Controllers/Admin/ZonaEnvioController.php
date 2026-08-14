<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZonaEnvio;
use Illuminate\Http\Request;

class ZonaEnvioController extends Controller
{
    /**
     * Muestra la lista de zonas de envío.
     */
    public function index()
    {
        $zonas = ZonaEnvio::orderBy('nombre', 'asc')->get();

        return view('admin.configuracion.zonas-envio', compact('zonas'));
    }

    /**
     * Almacena una nueva zona de envío en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'costo' => 'required|numeric|min:0|max:99999999.99',
            'activo' => 'nullable|boolean',
        ]);

        try {
            $validated['activo'] = $request->has('activo') ? (bool)$request->input('activo') : true;

            ZonaEnvio::create($validated);

            return redirect()->route('admin.zonas-envio.index')
                ->with('success', 'Zona de envío creada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()
                ->with('error', 'Error al guardar: El monto ingresado excede la precisión de la base de datos (máx. 99,999,999.99).')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocurrió un error al intentar crear la zona de envío.')
                ->withInput();
        }
    }

    /**
     * Actualiza una zona de envío existente.
     */
    public function update(Request $request, ZonaEnvio $zonaEnvio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'costo' => 'required|numeric|min:0|max:99999999.99',
            'activo' => 'nullable|boolean',
        ]);

        try {
            $validated['activo'] = $request->has('activo') ? (bool)$request->input('activo') : false;

            $zonaEnvio->update($validated);

            return redirect()->route('admin.zonas-envio.index')
                ->with('success', 'Zona de envío actualizada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar: El monto ingresado excede la precisión de la base de datos (máx. 99,999,999.99).')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocurrió un error al intentar actualizar la zona de envío.')
                ->withInput();
        }
    }

    /**
     * Alterna el estado activo / inactivo de una zona de envío.
     */
    public function toggle(ZonaEnvio $zonaEnvio)
    {
        try {
            $zonaEnvio->update([
                'activo' => !$zonaEnvio->activo,
            ]);

            $estadoTexto = $zonaEnvio->activo ? 'activada' : 'desactivada';

            return redirect()->route('admin.zonas-envio.index')
                ->with('success', 'Estado de zona de envío actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocurrió un error al cambiar el estado de la zona de envío.');
        }
    }

    /**
     * Elimina una zona de envío de la base de datos.
     */
    public function destroy(ZonaEnvio $zonaEnvio)
    {
        try {
            $nombre = $zonaEnvio->nombre;
            $zonaEnvio->delete();

            return redirect()->route('admin.zonas-envio.index')
                ->with('success', 'Zona de envío eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'No se pudo eliminar la zona de envío.');
        }
    }
}
