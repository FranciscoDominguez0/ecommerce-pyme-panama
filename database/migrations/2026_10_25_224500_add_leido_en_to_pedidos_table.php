<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->timestamp('leido_en')->nullable()->after('comprobante_pago_ruta');
        });

        // Marcar pedidos que ya fueron procesados o antiguos como leídos
        DB::table('pedidos')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('estados_pedido')
                      ->whereColumn('estados_pedido.pedido_id', 'pedidos.id')
                      ->whereNotIn('estados_pedido.estado', ['pendiente']);
            })
            ->orWhere('creado_en', '<', now()->subDays(1))
            ->update(['leido_en' => DB::raw('creado_en')]);
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('leido_en');
        });
    }
};
