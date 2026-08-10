<?php

namespace App\Livewire;

use App\Helpers\GeolocalizacionPanama;
use App\Models\Direccion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Componente reutilizable de gestión de direcciones de envío.
 *
 * Modos de uso (via prop "compact"):
 *  - compact = false  → /mi-cuenta/direcciones (listar, agregar, editar, eliminar, predeterminada).
 *  - compact = true   → /checkout (seleccionar existente o crear nueva y continuar el checkout).
 *
 * La validación (reglas) y la lógica de guardado (guardar) son la única fuente de verdad
 * para el alta/edición de direcciones en toda la aplicación.
 */
class GestionDirecciones extends Component
{
    public bool $compact = false;

    public bool $mostrarPredeterminada = true;

    public array $provincias = [];

    public array $distritos = [];

    public array $corregimientos = [];

    public $zonasEnvio = [];

    public string $alias = '';

    public string $nombreReceptor = '';

    public string $provincia = '';

    public string $distrito = '';

    public string $corregimiento = '';

    public string $direccionExacta = '';

    public string $referencia = '';

    public bool $esPredeterminada = false;

    public string $seleccion = '';

    public ?int $zonaEnvioId = null;

    public bool $mostrarFormulario = false;

    public ?int $editandoId = null;

    public function mount($compact = false, $mostrarPredeterminada = true, $zonasEnvio = [])
    {
        $this->compact = (bool) $compact;
        $this->mostrarPredeterminada = (bool) $mostrarPredeterminada;

        if ($zonasEnvio instanceof \Illuminate\Support\Collection) {
            $zonasEnvio = $zonasEnvio->toArray();
        }

        $this->zonasEnvio = (array) $zonasEnvio;

        $this->provincias = GeolocalizacionPanama::provincias();

        if ($this->compact) {
            $this->seleccion = $this->direcciones->isEmpty()
                ? 'nueva'
                : (string) $this->direcciones->first()->id;
        }
    }

    #[Computed]
    public function direcciones()
    {
        return Direccion::where('usuario_id', Auth::id())
            ->sinEliminar()
            ->orderByDesc('es_predeterminada')
            ->orderByDesc('creado_en')
            ->get();
    }

