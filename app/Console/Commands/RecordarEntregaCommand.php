<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Models\LogAuditoria;
use App\Mail\RecordatorioEntregaMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class RecordarEntregaCommand extends Command
{
    /**
     * Firma del comando en consola.
     *
     * @var string
     */
    protected $signature = 'app:recordar-entrega';

    /**
     * Descripción de la tarea del comando.
     *
     * @var string
     */
    protected $description = 'Envía un email a los clientes para que confirmen la entrega de su pedido 3 días después de ser enviado';

    /**
     * Ejecuta el recordatorio de entrega para pedidos despachados hace 3 días.
     */
    public function handle(): void
    {
        $fechaObjetivo = Carbon::now()->subDays(3)->toDateString();

        $pedidos = Pedido::whereHas('ultimoEstado', function ($query) {
            $query->whereIn('estado', ['enviado', 'en_transito']);
        })->get();

        $contador = 0;

        foreach ($pedidos as $pedido) {
            $ultimo = $pedido->ultimoEstado;
            
            // Evaluamos si el pedido fue marcado como enviado hace 3 días
            if ($ultimo && $ultimo->creado_en->toDateString() === $fechaObjetivo) {
                // Comprobamos en auditoría si ya se despachó el recordatorio para evitar duplicados
                $yaNotificado = LogAuditoria::where('modulo', 'pedidos')
                    ->where('accion', 'recordatorio_entrega')
                    ->where('valor_nuevo', $pedido->id)
                    ->exists();

                if (!$yaNotificado) {
                    Mail::to($pedido->usuario->email)->send(new RecordatorioEntregaMail($pedido));
                    
                    LogAuditoria::create([
                        'modulo' => 'pedidos',
                        'accion' => 'recordatorio_entrega',
                        'descripcion' => 'Correo de recordatorio de entrega enviado al cliente',
                        'valor_nuevo' => $pedido->id,
                        'ip' => '127.0.0.1',
                    ]);
                    
                    $contador++;
                }
            }
        }

        $this->info("Se enviaron {$contador} recordatorios de entrega.");
    }
}
