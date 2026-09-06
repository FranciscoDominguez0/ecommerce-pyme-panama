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
        // 1. Actualizar check constraint en estados_pedido para permitir 'reembolsado'
        DB::statement('ALTER TABLE estados_pedido DROP CONSTRAINT IF EXISTS estados_pedido_estado_check');
        DB::statement("ALTER TABLE estados_pedido ADD CONSTRAINT estados_pedido_estado_check CHECK (estado IN (
            'pendiente', 'pago_confirmado', 'pago_rechazado', 
            'en_preparacion', 'listo_para_envio', 'enviado', 
            'en_transito', 'entregado', 'problema_entrega', 
            'cancelado', 'devolucion_solicitada', 'devolucion_aprobada', 'devolucion_rechazada',
            'reembolsado'
        ))");

        // 2. Agregar columnas de rastreo de Stripe y reembolsos en pedidos
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id', 100)->nullable()->index()->after('metodo_pago');
            }
            if (!Schema::hasColumn('pedidos', 'monto_reembolsado')) {
                $table->decimal('monto_reembolsado', 10, 2)->default(0.00)->after('total');
            }
        });

        // 3. Vincular Pedido #9 con su PaymentIntent real de Stripe
        DB::table('pedidos')
            ->where('id', 9)
            ->update([
                'stripe_payment_intent_id' => 'pi_3UCZ6iHVfZ9fGEEf0gGrJVDJ',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
            if (Schema::hasColumn('pedidos', 'monto_reembolsado')) {
                $table->dropColumn('monto_reembolsado');
            }
        });

        DB::statement('ALTER TABLE estados_pedido DROP CONSTRAINT IF EXISTS estados_pedido_estado_check');
        DB::statement("ALTER TABLE estados_pedido ADD CONSTRAINT estados_pedido_estado_check CHECK (estado IN (
            'pendiente', 'pago_confirmado', 'pago_rechazado', 
            'en_preparacion', 'listo_para_envio', 'enviado', 
            'en_transito', 'entregado', 'problema_entrega', 
            'cancelado', 'devolucion_solicitada', 'devolucion_aprobada', 'devolucion_rechazada'
        ))");
    }
};
