<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE estados_pedido DROP CONSTRAINT estados_pedido_estado_check');
        DB::statement("ALTER TABLE estados_pedido ADD CONSTRAINT estados_pedido_estado_check CHECK (estado IN (
            'pendiente', 'pago_confirmado', 'pago_rechazado', 
            'en_preparacion', 'listo_para_envio', 'enviado', 
            'en_transito', 'entregado', 'problema_entrega', 
            'cancelado', 'devolucion_solicitada', 'devolucion_aprobada', 'devolucion_rechazada'
        ))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE estados_pedido DROP CONSTRAINT estados_pedido_estado_check');
        DB::statement("ALTER TABLE estados_pedido ADD CONSTRAINT estados_pedido_estado_check CHECK (estado IN (
            'pendiente', 'pago_confirmado', 'pago_rechazado', 
            'en_preparacion', 'listo_para_envio', 'enviado', 
            'entregado', 'cancelado', 'devolucion_solicitada', 'devolucion_aprobada', 'devolucion_rechazada'
        ))");
    }
};
