<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    /**
     * Muestra el formulario de configuración general de la empresa.
     */
    public function general()
    {
        $configuraciones = Configuracion::porGrupo('empresa')->pluck('valor', 'clave')->toArray();
        return view('admin.configuracion.general', compact('configuraciones'));
    }

    /**
     * Guarda la configuración general de la empresa.
     */
    public function guardarGeneral(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'ruc' => 'required|string|max:150',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:50',
            'correo_contacto' => 'nullable|email|max:150',
            'logo' => 'nullable|image|max:2048', // max 2MB
        ]);

        Configuracion::guardar('empresa.nombre', $request->nombre, 'empresa', 'Nombre de la empresa');
        Configuracion::guardar('empresa.ruc', $request->ruc, 'empresa', 'RUC o Aviso de Operación');
        Configuracion::guardar('empresa.direccion', $request->direccion, 'empresa', 'Dirección física de la empresa');
        Configuracion::guardar('empresa.telefono', $request->telefono, 'empresa', 'Teléfono de contacto');
        Configuracion::guardar('empresa.correo_contacto', $request->correo_contacto, 'empresa', 'Correo de contacto principal');

        if ($request->hasFile('logo')) {
            $logoAnterior = Configuracion::obtener('empresa.logo_ruta');
            if ($logoAnterior && Storage::disk('public')->exists($logoAnterior)) {
                Storage::disk('public')->delete($logoAnterior);
            }
            
            $rutaLogo = $request->file('logo')->store('empresa', 'public');
            Configuracion::guardar('empresa.logo_ruta', $rutaLogo, 'empresa', 'Ruta del logo de la empresa');
        }

        // TODO: AuditoriaService::registrar(..., modulo: 'Configuración', accion: 'actualizado', ...);

        return redirect()->route('admin.configuracion.general')->with('toast_success', 'Configuración general guardada exitosamente.');
    }

    /**
     * Muestra el formulario de configuración de métodos de pago.
     */
    public function pagos()
    {
        $configuraciones = Configuracion::porGrupo('pagos')->pluck('valor', 'clave')->toArray();
        
        $metodos = [
            'stripe' => ['nombre' => 'Tarjeta de crédito/débito', 'descripcion' => 'Procesado vía pasarela de pago (Stripe)', 'icono' => 'credit_card'],
            'yappy' => ['nombre' => 'Yappy', 'descripcion' => 'Pagos directos vía Yappy', 'icono' => 'qr_code'],
            'transferencia' => ['nombre' => 'Transferencia bancaria', 'descripcion' => 'ACH directo', 'icono' => 'account_balance'],
            'contra_entrega' => ['nombre' => 'Pago contra entrega', 'descripcion' => 'Efectivo al recibir', 'icono' => 'payments'],
        ];

        return view('admin.configuracion.pagos', compact('configuraciones', 'metodos'));
    }

    /**
     * Guarda la configuración de métodos de pago.
     */
    public function guardarPagos(Request $request)
    {
        $metodos = ['stripe', 'yappy', 'transferencia', 'contra_entrega'];
        
        foreach ($metodos as $metodo) {
            $activo = $request->has("metodos.$metodo") ? 'true' : 'false';
            Configuracion::guardar("pagos.$metodo.activo", $activo, 'pagos', "Estado del método de pago $metodo");
        }

        // TODO: AuditoriaService::registrar(..., modulo: 'Configuración', accion: 'actualizado', ...);

        return redirect()->route('admin.configuracion.pagos')->with('toast_success', 'Métodos de pago actualizados exitosamente.');
    }

    /**
     * Muestra el formulario de configuración de impuestos.
     */
    public function impuestos()
    {
        $itbmsTasa = Configuracion::obtenerFloat('impuestos.itbms.tasa', 7.00);
        $itbmsActivo = Configuracion::obtenerBool('impuestos.itbms.activo', true);

        return view('admin.configuracion.impuestos', compact('itbmsTasa', 'itbmsActivo'));
    }

    /**
     * Guarda la configuración de impuestos.
     */
    public function guardarImpuestos(Request $request)
    {
        $request->validate([
            'itbms_tasa' => 'required|numeric|min:0|max:100',
        ]);

        $activo = $request->has('itbms_activo') ? 'true' : 'false';
        
        Configuracion::guardar('impuestos.itbms.activo', $activo, 'impuestos', 'Indica si se aplica ITBMS de forma global');
        Configuracion::guardar('impuestos.itbms.tasa', $request->itbms_tasa, 'impuestos', 'Tasa por defecto del ITBMS');

        // TODO: AuditoriaService::registrar(..., modulo: 'Configuración', accion: 'actualizado', ...);

        return redirect()->route('admin.configuracion.impuestos')->with('toast_success', 'Configuración de impuestos guardada exitosamente.');
    }
}
