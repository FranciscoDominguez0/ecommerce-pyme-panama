<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Models\EstadoPedido;
use App\Models\LogAuditoria;
use App\Mail\RecordatorioEntregaMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class RecordarEntregaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recordar-entrega';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un email a los clientes para que confirmen la entrega de su pedido 3 días después de ser enviado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fechaObjetivo = Carbon::now()->subDays(3)->toDateString();

        $pedidos = Pedido::whereHas('ultimoEstado', function ($query) {
            $query->whereIn('estado', ['enviado', 'en_transito']);
        })->get();

        $contador = 0;

        foreach ($pedidos as $pedido) {
            $ultimo = $pedido->ultimoEstado;
            
            // Si el estado se creó hace exactamente 3 días
            if ($ultimo && $ultimo->creado_en->toDateString() === $fechaObjetivo) {
                // Verificar que no se haya enviado ya en el Log de Auditoría
                $yaNotificado = LogAuditoria::where('modulo', 'pedidos')
                    ->where('accion', 'recordatorio_entrega')
                    ->where('valor_nuevo', $pedido->id)
                    ->exists();

                if (!$yaNotificado) {
                    Mail::to($pedido->usuario->email)->send(new RecordatorioEntregaMail($pedido));
                    
                    // Marcar como notificado
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
