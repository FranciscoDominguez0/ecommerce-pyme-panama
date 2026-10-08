<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZonaEnvio;
use Illuminate\Http\Request;

class ZonaEnvioController extends Controller
{
    /**
     * Muestra la lista de zonas de envío (Couriers).
     */
    public function index()
    {
        $zonas = \App\Models\CourierSucursal::orderBy('zona', 'asc')->orderBy('courier', 'asc')->paginate(15);

        return view('admin.configuracion.zonas-envio', compact('zonas'));
    }

    /**
     * Almacena una nueva sucursal de courier en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'zona' => 'required|string|max:255',
            'courier' => 'required|string|max:255',
            'sucursal' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'tarifa_uno_hasta_7lb' => 'required|numeric|min:0|max:99999999.99',
            'telefono' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        try {
            $validated['activo'] = $request->has('activo') ? (bool)$request->input('activo') : true;
            $validated['direccion'] = $validated['direccion'] ?? '';

            \App\Models\CourierSucursal::create($validated);

            return redirect()->route('admin.zonas-envio.index')
                ->with('success', 'Sucursal de Courier creada exitosamente.');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            dd($e->getMessage());
            return redirect()->back()
                ->with('error', 'Ocurrió un error al intentar crear la sucursal de Courier.')
                ->withInput();
        }
    }

    /**
     * Actualiza una sucursal de courier existente.
     */
    public function update(Request $request, $id)
    {
        $courierSucursal = \App\Models\CourierSucursal::findOrFail($id);

        $validated = $request->validate([
            'zona' => 'required|string|max:255',
            'courier' => 'required|string|max:255',
            'sucursal' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'tarifa_uno_hasta_7lb' => 'required|numeric|min:0|max:99999999.99',
            'telefono' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        try {
            $validated['activo'] = $request->has('activo') ? (bool)$request->input('activo') : false;
            $validated['direccion'] = $validated['direccion'] ?? '';

            $courierSucursal->update($validated);

            return redirect()->route('admin.zonas-envio.index')
                ->with('success', 'Sucursal de Courier actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocurrió un error al intentar actualizar la sucursal de Courier.')
                ->withInput();
        }
    }

    /**
     * Alterna el estado activo / inactivo de una sucursal.
     */
    public function toggle($id)
    {
        $courierSucursal = \App\Models\CourierSucursal::findOrFail($id);
        try {
            $courierSucursal->update([
                'activo' => !$courierSucursal->activo,
            ]);

            return redirect()->route('admin.zonas-envio.index')
                ->with('success', 'Estado actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocurrió un error al cambiar el estado.');
        }
    }

    /**
     * Elimina una sucursal de courier de la base de datos.
     */
    public function destroy($id)
    {
        $courierSucursal = \App\Models\CourierSucursal::findOrFail($id);
        try {
            $courierSucursal->delete();

            return redirect()->route('admin.zonas-envio.index')
                ->with('success', 'Sucursal eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'No se pudo eliminar la sucursal.');
        }
    }
}
