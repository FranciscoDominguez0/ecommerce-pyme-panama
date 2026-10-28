<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->index('leido_en', 'idx_pedidos_leido_en');
        });

        Schema::table('devoluciones', function (Blueprint $table) {
            $table->index('estado', 'idx_devoluciones_estado');
            $table->index('pedido_id', 'idx_devoluciones_pedido_id');
        });

        Schema::table('facturas', function (Blueprint $table) {
            $table->index(['estado', 'emitida_en'], 'idx_facturas_estado_emitida');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex('idx_pedidos_leido_en');
        });

        Schema::table('devoluciones', function (Blueprint $table) {
            $table->dropIndex('idx_devoluciones_estado');
            $table->dropIndex('idx_devoluciones_pedido_id');
        });

        Schema::table('facturas', function (Blueprint $table) {
            $table->dropIndex('idx_facturas_estado_emitida');
        });
    }
};