    /**
     * Reglas de validación — única fuente de verdad para ambos contextos.
     */
    protected function reglas(): array
    {
        return [
            'alias' => ['required', 'string', 'max:100'],
            'nombreReceptor' => ['required', 'string', 'max:255'],
            'provincia' => ['required', 'string', 'max:100'],
            'distrito' => ['required', 'string', 'max:100'],
            'corregimiento' => ['required', 'string', 'max:100'],
            'direccionExacta' => ['required', 'string'],
            'referencia' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updatedProvincia($value)
    {
        $this->distrito = '';
        $this->corregimiento = '';
        $this->distritos = $value ? GeolocalizacionPanama::distritosPorProvincia($value) : [];
        $this->corregimientos = [];
    }

    public function updatedDistrito($value)
    {
        $this->corregimiento = '';
        $this->corregimientos = $value ? GeolocalizacionPanama::corregimientosPorDistrito($value) : [];
    }

    public function abrirNueva()
    {
        $this->reiniciarFormulario();
        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario()
    {
        $this->mostrarFormulario = false;
        $this->reiniciarFormulario();
    }

    public function iniciarEdicion($id)
    {
        $direccion = Direccion::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->sinEliminar()
            ->firstOrFail();

        $this->alias = $direccion->alias;
        $this->nombreReceptor = $direccion->nombre_receptor;
        $this->provincia = $direccion->provincia;
        $this->distrito = $direccion->distrito;
        $this->corregimiento = $direccion->corregimiento;
        $this->direccionExacta = $direccion->direccion_exacta;
        $this->referencia = $direccion->referencia ?? '';
        $this->esPredeterminada = $direccion->es_predeterminada;
        $this->editandoId = $direccion->id;

        $this->distritos = GeolocalizacionPanama::distritosPorProvincia($direccion->provincia);
        $this->corregimientos = GeolocalizacionPanama::corregimientosPorDistrito($direccion->distrito);

        $this->mostrarFormulario = true;
    }

    protected function reiniciarFormulario()
    {
        $this->reset([
            'alias',
            'nombreReceptor',
            'provincia',
            'distrito',
            'corregimiento',
            'direccionExacta',
            'referencia',
            'esPredeterminada',
            'editandoId',
            'distritos',
            'corregimientos',
        ]);

        $this->resetValidation();
    }

    /**
     * Método único que crea o actualiza una dirección en la base de datos.
     * Emite el evento "addressSaved" para que cada contexto reaccione de forma distinta.
     */
    public function guardar()
    {
        $this->validate($this->reglas());

        $usuarioId = Auth::id();
        $esPredeterminada = $this->mostrarPredeterminada && $this->esPredeterminada;

        $nuevoId = DB::transaction(function () use ($usuarioId, $esPredeterminada) {
            if ($esPredeterminada) {
                Direccion::where('usuario_id', $usuarioId)
                    ->sinEliminar()
                    ->where('id', '!=', $this->editandoId)
                    ->update(['es_predeterminada' => false]);
            }

            $data = [
                'alias' => $this->alias,
                'nombre_receptor' => $this->nombreReceptor,
                'provincia' => $this->provincia,
                'distrito' => $this->distrito,
                'corregimiento' => $this->corregimiento,
                'direccion_exacta' => $this->direccionExacta,
                'referencia' => $this->referencia ?: null,
                'es_predeterminada' => $esPredeterminada,
            ];

            if ($this->editandoId) {
                $direccion = Direccion::where('id', $this->editandoId)
                    ->where('usuario_id', $usuarioId)
                    ->sinEliminar()
                    ->firstOrFail();

                $direccion->fill($data)->save();
            } else {
                $direccion = Direccion::create($data + ['usuario_id' => $usuarioId]);
            }

            return $direccion->id;
        });

        $id = $this->editandoId ?: $nuevoId;

        $this->dispatch('addressSaved', direccionId: $id);
        $this->dispatch(
            'mostrar-toast',
            tipo: 'success',
            mensaje: $this->editandoId ? 'Dirección actualizada correctamente.' : 'Dirección agregada correctamente.'
        );

        if ($this->compact) {
            $this->seleccion = (string) $id;
        } else {
            $this->mostrarFormulario = false;
            $this->reiniciarFormulario();
        }
    }

    #[On('eliminar-direccion')]
    public function eliminar($id)
    {
        $direccion = Direccion::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->sinEliminar()
            ->firstOrFail();

        $direccion->update(['eliminado_en' => now()]);

        $this->dispatch('mostrar-toast', tipo: 'success', mensaje: 'Dirección eliminada correctamente.');
    }

    public function establecerPredeterminada($id)
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

        $this->dispatch('mostrar-toast', tipo: 'success', mensaje: 'Dirección establecida como predeterminada.');
    }

    /**
     * Acción exclusiva del modo checkout (compact = true):
     * guarda la nueva dirección si hace falta y continúa hacia el paso de pago.
     */
    public function continuar()
    {
        if ($this->seleccion === 'nueva') {
            $this->guardar();
        }

        if ($this->seleccion === '' || $this->seleccion === 'nueva') {
            $this->addError('seleccion', 'Selecciona una dirección de envío o ingresa una nueva.');
            return;
        }

        $this->validate(
            ['zonaEnvioId' => ['required', 'exists:zonas_envio,id']],
            [],
            ['zonaEnvioId' => 'zona de envío']
        );

        session([
            'checkout_direccion_id' => (int) $this->seleccion,
            'checkout_zona_envio_id' => (int) $this->zonaEnvioId,
        ]);

        $this->redirect(route('cliente.checkout.pago'));
    }

    public function render()
    {
        return view('livewire.gestion-direcciones');
    }
}
